<?php

namespace App\Console\Commands\Subscriptions;

use App\Config\PaymentConstants;
use App\Config\SubscriptionConstants;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Process M-Pesa Renewals Command
 *
 * Scheduled command to process M-Pesa subscription renewals.
 * M-Pesa has no native recurring support, so renewals are system-driven.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 5.1
 */
class ProcessMpesaRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-mpesa-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process M-Pesa subscription renewals (system-driven, no native recurring support)';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionService $subscriptionService): int
    {
        $this->info('Processing M-Pesa subscription renewals...');

        // Find active M-Pesa subscriptions due for renewal
        $dueSubscriptions = Subscription::where('gateway', PaymentConstants::GATEWAY_MPESA)
            ->where('status', SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE)
            ->where('auto_renew', true)
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', now())
            ->with(['user', 'plan', 'company'])
            ->get();

        if ($dueSubscriptions->isEmpty()) {
            $this->info('No M-Pesa subscriptions due for renewal.');

            return Command::SUCCESS;
        }

        $this->info("Found {$dueSubscriptions->count()} subscription(s) due for renewal.");

        $processed = 0;
        $failed = 0;

        foreach ($dueSubscriptions as $subscription) {
            try {
                $this->line("Processing renewal for subscription ID: {$subscription->id}");

                // Get user phone number (required for M-Pesa STK Push)
                $user = $subscription->user;
                if (! $user) {
                    $this->error('  → Skipped: User not found');
                    $this->handleRenewalFailure($subscriptionService, $subscription, 'User not found');
                    $failed++;

                    continue;
                }

                // Get phone from user or company (M-Pesa requires phone)
                $phone = $user->phone ?? $subscription->company->phone ?? null;
                if (! $phone) {
                    $this->error("  → Skipped: Phone number missing for user {$user->id}");
                    $this->handleRenewalFailure($subscriptionService, $subscription, 'Phone number missing');
                    $failed++;

                    continue;
                }

                // Prepare user details for M-Pesa
                $userDetails = [
                    'phone' => $phone,
                    'country' => $user->country ?? $subscription->company->country ?? 'KE',
                ];

                // Initiate renewal payment
                $result = $subscriptionService->initiateSubscriptionPayment($subscription, $userDetails);

                if ($result['success']) {
                    $this->info("  → STK Push initiated successfully. Transaction ID: {$result['transaction_id']}");
                    $processed++;
                } else {
                    $this->error("  → Failed: {$result['error']}");
                    $this->handleRenewalFailure($subscriptionService, $subscription, $result['error'] ?? 'Payment initiation failed');
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("  → Exception: {$e->getMessage()}");
                Log::error('M-Pesa renewal processing failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $this->handleRenewalFailure($subscriptionService, $subscription, $e->getMessage());
                $failed++;
            }
        }

        $this->info("Renewal processing complete. Processed: {$processed}, Failed: {$failed}");

        return Command::SUCCESS;
    }

    /**
     * Handle renewal failure - transition to GRACE.
     */
    private function handleRenewalFailure(SubscriptionService $subscriptionService, Subscription $subscription, string $reason): void
    {
        try {
            $subscriptionService->handleRenewalFailure($subscription);
            Log::info('Subscription moved to GRACE period due to renewal failure', [
                'subscription_id' => $subscription->id,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to transition subscription to GRACE', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
