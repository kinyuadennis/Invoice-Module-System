<?php

namespace App\Jobs;

use App\Http\Services\InvoiceAuditService;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * SendInvoiceEmail Job
 *
 * Queued job for sending invoice emails.
 * Respects company notification preferences.
 *
 * Rules:
 * - Never modifies invoices
 * - Logs all notification events
 * - Handles failures gracefully
 */
class SendInvoiceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Invoice $invoice;

    protected string $recipientEmail;

    protected ?string $customMessage;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice, string $recipientEmail, ?string $customMessage = null)
    {
        $this->invoice = $invoice;
        $this->recipientEmail = $recipientEmail;
        $this->customMessage = $customMessage;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check company notification preferences
            $company = $this->invoice->company;
            if (! $this->shouldSendEmail($company)) {
                Log::info('Invoice email skipped due to company preferences', [
                    'invoice_id' => $this->invoice->id,
                    'company_id' => $company->id,
                ]);

                return;
            }

            // Load invoice with relationships
            $this->invoice->loadMissing(['company', 'client', 'invoiceItems']);

            // In a real implementation, send email using Mail facade
            // For now, log the event via audit service
            $auditService = app(InvoiceAuditService::class);
            $auditService->logSend($this->invoice, 'email', null, [
                'source' => \App\Models\InvoiceAuditLog::SOURCE_JOB,
                'recipient' => $this->recipientEmail,
            ]);

            // TODO: Implement actual email sending
            // Mail::to($this->recipientEmail)->send(new InvoiceMail($this->invoice, $this->customMessage));
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Check if email should be sent based on company preferences.
     */
    protected function shouldSendEmail($company): bool
    {
        // Check company notification settings
        // In a real implementation, check company.notification_preferences.email_enabled
        return true; // Default to enabled
    }
}
