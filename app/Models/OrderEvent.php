<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class OrderEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'reason',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Event reason constants for type safety
     */
    public const REASON_WEBHOOK_PAYMENT = 'webhook_payment';
    public const REASON_WEBHOOK_REFUND = 'webhook_refund';
    public const REASON_MANUAL_UPDATE = 'manual_update';
    public const REASON_SYSTEM_RETRY = 'system_retry';
    public const REASON_ADMIN_ACTION = 'admin_action';

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope queries for performance optimization
     */
    public function scopeByOrder(Builder $query, string $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByStatusTransition(Builder $query, string $fromStatus, string $toStatus): Builder
    {
        return $query->where('from_status', $fromStatus)->where('to_status', $toStatus);
    }

    public function scopeByReason(Builder $query, string $reason): Builder
    {
        return $query->where('reason', $reason);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Business logic methods
     */
    public function isWebhookEvent(): bool
    {
        return in_array($this->reason, [
            self::REASON_WEBHOOK_PAYMENT,
            self::REASON_WEBHOOK_REFUND
        ]);
    }

    public function isManualEvent(): bool
    {
        return $this->reason === self::REASON_MANUAL_UPDATE;
    }

    public function isSystemEvent(): bool
    {
        return $this->reason === self::REASON_SYSTEM_RETRY;
    }

    public function getTransitionDescription(): string
    {
        return "{$this->from_status} → {$this->to_status}";
    }

    /**
     * Performance optimization: Select only necessary columns
     */
    public function scopeSelectForAudit(Builder $query): Builder
    {
        return $query->select([
            'id', 'order_id', 'from_status', 'to_status', 
            'reason', 'metadata', 'created_at'
        ]);
    }
}
