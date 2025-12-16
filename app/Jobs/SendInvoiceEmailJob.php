<?php

namespace App\Jobs;

use App\Http\Services\InvoiceAccessTokenService;
use App\Http\Services\InvoiceService;
use App\Mail\InvoiceSentMail;
use App\Models\Invoice;
use App\Models\InvoiceReminderLog;
use App\Services\CurrentCompanyService;
use Barryvdh\DomPDF\Facade\Pdf;
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
    public function handle(InvoiceService $invoiceService): void
    {
        try {
            // Ensure invoice has required relationships
            $this->invoice->load(['client', 'invoiceItems', 'company', 'user']);

            // Validate invoice can be sent
            if (! $this->invoice->client || ! $this->invoice->client->email) {
                throw new \Exception('Invoice client does not have an email address.');
            }

            // Generate PDF
            $pdfPath = $this->generatePdf($invoiceService);

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
    protected function generatePdf(InvoiceService $invoiceService): string
    {
        // Set company context for PDF generation
        CurrentCompanyService::setId($this->invoice->company_id);

        // Format invoice data
        $formattedInvoice = $invoiceService->formatInvoiceForShow($this->invoice);

        // Get template
        $template = $this->invoice->getInvoiceTemplate();
        $templateView = $template->view_path;
        if (! view()->exists($templateView)) {
            $templateView = 'invoices.templates.modern-clean';
        }

        // Prepare logo path
        $logoPath = null;
        if ($this->invoice->company->logo) {
            $logoPath = $this->invoice->company->logo;
            if (! str_starts_with($logoPath, 'http://') && ! str_starts_with($logoPath, 'https://')) {
                $fullPath = public_path('storage/'.$logoPath);
                if (file_exists($fullPath)) {
                    $logoPath = $fullPath;
                } else {
                    $logoPath = null;
                }
            }
        }

        // Generate PDF
        $pdf = Pdf::loadView($templateView, [
            'invoice' => $formattedInvoice,
            'template' => $template,
            'company' => $this->invoice->company,
        ]);

        // Save to temporary file
        $tempPath = storage_path('app/temp/invoice-'.$this->invoice->id.'-'.time().'.pdf');
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        file_put_contents($tempPath, $pdf->output());

        return $tempPath;
    }
}
