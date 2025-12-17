<?php

namespace App\Jobs;

use App\Http\Services\InvoiceAccessTokenService;
use App\Http\Services\InvoiceService;
use App\Mail\InvoiceSentMail;
use App\Models\Invoice;
use App\Models\InvoiceReminderLog;
use App\Services\CurrentCompanyService;
use App\Services\InvoiceSnapshotService;
use App\Services\PdfInvoiceRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        InvoiceService $invoiceService,
        InvoiceSnapshotService $snapshotService,
        PdfInvoiceRenderer $pdfRenderer
    ): void {
        try {
            // Ensure invoice has required relationships
            $this->invoice->load(['client', 'invoiceItems', 'company', 'user']);

            // Validate invoice can be sent
            if (! $this->invoice->client || ! $this->invoice->client->email) {
                throw new \Exception('Invoice client does not have an email address.');
            }

            // Generate PDF
            $pdfPath = $this->generatePdf($snapshotService, $pdfRenderer);

            // Generate access token for customer portal
            $tokenService = app(InvoiceAccessTokenService::class);
            $accessToken = $tokenService->generateToken($this->invoice);
            $accessUrl = $tokenService->getAccessUrl($accessToken);

            // Send email
            Mail::to($this->invoice->client->email)
                ->send(new InvoiceSentMail($this->invoice, $pdfPath, $accessUrl));

            // Log successful send
            InvoiceReminderLog::create([
                'invoice_id' => $this->invoice->id,
                'company_id' => $this->invoice->company_id,
                'reminder_type' => 'invoice_sent',
                'sent_at' => now(),
                'recipient_email' => $this->invoice->client->email,
                'sent_successfully' => true,
            ]);

            // Clean up temporary PDF file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
            ]);

            // Log failed send
            InvoiceReminderLog::create([
                'invoice_id' => $this->invoice->id,
                'company_id' => $this->invoice->company_id,
                'reminder_type' => 'invoice_sent',
                'sent_at' => now(),
                'recipient_email' => $this->invoice->client->email ?? 'unknown',
                'sent_successfully' => false,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate PDF for the invoice.
     */
    /**
     * Generate PDF for the invoice.
     */
    protected function generatePdf(InvoiceSnapshotService $snapshotService, PdfInvoiceRenderer $pdfRenderer): string
    {
        // Set company context for PDF generation
        CurrentCompanyService::setId($this->invoice->company_id);

        // Find existing snapshot or create one (should exist as it's sent)
        $snapshot = $snapshotService->findLatestSnapshot($this->invoice);
        
        if (! $snapshot) {
            // Fallback: create snapshot if missing (e.g. legacy invoices)
            $snapshot = $snapshotService->createSnapshot($this->invoice, 'sent');
        }

        // Render PDF
        $pdfContent = $pdfRenderer->render($snapshot);

        // Save to temporary file
        $tempPath = storage_path('app/temp/invoice-'.$this->invoice->id.'-'.time().'.pdf');
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        file_put_contents($tempPath, $pdfContent);

        return $tempPath;
    }
}
