<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
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
        // Note: statusDistribution is fetched fresh (not cached) to always show current invoice data
        $cachedData = Cache::remember("dashboard_data_{$companyId}", 300, function () use ($companyId) {
            try {
                // Calculate stats once and reuse to avoid double calculation
                $stats = $this->getStats($companyId);

                $insightsService = new BusinessInsightsService;
                $insights = $insightsService->getAllInsights($companyId);

                return [
                    'stats' => $stats,
                    'recentInvoices' => $this->getRecentInvoices($companyId),
                    'alerts' => $this->getAlerts($companyId, $stats), // Pass stats to avoid recalculation
                    'statusBreakdown' => $this->getStatusBreakdown($companyId),
                    'insights' => $insights,
                    'expenseStats' => $this->getExpenseStats($companyId),
                    'inventoryAlerts' => $this->getInventoryAlerts($companyId),
                    'cashFlow' => $this->getCashFlow($companyId),
                    // New metrics and features
                    'additionalMetrics' => $this->getAdditionalMetrics($companyId),
                    'complianceData' => $this->getComplianceData($companyId),
                    'paymentMethodBreakdown' => $this->getPaymentMethodBreakdown($companyId),
                    'cashFlowForecast' => $this->getCashFlowForecast($companyId),
                    'bankReconciliationStatus' => $this->getBankReconciliationStatus($companyId),
                    'recentActivity' => $this->getRecentActivity($companyId),
                    'fraudIndicators' => $this->getFraudIndicators($companyId),
                    'aiInsights' => $this->getAiInsights($companyId),
                    'multiCompanyOverview' => $this->getMultiCompanyOverview($companyId),
                ];
            } catch (\Exception $e) {
                return $this->getEmptyData();
            }
        });

        // Always fetch fresh status distribution to reflect current invoice data
        $cachedData['statusDistribution'] = $this->getStatusDistribution($companyId);

        return $cachedData;
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

        // Calculate Average Invoice Value
        $totalInvoices = (clone $invoiceQuery)->where('status', '!=', 'draft')->count();
        $averageInvoiceValue = $totalInvoices > 0
            ? (float) (clone $invoiceQuery)->where('status', '!=', 'draft')->avg('grand_total')
            : 0.0;

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
            'averageInvoiceValue' => $averageInvoiceValue,
        ];
    }

    /**
     * Get expense statistics for dashboard
     *
     * @param  int  $companyId  Company ID to scope expenses
     */
    protected function getExpenseStats(int $companyId): array
    {
        if (! Schema::hasTable('expenses')) {
            return [
                'total_expenses' => 0,
                'this_month_expenses' => 0,
                'expense_count' => 0,
                'tax_deductible' => 0,
                'this_month_count' => 0,
            ];
        }

        $expenseQuery = Expense::where('company_id', $companyId);

        $totalExpenses = (float) (clone $expenseQuery)->sum('amount');
        $expenseCount = (clone $expenseQuery)->count();

        $thisMonthExpenses = (float) (clone $expenseQuery)
            ->whereBetween('expense_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->sum('amount');

        $thisMonthCount = (clone $expenseQuery)
            ->whereBetween('expense_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();

        $taxDeductible = (float) (clone $expenseQuery)
            ->where('tax_deductible', true)
            ->sum('amount');

        return [
            'total_expenses' => $totalExpenses,
            'this_month_expenses' => $thisMonthExpenses,
            'expense_count' => $expenseCount,
            'tax_deductible' => $taxDeductible,
            'this_month_count' => $thisMonthCount,
        ];
    }

    /**
     * Get inventory alerts (low stock and out of stock items)
     *
     * @param  int  $companyId  Company ID to scope inventory
     */
    protected function getInventoryAlerts(int $companyId): array
    {
        if (! Schema::hasTable('inventory_items')) {
            return [
                'low_stock' => [],
                'out_of_stock' => [],
                'low_stock_count' => 0,
                'out_of_stock_count' => 0,
            ];
        }

        $inventoryQuery = InventoryItem::where('company_id', $companyId)
            ->where('track_stock', true)
            ->where('is_active', true);

        $lowStockItems = (clone $inventoryQuery)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0)
            ->with(['item'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->item?->name ?? $item->name ?? 'Unknown',
                    'sku' => $item->sku,
                    'current_stock' => (float) $item->current_stock,
                    'minimum_stock' => (float) $item->minimum_stock,
                ];
            })
            ->toArray();

        $outOfStockItems = (clone $inventoryQuery)
            ->where('current_stock', '<=', 0)
            ->with(['item'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->item?->name ?? $item->name ?? 'Unknown',
                    'sku' => $item->sku,
                    'current_stock' => (float) $item->current_stock,
                ];
            })
            ->toArray();

        return [
            'low_stock' => $lowStockItems,
            'out_of_stock' => $outOfStockItems,
            'low_stock_count' => count($lowStockItems),
            'out_of_stock_count' => count($outOfStockItems),
        ];
    }

    /**
     * Get cash flow metrics (inflow, outflow, net cash flow)
     *
     * @param  int  $companyId  Company ID to scope cash flow
     */
    protected function getCashFlow(int $companyId): array
    {
        // Cash Inflow: Payments received (this month)
        $inflowThisMonth = 0;
        if (Schema::hasTable('payments')) {
            $inflowThisMonth = (float) Payment::where('company_id', $companyId)
                ->whereBetween('payment_date', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ])
                ->sum('amount');
        }

        // Cash Outflow: Expenses paid (this month)
        $outflowThisMonth = 0;
        if (Schema::hasTable('expenses')) {
            $outflowThisMonth = (float) Expense::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereBetween('expense_date', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ])
                ->sum('amount');
        }

        $netCashFlow = $inflowThisMonth - $outflowThisMonth;

        // Calculate previous month for comparison
        $inflowLastMonth = 0;
        if (Schema::hasTable('payments')) {
            $inflowLastMonth = (float) Payment::where('company_id', $companyId)
                ->whereBetween('payment_date', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth(),
                ])
                ->sum('amount');
        }

        $outflowLastMonth = 0;
        if (Schema::hasTable('expenses')) {
            $outflowLastMonth = (float) Expense::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereBetween('expense_date', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth(),
                ])
                ->sum('amount');
        }

        $netCashFlowLastMonth = $inflowLastMonth - $outflowLastMonth;
        $cashFlowChange = $netCashFlowLastMonth != 0
            ? round((($netCashFlow - $netCashFlowLastMonth) / abs($netCashFlowLastMonth)) * 100, 1)
            : 0;

        return [
            'inflow_this_month' => $inflowThisMonth,
            'outflow_this_month' => $outflowThisMonth,
            'net_cash_flow' => $netCashFlow,
            'cash_flow_change' => $cashFlowChange,
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
                'averageInvoiceValue' => 0,
            ],
            'recentInvoices' => [],
            'statusDistribution' => $statusDistribution,
            'alerts' => [],
            'expenseStats' => [
                'total_expenses' => 0,
                'this_month_expenses' => 0,
                'expense_count' => 0,
                'tax_deductible' => 0,
                'this_month_count' => 0,
            ],
            'inventoryAlerts' => [
                'low_stock' => [],
                'out_of_stock' => [],
                'low_stock_count' => 0,
                'out_of_stock_count' => 0,
            ],
            'cashFlow' => [
                'inflow_this_month' => 0,
                'outflow_this_month' => 0,
                'net_cash_flow' => 0,
                'cash_flow_change' => 0,
            ],
            'additionalMetrics' => [
                'invoiceConversionRate' => 0,
                'paymentSuccessRate' => 0,
                'totalEstimates' => 0,
                'convertedEstimates' => 0,
                'paidOnTimeCount' => 0,
                'totalSentInvoices' => 0,
            ],
            'complianceData' => [
                'etimsComplianceRate' => 0,
                'etimsSubmittedCount' => 0,
                'totalInvoicesForCompliance' => 0,
                'duplicateCount' => 0,
                'recentEtimsSubmissions' => 0,
            ],
            'paymentMethodBreakdown' => [
                'breakdown' => [],
                'total_methods' => 0,
                'total_payments' => 0,
                'total_amount' => 0,
            ],
            'cashFlowForecast' => [],
            'bankReconciliationStatus' => [
                'is_enabled' => false,
                'reconciled_percentage' => 0,
                'matched_payments' => 0,
                'total_payments' => 0,
                'pending_transactions' => 0,
            ],
            'recentActivity' => [],
            'fraudIndicators' => [
                'flagged_count' => 0,
                'total_reviewed' => 0,
                'average_fraud_score' => 0,
                'requires_review' => 0,
            ],
            'multiCompanyOverview' => [
                'total_companies' => 0,
                'total_revenue' => 0,
                'total_invoices' => 0,
                'companies' => [],
            ],
            'aiInsights' => [
                'predicted_revenue_next_month' => 0,
                'revenue_trend' => 'stable',
                'recommendations' => [],
                'risk_alerts' => [],
            ],
            'insights' => [
                'dso' => 0,
                'dso_90' => 0,
                'invoice_aging' => [
                    '0-30' => ['count' => 0, 'amount' => 0.0],
                    '31-60' => ['count' => 0, 'amount' => 0.0],
                    '61-90' => ['count' => 0, 'amount' => 0.0],
                    '90+' => ['count' => 0, 'amount' => 0.0],
                ],
                'top_clients' => [],
                'avg_payment_time' => null,
                'revenue_trends' => [],
            ],
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

    /**
     * Get additional metrics (Invoice Conversion Rate, Payment Success Rate)
     *
     * @param  int  $companyId  Company ID to scope metrics
     */
    protected function getAdditionalMetrics(int $companyId): array
    {
        // Invoice Conversion Rate (Estimates converted to invoices)
        $totalEstimates = Schema::hasTable('estimates')
            ? \App\Models\Estimate::where('company_id', $companyId)->count()
            : 0;
        $convertedEstimates = Schema::hasTable('estimates')
            ? \App\Models\Estimate::where('company_id', $companyId)->where('status', 'converted')->count()
            : 0;
        $invoiceConversionRate = $totalEstimates > 0
            ? round(($convertedEstimates / $totalEstimates) * 100, 1)
            : 0.0;

        // Payment Success Rate (Invoices paid on time vs overdue)
        $invoiceQuery = Invoice::where('company_id', $companyId);
        $totalSentInvoices = (clone $invoiceQuery)->whereIn('status', ['sent', 'paid', 'overdue'])->count();

        // Get paid invoices and check if paid before or on due date
        $paidInvoices = (clone $invoiceQuery)
            ->where('status', 'paid')
            ->whereNotNull('due_date')
            ->with('payments')
            ->get();

        $paidOnTime = $paidInvoices->filter(function ($invoice) {
            $payment = $invoice->payments->first();
            if ($payment && $payment->paid_at) {
                return Carbon::parse($payment->paid_at)->lte(Carbon::parse($invoice->due_date));
            }

            // Fallback: if no payment date, check updated_at
            return Carbon::parse($invoice->updated_at)->lte(Carbon::parse($invoice->due_date));
        })->count();
        $paymentSuccessRate = $totalSentInvoices > 0
            ? round(($paidOnTime / $totalSentInvoices) * 100, 1)
            : 0.0;

        return [
            'invoiceConversionRate' => $invoiceConversionRate,
            'paymentSuccessRate' => $paymentSuccessRate,
            'totalEstimates' => $totalEstimates,
            'convertedEstimates' => $convertedEstimates,
            'paidOnTimeCount' => $paidOnTime,
            'totalSentInvoices' => $totalSentInvoices,
        ];
    }

    /**
     * Get compliance data (eTIMS status, duplicate detection)
     *
     * @param  int  $companyId  Company ID to scope compliance data
     */
    protected function getComplianceData(int $companyId): array
    {
        $invoiceQuery = Invoice::where('company_id', $companyId)->where('status', '!=', 'draft');
        $totalInvoices = $invoiceQuery->count();

        // eTIMS Compliance Status
        $etimsSubmitted = (clone $invoiceQuery)->whereNotNull('etims_submitted_at')->count();
        $etimsComplianceRate = $totalInvoices > 0
            ? round(($etimsSubmitted / $totalInvoices) * 100, 1)
            : 0.0;

        // Duplicate Detection (check for invoices with same amount, client, and date)
        $duplicateGroups = (clone $invoiceQuery)
            ->selectRaw('client_id, grand_total, DATE(issue_date) as issue_date_only, COUNT(*) as count')
            ->groupBy('client_id', 'grand_total', 'issue_date_only')
            ->having('count', '>', 1)
            ->get();

        $duplicateCount = $duplicateGroups->sum(function ($group) {
            return $group->count - 1; // Subtract 1 because one is the original
        });

        // Recent eTIMS submissions (last 7 days)
        $recentEtimsSubmissions = (clone $invoiceQuery)
            ->whereNotNull('etims_submitted_at')
            ->where('etims_submitted_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return [
            'etimsComplianceRate' => $etimsComplianceRate,
            'etimsSubmittedCount' => $etimsSubmitted,
            'totalInvoicesForCompliance' => $totalInvoices,
            'duplicateCount' => $duplicateCount,
            'recentEtimsSubmissions' => $recentEtimsSubmissions,
        ];
    }

    /**
     * Get payment method breakdown
     *
     * @param  int  $companyId  Company ID to scope payments
     */
    protected function getPaymentMethodBreakdown(int $companyId): array
    {
        if (! Schema::hasTable('payments')) {
            return [];
        }

        $paymentQuery = Payment::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereNotNull('payment_method');

        $breakdown = (clone $paymentQuery)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method ?? 'Unknown',
                    'count' => (int) $item->count,
                    'total_amount' => (float) $item->total_amount,
                ];
            })
            ->toArray();

        $totalPayments = array_sum(array_column($breakdown, 'count'));
        $totalAmount = array_sum(array_column($breakdown, 'total_amount'));

        // Calculate percentages
        foreach ($breakdown as &$item) {
            $item['percentage'] = $totalAmount > 0
                ? round(($item['total_amount'] / $totalAmount) * 100, 1)
                : 0.0;
        }

        return [
            'breakdown' => $breakdown,
            'total_methods' => count($breakdown),
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Get cash flow forecast (next 3 months based on historical averages)
     *
     * @param  int  $companyId  Company ID to scope forecast
     */
    protected function getCashFlowForecast(int $companyId): array
    {
        $forecast = [];

        // Get average monthly revenue from last 3 months
        $last3MonthsRevenue = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('issue_date', '>=', Carbon::now()->subMonths(3)->startOfMonth())
            ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as month, SUM(grand_total) as revenue')
            ->groupBy('month')
            ->get()
            ->avg('revenue') ?? 0;

        // Get average monthly expenses from last 3 months
        $last3MonthsExpenses = 0;
        if (Schema::hasTable('expenses')) {
            $last3MonthsExpenses = Expense::where('company_id', $companyId)
                ->where('status', 'paid')
                ->where('expense_date', '>=', Carbon::now()->subMonths(3)->startOfMonth())
                ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, SUM(amount) as expenses')
                ->groupBy('month')
                ->get()
                ->avg('expenses') ?? 0;
        }

        // Generate forecast for next 3 months
        $forecast = [];
        for ($i = 1; $i <= 3; $i++) {
            $forecastDate = Carbon::now()->addMonths($i);
            $forecast[] = [
                'month' => $forecastDate->format('M Y'),
                'month_key' => $forecastDate->format('Y-m'),
                'projected_inflow' => (float) $last3MonthsRevenue,
                'projected_outflow' => (float) $last3MonthsExpenses,
                'projected_net' => (float) $last3MonthsRevenue - (float) $last3MonthsExpenses,
            ];
        }

        return $forecast;
    }

    /**
     * Get bank reconciliation status
     *
     * @param  int  $companyId  Company ID to scope reconciliation
     */
    protected function getBankReconciliationStatus(int $companyId): array
    {
        if (! Schema::hasTable('bank_reconciliations')) {
            return [
                'is_enabled' => false,
                'reconciled_percentage' => 0,
                'pending_transactions' => 0,
            ];
        }

        $totalPayments = Schema::hasTable('payments')
            ? Payment::where('company_id', $companyId)->where('status', 'completed')->count()
            : 0;

        $matchedPayments = Schema::hasTable('payments') && Schema::hasTable('bank_transactions')
            ? Payment::where('company_id', $companyId)
                ->where('status', 'completed')
                ->whereHas('bankTransaction')
                ->count()
            : 0;

        $reconciledPercentage = $totalPayments > 0
            ? round(($matchedPayments / $totalPayments) * 100, 1)
            : 0.0;

        $pendingTransactions = Schema::hasTable('bank_transactions')
            ? \App\Models\BankTransaction::where('company_id', $companyId)
                ->where('status', 'pending')
                ->count()
            : 0;

        return [
            'is_enabled' => true,
            'reconciled_percentage' => $reconciledPercentage,
            'matched_payments' => $matchedPayments,
            'total_payments' => $totalPayments,
            'pending_transactions' => $pendingTransactions,
        ];
    }

    /**
     * Get recent activity feed (invoices, payments, estimates)
     *
     * @param  int  $companyId  Company ID to scope activity
     */
    protected function getRecentActivity(int $companyId): array
    {
        $activities = collect();

        // Recent invoices
        $recentInvoices = Invoice::where('company_id', $companyId)
            ->with('client')
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'type' => 'invoice',
                    'id' => $invoice->id,
                    'title' => 'Invoice '.($invoice->full_number ?? $invoice->invoice_number ?? 'INV-'.$invoice->id),
                    'description' => $invoice->client?->name ?? 'Unknown Client',
                    'status' => $invoice->status,
                    'amount' => (float) $invoice->grand_total,
                    'date' => $invoice->updated_at,
                    'url' => route('user.invoices.show', $invoice->id),
                ];
            });

        $activities = $activities->merge($recentInvoices);

        // Recent payments
        if (Schema::hasTable('payments')) {
            $recentPayments = Payment::where('company_id', $companyId)
                ->with('invoice.client')
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function ($payment) {
                    return [
                        'type' => 'payment',
                        'id' => $payment->id,
                        'title' => 'Payment received',
                        'description' => $payment->invoice?->client?->name ?? 'Unknown Client',
                        'amount' => (float) $payment->amount,
                        'date' => $payment->created_at,
                        'url' => $payment->invoice ? route('user.invoices.show', $payment->invoice_id) : null,
                    ];
                });

            $activities = $activities->merge($recentPayments);
        }

        // Recent estimates
        if (Schema::hasTable('estimates')) {
            $recentEstimates = \App\Models\Estimate::where('company_id', $companyId)
                ->with('client')
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(function ($estimate) {
                    return [
                        'type' => 'estimate',
                        'id' => $estimate->id,
                        'title' => 'Estimate '.($estimate->full_number ?? $estimate->estimate_number ?? 'EST-'.$estimate->id),
                        'description' => $estimate->client?->name ?? 'Unknown Client',
                        'status' => $estimate->status,
                        'amount' => (float) $estimate->grand_total,
                        'date' => $estimate->updated_at,
                        'url' => route('user.estimates.show', $estimate->id),
                    ];
                });

            $activities = $activities->merge($recentEstimates);
        }

        // Sort by date and limit to 15 most recent
        return $activities->sortByDesc('date')->take(15)->values()->toArray();
    }

    /**
     * Get multi-company overview for user
     *
     * @param  int  $currentCompanyId  Current active company ID
     */
    protected function getMultiCompanyOverview(int $currentCompanyId): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'total_companies' => 0,
                'total_revenue' => 0,
                'total_invoices' => 0,
                'companies' => [],
            ];
        }

        $companies = $user->ownedCompanies()->get();
        $totalCompanies = $companies->count();

        if ($totalCompanies <= 1) {
            return [
                'total_companies' => $totalCompanies,
                'total_revenue' => 0,
                'total_invoices' => 0,
                'companies' => [],
            ];
        }

        $totalRevenue = 0;
        $totalInvoices = 0;
        $companiesData = [];

        foreach ($companies as $company) {
            $revenue = (float) Invoice::where('company_id', $company->id)
                ->where('status', 'paid')
                ->sum('grand_total');

            $invoiceCount = Invoice::where('company_id', $company->id)->count();
            $outstanding = (float) Invoice::where('company_id', $company->id)
                ->whereIn('status', ['sent', 'draft'])
                ->sum('grand_total');
            $overdue = (float) Invoice::where('company_id', $company->id)
                ->where('status', 'overdue')
                ->sum('grand_total');

            $dso = (new BusinessInsightsService)->calculateDSO($company->id);

            $companyStats = [
                'id' => $company->id,
                'name' => $company->name,
                'revenue' => $revenue,
                'totalRevenue' => $revenue,
                'invoice_count' => $invoiceCount,
                'outstanding' => $outstanding,
                'overdue' => $overdue,
                'dso' => $dso,
                'isActive' => $currentCompanyId === $company->id,
                'is_current' => $currentCompanyId === $company->id,
                'currency' => $company->currency ?? 'KES',
            ];

            $totalRevenue += $revenue;
            $totalInvoices += $invoiceCount;
            $companiesData[] = $companyStats;
        }

        // Sort by revenue descending
        usort($companiesData, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return [
            'total_companies' => $totalCompanies,
            'total_revenue' => $totalRevenue,
            'total_invoices' => $totalInvoices,
            'companies' => $companiesData,
        ];
    }

    /**
     * Get AI insights and predictions
     *
     * @param  int  $companyId  Company ID to scope insights
     */
    protected function getAiInsights(int $companyId): array
    {
        // Get historical revenue trends for prediction
        $last3Months = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->where('issue_date', '>=', Carbon::now()->subMonths(3)->startOfMonth())
            ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as month, SUM(grand_total) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $revenueTrend = $last3Months->avg('revenue') ?? 0;

        // Predict next month revenue (simple average of last 3 months)
        $predictedNextMonthRevenue = $revenueTrend;

        // Calculate growth trend
        $lastMonthRevenue = $last3Months->last()?->revenue ?? 0;
        $firstMonthRevenue = $last3Months->first()?->revenue ?? 0;
        $growthTrend = $firstMonthRevenue > 0
            ? (($lastMonthRevenue - $firstMonthRevenue) / $firstMonthRevenue) * 100
            : 0;

        // Estimate cash flow projection
        $averageExpenses = 0;
        if (Schema::hasTable('expenses')) {
            $averageExpenses = Expense::where('company_id', $companyId)
                ->where('status', 'paid')
                ->where('expense_date', '>=', Carbon::now()->subMonths(3)->startOfMonth())
                ->avg('amount') ?? 0;
        }

        $predictedCashFlow = $predictedNextMonthRevenue - ($averageExpenses * 3); // Rough estimate

        // Payment pattern analysis
        $invoiceQuery = Invoice::where('company_id', $companyId);
        $avgDaysToPay = (clone $invoiceQuery)
            ->where('status', 'paid')
            ->whereNotNull('due_date')
            ->with('payments')
            ->get()
            ->map(function ($invoice) {
                $payment = $invoice->payments->first();
                if ($payment && $payment->paid_at) {
                    return Carbon::parse($invoice->issue_date)->diffInDays(Carbon::parse($payment->paid_at));
                }

                return null;
            })
            ->filter()
            ->average();

        // Risk assessment
        $overduePercentage = (clone $invoiceQuery)->where('status', 'overdue')->count() / max((clone $invoiceQuery)->whereIn('status', ['sent', 'paid', 'overdue'])->count(), 1) * 100;
        $riskLevel = $overduePercentage > 20 ? 'high' : ($overduePercentage > 10 ? 'medium' : 'low');

        return [
            'predictedNextMonthRevenue' => round((float) $predictedNextMonthRevenue, 2),
            'growthTrend' => round((float) $growthTrend, 1),
            'predictedCashFlow' => round((float) $predictedCashFlow, 2),
            'averageDaysToPay' => $avgDaysToPay ? round((float) $avgDaysToPay, 1) : null,
            'riskLevel' => $riskLevel,
            'overduePercentage' => round((float) $overduePercentage, 1),
            'recommendations' => $this->generateRecommendations($companyId, $riskLevel, $overduePercentage),
        ];
    }

    /**
     * Generate AI-powered recommendations
     *
     * @param  int  $companyId  Company ID
     * @param  string  $riskLevel  Risk level (low, medium, high)
     * @param  float  $overduePercentage  Percentage of overdue invoices
     */
    protected function generateRecommendations(int $companyId, string $riskLevel, float $overduePercentage): array
    {
        $recommendations = [];

        if ($overduePercentage > 15) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'High Overdue Rate',
                'message' => 'Consider implementing stricter payment terms or automated reminders.',
                'action' => route('user.invoices.index', ['status' => 'overdue']),
                'actionText' => 'Review Overdue Invoices',
            ];
        }

        $dso = (new BusinessInsightsService)->calculateDSO($companyId);
        if ($dso > 45) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'High Days Sales Outstanding',
                'message' => 'Your DSO is above industry average. Consider offering early payment discounts.',
                'action' => route('user.reports.index'),
                'actionText' => 'View Reports',
            ];
        }

        // Check for low conversion rate
        if (Schema::hasTable('estimates')) {
            $totalEstimates = \App\Models\Estimate::where('company_id', $companyId)->count();
            $convertedEstimates = \App\Models\Estimate::where('company_id', $companyId)->where('status', 'converted')->count();
            if ($totalEstimates > 5) {
                $conversionRate = ($convertedEstimates / $totalEstimates) * 100;
                if ($conversionRate < 30) {
                    $recommendations[] = [
                        'type' => 'info',
                        'title' => 'Low Estimate Conversion',
                        'message' => 'Only '.round($conversionRate, 1).'% of estimates are converted. Review pricing or follow-up process.',
                        'action' => route('user.estimates.index'),
                        'actionText' => 'View Estimates',
                    ];
                }
            }
        }

        return $recommendations;
    }

    /**
     * Get fraud indicators summary
     *
     * @param  int  $companyId  Company ID to scope fraud data
     */
    protected function getFraudIndicators(int $companyId): array
    {
        if (! Schema::hasTable('payments')) {
            return [
                'flagged_count' => 0,
                'total_reviewed' => 0,
                'average_fraud_score' => 0,
                'requires_review' => 0,
            ];
        }

        $paymentQuery = Payment::where('company_id', $companyId);

        $flaggedCount = (clone $paymentQuery)
            ->whereIn('fraud_status', ['flagged', 'rejected'])
            ->count();

        $reviewedCount = (clone $paymentQuery)
            ->whereNotNull('fraud_reviewed_at')
            ->count();

        $requiresReview = (clone $paymentQuery)
            ->where('fraud_status', 'pending')
            ->whereNotNull('fraud_score')
            ->where('fraud_score', '>', 50)
            ->count();

        $averageFraudScore = (clone $paymentQuery)
            ->whereNotNull('fraud_score')
            ->avg('fraud_score') ?? 0;

        return [
            'flagged_count' => $flaggedCount,
            'total_reviewed' => $reviewedCount,
            'average_fraud_score' => round((float) $averageFraudScore, 1),
            'requires_review' => $requiresReview,
        ];
    }
}
