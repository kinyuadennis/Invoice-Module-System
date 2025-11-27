<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\PlatformFee;
use App\Traits\FormatsInvoiceData;
use App\Traits\InvoiceStatusHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    use FormatsInvoiceData, InvoiceStatusHelper;

    /**
     * Get all dashboard data
     *
     * @param  int|null  $userId  If provided, scope data to this user. If null, get all data (admin).
     */
    public function getDashboardData(?int $userId = null): array
    {
        if (! $this->tablesExist()) {
            return $this->getEmptyData();
        }

        try {
            return [
                'stats' => $this->getStats($userId),
                'recentInvoices' => $this->getRecentInvoices($userId),
                'statusDistribution' => $this->getStatusDistribution($userId),
                'alerts' => $this->getAlerts($userId),
            ];
        } catch (\Exception $e) {
            return $this->getEmptyData();
        }
    }

    /**
     * Get dashboard statistics
     *
     * @param  int|null  $userId  If provided, scope to this user's invoices
     */
    protected function getStats(?int $userId = null): array
    {
        $invoiceQuery = Invoice::query();
        if ($userId) {
            $invoiceQuery->where('user_id', $userId);
        }

        $paidTotal = (float) (clone $invoiceQuery)->where('status', 'paid')->sum('total');

        $currentMonthRevenue = (float) (clone $invoiceQuery)->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->sum('total');

        $previousMonthRevenue = (float) (clone $invoiceQuery)->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ])
            ->sum('total');

        $revenueChange = $previousMonthRevenue > 0
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : 0;

        $totalPlatformFees = Schema::hasTable('platform_fees')
            ? (float) PlatformFee::sum('fee_amount')
            : 0;

        $outstandingCount = (clone $invoiceQuery)->whereIn('status', ['draft', 'sent'])->count();
        $outstandingAmount = (float) (clone $invoiceQuery)->whereIn('status', ['draft', 'sent'])->sum('total');
        $overdueCount = (clone $invoiceQuery)->where('status', 'overdue')->count();
        $overdueAmount = (float) (clone $invoiceQuery)->where('status', 'overdue')->sum('total');
        $paidCount = (clone $invoiceQuery)->where('status', 'paid')->count();

        // Clients are shared, but we can count clients with invoices for this user
        if ($userId) {
            $totalClients = Client::whereHas('invoices', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count();
            $activeClients = $totalClients; // Same for user scope
        } else {
            $totalClients = Client::count();
            $activeClients = Client::has('invoices')->count();
        }

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
     * Get recent invoices
     *
     * @param  int|null  $userId  If provided, scope to this user's invoices
     */
    protected function getRecentInvoices(?int $userId = null): array
    {
        $query = Invoice::with('client')->latest();
        if ($userId) {
            $query->where('user_id', $userId);
        }

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
     * Get status distribution for charts
     *
     * @param  int|null  $userId  If provided, scope to this user's invoices
     */
    protected function getStatusDistribution(?int $userId = null): array
    {
        $statusColors = $this->getStatusColors();
        $allStatuses = $this->getInvoiceStatuses();

        $query = Invoice::selectRaw('status, COUNT(*) as count');
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $statusCounts = $query->groupBy('status')
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
     * Get dashboard alerts
     *
     * @param  int|null  $userId  If provided, scope to this user's invoices
     */
    protected function getAlerts(?int $userId = null): array
    {
        $alerts = collect();
        $stats = $this->getStats($userId);

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
