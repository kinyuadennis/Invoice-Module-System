<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\PlatformFee;
use App\Traits\FormatsInvoiceData;
use App\Traits\InvoiceStatusHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    use FormatsInvoiceData, InvoiceStatusHelper;

    /**
     * Get all dashboard data scoped by company
     * Uses lightweight caching (5 minutes) to improve performance.
     *
     * @param  int  $companyId  Company ID to scope data
     */
    public function getDashboardData(int $companyId): array
    {
        if (! $this->tablesExist()) {
            return $this->getEmptyData();
        }

        // Cache dashboard data for 5 minutes to reduce DB load
        // Cache key includes company_id to ensure data is scoped correctly
        return Cache::remember("dashboard_data_{$companyId}", 300, function () use ($companyId) {
            try {
                // Calculate stats once and reuse to avoid double calculation
                $stats = $this->getStats($companyId);

                return [
                    'stats' => $stats,
                    'recentInvoices' => $this->getRecentInvoices($companyId),
                    'statusDistribution' => $this->getStatusDistribution($companyId),
                    'alerts' => $this->getAlerts($companyId, $stats), // Pass stats to avoid recalculation
                    'statusBreakdown' => $this->getStatusBreakdown($companyId),
                ];
            } catch (\Exception $e) {
                return $this->getEmptyData();
            }
        });
    }

    /**
     * Get dashboard statistics scoped by company
     *
     * @param  int  $companyId  Company ID to scope statistics
     */
    protected function getStats(int $companyId): array
    {
        $invoiceQuery = Invoice::where('company_id', $companyId);

        $paidTotal = (float) (clone $invoiceQuery)->where('status', 'paid')->sum('grand_total');

        $currentMonthRevenue = (float) (clone $invoiceQuery)->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->sum('grand_total');

        $previousMonthRevenue = (float) (clone $invoiceQuery)->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ])
            ->sum('grand_total');

        $revenueChange = $previousMonthRevenue > 0
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : 0;

        $totalPlatformFees = Schema::hasTable('platform_fees')
            ? (float) PlatformFee::where('company_id', $companyId)->sum('fee_amount')
            : 0;

        $outstandingCount = (clone $invoiceQuery)->whereIn('status', ['draft', 'sent'])->count();
        $outstandingAmount = (float) (clone $invoiceQuery)->whereIn('status', ['draft', 'sent'])->sum('grand_total');
        $overdueCount = (clone $invoiceQuery)->where('status', 'overdue')->count();
        $overdueAmount = (float) (clone $invoiceQuery)->where('status', 'overdue')->sum('grand_total');
        $paidCount = (clone $invoiceQuery)->where('status', 'paid')->count();

        // Clients scoped to company
        $totalClients = Client::where('company_id', $companyId)->count();
        $activeClients = Client::where('company_id', $companyId)->has('invoices')->count();

        return [
            'totalRevenue' => $paidTotal,
            'revenueChange' => $revenueChange,
            'totalPlatformFees' => $totalPlatformFees,
            'outstanding' => $outstandingAmount,
            'outstandingCount' => $outstandingCount,
            'overdue' => $overdueAmount,
            'overdueCount' => $overdueCount,
            'paidCount' => $paidCount,
            'totalClients' => $totalClients,
            'activeClients' => $activeClients,
        ];
    }

    /**
     * Get recent invoices scoped by company
     *
     * @param  int  $companyId  Company ID to scope invoices
     */
    protected function getRecentInvoices(int $companyId): array
    {
        // Eager load all necessary relations to prevent N+1 queries
        $query = Invoice::where('company_id', $companyId)
            ->with(['client', 'company', 'invoiceItems'])
            ->latest();

        return $query->take(5)
            ->get()
            ->map(function (Invoice $invoice) {
                $data = $this->formatInvoiceForDisplay($invoice);

                // Only return needed fields for recent invoices list
                return [
                    'id' => $data['id'],
                    'invoice_number' => $data['invoice_number'],
                    'status' => $data['status'],
                    'total' => $data['total'],
                    'due_date' => $data['due_date'],
                    'client' => [
                        'name' => $data['client']['name'],
                    ],
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get status distribution for charts scoped by company
     *
     * @param  int  $companyId  Company ID to scope invoices
     */
    protected function getStatusDistribution(int $companyId): array
    {
        $statusColors = $this->getStatusColors();
        $allStatuses = $this->getInvoiceStatuses();

        $statusCounts = Invoice::where('company_id', $companyId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalInvoices = array_sum($statusCounts);

        return collect($allStatuses)->map(function (string $status) use ($statusCounts, $statusColors, $totalInvoices) {
            $count = (int) ($statusCounts[$status] ?? 0);
            $colors = $statusColors[$status] ?? $statusColors['default'];
            $percentage = $totalInvoices > 0
                ? round(($count / $totalInvoices) * 100)
                : 0;

            return [
                'name' => ucfirst($status),
                'count' => $count,
                'percentage' => $percentage,
                'color' => $colors['color'],
                'bgColor' => $colors['bgColor'],
            ];
        })->values()->toArray();
    }

    /**
     * Get status breakdown with counts and amounts
     *
     * @param  int  $companyId  Company ID to scope invoices
     */
    protected function getStatusBreakdown(int $companyId): array
    {
        $breakdown = [];
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

        foreach ($statuses as $status) {
            $query = Invoice::where('company_id', $companyId)->where('status', $status);
            $count = $query->count();
            $amount = (float) (clone $query)->sum('grand_total');

            $breakdown[$status] = [
                'count' => $count,
                'amount' => $amount,
            ];
        }

        return $breakdown;
    }

    /**
     * Get dashboard alerts scoped by company
     *
     * @param  int  $companyId  Company ID to scope invoices
     * @param  array|null  $stats  Pre-calculated stats to avoid recalculation (optional for backward compatibility)
     */
    protected function getAlerts(int $companyId, ?array $stats = null): array
    {
        $alerts = collect();

        // Use provided stats or calculate if not provided (backward compatibility)
        if ($stats === null) {
            $stats = $this->getStats($companyId);
        }

        if ($stats['overdueCount'] > 0) {
            $alerts->push([
                'id' => 'overdue-invoices',
                'type' => 'error',
                'message' => "{$stats['overdueCount']} invoice(s) are overdue ({$this->formatCurrency($stats['overdue'])} outstanding).",
                'link' => '/invoices?status=overdue',
            ]);
        }

        if ($stats['outstandingCount'] > 0) {
            $alerts->push([
                'id' => 'outstanding-invoices',
                'type' => 'warning',
                'message' => "{$stats['outstandingCount']} invoice(s) awaiting payment ({$this->formatCurrency($stats['outstanding'])}).",
                'link' => '/invoices?status=sent',
            ]);
        }

        return $alerts->values()->toArray();
    }

    /**
     * Get empty dashboard data when tables don't exist
     */
    protected function getEmptyData(): array
    {
        $statusColors = $this->getStatusColors();
        $allStatuses = $this->getInvoiceStatuses();

        $statusDistribution = collect($allStatuses)->map(function (string $status) use ($statusColors) {
            $colors = $statusColors[$status] ?? $statusColors['cancelled'];

            return [
                'name' => ucfirst($status),
                'count' => 0,
                'percentage' => 0,
                'color' => $colors['color'],
                'bgColor' => $colors['bgColor'],
            ];
        })->values()->toArray();

        return [
            'stats' => [
                'totalRevenue' => 0,
                'revenueChange' => 0,
                'totalPlatformFees' => 0,
                'outstanding' => 0,
                'outstandingCount' => 0,
                'overdue' => 0,
                'overdueCount' => 0,
                'paidCount' => 0,
                'totalClients' => 0,
                'activeClients' => 0,
            ],
            'recentInvoices' => [],
            'statusDistribution' => $statusDistribution,
            'alerts' => [],
        ];
    }

    /**
     * Check if required tables exist
     */
    protected function tablesExist(): bool
    {
        try {
            return Schema::hasTable('invoices') && Schema::hasTable('clients');
        } catch (\Exception $e) {
            return false;
        }
    }
}
