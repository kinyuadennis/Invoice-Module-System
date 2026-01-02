<?php

namespace App\Config;

/**
 * Gateway-related constants.
 *
 * All values are explicitly defined per "No Silent Defaults" policy.
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class GatewayConstants
{
    /**
     * Maximum number of retries for webhook processing.
     *
     * Confirmed: 3 retries per blueprint section 5 failure playbook.
     */
    public const WEBHOOK_MAX_RETRIES = 3;

    /**
     * Base delay in seconds for exponential backoff retries.
     *
     * Confirmed: 60 seconds per blueprint section 5.
     * Retry delays: 60s, 120s, 240s (doubles each time).
     */
    public const RETRY_BACKOFF_BASE_SECONDS = 60;

    /**
     * Webhook timeout in seconds.
     *
     * Maximum time to wait for webhook processing before timeout.
     */
    public const WEBHOOK_TIMEOUT_SECONDS = 30;

    /**
     * Country code: Kenya
     *
     * Used for gateway routing (KE → M-Pesa, else → Stripe).
     */
    public const COUNTRY_KENYA = 'KE';
}
