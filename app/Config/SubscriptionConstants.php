<?php

namespace App\Config;

/**
 * Subscription-related constants.
 *
 * All values are explicitly defined per "No Silent Defaults" policy.
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class SubscriptionConstants
{
    /**
     * Subscription status: FREE
     *
     * Free plan subscription (no payment required).
     * Per blueprint: Cannot transition back to FREE once left.
     */
    public const SUBSCRIPTION_STATUS_FREE = 'free';

    /**
     * Subscription status: TRIAL_ACTIVE
     *
     * Subscription is in active trial period.
     * Per blueprint: Must have corresponding trial record in trials table.
     */
    public const SUBSCRIPTION_STATUS_TRIAL_ACTIVE = 'trial_active';

    /**
     * Subscription status: TRIAL_EXPIRED
     *
     * Trial period has expired.
     * Per blueprint: Cannot restart trial (trial_expired → trial_active forbidden).
     */
    public const SUBSCRIPTION_STATUS_TRIAL_EXPIRED = 'trial_expired';

    /**
     * Subscription status: ACTIVE
     *
     * Subscription is active with confirmed payment.
     * Per blueprint: Must have at least one succeeded payment and valid period dates.
     */
    public const SUBSCRIPTION_STATUS_ACTIVE = 'active';

    /**
     * Subscription status: PAST_DUE
     *
     * Subscription renewal payment failed, in grace period.
     * Per blueprint: 7-day grace period before transition to canceled.
     */
    public const SUBSCRIPTION_STATUS_PAST_DUE = 'past_due';

    /**
     * Subscription status: CANCELED
     *
     * Subscription was cancelled by user or grace period expired.
     * Per blueprint: Cannot transition back to active states (require new subscription).
     */
    public const SUBSCRIPTION_STATUS_CANCELED = 'canceled';

    /**
     * Legacy status: PENDING (deprecated, use appropriate status)
     *
     * @deprecated Use FREE, TRIAL_ACTIVE, or ACTIVE based on context
     */
    public const SUBSCRIPTION_STATUS_PENDING = 'PENDING';

    /**
     * Legacy status: GRACE (deprecated, use PAST_DUE)
     *
     * @deprecated Use PAST_DUE
     */
    public const SUBSCRIPTION_STATUS_GRACE = 'GRACE';

    /**
     * Legacy status: EXPIRED (deprecated, use CANCELED)
     *
     * @deprecated Use CANCELED
     */
    public const SUBSCRIPTION_STATUS_EXPIRED = 'EXPIRED';

    /**
     * Legacy status: CANCELLED (deprecated, use CANCELED)
     *
     * @deprecated Use CANCELED
     */
    public const SUBSCRIPTION_STATUS_CANCELLED = 'CANCELLED';

    /**
     * Renewal grace period in days.
     *
     * Confirmed: 7 days per blueprint section 2/5.
     * Applied when renewal payment fails before subscription expires.
     */
    public const RENEWAL_GRACE_DAYS = 7;

    /**
     * Renewal notification lead time in days.
     *
     * Confirmed: 3 days before due per blueprint section 5.
     * Users will be notified this many days before renewal is due.
     */
    public const RENEWAL_NOTIFICATION_LEAD_DAYS = 3;

    /**
     * Gateway identifier: M-Pesa
     */
    public const GATEWAY_MPESA = 'mpesa';

    /**
     * Gateway identifier: Stripe
     */
    public const GATEWAY_STRIPE = 'stripe';
}
