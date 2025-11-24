<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlatformFeeService;
use App\Models\PlatformFee;

class PlatformFeeController extends Controller
{
    protected $platformFeeService;

    public function __construct(PlatformFeeService $platformFeeService)
    {
        $this->platformFeeService = $platformFeeService;
    }

    /**
     * Generate a platform fee for an invoice.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = \App\Models\Invoice::findOrFail($request->invoice_id);

        $fee = $this->platformFeeService->generateFeeForInvoice($invoice);

        return response()->json(['platform_fee' => $fee], 201);
    }

    /**
     * Update the status of an existing platform fee.
     */
    public function updateStatus(Request $request, PlatformFee $platformFee)
    {
        $request->validate([
            'fee_status' => 'required|in:pending,paid,waived',
        ]);

        $updatedFee = $this->platformFeeService->updateFeeStatus($platformFee, $request->fee_status);

        return response()->json(['platform_fee' => $updatedFee]);
    }

    /**
     * List platform fees, optionally filtered by status.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $fees = $this->platformFeeService->getFeesByStatus($status);

        return response()->json(['platform_fees' => $fees]);
    }
}
