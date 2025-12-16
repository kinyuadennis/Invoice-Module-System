<?php

namespace App\Http\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Get comprehensive dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return [
            'overview' => $this->getOverviewStats(),
            'revenue' => $this->getRevenueStats($today, $thisMonth, $lastMonth, $thisYear),
            'invoices' => $this->getInvoiceStats($today, $thisMonth),
            'companies' => $this->getCompanyStats($today, $thisMonth),
            'users' => $this->getUserStats($today, $thisMonth),
            'monthlyTrends' => $this->getMonthlyTrends(),
            'invoiceStatusDistribution' => $this->getInvoiceStatusDistribution(),
        ];
    }

    /**
     * Get overview statistics.
     */
    protected function getOverviewStats(): array
    {
        return [
            'totalCompanies' => Company::count(),
            'totalUsers' => User::count(),
            'totalInvoices' => Invoice::count(),
            'totalRevenue' => Invoice::where('status', 'paid')->sum('grand_total'),
            'pendingInvoices' => Invoice::where('status', 'sent')->count(),
            'overdueInvoices' => Invoice::where('status', 'overdue')->count(),
            'paidInvoices' => Invoice::where('status', 'paid')->count(),
        ];
    }

    /**
     * Get revenue statistics.
     */
    protected function getRevenueStats(Carbon $today, Carbon $thisMonth, Carbon $lastMonth, Carbon $thisYear): array
    {
        $todayRevenue = Invoice::where('status', 'paid')
            ->whereDate('updated_at', $today)
            ->sum('grand_total');

        $thisMonthRevenue = Invoice::where('status', 'paid')
            ->where('updated_at', '>=', $thisMonth)
            ->sum('grand_total');

        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereBetween('updated_at', [$lastMonth, $thisMonth])
            ->sum('grand_total');

        $thisYearRevenue = Invoice::where('status', 'paid')
            ->where('updated_at', '>=', $thisYear)
            ->sum('grand_total');

        $monthOverMonth = $lastMonthRevenue > 0
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        return [
            'today' => (float) $todayRevenue,
            'thisMonth' => (float) $thisMonthRevenue,
            'lastMonth' => (float) $lastMonthRevenue,
            'thisYear' => (float) $thisYearRevenue,
            'monthOverMonth' => round($monthOverMonth, 2),
        ];
    }

    /**
     * Get invoice statistics.
     */
    protected function getInvoiceStats(Carbon $today, Carbon $thisMonth): array
    {
        $todayInvoices = Invoice::whereDate('created_at', $today)->count();
        $thisMonthInvoices = Invoice::where('created_at', '>=', $thisMonth)->count();

        return [
            'today' => $todayInvoices,
            'thisMonth' => $thisMonthInvoices,
        ];
    }

    /**
     * Get company statistics.
     */
    protected function getCompanyStats(Carbon $today, Carbon $thisMonth): array
    {
        $todayCompanies = Company::whereDate('created_at', $today)->count();
        $thisMonthCompanies = Company::where('created_at', '>=', $thisMonth)->count();

        return [
            'today' => $todayCompanies,
            'thisMonth' => $thisMonthCompanies,
        ];
    }

    /**
     * Get user statistics.
     */
    protected function getUserStats(Carbon $today, Carbon $thisMonth): array
    {
        $todayUsers = User::whereDate('created_at', $today)->count();
        $thisMonthUsers = User::where('created_at', '>=', $thisMonth)->count();

        return [
            'today' => $todayUsers,
            'thisMonth' => $thisMonthUsers,
        ];
    }

    /**
     * Get monthly revenue trends for the last 12 months.
     */
    protected function getMonthlyTrends(): array
    {
        $trends = [];
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthStart = $startDate->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();

            $revenue = Invoice::where('status', 'paid')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->sum('grand_total');

            $count = Invoice::whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $trends[] = [
                'month' => $monthStart->format('M Y'),
                'revenue' => (float) $revenue,
                'invoices' => $count,
            ];
        }

        return $trends;
    }

    /**
     * Get invoice status distribution.
     */
    protected function getInvoiceStatusDistribution(): array
    {
        return Invoice::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            })
            ->toArray();
    }

    /**
     * Get top companies by revenue.
     */
    public function getTopCompanies(int $limit = 10): array
    {
        return Company::with('owner')
            ->withCount(['users', 'clients', 'invoices'])
            ->get()
            ->map(function ($company) {
                $revenue = $company->invoices()
                    ->where('status', 'paid')
                    ->sum('grand_total');

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo' => $company->logo,
                    'revenue' => (float) $revenue,
                    'invoices_count' => $company->invoices_count,
                    'users_count' => $company->users_count,
                    'clients_count' => $company->clients_count,
                    'owner' => $company->owner ? [
                        'id' => $company->owner->id,
                        'name' => $company->owner->name,
                    ] : null,
                ];
            })
            ->sortByDesc('revenue')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
