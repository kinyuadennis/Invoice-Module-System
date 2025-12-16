<?php

namespace App\Console\Commands;

use App\Http\Services\PaymentReminderService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders {--days= : Number of days before due date to send reminder (uses company preference if not specified)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders for invoices due soon or overdue';

    /**
     * Execute the console command.
     */
    public function handle(PaymentReminderService $reminderService): int
    {
        $daysBeforeDue = $this->option('days') !== null ? (int) $this->option('days') : null;

        if ($daysBeforeDue !== null) {
            $this->info("Sending payment reminders (reminding {$daysBeforeDue} days before due date)...");
        } else {
            $this->info('Sending payment reminders (using company-specific preferences)...');
        }

        // First, check and update overdue invoices (null means all companies)
        $overdueCount = $reminderService->checkAndUpdateOverdue(null);
        if ($overdueCount > 0) {
            $this->info("Updated {$overdueCount} invoices to overdue status.");
        }

        // Send reminders for all companies (will use company preferences if daysBeforeDue is null)
        $results = $reminderService->sendRemindersForAllCompanies($daysBeforeDue);
        $totalSent = array_sum($results);

        if ($totalSent > 0) {
            $this->info("Successfully sent {$totalSent} payment reminder(s).");
            foreach ($results as $companyId => $count) {
                if ($count > 0) {
                    $this->line("  - Company {$companyId}: {$count} reminder(s)");
                }
            }
        } else {
            $this->info('No reminders to send at this time.');
        }

        return Command::SUCCESS;
    }
}
