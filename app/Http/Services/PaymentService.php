<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;

class PaymentService
{
    public function addPayment(Invoice $invoice, $data)
    {
        // Add the payment
        Payment::create([
            'invoice_id' => $invoice->id,
            'amount'     => $data['amount'],
            'method'     => $data['method'],
            'reference'  => $data['reference'] ?? null,
        ]);

        // Update invoice status
        $this->updateInvoiceStatus($invoice);
    }

    private function updateInvoiceStatus(Invoice $invoice)
    {
        $paid = $invoice->payments->sum('amount');

        if ($paid == 0) {
            $invoice->update(['status' => 'unpaid']);
        } elseif ($paid < $invoice->total) {
            $invoice->update(['status' => 'partial']);
        } else {
            $invoice->update(['status' => 'paid']);
        }
    }
}
