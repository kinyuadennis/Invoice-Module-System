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
     * Subscription status: PENDING
     *
     * Subscription created but payment not yet confirmed.
     */
    public const SUBSCRIPTION_STATUS_PENDING = 'PENDING';

    /**
     * Subscription status: ACTIVE
     *
     * Subscription is active with confirmed payment.
     */
    public const SUBSCRIPTION_STATUS_ACTIVE = 'ACTIVE';

    /**
     * Subscription status: GRACE
     *
     * Subscription renewal failed, in grace period.
     */
    public const SUBSCRIPTION_STATUS_GRACE = 'GRACE';

    /**
     * Subscription status: EXPIRED
     *
     * Subscription grace period expired.
     */
    public const SUBSCRIPTION_STATUS_EXPIRED = 'EXPIRED';

    /**
     * Subscription status: CANCELLED
     *
     * Subscription was cancelled by user.
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
