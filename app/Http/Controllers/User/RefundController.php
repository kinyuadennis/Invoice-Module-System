<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Http\Services\RefundService;
use App\Models\Invoice;
use App\Models\Refund;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RefundController extends Controller
{
    public function __construct(
        protected RefundService $refundService
    ) {}

    /**
     * Display a listing of refunds for an invoice.
     */
    public function index(Request $request, $invoiceId)
    {
        $companyId = CurrentCompanyService::requireId();

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['refunds.payment', 'refunds.user', 'payments'])
            ->findOrFail($invoiceId);

        $refunds = $invoice->refunds()
            ->with(['payment', 'user'])
            ->latest()
            ->get()
            ->map(function ($refund) {
                return $this->refundService->formatRefundForDisplay($refund);
            });

        $refundSummary = $this->refundService->getRefundSummary($invoice);

        // Get payments with available refund amounts
        $payments = $invoice->payments()
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'refunded_amount' => (float) ($payment->refunded_amount ?? 0),
                ];
            });

        return response()->json([
            'refunds' => $refunds,
            'summary' => $refundSummary,
            'payments' => $payments,
        ]);
    }

    /**
     * Store a newly created refund.
     */
    public function store(StoreRefundRequest $request, $invoiceId)
    {
        $companyId = CurrentCompanyService::requireId();

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['payments', 'refunds'])
            ->findOrFail($invoiceId);

        try {
            $refund = $this->refundService->createRefund($invoice, $request);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return response()->json([
                'success' => true,
                'message' => 'Refund created successfully.',
                'refund' => $this->refundService->formatRefundForDisplay($refund->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create refund: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process a pending refund.
     */
    public function process(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $refund = Refund::where('company_id', $companyId)
            ->with(['invoice', 'payment'])
            ->findOrFail($id);

        try {
            $refund = $this->refundService->processRefund($refund);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully.',
                'refund' => $this->refundService->formatRefundForDisplay($refund->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a pending refund.
     */
    public function cancel(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $refund = Refund::where('company_id', $companyId)
            ->findOrFail($id);

        try {
            $refund = $this->refundService->cancelRefund($refund);

            return response()->json([
                'success' => true,
                'message' => 'Refund cancelled successfully.',
                'refund' => $this->refundService->formatRefundForDisplay($refund->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel refund: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified refund.
     */
    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $refund = Refund::where('company_id', $companyId)
            ->with(['invoice', 'payment', 'user'])
            ->findOrFail($id);

        return response()->json([
            'refund' => $this->refundService->formatRefundForDisplay($refund),
        ]);
    }
}
