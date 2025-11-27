<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Process a payment for an invoice.
     */
    public function processPayment(Request $request)
    {
        // Validate invoice existence
        $invoice = Invoice::findOrFail($request->invoice_id);

        $amount = $request->amount;

        // Check if payment amount is not greater than invoice balance
        $balance = $invoice->total - $invoice->payments->sum('amount');
        if ($amount > $balance) {
            throw new \Exception('Payment amount exceeds invoice balance.');
        }

        // Transactionally create payment and update invoice status
        return DB::transaction(function () use ($invoice, $request, $amount) {
            // Create the payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => now(),
                'amount' => $amount,
                'payment_method' => $request->payment_method ?? 'unknown',
            ]);

            // Calculate updated total paid
            $totalPaid = $invoice->payments->sum('amount') + $amount;

            // Update invoice status
            if ($totalPaid >= $invoice->total) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->status = 'partially_paid'; // You can add this status to invoices table enum
            }
            $invoice->save();

            return $payment;
        });
    }
}
