<?php

namespace App\Observers;

use App\Config\PaymentConstants;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

/**
 * Subscription Observer
 *
 * Updates payment records when subscription status changes,
 * especially when Cashier updates stripe_status via webhooks.
 */
class SubscriptionObserver
{
    /**
     * Handle the Subscription "created" event.
     */
    public function created(Subscription $subscription): void
    {
        //
    }

    /**
     * Handle the Subscription "updated" event.
     *
     * Syncs payment records when subscription status changes,
     * particularly for Stripe subscriptions updated via Cashier webhooks.
     */
    public function updated(Subscription $subscription): void
    {
        // Check if stripe_status changed to 'active' (Cashier webhook update)
        if ($subscription->wasChanged('stripe_status') && $subscription->stripe_status === 'active') {
            $this->syncPaymentForStripeSubscription($subscription);
        }

        // Check if subscription status changed to ACTIVE
        if ($subscription->wasChanged('status') && $subscription->status === \App\Config\SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE) {
            $this->syncPaymentForActiveSubscription($subscription);
        }
    }

    /**
     * Sync payment record when Stripe subscription becomes active.
     */
    private function syncPaymentForStripeSubscription(Subscription $subscription): void
    {
        // Find the most recent payment for this subscription
        $payment = $subscription->payments()
            ->where('gateway', PaymentConstants::GATEWAY_STRIPE)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $payment) {
            Log::warning('No payment found for Stripe subscription', [
                'subscription_id' => $subscription->id,
                'stripe_id' => $subscription->stripe_id,
            ]);

            return;
        }

        // Update payment status if it's still INITIATED
        if ($payment->status === PaymentConstants::PAYMENT_STATUS_INITIATED) {
            $payment->update([
                'status' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                'gateway_transaction_id' => $subscription->stripe_id, // Use subscription ID as transaction ID
                'paid_at' => now(),
            ]);

            Log::info('Payment updated from Stripe subscription webhook', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
                'stripe_id' => $subscription->stripe_id,
            ]);
        }
    }

    /**
     * Sync payment record when subscription becomes active.
     */
    private function syncPaymentForActiveSubscription(Subscription $subscription): void
    {
        // Find the most recent payment for this subscription
        $payment = $subscription->payments()
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $payment) {
            return;
        }

        // Update payment status if it's still INITIATED and subscription is now active
        if ($payment->status === PaymentConstants::PAYMENT_STATUS_INITIATED) {
            $payment->update([
                'status' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                'paid_at' => now(),
            ]);

            Log::info('Payment updated when subscription became active', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Handle the Subscription "deleted" event.
     */
    public function deleted(Subscription $subscription): void
    {
        //
    }

    /**
     * Handle the Subscription "restored" event.
     */
    public function restored(Subscription $subscription): void
    {
        //
    }

    /**
     * Handle the Subscription "force deleted" event.
     */
    public function forceDeleted(Subscription $subscription): void
    {
        //
    }
}
