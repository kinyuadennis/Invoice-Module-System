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
        // Blueprint fields
        'current_period_start',
        'current_period_end',
        'gateway_subscription_id',
        // Cashier columns (backward compatibility)
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
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
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
     * Per blueprint: Only succeeded payments are stored here.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Payment attempts for this subscription.
     * Per blueprint: All attempts (including failures) are tracked here.
     */
    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    /**
     * Trial record for this subscription.
     * Per blueprint: Single source of truth for trial status.
     */
    public function trial(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Trial::class);
    }

    /**
     * Billing history for this subscription.
     */
    public function billingHistory(): HasMany
    {
        return $this->hasMany(BillingHistory::class, 'company_subscription_id');
    }

    /**
     * Check if subscription is free.
     */
    public function isFree(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_FREE;
    }

    /**
     * Check if subscription is in active trial.
     * Per blueprint: Must check trials table, not infer from trial_ends_at.
     */
    public function isTrialActive(): bool
    {
        if ($this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_TRIAL_ACTIVE) {
            return false;
        }

        // Per blueprint: Trial status from trials table only
        return $this->trial?->isActive() ?? false;
    }

    /**
     * Check if subscription trial has expired.
     * Per blueprint: Must check trials table.
     */
    public function isTrialExpired(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_TRIAL_EXPIRED
            || ($this->trial?->isExpired() ?? false);
    }

    /**
     * Check if subscription is active.
     * Per blueprint: Must have at least one succeeded payment AND valid period dates.
     */
    public function isActive(): bool
    {
        if ($this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE) {
            return false;
        }

        // Enforce invariant: Must have valid period dates
        if (! $this->current_period_start || ! $this->current_period_end) {
            return false;
        }

        // Enforce invariant: Period must be valid (current_period_end in future)
        if ($this->current_period_end->isPast()) {
            return false;
        }

        // Enforce invariant: Must have at least one succeeded payment
        $hasSuccessfulPayment = $this->payments()
            ->where('status', PaymentConstants::PAYMENT_STATUS_SUCCESS)
            ->exists();

        return $hasSuccessfulPayment;
    }

    /**
     * Check if subscription is past due (grace period).
     */
    public function isPastDue(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_PAST_DUE;
    }

    /**
     * Check if subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELED;
    }

    /**
     * Legacy method: Check if subscription is pending (deprecated).
     *
     * @deprecated Use isFree(), isTrialActive(), or isActive() based on context
     */
    public function isPending(): bool
    {
        return $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_PENDING;
    }

    /**
     * Legacy method: Check if subscription is in grace period (deprecated).
     *
     * @deprecated Use isPastDue()
     */
    public function isInGrace(): bool
    {
        return $this->isPastDue() || $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE;
    }

    /**
     * Legacy method: Check if subscription is expired (deprecated).
     *
     * @deprecated Use isCanceled()
     */
    public function isExpired(): bool
    {
        return $this->isCanceled() || $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_EXPIRED;
    }

    /**
     * Legacy method: Check if subscription is cancelled (deprecated).
     *
     * @deprecated Use isCanceled()
     */
    public function isCancelled(): bool
    {
        return $this->isCanceled() || $this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELLED;
    }

    /**
     * Legacy method: Check if subscription is in trial (deprecated).
     *
     * @deprecated Use isTrialActive() - per blueprint, trial status from trials table only
     */
    public function isTrial(): bool
    {
        return $this->isTrialActive();
    }

    /**
     * State Machine: Transition to FREE status.
     * Per blueprint: Only valid from initial creation. Cannot transition back to FREE once left.
     *
     * @throws \Exception If transition is forbidden
     */
    public function transitionToFree(): void
    {
        // Per blueprint: Any → free is FORBIDDEN (irreversible)
        if ($this->exists && $this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_FREE) {
            throw new \Exception('Cannot transition to FREE status. FREE is only for initial creation.');
        }

        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_FREE,
        ]);
    }

    /**
     * State Machine: Transition to TRIAL_ACTIVE status.
     * Valid transitions: free → trial_active
     *
     * @throws \Exception If transition is forbidden
     */
    public function transitionToTrialActive(): void
    {
        // Valid: free → trial_active
        if ($this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_FREE) {
            throw new \Exception("Cannot transition to trial_active from {$this->status}. Only valid from free.");
        }

        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_TRIAL_ACTIVE,
        ]);
    }

    /**
     * State Machine: Transition to TRIAL_EXPIRED status.
     * Valid transitions: trial_active → trial_expired (cron on ends_at)
     *
     * @throws \Exception If transition is forbidden
     */
    public function transitionToTrialExpired(): void
    {
        // Valid: trial_active → trial_expired
        if ($this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_TRIAL_ACTIVE) {
            throw new \Exception("Cannot transition to trial_expired from {$this->status}. Only valid from trial_active.");
        }

        // Per blueprint: trial_expired → trial_active is FORBIDDEN (no restart)
        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_TRIAL_EXPIRED,
        ]);
    }

    /**
     * State Machine: Transition to ACTIVE status.
     * Valid transitions:
     * - free → active (user pays for paid plan)
     * - trial_active → active (user pays before expiry)
     * - trial_expired → active (user pays after expiry)
     * - past_due → active (successful retry payment)
     *
     * Enforces blueprint invariant: Must have at least one succeeded payment AND valid period dates.
     *
     * @throws \Exception If invariant is violated or transition is forbidden
     */
    public function transitionToActive(): void
    {
        $validFromStates = [
            SubscriptionConstants::SUBSCRIPTION_STATUS_FREE,
            SubscriptionConstants::SUBSCRIPTION_STATUS_TRIAL_ACTIVE,
            SubscriptionConstants::SUBSCRIPTION_STATUS_TRIAL_EXPIRED,
            SubscriptionConstants::SUBSCRIPTION_STATUS_PAST_DUE,
        ];

        // Check if transition is valid
        // Per blueprint: canceled → any active state is FORBIDDEN (not in validFromStates)
        if (! in_array($this->status, $validFromStates)) {
            $message = "Cannot transition to active from {$this->status}. Valid from: free, trial_active, trial_expired, past_due.";
            if ($this->status === SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELED) {
                $message = 'Cannot reactivate canceled subscription. Require new subscription.';
            }
            throw new \Exception($message);
        }

        // Enforce invariant: Must have at least one succeeded payment
        $hasSuccessfulPayment = $this->payments()
            ->where('status', PaymentConstants::PAYMENT_STATUS_SUCCESS)
            ->exists();

        if (! $hasSuccessfulPayment) {
            throw new \Exception('Cannot activate subscription without a successful payment');
        }

        // Set period dates if not set
        $periodStart = $this->current_period_start ?? now();
        $periodEnd = $this->current_period_end ?? now()->addMonth();

        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE,
            'starts_at' => $this->starts_at ?? now(),
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]);
    }

    /**
     * State Machine: Transition to PAST_DUE status.
     * Valid transitions: active → past_due (webhook/cron on failed renewal)
     *
     * @throws \Exception If transition is forbidden
     */
    public function transitionToPastDue(): void
    {
        // Valid: active → past_due
        if ($this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE) {
            throw new \Exception("Cannot transition to past_due from {$this->status}. Only valid from active.");
        }

        // Per blueprint: past_due grants 7-day grace access
        $gracePeriodEnd = now()->addDays(SubscriptionConstants::RENEWAL_GRACE_DAYS);

        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_PAST_DUE,
            'ends_at' => $gracePeriodEnd,
        ]);
    }

    /**
     * State Machine: Transition to CANCELED status.
     * Valid transitions:
     * - active → canceled (user request or non-renew)
     * - past_due → canceled (grace period ends, cron)
     *
     * @throws \Exception If transition is forbidden
     */
    public function transitionToCanceled(): void
    {
        $validFromStates = [
            SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE,
            SubscriptionConstants::SUBSCRIPTION_STATUS_PAST_DUE,
        ];

        // Check if transition is valid
        if (! in_array($this->status, $validFromStates)) {
            throw new \Exception("Cannot transition to canceled from {$this->status}. Valid from: active, past_due.");
        }

        // Per blueprint: canceled → any active state is FORBIDDEN
        $this->update([
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Legacy method: Transition to GRACE status (deprecated).
     *
     * @deprecated Use transitionToPastDue()
     */
    public function transitionToGrace(): void
    {
        $this->transitionToPastDue();
    }

    /**
     * Legacy method: Transition to EXPIRED status (deprecated).
     *
     * @deprecated Use transitionToCanceled()
     */
    public function transitionToExpired(): void
    {
        // Can only transition from past_due (grace) to canceled
        if ($this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_PAST_DUE
            && $this->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE) {
            throw new \Exception("Cannot transition to expired from {$this->status}. Only valid from past_due/grace.");
        }

        $this->transitionToCanceled();
    }

    /**
     * Legacy method: Transition to CANCELLED status (deprecated).
     *
     * @deprecated Use transitionToCanceled()
     */
    public function transitionToCancelled(): void
    {
        $this->transitionToCanceled();
    }
}
