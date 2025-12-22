<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    /**
     * Create a refund for an invoice or specific payment.
     */
    public function createRefund(Invoice $invoice, Request $request): Refund
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_id' => 'nullable|exists:payments,id',
            'refund_date' => 'required|date',
            'refund_method' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validate refund amount
        $this->validateRefundAmount($invoice, $validated['amount'], $validated['payment_id'] ?? null);

        return DB::transaction(function () use ($companyId, $invoice, $validated, $user) {
            // Create refund record
            $refund = Refund::create([
                'company_id' => $companyId,
                'invoice_id' => $invoice->id,
                'payment_id' => $validated['payment_id'] ?? null,
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
                'refund_method' => $validated['refund_method'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'refund_date' => $validated['refund_date'],
            ]);

            // If processing immediately, process the refund
            if ($request->has('process_immediately') && $request->boolean('process_immediately')) {
                $this->processRefund($refund);
            }

            return $refund;
        });
    }

    /**
     * Process a refund (mark as processed and update payment/invoice).
     */
    public function processRefund(Refund $refund): Refund
    {
        return DB::transaction(function () use ($refund) {
            if ($refund->isProcessed()) {
                throw ValidationException::withMessages([
                    'refund' => 'This refund has already been processed.',
                ]);
            }

            if ($refund->isCancelled()) {
                throw ValidationException::withMessages([
                    'refund' => 'Cannot process a cancelled refund.',
                ]);
            }

            // Update payment refunded_amount if refund is for specific payment
            if ($refund->payment_id) {
                $payment = Payment::findOrFail($refund->payment_id);
                $newRefundedAmount = (float) ($payment->refunded_amount ?? 0) + (float) $refund->amount;

                // Validate refund doesn't exceed payment amount
                if ($newRefundedAmount > (float) $payment->amount) {
                    throw ValidationException::withMessages([
                        'amount' => 'Refund amount exceeds the payment amount.',
                    ]);
                }

                $payment->refunded_amount = $newRefundedAmount;

                // Update payment status
                if ($newRefundedAmount >= (float) $payment->amount) {
                    $payment->status = 'fully_refunded';
                } else {
                    $payment->status = 'partially_refunded';
                }

                $payment->save();
            } else {
                // Refund against invoice (distribute across payments)
                $this->distributeRefundAcrossPayments($refund);
            }

            // Update refund status
            $refund->status = 'processed';
            $refund->processed_at = now();
            $refund->save();

            // Update invoice status
            $invoice = $refund->invoice;
            $invoice->refresh();
            $paymentService = new PaymentService(new InvoiceStatusService);
            $paymentService->updateInvoiceStatus($invoice);

            return $refund;
        });
    }

    /**
     * Cancel a refund.
     */
    public function cancelRefund(Refund $refund): Refund
    {
        if ($refund->isProcessed()) {
            throw ValidationException::withMessages([
                'refund' => 'Cannot cancel a processed refund.',
            ]);
        }

        if ($refund->isCancelled()) {
            throw ValidationException::withMessages([
                'refund' => 'This refund is already cancelled.',
            ]);
        }

        $refund->status = 'cancelled';
        $refund->save();

        return $refund;
    }

    /**
     * Validate refund amount.
     */
    protected function validateRefundAmount(Invoice $invoice, float $amount, ?int $paymentId = null): void
    {
        $paymentService = new PaymentService(new InvoiceStatusService);
        $summary = $paymentService->getPaymentSummary($invoice);

        if ($paymentId) {
            // Refund for specific payment
            $payment = Payment::findOrFail($paymentId);

            if ($payment->invoice_id !== $invoice->id) {
                throw ValidationException::withMessages([
                    'payment_id' => 'Payment does not belong to this invoice.',
                ]);
            }

            $availableToRefund = (float) $payment->amount - (float) ($payment->refunded_amount ?? 0);

            if ($amount > $availableToRefund) {
                throw ValidationException::withMessages([
                    'amount' => "Refund amount cannot exceed the available refund amount for this payment (KES {$availableToRefund}).",
                ]);
            }
        } else {
            // Refund against invoice (total available)
            $availableToRefund = $summary['total_paid'] - $summary['total_refunded'];

            if ($amount > $availableToRefund) {
                throw ValidationException::withMessages([
                    'amount' => "Refund amount cannot exceed the available refund amount (KES {$availableToRefund}).",
                ]);
            }
        }
    }

    /**
     * Distribute refund across payments (FIFO - First In First Out).
     */
    protected function distributeRefundAcrossPayments(Refund $refund): void
    {
        $refundAmount = (float) $refund->amount;
        $payments = Payment::where('invoice_id', $refund->invoice_id)
            ->orderBy('payment_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($payments as $payment) {
            if ($refundAmount <= 0) {
                break;
            }

            $availableToRefund = (float) $payment->amount - (float) ($payment->refunded_amount ?? 0);

            if ($availableToRefund > 0) {
                $refundForThisPayment = min($refundAmount, $availableToRefund);

                $payment->refunded_amount = (float) ($payment->refunded_amount ?? 0) + $refundForThisPayment;

                // Update payment status
                if ((float) $payment->refunded_amount >= (float) $payment->amount) {
                    $payment->status = 'fully_refunded';
                } else {
                    $payment->status = 'partially_refunded';
                }

                $payment->save();
                $refundAmount -= $refundForThisPayment;
            }
        }
    }

    /**
     * Get refund summary for an invoice.
     */
    public function getRefundSummary(Invoice $invoice): array
    {
        $refunds = $invoice->refunds;

        return [
            'total_refunded' => (float) $refunds->where('status', 'processed')->sum('amount'),
            'pending_refunds' => (float) $refunds->where('status', 'pending')->sum('amount'),
            'failed_refunds' => (float) $refunds->where('status', 'failed')->sum('amount'),
            'refund_count' => $refunds->count(),
            'processed_count' => $refunds->where('status', 'processed')->count(),
        ];
    }

    /**
     * Format refund for display.
     */
    public function formatRefundForDisplay(Refund $refund): array
    {
        return [
            'id' => $refund->id,
            'refund_reference' => $refund->refund_reference,
            'amount' => (float) $refund->amount,
            'status' => $refund->status,
            'refund_method' => $refund->refund_method,
            'reason' => $refund->reason,
            'notes' => $refund->notes,
            'refund_date' => $refund->refund_date->format('Y-m-d'),
            'processed_at' => $refund->processed_at?->format('Y-m-d H:i:s'),
            'payment' => $refund->payment ? [
                'id' => $refund->payment->id,
                'amount' => (float) $refund->payment->amount,
                'payment_date' => $refund->payment->payment_date->format('Y-m-d'),
            ] : null,
            'created_by' => $refund->user ? $refund->user->name : null,
        ];
    }
}
