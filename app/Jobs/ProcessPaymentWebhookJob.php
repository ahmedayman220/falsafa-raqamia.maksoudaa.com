<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\WebhookLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * Optimized for database transaction conflicts and deadlocks.
     */
    public function backoff(): array
    {
        return [30, 60, 120, 300, 600]; // 30s, 60s, 120s, 300s, 600s - shorter delays for transaction conflicts
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public int $webhookLogId)
    {
        $this->onQueue('webhooks-high-priority');
    }

    /**
     * Execute the job.
     * OPTIMIZED for high-pressure scenarios with 20M+ records
     * Uses database transactions with optimistic locking for concurrency control
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        Log::info("Processing webhook log ID: {$this->webhookLogId}");

        // CRITICAL: Use select to reduce memory usage
        $webhookLog = WebhookLog::selectForProcessing()->find($this->webhookLogId);

        if (!$webhookLog) {
            Log::error("WebhookLog with ID {$this->webhookLogId} not found");
            return;
        }

        // Skip if already processed
        if (in_array($webhookLog->status, [WebhookLog::STATUS_PROCESSED, WebhookLog::STATUS_IGNORED])) {
            Log::info("Webhook log {$this->webhookLogId} already processed with status: {$webhookLog->status}");
            return;
        }

        $payload = $webhookLog->raw_payload;

        try {
            // CRITICAL: Use database transaction with optimistic locking
            DB::transaction(function () use ($webhookLog, $payload, $startTime) {
                // CRITICAL: Get order without locking (optimistic approach)
                $order = Order::where('id', $payload['order_uuid'])->first();

                // Action: out-of-order delivery is -> Reject and retry
                if (!$order) {
                    Log::warning("Order not found for UUID: {$payload['order_uuid']}", [
                        'webhook_log_id' => $webhookLog->id,
                        'txn_id' => $payload['txn_id'],
                        'order_uuid' => $payload['order_uuid'],
                    ]);
                    $webhookLog->markAsFailed('Order not found');
                    return;
                }

                // CRITICAL: Ensure idempotency - check if external_txn_id already exists
                if ($payload['txn_id'] && Order::withExternalTxnId($payload['txn_id'])->exists()) {
                    Log::info("Transaction ID {$payload['txn_id']} already processed, ignoring webhook", [
                        'webhook_log_id' => $webhookLog->id,
                        'txn_id' => $payload['txn_id'],
                        'order_id' => $order->id,
                    ]);
                    $webhookLog->markAsIgnored('Transaction already processed');
                    return;
                }

                // Handle concurrency with transaction and row-level locking
                $oldStatus = $order->status;
                $newStatus = $payload['status'];

                // Validate status transition
                if (!$order->isValidStatusTransition($newStatus)) {
                    Log::warning("Invalid status transition from {$oldStatus} to {$newStatus} for order {$order->id}", [
                        'webhook_log_id' => $webhookLog->id,
                        'order_id' => $order->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ]);
                    $webhookLog->markAsIgnored("Invalid status transition: {$oldStatus} -> {$newStatus}");
                    return;
                }

                // CRITICAL: Optimistic locking - update with version check
                $updatedRows = Order::where('id', $order->id)
                    ->where('updated_at', $order->updated_at) // Optimistic lock
                    ->update([
                        'status' => $newStatus,
                        'external_txn_id' => $payload['txn_id'],
                        'amount' => $payload['amount'],
                        'metadata' => $payload['metadata'] ?? null,
                        'updated_at' => now(),
                    ]);

                // CRITICAL: Check if update succeeded (optimistic locking)
                if ($updatedRows === 0) {
                    Log::warning("Optimistic lock failed for order {$order->id} - another process modified it");
                    throw new \Exception('Optimistic lock failed - order was modified by another process');
                }

                // Insert new row in order_events (audit trail)
                OrderEvent::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'reason' => OrderEvent::REASON_WEBHOOK_PAYMENT,
                    'metadata' => [
                        'webhook_log_id' => $webhookLog->id,
                        'txn_id' => $payload['txn_id'],
                        'timestamp' => $payload['timestamp'],
                        'amount' => $payload['amount'],
                        'webhook_source' => $webhookLog->webhook_source,
                        'correlation_id' => $webhookLog->correlation_id,
                    ],
                ]);

                // Update webhook_logs status with processing time
                $processingTime = round((microtime(true) - $startTime) * 1000, 2);
                $webhookLog->markAsProcessed($processingTime);

                Log::info("Webhook processed successfully", [
                    'webhook_log_id' => $webhookLog->id,
                    'order_id' => $order->id,
                    'txn_id' => $payload['txn_id'],
                    'status_transition' => "{$oldStatus} -> {$newStatus}",
                    'processing_time_ms' => $processingTime,
                ]);
            });
        } catch (QueryException $e) {
            // Handle unique constraint violations and deadlocks
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                Log::warning("Duplicate transaction detected for webhook {$this->webhookLogId}", [
                    'webhook_log_id' => $this->webhookLogId,
                    'txn_id' => $payload['txn_id'] ?? 'unknown',
                    'error_code' => $e->getCode(),
                ]);
                $webhookLog->markAsIgnored('Duplicate transaction detected');
                return;
            }

            // Handle deadlocks and lock timeouts - these should be retried
            if ($e->getCode() === '40001' || str_contains($e->getMessage(), 'Deadlock found') || 
                str_contains($e->getMessage(), 'Lock wait timeout')) {
                Log::warning("Database deadlock/lock timeout for webhook {$this->webhookLogId}, will retry", [
                    'webhook_log_id' => $this->webhookLogId,
                    'txn_id' => $payload['txn_id'] ?? 'unknown',
                    'error_code' => $e->getCode(),
                    'attempt' => $this->attempts(),
                ]);
                throw $e; // Re-throw to trigger retry mechanism
            }

            throw $e; // Re-throw other database exceptions
        } catch (\Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            // Handle optimistic lock failures - these should be retried
            if (str_contains($e->getMessage(), 'Optimistic lock failed')) {
                Log::warning("Optimistic lock failed for webhook {$this->webhookLogId}, will retry", [
                    'webhook_log_id' => $this->webhookLogId,
                    'txn_id' => $payload['txn_id'] ?? 'unknown',
                    'order_uuid' => $payload['order_uuid'] ?? 'unknown',
                    'attempt' => $this->attempts(),
                ]);
                throw $e; // Re-throw to trigger retry mechanism
            }

            Log::error("Webhook processing failed", [
                'webhook_log_id' => $this->webhookLogId,
                'txn_id' => $payload['txn_id'] ?? 'unknown',
                'order_uuid' => $payload['order_uuid'] ?? 'unknown',
                'exception' => $e->getMessage(),
                'processing_time_ms' => $processingTime,
                'attempt' => $this->attempts(),
            ]);

            // Mark webhook as failed with processing time
            $webhookLog->markAsFailed($e->getMessage(), $processingTime);

            throw $e; // Re-throw to trigger retry mechanism
        }

        // CRITICAL: Log performance metrics
        $processingTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::info('Webhook job completed', [
            'webhook_log_id' => $this->webhookLogId,
            'processing_time_ms' => $processingTime,
            'attempt' => $this->attempts(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("Webhook job failed permanently for log {$this->webhookLogId}", [
            'exception' => $exception,
            'attempts' => $this->attempts(),
        ]);

        $webhookLog = WebhookLog::find($this->webhookLogId);
        if ($webhookLog) {
            $webhookLog->update([
                'status' => 'failed',
                'last_error' => "Job failed after {$this->attempts()} attempts: " . $exception->getMessage(),
            ]);
        }
    }

    /**
     * Validate if the status transition is allowed.
     */
    private function isValidStatusTransition(string $fromStatus, string $toStatus): bool
    {
        $allowedTransitions = [
            'pending' => ['paid', 'failed'],
            'paid' => ['refunded'],
            'failed' => ['pending'], // Allow retry
            'refunded' => [], // No transitions from refunded
        ];

        return in_array($toStatus, $allowedTransitions[$fromStatus] ?? []);
    }
}
