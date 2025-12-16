<?php

namespace App\Jobs;

use App\Mail\PaymentReminderMail;
use App\Models\Invoice;
use App\Models\InvoiceReminderLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminderJob implements ShouldQueue
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
    public function handle(): void
    {
        try {
            // Ensure invoice has required relationships
            $this->invoice->load(['client', 'company']);

            // Validate invoice can receive reminder
            if (! $this->invoice->client || ! $this->invoice->client->email) {
                throw new \Exception('Invoice client does not have an email address.');
            }

            if ($this->invoice->status === 'paid') {
                throw new \Exception('Invoice is already paid.');
            }

            // Send reminder email
            Mail::to($this->invoice->client->email)
                ->send(new PaymentReminderMail($this->invoice, $this->reminderType));

            // Log successful send
            InvoiceReminderLog::create([
                'invoice_id' => $this->invoice->id,
                'company_id' => $this->invoice->company_id,
                'reminder_type' => $this->reminderType,
                'sent_at' => now(),
                'recipient_email' => $this->invoice->client->email,
                'sent_successfully' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder', [
                'invoice_id' => $this->invoice->id,
                'reminder_type' => $this->reminderType,
                'error' => $e->getMessage(),
            ]);

            // Log failed send
            InvoiceReminderLog::create([
                'invoice_id' => $this->invoice->id,
                'company_id' => $this->invoice->company_id,
                'reminder_type' => $this->reminderType,
                'sent_at' => now(),
                'recipient_email' => $this->invoice->client->email ?? 'unknown',
                'sent_successfully' => false,
                'error_message' => $e->getMessage(),
            ]);

            // Don't throw - allow other reminders to be sent
        }
    }
}
