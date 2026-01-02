<?php

namespace App\Config;

/**
 * Payment-related constants.
 *
 * All values are explicitly defined per "No Silent Defaults" policy.
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class PaymentConstants
{
    /**
     * Payment timeout in seconds.
     *
     * Confirmed: 5 minutes (300 seconds) per blueprint section 4.
     * Payments in INITIATED state will transition to TIMEOUT after this duration.
     */
    public const PAYMENT_TIMEOUT_SECONDS = 300;

    /**
     * Payment status: INITIATED
     *
     * Payment has been initiated but not yet confirmed by gateway.
     */
    public const PAYMENT_STATUS_INITIATED = 'INITIATED';

    /**
     * Payment status: SUCCESS
     *
     * Payment has been successfully confirmed by gateway.
     */
    public const PAYMENT_STATUS_SUCCESS = 'SUCCESS';

    /**
     * Payment status: FAILED
     *
     * Payment was explicitly failed by gateway.
     */
    public const PAYMENT_STATUS_FAILED = 'FAILED';

    /**
     * Payment status: TIMEOUT
     *
     * Payment confirmation not received within timeout period.
     */
    public const PAYMENT_STATUS_TIMEOUT = 'TIMEOUT';

    /**
     * Gateway identifier: M-Pesa
     */
    public const GATEWAY_MPESA = 'mpesa';

    /**
     * Gateway identifier: Stripe
     */
    public const GATEWAY_STRIPE = 'stripe';
}
