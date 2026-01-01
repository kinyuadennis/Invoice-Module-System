<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\EstimateAccessTokenService;
use App\Services\PdfEstimateRenderer;

class EstimateController extends Controller
{
    public function __construct(
        private EstimateAccessTokenService $tokenService,
        private PdfEstimateRenderer $pdfRenderer
    ) {}

    /**
     * Show estimate using access token.
     */
    public function show(string $token)
    {
        $accessToken = $this->tokenService->validateToken($token);

        if (! $accessToken || ! $accessToken->isValid()) {
            return view('customer.invalid-token', [
                'message' => 'This estimate link is invalid or has expired.',
            ]);
        }

        $estimate = $accessToken->estimate;

        $estimate->load(['company', 'client', 'items']);

        return view('customer.estimates.show', [
            'estimate' => $estimate,
            'accessToken' => $accessToken,
        ]);
    }

    /**
     * Download estimate PDF using access token.
     */
    public function downloadPdf(string $token)
    {
        $accessToken = $this->tokenService->validateToken($token);

        if (! $accessToken || ! $accessToken->isValid()) {
            abort(403, 'Invalid or expired access token');
        }

        $estimate = $accessToken->estimate;

        // Render PDF
        try {
            $pdfContent = $this->pdfRenderer->render($estimate);

            // Generate filename
            $filename = 'estimate-'.($estimate->full_number ?? $estimate->estimate_number ?? $estimate->estimate_reference ?? $estimate->id).'.pdf';

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');

        } catch (\Exception $e) {
            \Log::error('Customer estimate PDF generation error', [
                'estimate_id' => $estimate->id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to generate PDF');
        }
    }
}
