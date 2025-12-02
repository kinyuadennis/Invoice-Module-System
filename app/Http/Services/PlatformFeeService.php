<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\PlatformFee;

class PlatformFeeService
{
    /**
     * Platform fee rate (3%)
     */
    private const FEE_RATE = 0.03;

    /**
     * Calculate and create a platform fee for a given invoice.
     */
    public function generateFeeForInvoice(Invoice $invoice): PlatformFee
    {
        // Calculate 3% fee on invoice grand_total (subtotal + VAT)
        $feeAmount = ($invoice->subtotal + $invoice->vat_amount) * self::FEE_RATE;

        // Check if fee already exists
        $existingFee = PlatformFee::where('invoice_id', $invoice->id)
            ->where('company_id', $invoice->company_id)
            ->first();

        if ($existingFee) {
            // Update existing fee
            $existingFee->update([
                'company_id' => $invoice->company_id,
                'fee_amount' => $feeAmount,
                'fee_rate' => self::FEE_RATE * 100, // Store as percentage (0.8)
            ]);

            return $existingFee;
        }

        // Create the fee record linked to invoice
        return PlatformFee::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'fee_amount' => $feeAmount,
            'fee_rate' => self::FEE_RATE * 100, // Store as percentage (0.8)
            'fee_status' => 'pending',
        ]);
    }

    /**
     * Calculate platform fee amount for a given total
     */
    public function calculateFee(float $total): float
    {
        return $total * self::FEE_RATE;
    }

    /**
     * Get platform fee rate
     */
    public function getFeeRate(): float
    {
        return self::FEE_RATE;
    }
}
