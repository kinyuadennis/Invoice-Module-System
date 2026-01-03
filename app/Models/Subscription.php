<?php

namespace App\Models;

use App\Config\PaymentConstants;
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
    protected $table = 'subscriptions';

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
        // Cashier columns
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'type',
        'quantity',
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

    /**
     * Transition subscription to ACTIVE status.
     *
     * Enforces blueprint invariant: A subscription cannot be ACTIVE without at least one successful Payment.
     *
     * @throws \Exception If invariant is violated
     */
    public function transitionToActive(): void
    {
        // Enforce invariant: Must have at least one successful payment
        $hasSuccessfulPayment = $this->payments()
            ->where('status', PaymentConstants::PAYMENT_STATUS_SUCCESS)
            ->exists();

        if (! $hasSuccessfulPayment) {
            throw new \Exception('Cannot activate subscription without a successful payment');
        }

        // Prevent backward transitions from EXPIRED
        if ($this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_EXPIRED) {
            throw new \Exception('Cannot reactivate expired subscription without a new payment');
        }

        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE,
            'starts_at' => $this->starts_at ?? now(),
        ]);
    }

    /**
     * Transition subscription to GRACE status.
     *
     * Sets grace period end date based on SubscriptionConstants::RENEWAL_GRACE_DAYS.
     */
    public function transitionToGrace(): void
    {
        $gracePeriodEnd = now()->addDays(SubscriptionConstants::RENEWAL_GRACE_DAYS);

        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE,
            'ends_at' => $gracePeriodEnd,
        ]);
    }

    /**
     * Transition subscription to EXPIRED status.
     *
     * Enforces blueprint invariant: No backward transitions allowed.
     */
    public function transitionToExpired(): void
    {
        // Can only transition from GRACE to EXPIRED
        if ($this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE) {
            throw new \Exception('Can only expire subscription from GRACE status');
        }

        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_EXPIRED,
        ]);
    }

    /**
     * Transition subscription to CANCELLED status.
     */
    public function transitionToCancelled(): void
    {
        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
