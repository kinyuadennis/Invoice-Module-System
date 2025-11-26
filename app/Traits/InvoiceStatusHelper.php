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
            'draft' => ['color' => 'bg-gray-400', 'bgColor' => 'bg-gray-500'],
            'sent' => ['color' => 'bg-blue-400', 'bgColor' => 'bg-blue-500'],
            'paid' => ['color' => 'bg-green-400', 'bgColor' => 'bg-green-500'],
            'overdue' => ['color' => 'bg-red-400', 'bgColor' => 'bg-red-500'],
            'cancelled' => ['color' => 'bg-gray-300', 'bgColor' => 'bg-gray-400'],
            'default' => ['color' => 'bg-gray-300', 'bgColor' => 'bg-gray-400'],
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

