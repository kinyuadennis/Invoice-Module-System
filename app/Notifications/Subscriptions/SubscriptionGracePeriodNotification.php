<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Subscription Grace Period Notification
 *
 * Sent to users during grace period to remind them to update payment method.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 3.5
 */
class SubscriptionGracePeriodNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Subscription $subscription
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
        $daysRemaining = now()->diffInDays($this->subscription->ends_at, false);

        return (new MailMessage)
            ->subject('Subscription Grace Period Reminder')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$planName}** subscription is currently in a grace period.")
            ->line("You have **{$daysRemaining} day(s)** remaining before your subscription expires on **{$gracePeriodEnd}**.")
            ->line('Please update your payment method to avoid service interruption.')
            ->action('Update Payment Method', route('user.subscriptions.index'))
            ->line('Thank you for using our service!');
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
            'grace_period_end' => $this->subscription->ends_at?->toIso8601String(),
            'days_remaining' => now()->diffInDays($this->subscription->ends_at, false),
        ];
    }
}
