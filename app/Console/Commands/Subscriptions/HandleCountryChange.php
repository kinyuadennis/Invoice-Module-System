<?php

namespace App\Console\Commands\Subscriptions;

use App\Config\GatewayConstants;
use App\Config\PaymentConstants;
use App\Config\SubscriptionConstants;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handle Country Change Command
 *
 * Detects users who have changed countries and need subscription gateway migration.
 * Cancels existing subscription and creates new one with appropriate gateway.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 3.4
 */
class HandleCountryChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:handle-country-change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and handle user country changes that require gateway migration';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionService $subscriptionService): int
    {
        $this->info('Checking for users with country changes requiring gateway migration...');

        // Find active subscriptions where user country doesn't match gateway
        // Include both new blueprint statuses and legacy statuses for backward compatibility
        $mismatchedSubscriptions = Subscription::whereIn('status', [
            SubscriptionConstants::SUBSCRIPTION_STATUS_FREE,
            SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE,
            SubscriptionConstants::SUBSCRIPTION_STATUS_PAST_DUE,
            // Legacy statuses for backward compatibility
            'PENDING',
            'ACTIVE',
            'GRACE',
        ])
            ->with(['user', 'plan'])
            ->get()
            ->filter(function ($subscription) {
                if (! $subscription->user || ! $subscription->gateway) {
                    return false;
                }

                $userCountry = $subscription->user->country ?? null;
                $requiredGateway = $userCountry === GatewayConstants::COUNTRY_KENYA
                    ? PaymentConstants::GATEWAY_MPESA
                    : PaymentConstants::GATEWAY_STRIPE;

                // Check if gateway mismatch exists
                return $subscription->gateway !== $requiredGateway;
            });

        if ($mismatchedSubscriptions->isEmpty()) {
            $this->info('No subscriptions require gateway migration.');

            return Command::SUCCESS;
        }

        $this->info("Found {$mismatchedSubscriptions->count()} subscription(s) requiring gateway migration.");

        $processed = 0;
        $failed = 0;

        foreach ($mismatchedSubscriptions as $subscription) {
            try {
                $user = $subscription->user;
                $oldGateway = $subscription->gateway;
                $newGateway = $user->country === GatewayConstants::COUNTRY_KENYA
                    ? PaymentConstants::GATEWAY_MPESA
                    : PaymentConstants::GATEWAY_STRIPE;

                $this->line("Processing subscription ID {$subscription->id} for user {$user->id}");
                $this->line("  → Country: {$user->country}, Gateway: {$oldGateway} → {$newGateway}");

                DB::beginTransaction();

                // Cancel existing subscription
                $subscriptionService->cancelSubscription(
                    $subscription,
                    "Country changed from {$oldGateway} to {$newGateway} gateway"
                );

                // Create new subscription with correct gateway
                // Note: This assumes the user wants to continue subscription
                // In production, you might want to prompt user or send notification
                // Per blueprint: New subscriptions start as 'free' status
                $newSubscription = Subscription::create([
                    'user_id' => $user->id,
                    'company_id' => $subscription->company_id,
                    'subscription_plan_id' => $subscription->subscription_plan_id,
                    'plan_code' => $subscription->plan_code,
                    'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_FREE,
                    'gateway' => $newGateway,
                    'auto_renew' => $subscription->auto_renew,
                    'starts_at' => now(),
                ]);

                $this->info("  → Created new subscription ID {$newSubscription->id} with gateway {$newGateway}");

                Log::info('Subscription gateway migrated due to country change', [
                    'old_subscription_id' => $subscription->id,
                    'new_subscription_id' => $newSubscription->id,
                    'user_id' => $user->id,
                    'country' => $user->country,
                    'old_gateway' => $oldGateway,
                    'new_gateway' => $newGateway,
                ]);

                DB::commit();
                $processed++;
            } catch (\Exception $e) {
                DB::rollBack();

                $this->error("  → Failed: {$e->getMessage()}");
                Log::error('Failed to handle country change for subscription', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $this->info("Country change processing complete. Processed: {$processed}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
