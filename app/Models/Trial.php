<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trial Model
 *
 * Single source of truth for trial status (separate to avoid polluting subscriptions).
 *
 * Per blueprint: Trial status must be read from trials table only, never inferred.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.1
 */
class Trial extends Model
{
    protected $fillable = [
        'subscription_id',
        'starts_at',
        'ends_at',
        'extended',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'extended' => 'boolean',
        ];
    }

    /**
     * The subscription this trial belongs to.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if trial is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ends_at->isFuture();
    }

    /**
     * Check if trial is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || $this->ends_at->isPast();
    }

    /**
     * Mark trial as expired.
     */
    public function markAsExpired(): void
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return; // Already expired
        }

        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    // Status constants
    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';
}
