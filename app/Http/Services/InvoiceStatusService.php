<?php

namespace App\Http\Services;

use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceStatusService
{
    /**
     * Get all available invoice statuses.
     */
    public static function getStatuses(): array
    {
        return [
            'draft' => [
                'label' => 'Draft',
                'color' => 'gray',
                'description' => 'Invoice is being prepared and can be edited',
            ],
            'sent' => [
                'label' => 'Sent',
                'color' => 'blue',
                'description' => 'Invoice has been sent to the client',
            ],
            'paid' => [
                'label' => 'Paid',
                'color' => 'green',
                'description' => 'Invoice has been fully paid',
            ],
            'overdue' => [
                'label' => 'Overdue',
                'color' => 'red',
                'description' => 'Invoice is past its due date',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'color' => 'gray',
                'description' => 'Invoice has been cancelled/voided',
            ],
        ];
    }

    /**
     * Get status badge variant for UI components.
     */
    public static function getStatusVariant(string $status): string
    {
        return match (strtolower($status)) {
            'paid' => 'success',
            'sent' => 'info',
            'overdue' => 'danger',
            'draft' => 'default',
            'cancelled' => 'default',
            default => 'default',
        };
    }

    /**
     * Check if status transition is allowed.
     */
    public static function canTransition(string $fromStatus, string $toStatus): bool
    {
        $allowedTransitions = [
            'draft' => ['sent', 'cancelled'],
            'sent' => ['paid', 'overdue', 'cancelled'],
            'overdue' => ['paid', 'cancelled'],
            'paid' => [], // Paid is final
            'cancelled' => [], // Cancelled is final
        ];

        return in_array($toStatus, $allowedTransitions[$fromStatus] ?? []);
    }

    /**
     * Update invoice status with validation.
     */
    public function updateStatus(Invoice $invoice, string $newStatus, ?string $reason = null): bool
    {
        if (! self::canTransition($invoice->status, $newStatus)) {
            return false;
        }

        $invoice->update(['status' => $newStatus]);

        // Log status change (if audit logging is implemented)
        // InvoiceAuditService::logStatusChange($invoice, $newStatus, $reason);

        return true;
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(Invoice $invoice): bool
    {
        return $this->updateStatus($invoice, 'sent');
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): bool
    {
        return $this->updateStatus($invoice, 'paid');
    }

    /**
     * Mark invoice as cancelled/void.
     */
    public function markAsCancelled(Invoice $invoice, ?string $reason = null): bool
    {
        return $this->updateStatus($invoice, 'cancelled', $reason);
    }

    /**
     * Check and update overdue invoices.
     */
    public function checkAndUpdateOverdue(): int
    {
        $overdueCount = 0;
        $today = Carbon::today();

        Invoice::where('status', 'sent')
            ->where('due_date', '<', $today)
            ->whereNotNull('due_date')
            ->chunk(100, function ($invoices) use (&$overdueCount) {
                foreach ($invoices as $invoice) {
                    if ($this->updateStatus($invoice, 'overdue')) {
                        $overdueCount++;
                    }
                }
            });

        return $overdueCount;
    }

    /**
     * Get status color for Tailwind CSS.
     */
    public static function getStatusColor(string $status): string
    {
        return match (strtolower($status)) {
            'paid' => 'green',
            'sent' => 'blue',
            'overdue' => 'red',
            'draft' => 'gray',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}
