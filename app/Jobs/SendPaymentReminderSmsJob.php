<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\InvoiceReminderLog;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentReminderSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invoice $invoice,
        public string $reminderType // 'due_soon' or 'overdue'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        try {
            // Ensure invoice has required relationships
            $this->invoice->load(['client', 'company']);

            // Validate invoice can receive reminder
            if (! $this->invoice->client || ! $this->invoice->client->phone) {
                throw new \Exception('Invoice client does not have a phone number.');
            }

            if ($this->invoice->status === 'paid') {
                throw new \Exception('Invoice is already paid.');
            }

            // Generate SMS message
            $message = $this->generateMessage();

            // Send SMS
            $success = $smsService->send($this->invoice->client->phone, $message);

            // Log send attempt
            InvoiceReminderLog::create([
                'invoice_id' => $this->invoice->id,
                'company_id' => $this->invoice->company_id,
                'reminder_type' => $this->reminderType,
                'channel' => 'sms',
                'sent_at' => now(),
                'recipient_phone' => $this->invoice->client->phone,
                'sent_successfully' => $success,
                'error_message' => $success ? null : 'SMS sending failed',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder SMS', [
                'invoice_id' => $this->invoice->id,
                'reminder_type' => $this->reminderType,
                'error' => $e->getMessage(),
            ]);

            // Log failed send
            InvoiceReminderLog::create([
                'invoice_id' => $this->invoice->id,
                'company_id' => $this->invoice->company_id,
                'reminder_type' => $this->reminderType,
                'channel' => 'sms',
                'sent_at' => now(),
                'recipient_phone' => $this->invoice->client->phone ?? 'unknown',
                'sent_successfully' => false,
                'error_message' => $e->getMessage(),
            ]);

            // Don't throw - allow other reminders to be sent
        }
    }

    /**
     * Generate SMS message text.
     */
    protected function generateMessage(): string
    {
        $invoiceNumber = $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT);
        $companyName = $this->invoice->company->name ?? 'InvoiceHub';
        $total = $this->invoice->grand_total ?? $this->invoice->total;
        $dueDate = $this->invoice->due_date ? \Carbon\Carbon::parse($this->invoice->due_date)->format('M d, Y') : 'N/A';

        if ($this->reminderType === 'overdue') {
            $daysOverdue = $this->invoice->due_date ? now()->diffInDays($this->invoice->due_date, false) : 0;

            return "Hi {$this->invoice->client->name}, your invoice #{$invoiceNumber} from {$companyName} for KES ".number_format($total, 2)." is {$daysOverdue} day(s) overdue. Please arrange payment. Thank you!";
        }

        return "Hi {$this->invoice->client->name}, reminder: Invoice #{$invoiceNumber} from {$companyName} for KES ".number_format($total, 2)." is due on {$dueDate}. Thank you!";
    }
}
