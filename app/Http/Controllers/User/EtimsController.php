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
     * Submit invoice to eTIMS API.
     */
    public function submit(Invoice $invoice)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify invoice belongs to company
        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        try {
            $result = $this->etimsService->submitToEtims($invoice);

            if ($result['success']) {
                return back()->with('success', $result['message'] ?? 'Invoice submitted to eTIMS successfully');
            }

            return back()->withErrors(['error' => $result['error'] ?? 'Failed to submit to eTIMS']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to submit to eTIMS: '.$e->getMessage()]);
        }
    }
}
