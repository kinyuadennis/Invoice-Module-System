<?php

namespace App\Console\Commands\Payments;

use App\Config\PaymentConstants;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Process Payment Timeouts Command
 *
 * Scheduled command to mark payments as TIMEOUT if no confirmation received
 * within the timeout period (5 minutes per blueprint).
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 4
 */
class ProcessPaymentTimeouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-timeouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark payments as TIMEOUT if no confirmation received within timeout period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing payment timeouts...');

        $timeoutThreshold = now()->subSeconds(PaymentConstants::PAYMENT_TIMEOUT_SECONDS);

        // Find payments in INITIATED status older than timeout threshold
        $timedOutPayments = Payment::where('status', PaymentConstants::PAYMENT_STATUS_INITIATED)
            ->where('created_at', '<=', $timeoutThreshold)
            ->get();

        if ($timedOutPayments->isEmpty()) {
            $this->info('No payments timed out.');

            return Command::SUCCESS;
        }

        $this->info("Found {$timedOutPayments->count()} payment(s) to mark as TIMEOUT.");

        $processed = 0;

        foreach ($timedOutPayments as $payment) {
            try {
                // Mark payment as TIMEOUT (enforces terminal state invariant)
                $payment->markAsTimeout();

                $this->line("  → Payment ID {$payment->id} marked as TIMEOUT");

                Log::info('Payment marked as TIMEOUT', [
                    'payment_id' => $payment->id,
                    'gateway' => $payment->gateway,
                    'created_at' => $payment->created_at,
                ]);

                $processed++;
            } catch (\Exception $e) {
                $this->error("  → Failed to mark payment ID {$payment->id} as TIMEOUT: {$e->getMessage()}");
                Log::error('Failed to mark payment as TIMEOUT', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Timeout processing complete. Processed: {$processed}");

        return Command::SUCCESS;
    }
}
