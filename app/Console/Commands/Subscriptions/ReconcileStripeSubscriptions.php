<?php

namespace App\Console\Commands\Subscriptions;

use App\Config\PaymentConstants;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Reconcile Stripe Subscriptions Command
 *
 * Queries Stripe API for subscription status and compares with InvoiceHub database.
 * Logs discrepancies for manual review.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 3.4
 */
class ReconcileStripeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:reconcile-stripe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile Stripe subscription status with InvoiceHub database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Reconciling Stripe subscriptions...');

        // Find all Stripe subscriptions
        $stripeSubscriptions = Subscription::where('gateway', PaymentConstants::GATEWAY_STRIPE)
            ->whereNotNull('payment_reference')
            ->with(['user', 'plan'])
            ->get();

        if ($stripeSubscriptions->isEmpty()) {
            $this->info('No Stripe subscriptions found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$stripeSubscriptions->count()} Stripe subscription(s) to reconcile.");

        $reconciled = 0;
        $discrepancies = 0;

        foreach ($stripeSubscriptions as $subscription) {
            try {
                $stripeSubscriptionId = $subscription->payment_reference;

                if (! $stripeSubscriptionId) {
                    $this->warn("  → Subscription ID {$subscription->id} has no Stripe subscription ID");
                    $discrepancies++;

                    continue;
                }

                // Query Stripe API
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $stripeSubscription = \Stripe\Subscription::retrieve($stripeSubscriptionId);

                // Compare status
                $stripeStatus = $stripeSubscription->status; // active, canceled, past_due, etc.
                $ourStatus = $subscription->status;

                // Map Stripe status to our status
                $mappedStatus = match ($stripeStatus) {
                    'active', 'trialing' => 'ACTIVE',
                    'past_due', 'unpaid' => 'GRACE',
                    'canceled' => 'CANCELLED',
                    default => 'PENDING',
                };

                if ($mappedStatus !== $ourStatus) {
                    $this->warn("  → Discrepancy found for subscription ID {$subscription->id}");
                    $this->line("    Stripe: {$stripeStatus} (mapped: {$mappedStatus})");
                    $this->line("    InvoiceHub: {$ourStatus}");

                    Log::warning('Stripe subscription status discrepancy', [
                        'subscription_id' => $subscription->id,
                        'stripe_subscription_id' => $stripeSubscriptionId,
                        'stripe_status' => $stripeStatus,
                        'mapped_status' => $mappedStatus,
                        'our_status' => $ourStatus,
                        'requires_manual_review' => true,
                    ]);

                    $discrepancies++;
                } else {
                    $reconciled++;
                }

                // Check next billing date
                $stripeNextBilling = $stripeSubscription->current_period_end ?? null;
                $ourNextBilling = $subscription->next_billing_at?->timestamp;

                if ($stripeNextBilling && $ourNextBilling) {
                    $diff = abs($stripeNextBilling - $ourNextBilling);
                    if ($diff > 86400) { // More than 1 day difference
                        $this->warn("  → Next billing date mismatch for subscription ID {$subscription->id}");
                        $this->line('    Stripe: '.date('Y-m-d H:i:s', $stripeNextBilling));
                        $this->line("    InvoiceHub: {$subscription->next_billing_at}");

                        Log::warning('Stripe subscription next billing date discrepancy', [
                            'subscription_id' => $subscription->id,
                            'stripe_next_billing' => $stripeNextBilling,
                            'our_next_billing' => $ourNextBilling,
                            'difference_seconds' => $diff,
                        ]);
                    }
                }
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Subscription not found in Stripe
                $this->error("  → Stripe subscription not found: {$stripeSubscriptionId}");
                Log::error('Stripe subscription not found during reconciliation', [
                    'subscription_id' => $subscription->id,
                    'stripe_subscription_id' => $stripeSubscriptionId,
                    'error' => $e->getMessage(),
                ]);
                $discrepancies++;
            } catch (\Exception $e) {
                $this->error("  → Error reconciling subscription ID {$subscription->id}: {$e->getMessage()}");
                Log::error('Stripe reconciliation error', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $discrepancies++;
            }
        }

        $this->info("Reconciliation complete. Reconciled: {$reconciled}, Discrepancies: {$discrepancies}");

        if ($discrepancies > 0) {
            $this->warn("⚠️  {$discrepancies} discrepancy(ies) found. Please review logs for manual intervention.");
        }

        return Command::SUCCESS;
    }
}
