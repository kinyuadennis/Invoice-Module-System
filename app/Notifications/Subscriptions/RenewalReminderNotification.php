<?php

namespace App\Notifications\Subscriptions;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Renewal Reminder Notification
 *
 * Sent to users 3 days before subscription renewal is due.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 5
 */
class RenewalReminderNotification extends Notification implements ShouldQueue
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
        $amount = $plan ? $plan->price : 0;
        $currency = $plan ? ($plan->currency ?? 'KES') : 'KES';
        $nextBillingDate = $this->subscription->next_billing_at?->format('F d, Y') ?? 'N/A';
        $planName = $plan ? ($plan->name ?? 'Plan') : 'Plan';

        return (new MailMessage)
            ->subject('Subscription Renewal Reminder')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your subscription for **{$planName}** will renew on **{$nextBillingDate}**.")
            ->line("Amount: **{$currency} ".number_format($amount, 2).'**')
            ->line('Please ensure your payment method is up to date to avoid service interruption.')
            ->action('Manage Subscription', route('user.subscriptions.index'))
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
            'next_billing_at' => $this->subscription->next_billing_at?->toIso8601String(),
        ];
    }
}
