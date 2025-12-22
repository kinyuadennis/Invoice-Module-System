<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class PaymentService
{
    protected InvoiceStatusService $statusService;

    public function __construct(InvoiceStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Record a payment for an invoice.
     */
    public function recordPayment(Invoice $invoice, Request $request): Payment
    {
        $companyId = CurrentCompanyService::requireId();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'mpesa_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Create payment record
        $payment = Payment::create([
            'company_id' => $companyId,
            'invoice_id' => $invoice->id,
            'amount' => $validated['amount'],
            'refunded_amount' => 0,
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'mpesa_reference' => $validated['mpesa_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Refresh invoice to get latest payments
        $invoice->refresh();
        $invoice->load('payments');

        // Update invoice status based on payment amount
        $this->updateInvoiceStatus($invoice);

        return $payment;
    }

    /**
     * Get payment summary for an invoice (accounting for refunds).
     */
    public function getPaymentSummary(Invoice $invoice): array
    {
        // Calculate net payments (amount - refunded_amount) for each payment
        $totalPaid = (float) $invoice->payments()
            ->get()
            ->sum(function ($payment) {
                return (float) $payment->amount - (float) ($payment->refunded_amount ?? 0);
            });

        $totalRefunded = (float) $invoice->refunds()
            ->where('status', 'processed')
            ->sum('amount');

        $invoiceTotal = (float) $invoice->grand_total;
        $remaining = max(0, $invoiceTotal - $totalPaid);
        $isFullyPaid = $totalPaid >= $invoiceTotal;

        return [
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'invoice_total' => $invoiceTotal,
            'remaining' => $remaining,
            'is_fully_paid' => $isFullyPaid,
            'is_partially_paid' => $totalPaid > 0 && $totalPaid < $invoiceTotal,
            'payment_percentage' => $invoiceTotal > 0 ? round(($totalPaid / $invoiceTotal) * 100, 2) : 0,
        ];
    }

    /**
     * Update invoice status based on payment amount.
     */
    protected function updateInvoiceStatus(Invoice $invoice): void
    {
        $summary = $this->getPaymentSummary($invoice);
        $invoiceTotal = (float) $invoice->grand_total;

        if ($summary['is_fully_paid']) {
            // Fully paid
            $this->statusService->markAsPaid($invoice);
        } elseif ($summary['total_paid'] > 0 && $invoice->status === 'draft') {
            // Partial payment on draft - mark as sent
            $this->statusService->markAsSent($invoice);
        }
        // If partially paid but already sent, keep as sent
    }
}
