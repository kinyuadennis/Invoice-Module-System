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
};
