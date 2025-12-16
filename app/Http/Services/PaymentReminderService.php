<?php

namespace App\Http\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentReminderService
{
    protected InvoiceStatusService $statusService;

    public function __construct(InvoiceStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Send payment reminders for invoices that are due soon or overdue.
     */
    public function sendReminders(int $companyId, ?int $daysBeforeDue = null): int
    {
        $company = \App\Models\Company::find($companyId);
        if (! $company) {
            return 0;
        }

        // Use company's preference or fallback to default
        $daysBeforeDue = $daysBeforeDue ?? $company->reminder_days_before_due ?? 3;
        $reminderCount = 0;
        $today = Carbon::today();
        $dueSoonDate = $today->copy()->addDays($daysBeforeDue);

        // Get invoices that are due soon (within X days) or overdue
        $invoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->whereNotNull('due_date')
            ->whereNotNull('client_id')
            ->with(['client', 'company'])
            ->where(function ($query) use ($today, $dueSoonDate) {
                $query->where(function ($q) use ($today, $dueSoonDate) {
                    // Due soon (within X days)
                    $q->where('due_date', '<=', $dueSoonDate)
                        ->where('due_date', '>=', $today)
                        ->where('status', 'sent');
                })->orWhere(function ($q) use ($today) {
                    // Already overdue
                    $q->where('status', 'overdue')
                        ->where('due_date', '<', $today);
                });
            })
            ->get();

        foreach ($invoices as $invoice) {
            // sendReminder will check both email and SMS preferences
            if ($this->shouldSendReminder($invoice, 'email') || $this->shouldSendReminder($invoice, 'sms')) {
                try {
                    $this->sendReminder($invoice);
                    $reminderCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to send payment reminder for invoice '.$invoice->id.': '.$e->getMessage());
                }
            }
        }

        return $reminderCount;
    }

    /**
     * Check if a reminder should be sent for this invoice.
     */
    protected function shouldSendReminder(Invoice $invoice, string $channel = 'email'): bool
    {
        $company = $invoice->company;

        // Don't send if invoice is already paid
        if ($invoice->status === 'paid') {
            return false;
        }

        // Check company preferences
        if ($channel === 'email' && (! $company->reminder_enable_email ?? true)) {
            return false;
        }

        if ($channel === 'sms' && (! $company->reminder_enable_sms ?? false)) {
            return false;
        }

        // Validate recipient contact info
        if ($channel === 'email' && (! $invoice->client || ! $invoice->client->email)) {
            return false;
        }

        if ($channel === 'sms' && (! $invoice->client || ! $invoice->client->phone)) {
            return false;
        }

        // Check if reminder was already sent recently
        $reminderType = $invoice->due_date && $invoice->due_date->isPast() ? 'overdue' : 'due_soon';
        $frequencyDays = $reminderType === 'overdue'
            ? ($company->overdue_reminder_frequency_days ?? 3)
            : ($company->reminder_frequency_days ?? 7);

        if (\App\Models\InvoiceReminderLog::wasSentRecently($invoice->id, $reminderType, $channel, $frequencyDays)) {
            return false;
        }

        return true;
    }

    /**
     * Send a payment reminder for an invoice.
     */
    protected function sendReminder(Invoice $invoice): void
    {
        // Determine reminder type
        $reminderType = $invoice->due_date && $invoice->due_date->isPast() ? 'overdue' : 'due_soon';

        // Update status to overdue if past due date
        if ($reminderType === 'overdue' && $invoice->status === 'sent') {
            $this->statusService->updateStatus($invoice, 'overdue');
        }

        $company = $invoice->company;

        // Send email reminder if enabled
        if ($this->shouldSendReminder($invoice, 'email')) {
            \App\Jobs\SendPaymentReminderJob::dispatch($invoice, $reminderType);
        }

        // Send SMS reminder if enabled
        if ($this->shouldSendReminder($invoice, 'sms')) {
            \App\Jobs\SendPaymentReminderSmsJob::dispatch($invoice, $reminderType);
        }
    }

    /**
     * Check and update overdue invoices.
     */
    public function checkAndUpdateOverdue(?int $companyId = null): int
    {
        if ($companyId) {
            // Check for specific company
            $query = Invoice::where('company_id', $companyId);
        } else {
            // Check for all companies
            $query = Invoice::query();
        }

        $overdueCount = 0;
        $today = Carbon::today();

        $query->where('status', 'sent')
            ->where('due_date', '<', $today)
            ->whereNotNull('due_date')
            ->chunk(100, function ($invoices) use (&$overdueCount) {
                foreach ($invoices as $invoice) {
                    if ($this->statusService->updateStatus($invoice, 'overdue')) {
                        $overdueCount++;
                    }
                }
            });

        return $overdueCount;
    }

    /**
     * Send reminders for all companies (for scheduled command).
     */
    public function sendRemindersForAllCompanies(?int $daysBeforeDue = 3): array
    {
        $results = [];
        $companies = \App\Models\Company::all();

        foreach ($companies as $company) {
            try {
                $count = $this->sendReminders($company->id, $daysBeforeDue);
                $results[$company->id] = $count;
            } catch (\Exception $e) {
                Log::error("Failed to send reminders for company {$company->id}: ".$e->getMessage());
                $results[$company->id] = 0;
            }
        }

        return $results;
    }
}
