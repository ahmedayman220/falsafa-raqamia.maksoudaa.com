<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'external_txn_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The data type of the primary key ID.
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Order status constants for type safety
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Valid status transitions for business logic validation
     */
    public const VALID_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PAID, self::STATUS_FAILED],
        self::STATUS_PAID => [self::STATUS_REFUNDED],
        self::STATUS_FAILED => [self::STATUS_PENDING], // Allow retry
        self::STATUS_REFUNDED => [], // Terminal state
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class, 'order_uuid', 'id');
    }

    /**
     * Scope queries for performance optimization
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAmountRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('amount', [$min, $max]);
    }

    public function scopeWithExternalTxnId(Builder $query, string $txnId): Builder
    {
        return $query->where('external_txn_id', $txnId);
    }

    /**
     * Business logic methods
     */
    public function isValidStatusTransition(string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$this->status] ?? []);
    }

    public function canBeProcessed(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Performance optimization: Select only necessary columns
     */
    public function scopeSelectForWebhook(Builder $query): Builder
    {
        return $query->select(['id', 'user_id', 'amount', 'status', 'external_txn_id', 'metadata', 'updated_at']);
    }
}
