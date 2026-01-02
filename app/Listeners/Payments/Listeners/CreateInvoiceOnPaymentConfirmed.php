<?php

namespace App\Listeners\Payments\Listeners;

use App\Payments\Events\PaymentConfirmed;
use App\Services\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Create Invoice On Payment Confirmed Listener
 *
 * Handles PaymentConfirmed event to activate subscription and create invoice snapshot.
 * This listener is specifically for subscription payments.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 6
 */
class CreateInvoiceOnPaymentConfirmed implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;

        // Only process subscription payments
        if ($payment->payable_type !== \App\Models\Subscription::class) {
            return;
        }

        // Activate subscription and create invoice snapshot
        $this->subscriptionService->activateSubscription($payment);
    }
}
