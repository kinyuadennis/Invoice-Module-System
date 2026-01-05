<?php

namespace App\Http\Services;

// ... (imports)

class DashboardService
{
    // ... (existing methods)

    /**
     * Get revenue data for the last 6 months for charting
     */
    public function getRevenueChartData(int $companyId): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = \Carbon\Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $revenue = \App\Models\Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ])
                ->sum('grand_total');

            $data[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
