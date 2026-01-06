<?php

namespace App\Console\Commands\Subscriptions;

use App\Config\SubscriptionConstants;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Process Grace Period Expirations Command
 *
 * Transitions subscriptions from GRACE to EXPIRED when grace period ends.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2/5
 */
class ProcessGracePeriodExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-grace-period-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transition subscriptions from GRACE to EXPIRED when grace period ends';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing grace period expirations...');

        // Find subscriptions in GRACE status where grace period has ended
        $expiredGraceSubscriptions = Subscription::where('status', SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->get();

        if ($expiredGraceSubscriptions->isEmpty()) {
            $this->info('No grace period expirations to process.');

            return Command::SUCCESS;
        }

        $this->info("Found {$expiredGraceSubscriptions->count()} subscription(s) with expired grace periods.");

        $processed = 0;
        $failed = 0;

        foreach ($expiredGraceSubscriptions as $subscription) {
            try {
                // Transition to EXPIRED (enforces invariant: only from GRACE)
                $subscription->transitionToExpired();

                // Send expiration notification
                $user = $subscription->user;
                if ($user) {
                    $user->notify(new \App\Notifications\Subscriptions\SubscriptionExpiredNotification($subscription));
                }

                $this->line("  → Subscription ID {$subscription->id} transitioned to EXPIRED");

                Log::info('Subscription grace period expired', [
                    'subscription_id' => $subscription->id,
                    'grace_period_end' => $subscription->ends_at,
                ]);

                $processed++;
            } catch (\Exception $e) {
                $this->error("  → Failed to expire subscription ID {$subscription->id}: {$e->getMessage()}");
                Log::error('Failed to transition subscription to EXPIRED', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $this->info("Grace period expiration processing complete. Processed: {$processed}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
