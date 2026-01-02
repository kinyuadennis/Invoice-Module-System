<?php

namespace App\Jobs\Payments;

use App\Config\GatewayConstants;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process Webhook Retry Job
 *
 * Retries failed webhook processing with exponential backoff.
 * Implements retry logic per blueprint section 5 failure playbook.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 3.4
 */
class ProcessWebhookRetry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = GatewayConstants::WEBHOOK_MAX_RETRIES;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $gatewayName,
        public GatewayCallbackPayload $payload,
        public int $attemptNumber = 1
    ) {
        // Calculate delay based on attempt number (exponential backoff)
        $delay = GatewayConstants::RETRY_BACKOFF_BASE_SECONDS * (2 ** ($attemptNumber - 1));
        $this->delay($delay);
    }

    /**
     * Execute the job.
     */
    public function handle(SubscriptionService $subscriptionService): void
    {
        try {
            Log::info('Retrying webhook processing', [
                'gateway' => $this->gatewayName,
                'attempt' => $this->attemptNumber,
                'max_retries' => GatewayConstants::WEBHOOK_MAX_RETRIES,
            ]);

            // Attempt to confirm payment via SubscriptionService
            $payment = $subscriptionService->confirmPayment($this->gatewayName, $this->payload);

            if ($payment) {
                Log::info('Webhook retry succeeded', [
                    'gateway' => $this->gatewayName,
                    'payment_id' => $payment->id,
                    'attempt' => $this->attemptNumber,
                ]);
            } else {
                // If still failing and we have retries left, dispatch again
                if ($this->attemptNumber < GatewayConstants::WEBHOOK_MAX_RETRIES) {
                    Log::warning('Webhook retry failed, will retry again', [
                        'gateway' => $this->gatewayName,
                        'attempt' => $this->attemptNumber,
                        'next_attempt' => $this->attemptNumber + 1,
                    ]);

                    // Dispatch next retry
                    self::dispatch(
                        $this->gatewayName,
                        $this->payload,
                        $this->attemptNumber + 1
                    );
                } else {
                    Log::error('Webhook retry exhausted all attempts', [
                        'gateway' => $this->gatewayName,
                        'final_attempt' => $this->attemptNumber,
                        'max_retries' => GatewayConstants::WEBHOOK_MAX_RETRIES,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Webhook retry job failed with exception', [
                'gateway' => $this->gatewayName,
                'attempt' => $this->attemptNumber,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger Laravel's retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook retry job permanently failed', [
            'gateway' => $this->gatewayName,
            'attempt' => $this->attemptNumber,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
