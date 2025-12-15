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

/**
 * SendInvoiceWhatsApp Job
 *
 * Queued job for sending invoice via WhatsApp.
 * Respects company notification preferences.
 *
 * Rules:
 * - Never modifies invoices
 * - Logs all notification events
 * - Handles failures gracefully
 */
class SendInvoiceWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Invoice $invoice;

    protected string $recipientPhone;

    protected ?string $customMessage;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice, string $recipientPhone, ?string $customMessage = null)
    {
        $this->invoice = $invoice;
        $this->recipientPhone = $recipientPhone;
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
            if (! $this->shouldSendWhatsApp($company)) {
                Log::info('Invoice WhatsApp skipped due to company preferences', [
                    'invoice_id' => $this->invoice->id,
                    'company_id' => $company->id,
                ]);

                return;
            }

            // Load invoice with relationships
            $this->invoice->loadMissing(['company', 'client', 'invoiceItems']);

            // In a real implementation, send WhatsApp message via API
            // For now, log the event via audit service
            $auditService = app(InvoiceAuditService::class);
            $auditService->logSend($this->invoice, 'whatsapp', null, [
                'source' => \App\Models\InvoiceAuditLog::SOURCE_JOB,
                'recipient' => $this->recipientPhone,
            ]);

            // TODO: Implement actual WhatsApp sending
            // WhatsAppService::sendMessage($this->recipientPhone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send invoice WhatsApp', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Check if WhatsApp should be sent based on company preferences.
     */
    protected function shouldSendWhatsApp($company): bool
    {
        // Check company notification settings
        return (bool) ($company->whatsapp_notifications_enabled ?? true);
    }
}
