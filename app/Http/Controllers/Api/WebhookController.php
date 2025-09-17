<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebhookPaymentRequest;
use App\Jobs\ProcessPaymentWebhookJob;
use App\Models\WebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming payment webhook.
     * OPTIMIZED for high-pressure scenarios with 20M+ records
     */
    public function store(WebhookPaymentRequest $request): JsonResponse
    {
        $startTime = microtime(true);
        
        // CRITICAL: Duplicate detection using cache for high performance
        $validatedData = $request->validated();
        $txnId = $validatedData['txn_id'];
        $cacheKey = "webhook_txn:{$txnId}";
        
        if (Cache::has($cacheKey)) {
            Log::warning("Duplicate webhook detected: {$txnId}", [
                'txn_id' => $txnId,
                'order_uuid' => $validatedData['order_uuid'],
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Duplicate webhook detected',
                'status' => 'ignored',
                'txn_id' => $txnId,
            ], 409);
        }

        try {
            DB::beginTransaction();

            // CRITICAL: Create webhook log 
            $webhookLog = WebhookLog::create([
                'order_uuid' => $validatedData['order_uuid'],
                'txn_id' => $txnId,
                'raw_payload' => $validatedData,
                'status' => WebhookLog::STATUS_PENDING,
                'attempts' => 1,
                'webhook_source' => $validatedData['webhook_source'] ?? WebhookLog::SOURCE_UNKNOWN,
                'correlation_id' => $validatedData['correlation_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // CRITICAL: Cache the transaction ID to prevent duplicates
            Cache::put($cacheKey, true, 300); // 5 minutes cache

            DB::commit();

            // CRITICAL: Dispatch job with high priority for webhook processing
            ProcessPaymentWebhookJob::dispatch($webhookLog->id)
                ->onQueue('webhooks-high-priority');

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info("Webhook received and queued", [
                'webhook_log_id' => $webhookLog->id,
                'txn_id' => $txnId,
                'order_uuid' => $validatedData['order_uuid'],
                'webhook_source' => $webhookLog->webhook_source,
                'correlation_id' => $webhookLog->correlation_id,
                'processing_time_ms' => $processingTime,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Webhook received and queued for processing',
                'webhook_log_id' => $webhookLog->id,
                'txn_id' => $txnId,
                'status' => 'queued',
                'processing_time_ms' => $processingTime,
            ], 202);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Webhook processing failed", [
                'error' => $e->getMessage(),
                'txn_id' => $txnId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Webhook processing failed',
                'error' => 'Internal server error',
            ], 500);
        }
    }


}
