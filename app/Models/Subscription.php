<?php

namespace App\Models;

use App\Config\SubscriptionConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Subscription Model
 *
 * Represents a subscription for invoice management services.
 * Refactored from CompanySubscription to align with blueprint requirements.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.1
 *
 * Invariants:
 * - A subscription cannot be ACTIVE without at least one successful Payment
 * - A subscription cannot revert to ACTIVE after EXPIRED without a new Payment
 * - Gateway field is immutable once subscription is created
 */
class Subscription extends Model
{
    protected $table = 'company_subscriptions';

    protected $fillable = [
        'user_id',
        'company_id',
        'subscription_plan_id',
        'plan_code',
        'status',
        'gateway',
        'starts_at',
        'ends_at',
        'next_billing_at',
        'trial_ends_at',
        'cancelled_at',
        'payment_method',
        'payment_reference',
        'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Enforce gateway immutability (per blueprint invariant)
        static::updating(function ($subscription) {
            if ($subscription->exists && $subscription->isDirty('gateway')) {
                $subscription->gateway = $subscription->getOriginal('gateway');
            }
        });
    }

    /**
     * The user this subscription belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The company this subscription belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The subscription plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Payments for this subscription (polymorphic relationship).
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Billing history for this subscription.
     */
    public function billingHistory(): HasMany
    {
        return $this->hasMany(BillingHistory::class, 'company_subscription_id');
    }

    /**
     * Check if subscription is pending.
     */
    public function isPending(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_PENDING;
    }

    /**
     * Check if subscription is active.
     *
     * Note: Per blueprint invariant, must have at least one successful payment.
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Check if subscription is in grace period.
     */
    public function isInGrace(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE;
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_EXPIRED;
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELLED;
    }

    /**
     * Check if subscription is in trial.
     */
    public function isTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
