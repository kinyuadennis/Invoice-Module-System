<?php

namespace App\Jobs;

use App\Http\Services\EstimateAccessTokenService;
use App\Mail\EstimateSentMail;
use App\Models\Estimate;
use App\Services\CurrentCompanyService;
use App\Services\PdfEstimateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEstimateEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Estimate $estimate
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        PdfEstimateRenderer $pdfRenderer
    ): void {
        try {
            // Ensure estimate has required relationships
            $this->estimate->load(['client', 'items', 'company']);

            // Validate estimate can be sent
            if (! $this->estimate->client || ! $this->estimate->client->email) {
                throw new \Exception('Estimate client does not have an email address.');
            }

            // Set company context for PDF generation
            CurrentCompanyService::setId($this->estimate->company_id);

            // Generate PDF
            $pdfPath = $this->generatePdf($pdfRenderer);

            // Generate access token for customer portal
            $tokenService = app(EstimateAccessTokenService::class);
            $accessToken = $tokenService->generateToken($this->estimate, 30); // 30 days expiry
            $accessUrl = $tokenService->getAccessUrl($accessToken);

            // Send email
            Mail::to($this->estimate->client->email)
                ->send(new EstimateSentMail($this->estimate, $pdfPath, $accessUrl));

            // Status is already updated to 'sent' in the controller when job is dispatched
            // Refresh the estimate to ensure we have the latest status
            $this->estimate->refresh();

            // Log client activity
            $activityService = app(\App\Http\Services\ClientActivityService::class);
            $activityService->logEstimateSent(
                $this->estimate->client,
                $this->estimate->id,
                $this->estimate->full_number ?? $this->estimate->estimate_number ?? $this->estimate->estimate_reference ?? 'EST-'.str_pad($this->estimate->id, 3, '0', STR_PAD_LEFT)
            );

            // Clean up temporary PDF file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send estimate email', [
                'estimate_id' => $this->estimate->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate PDF for the estimate.
     */
    protected function generatePdf(PdfEstimateRenderer $pdfRenderer): string
    {
        // Render PDF
        $pdfContent = $pdfRenderer->render($this->estimate);

        // Save to temporary file
        $tempPath = storage_path('app/temp/estimate-'.$this->estimate->id.'-'.time().'.pdf');
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        file_put_contents($tempPath, $pdfContent);

        return $tempPath;
    }
}
