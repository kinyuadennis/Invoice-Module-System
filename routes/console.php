<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel;

return function (Schedule $schedule): void {
    // Send payment reminders daily at 9:00 AM
    $schedule->command('invoices:send-reminders --days=3')
        ->dailyAt('09:00')
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();

    // Also send reminders for overdue invoices daily at 2:00 PM
    $schedule->command('invoices:send-reminders --days=0')
        ->dailyAt('14:00')
        ->timezone('Africa/Nairobi')
        ->withoutOverlapping()
        ->runInBackground();
};
