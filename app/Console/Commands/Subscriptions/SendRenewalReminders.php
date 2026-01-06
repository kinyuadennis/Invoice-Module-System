<?php

namespace App\Console\Commands\Subscriptions;

use App\Config\SubscriptionConstants;
use App\Models\Subscription;
use App\Notifications\Subscriptions\RenewalReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Send Renewal Reminders Command
 *
 * Sends renewal reminder notifications 3 days before subscription renewal is due.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 5
 */
class SendRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-renewal-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminder notifications 3 days before subscription renewal is due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Sending renewal reminder notifications...');

        // Find active subscriptions due for renewal in 3 days
        $reminderDate = now()->addDays(SubscriptionConstants::RENEWAL_NOTIFICATION_LEAD_DAYS);

        $subscriptionsDue = Subscription::where('status', SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE)
            ->where('auto_renew', true)
            ->whereNotNull('next_billing_at')
            ->whereDate('next_billing_at', $reminderDate->toDateString())
            ->with(['user', 'plan'])
            ->get();

        if ($subscriptionsDue->isEmpty()) {
            $this->info('No subscriptions due for renewal reminders.');

            return Command::SUCCESS;
        }

        $this->info("Found {$subscriptionsDue->count()} subscription(s) due for renewal reminders.");

        $sent = 0;
        $failed = 0;

        foreach ($subscriptionsDue as $subscription) {
            try {
                $user = $subscription->user;
                if (! $user) {
                    $this->warn("  → Skipped subscription ID {$subscription->id}: User not found");
                    $failed++;

                    continue;
                }

                // Check if reminder was already sent (prevent duplicates)
                // You might want to add a 'renewal_reminder_sent_at' field to track this
                // For now, we'll send it (can be improved with tracking)

                $user->notify(new RenewalReminderNotification($subscription));

                $this->line("  → Reminder sent to {$user->email} for subscription ID: {$subscription->id}");
                $sent++;

                Log::info('Renewal reminder sent', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'next_billing_at' => $subscription->next_billing_at,
                ]);
            } catch (\Exception $e) {
                $this->error("  → Failed to send reminder for subscription ID {$subscription->id}: {$e->getMessage()}");
                Log::error('Failed to send renewal reminder', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $this->info("Renewal reminder processing complete. Sent: {$sent}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
