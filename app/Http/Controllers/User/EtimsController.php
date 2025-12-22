<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\EtimsService;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class EtimsController extends Controller
{
    public function __construct(
        private EtimsService $etimsService
    ) {}

    /**
     * Export invoice for eTIMS submission.
     */
    public function export(Invoice $invoice, Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify invoice belongs to company
        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        // Check if invoice is eTIMS compliant
        if (! $this->etimsService->isCompliant($invoice)) {
            return back()->withErrors(['error' => 'Invoice is not eTIMS compliant. Please ensure KRA PIN is configured.']);
        }

        $format = $request->get('format', 'json');

        try {
            $exportData = $this->etimsService->exportForEtims($invoice, $format);

            $filename = 'invoice_'.$invoice->invoice_number.'_etims_'.date('Y-m-d').'.'.$format;
            $contentType = $format === 'xml' ? 'application/xml' : 'application/json';

            return Response::make($exportData, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to export invoice: '.$e->getMessage()]);
        }
    }

    /**
     * Generate QR code for invoice.
     */
    public function generateQrCode(Invoice $invoice)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify invoice belongs to company
        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        try {
            $result = $this->etimsService->generateLocalQrCode($invoice);

            if ($result['success']) {
                return back()->with('success', 'QR code generated successfully');
            }

            return back()->withErrors(['error' => $result['error'] ?? 'Failed to generate QR code']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate QR code: '.$e->getMessage()]);
        }
    }

    /**
     * Pre-validate invoice before eTIMS submission.
     */
    public function preValidate(Invoice $invoice, Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify invoice belongs to company
        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        try {
            $errors = $this->etimsService->preValidateInvoice($invoice);

            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => empty($errors),
                    'errors' => $errors,
                    'is_compliant' => $this->etimsService->isCompliant($invoice),
                ]);
            }

            if (empty($errors)) {
                return back()->with('success', 'Invoice is valid for eTIMS submission');
            }

            return back()->withErrors(['validation' => $errors]);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => false,
                    'errors' => ['An error occurred during validation: '.$e->getMessage()],
                ], 500);
            }

            return back()->withErrors(['error' => 'Validation failed: '.$e->getMessage()]);
        }
    }

    /**
     * Submit invoice to eTIMS API with validation.
     */
    public function submit(Invoice $invoice)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify invoice belongs to company
        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        try {
            // Use validation-enabled submission
            $result = $this->etimsService->submitToEtimsWithValidation($invoice);

            if ($result['success']) {
                return back()->with('success', $result['message'] ?? 'Invoice submitted to eTIMS successfully');
            }

            // Handle validation errors
            if (isset($result['errors']) && ! empty($result['errors'])) {
                return back()->withErrors(['validation' => $result['errors']]);
            }

            return back()->withErrors(['error' => $result['error'] ?? $result['message'] ?? 'Failed to submit to eTIMS']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to submit to eTIMS: '.$e->getMessage()]);
        }
    }
}
