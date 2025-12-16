<?php

namespace App\Http\Services;

use App\Jobs\SendInvoiceEmailJob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RecurringInvoice;
use App\Services\CurrentCompanyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecurringInvoiceService
{
    public function __construct(
        private CurrentCompanyService $currentCompanyService,
        private InvoiceService $invoiceService,
        private InvoiceStatusService $statusService
    ) {}

    /**
     * Generate an invoice from a recurring template.
     */
    public function generateInvoice(RecurringInvoice $recurringInvoice): ?Invoice
    {
        try {
            DB::beginTransaction();

            // Set the active company context
            $this->currentCompanyService->setActiveCompany($recurringInvoice->company_id);

            // Get invoice data from template
            $invoiceData = $recurringInvoice->invoice_data;

            // Calculate dates
            $issueDate = now()->toDateString();
            $dueDate = $this->calculateDueDate($issueDate, $invoiceData['payment_terms'] ?? 30);

            // Create the invoice
            $invoice = Invoice::create([
                'company_id' => $recurringInvoice->company_id,
                'client_id' => $recurringInvoice->client_id,
                'user_id' => $recurringInvoice->user_id,
                'recurring_invoice_id' => $recurringInvoice->id,
                'status' => 'draft',
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'notes' => $invoiceData['notes'] ?? null,
                'terms_and_conditions' => $invoiceData['terms_and_conditions'] ?? null,
                'po_number' => $invoiceData['po_number'] ?? null,
                'payment_method' => $invoiceData['payment_method'] ?? null,
                'payment_details' => $invoiceData['payment_details'] ?? null,
            ]);

            // Add line items
            if (isset($invoiceData['line_items']) && is_array($invoiceData['line_items'])) {
                foreach ($invoiceData['line_items'] as $item) {
                    InvoiceItem::create([
                        'company_id' => $invoice->company_id,
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'] ?? '',
                        'quantity' => $item['quantity'] ?? 1,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'vat_rate' => $item['tax_rate'] ?? 0,
                        'vat_included' => false,
                    ]);
                }
            }

            // Calculate totals
            $this->invoiceService->updateTotals($invoice);

            // Update recurring invoice
            $recurringInvoice->increment('total_generated');
            $recurringInvoice->last_generated_at = now()->toDateString();
            $recurringInvoice->next_run_date = $recurringInvoice->calculateNextRunDate();

            // Check if we've reached max occurrences or end date
            if ($recurringInvoice->max_occurrences && $recurringInvoice->total_generated >= $recurringInvoice->max_occurrences) {
                $recurringInvoice->status = 'completed';
            } elseif ($recurringInvoice->end_date && $recurringInvoice->next_run_date > $recurringInvoice->end_date) {
                $recurringInvoice->status = 'completed';
            }

            $recurringInvoice->save();

            DB::commit();

            // Auto-send if enabled
            if ($recurringInvoice->auto_send) {
                $this->statusService->markAsSent($invoice);
                SendInvoiceEmailJob::dispatch($invoice);
            }

            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate recurring invoice', [
                'recurring_invoice_id' => $recurringInvoice->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate all due recurring invoices.
     */
    public function generateDueInvoices(): int
    {
        $recurringInvoices = RecurringInvoice::dueForGeneration()->get();
        $generated = 0;

        foreach ($recurringInvoices as $recurringInvoice) {
            if ($recurringInvoice->isDue()) {
                $invoice = $this->generateInvoice($recurringInvoice);
                if ($invoice) {
                    $generated++;
                }
            }
        }

        return $generated;
    }

    /**
     * Calculate due date based on payment terms.
     */
    private function calculateDueDate(string $issueDate, int $paymentTermsDays): string
    {
        return Carbon::parse($issueDate)->addDays($paymentTermsDays)->toDateString();
    }
}
