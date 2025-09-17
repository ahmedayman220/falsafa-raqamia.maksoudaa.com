<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class WebhookLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_uuid',
        'txn_id',
        'raw_payload',
        'status',
        'attempts',
        'last_error',
        'processed_at',
        'processing_time_ms',
        'retry_count',
        'webhook_source',
        'correlation_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'raw_payload' => 'array',
        'status' => 'string',
        'attempts' => 'integer',
        'retry_count' => 'integer',
        'processing_time_ms' => 'integer',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Webhook status constants for type safety
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IGNORED = 'ignored';

    /**
     * Webhook source constants
     */
    public const SOURCE_STRIPE = 'stripe';
    public const SOURCE_PAYPAL = 'paypal';
    public const SOURCE_SQUARE = 'square';
    public const SOURCE_UNKNOWN = 'unknown';

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_uuid', 'id');
    }

    /**
     * Scope queries for performance optimization
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByTxnId(Builder $query, string $txnId): Builder
    {
        return $query->where('txn_id', $txnId);
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('webhook_source', $source);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeIgnored(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IGNORED);
    }

    public function scopeWithRetries(Builder $query, int $minRetries = 1): Builder
    {
        return $query->where('retry_count', '>=', $minRetries);
    }

    public function scopeSlowProcessing(Builder $query, int $thresholdMs = 1000): Builder
    {
        return $query->where('processing_time_ms', '>', $thresholdMs);
    }

    /**
     * Business logic methods
     */
    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isIgnored(): bool
    {
        return $this->status === self::STATUS_IGNORED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED && $this->attempts < 5;
    }

    public function markAsProcessed(int $processingTimeMs = null): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
            'processing_time_ms' => $processingTimeMs,
        ]);
    }

    public function markAsFailed(string $error, int $processingTimeMs = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_error' => $error,
            'processing_time_ms' => $processingTimeMs,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function markAsIgnored(string $reason, int $processingTimeMs = null): void
    {
        $this->update([
            'status' => self::STATUS_IGNORED,
            'last_error' => $reason,
            'processing_time_ms' => $processingTimeMs,
        ]);
    }

    /**
     * Performance optimization: Select only necessary columns
     */
    public function scopeSelectForProcessing(Builder $query): Builder
    {
        return $query->select([
            'id', 'order_uuid', 'txn_id', 'status', 'raw_payload', 
            'last_error', 'attempts', 'retry_count', 'webhook_source'
        ]);
    }
}
