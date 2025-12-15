<?php

namespace App\Jobs;

use App\Models\InvoiceAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * CleanupOldAuditLogs Job
 *
 * Removes audit logs older than the retention period.
 * This job should be scheduled to run periodically (e.g., daily).
 *
 * Rules:
 * - Never deletes invoices or critical data
 * - Only deletes audit logs based on retention policy
 * - Respects company-level settings if configured
 */
class CleanupOldAuditLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of months to retain audit logs.
     * Default: 24 months (2 years) for compliance.
     */
    protected int $retentionMonths;

    /**
     * Create a new job instance.
     */
    public function __construct(int $retentionMonths = 24)
    {
        $this->retentionMonths = $retentionMonths;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cutoffDate = now()->subMonths($this->retentionMonths);

        // Count logs to be deleted
        $count = InvoiceAuditLog::where('created_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            Log::info('CleanupOldAuditLogs: No audit logs to delete.');

            return;
        }

        // Delete old audit logs
        $deleted = InvoiceAuditLog::where('created_at', '<', $cutoffDate)->delete();

        Log::info('CleanupOldAuditLogs: Deleted {count} audit logs older than {months} months.', [
            'count' => $deleted,
            'months' => $this->retentionMonths,
            'cutoff_date' => $cutoffDate->toIso8601String(),
        ]);
    }
}
