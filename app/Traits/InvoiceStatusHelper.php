<?php

namespace App\Traits;

trait InvoiceStatusHelper
{
    /**
     * Get all valid invoice statuses
     */
    protected function getInvoiceStatuses(): array
    {
        return ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
    }

    /**
     * Get status colors for UI display
     */
    protected function getStatusColors(): array
    {
        return [
            'draft' => ['color' => '#6B7280', 'bgColor' => '#6B7280'], // Gray 500
            'sent' => ['color' => '#3B82F6', 'bgColor' => '#3B82F6'], // Blue 500
            'paid' => ['color' => '#10B981', 'bgColor' => '#10B981'], // Green 500
            'overdue' => ['color' => '#EF4444', 'bgColor' => '#EF4444'], // Red 500
            'cancelled' => ['color' => '#9CA3AF', 'bgColor' => '#9CA3AF'], // Gray 400
            'default' => ['color' => '#9CA3AF', 'bgColor' => '#9CA3AF'], // Gray 400
        ];
    }

    /**
     * Get status color for a specific status
     */
    protected function getStatusColor(string $status): array
    {
        $colors = $this->getStatusColors();
        return $colors[$status] ?? $colors['default'];
    }

    /**
     * Format currency value
     */
    protected function formatCurrency(float $value): string
    {
        return '$' . number_format($value, 2);
    }
}
