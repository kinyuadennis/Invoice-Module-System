<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\PlatformFee;

class PlatformFeeService
{
    /**
     * Calculate and create a platform fee for a given invoice.
     */
    public function generateFeeForInvoice(Invoice $invoice)
    {
        // Example: 5% fee on invoice total
        $feeAmount = $invoice->total * 0.1;

        // Create the fee record linked to invoice
        return PlatformFee::create([
            'invoice_id' => $invoice->id,
            'fee_amount' => $feeAmount,
            'fee_status' => 'pending',
        ]);
    }

    /**
     * Update the status of a platform fee.
     */
    public function updateFeeStatus(PlatformFee $platformFee, string $status)
    {
        $platformFee->fee_status = $status;
        $platformFee->save();

        return $platformFee;
    }

    /**
     * Get all fees with optional status filtering.
     */
    public function getFeesByStatus(?string $status = null)
    {
        if ($status) {
            return PlatformFee::where('fee_status', $status)->get();
        }

        return PlatformFee::all();
    }
}
