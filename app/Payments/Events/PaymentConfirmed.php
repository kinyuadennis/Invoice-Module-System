<?php

namespace App\Payments\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Confirmed Event
 *
 * Fired when a payment is successfully confirmed by the gateway.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class PaymentConfirmed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Payment $payment
    ) {}
}
