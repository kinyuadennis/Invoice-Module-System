<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\PlatformFee;

class PlatformFeeService
{
    /**
     * Platform fee rate (0.8%)
     */
    private const FEE_RATE = 0.008;

    /**
     * Calculate and create a platform fee for a given invoice.
     */
    public function generateFeeForInvoice(Invoice $invoice): PlatformFee
    {
        // Calculate 0.8% fee on invoice total
        $feeAmount = $invoice->total * self::FEE_RATE;

        // Check if fee already exists
        $existingFee = PlatformFee::where('invoice_id', $invoice->id)->first();

        if ($existingFee) {
            // Update existing fee
            $existingFee->update([
                'fee_amount' => $feeAmount,
                'fee_rate' => self::FEE_RATE * 100, // Store as percentage (0.8)
            ]);

            return $existingFee;
        }

        // Create the fee record linked to invoice
        return PlatformFee::create([
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
     * Get fee summary for dashboard
     */
    public function getFeeSummary(?int $userId = null): array
    {
        $query = PlatformFee::query();

        if ($userId) {
            $query->whereHas('invoice', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        return [
            'total_collected' => (float) (clone $query)->where('fee_status', 'paid')->sum('fee_amount'),
            'pending' => (clone $query)->where('fee_status', 'pending')->count(),
            'paid' => (clone $query)->where('fee_status', 'paid')->count(),
            'total_amount' => (float) (clone $query)->sum('fee_amount'),
        ];
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
