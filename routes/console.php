<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule): void {
    // Send payment reminders daily at 9:00 AM
    // Uses company-specific preferences (days before due, frequency, etc.)
    // Expire estimates daily at midnight
    $schedule->command('estimates:expire')
        ->daily();

    // Send payment reminders daily at 9:00 AM
    // Uses company-specific preferences (days before due, frequency, etc.)
    $schedule->command('invoices:send-reminders')
        ->dailyAt('09:00')
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();

    // Also send reminders for overdue invoices daily at 2:00 PM
    // Uses company-specific overdue reminder frequency
    $schedule->command('invoices:send-reminders --days=0')
        ->dailyAt('14:00')
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();

    // Generate recurring invoices daily at 8:00 AM
    $schedule->command('invoices:generate-recurring')
        ->dailyAt('08:00')
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();

    // Subscription & Payment Management Commands
    // Process M-Pesa renewals (system-driven, no native recurring support)
    $schedule->command('subscriptions:process-mpesa-renewals')
        ->hourly()
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();

    // Send renewal reminders 3 days before due
    $schedule->command('subscriptions:send-renewal-reminders')
        ->dailyAt('10:00')
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();

    // Process grace period expirations (transition GRACE → EXPIRED)
    $schedule->command('subscriptions:process-grace-period-expirations')
        ->dailyAt('00:00')
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();

    // Process payment timeouts (mark INITIATED → TIMEOUT after 5 minutes)
    $schedule->command('payments:process-timeouts')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();
};
