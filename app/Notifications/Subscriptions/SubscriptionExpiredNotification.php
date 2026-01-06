<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Subscription Expired Notification
 *
 * Sent to users when their subscription expires.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 3.5
 */
class SubscriptionExpiredNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Subscription Expired')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$planName}** subscription has expired.")
            ->line('Your access to premium features has been suspended.')
            ->line('To reactivate your subscription, please update your payment method and make a payment.')
            ->action('Reactivate Subscription', route('user.subscriptions.index'))
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
            'expired_at' => now()->toIso8601String(),
        ];
    }
}
