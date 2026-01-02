<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Subscription Renewal Failed Notification
 *
 * Sent to users when a subscription renewal payment fails.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 3.5
 */
class SubscriptionRenewalFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Subscription $subscription,
        public string $reason = 'Payment failed'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $plan = $this->subscription->plan;
        $planName = $plan ? ($plan->name ?? 'Plan') : 'Plan';
        $gracePeriodEnd = $this->subscription->ends_at?->format('F d, Y') ?? 'N/A';

        return (new MailMessage)
            ->subject('Subscription Renewal Failed')
            ->greeting("Hello {$notifiable->name},")
            ->line("We were unable to process the renewal payment for your **{$planName}** subscription.")
            ->line("Reason: **{$this->reason}**")
            ->line("Your subscription is now in a grace period and will expire on **{$gracePeriodEnd}**.")
            ->line('Please update your payment method to continue using our service.')
            ->action('Update Payment Method', route('user.subscriptions.index'))
            ->line('If you have any questions, please contact our support team.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name ?? null,
            'reason' => $this->reason,
            'grace_period_end' => $this->subscription->ends_at?->toIso8601String(),
        ];
    }
}
