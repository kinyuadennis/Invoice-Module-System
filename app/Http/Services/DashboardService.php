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
     */
    public function getDashboardData(): array
    {
        if (!$this->tablesExist()) {
            return $this->getEmptyData();
        }

        try {
            return [
                'stats' => $this->getStats(),
                'recentInvoices' => $this->getRecentInvoices(),
                'statusDistribution' => $this->getStatusDistribution(),
                'alerts' => $this->getAlerts(),
            ];
        } catch (\Exception $e) {
            return $this->getEmptyData();
        }
    }

    /**
     * Get dashboard statistics
     */
    protected function getStats(): array
    {
        $paidTotal = (float) Invoice::where('status', 'paid')->sum('total');
        
        $currentMonthRevenue = (float) Invoice::where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(), 
                Carbon::now()->endOfMonth()
            ])
            ->sum('total');

        $previousMonthRevenue = (float) Invoice::where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(), 
                Carbon::now()->subMonth()->endOfMonth()
            ])
            ->sum('total');

        $revenueChange = $previousMonthRevenue > 0
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : 0;

        $totalPlatformFees = Schema::hasTable('platform_fees') 
            ? (float) PlatformFee::sum('fee_amount') 
            : 0;

        $outstandingCount = Invoice::whereIn('status', ['draft', 'sent'])->count();
        $outstandingAmount = (float) Invoice::whereIn('status', ['draft', 'sent'])->sum('total');
        $overdueCount = Invoice::where('status', 'overdue')->count();
        $overdueAmount = (float) Invoice::where('status', 'overdue')->sum('total');
        $paidCount = Invoice::where('status', 'paid')->count();
        $totalClients = Client::count();
        $activeClients = Client::has('invoices')->count();

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
     */
    protected function getRecentInvoices(): array
    {
        return Invoice::with('client')
            ->latest()
            ->take(5)
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
     */
    protected function getStatusDistribution(): array
    {
        $statusColors = $this->getStatusColors();
        $allStatuses = $this->getInvoiceStatuses();
        
        $statusCounts = Invoice::selectRaw('status, COUNT(*) as count')
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
     * Get dashboard alerts
     */
    protected function getAlerts(): array
    {
        $alerts = collect();
        $stats = $this->getStats();

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

