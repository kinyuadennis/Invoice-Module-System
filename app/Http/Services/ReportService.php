<?php

namespace App\Http\Services;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get revenue report data.
     */
    public function getRevenueReport(int $companyId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $query = Invoice::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $paidInvoices = (clone $query)->where('status', 'paid')->get();

        $totalRevenue = (float) $paidInvoices->sum('grand_total');
        $totalInvoices = $paidInvoices->count();
        $averageInvoiceValue = $totalInvoices > 0 ? $totalRevenue / $totalInvoices : 0;

        // Monthly breakdown
        $monthlyBreakdown = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthRevenue = (float) (clone $query)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('grand_total');

            $monthlyBreakdown[] = [
                'month' => $monthStart->format('M Y'),
                'revenue' => $monthRevenue,
                'count' => (clone $query)
                    ->where('status', 'paid')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count(),
            ];

            $current->addMonth();
        }

        // Top clients by revenue
        $topClients = DB::table('invoices')
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->select('clients.name', DB::raw('SUM(invoices.grand_total) as total_revenue'), DB::raw('COUNT(invoices.id) as invoice_count'))
            ->groupBy('clients.id', 'clients.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_invoices' => $totalInvoices,
                'average_invoice_value' => $averageInvoiceValue,
            ],
            'monthly_breakdown' => $monthlyBreakdown,
            'top_clients' => $topClients,
        ];
    }

    /**
     * Get invoice report data.
     */
    public function getInvoiceReport(int $companyId, ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $status = null, ?int $clientId = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $query = Invoice::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['client']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $invoices = $query->get();

        // Status breakdown
        $statusBreakdown = $invoices->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => (float) $group->sum('grand_total'),
            ];
        });

        // Client breakdown
        $clientBreakdown = $invoices->groupBy('client_id')->map(function ($group) {
            $invoice = $group->first();

            return [
                'client_name' => $invoice->client->name ?? 'N/A',
                'count' => $group->count(),
                'total' => (float) $group->sum('grand_total'),
            ];
        })->sortByDesc('total')->take(10);

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'filters' => [
                'status' => $status,
                'client_id' => $clientId,
            ],
            'invoices' => $invoices,
            'summary' => [
                'total_count' => $invoices->count(),
                'total_amount' => (float) $invoices->sum('grand_total'),
                'paid_amount' => (float) $invoices->where('status', 'paid')->sum('grand_total'),
                'outstanding_amount' => (float) $invoices->whereIn('status', ['draft', 'sent'])->sum('grand_total'),
                'overdue_amount' => (float) $invoices->where('status', 'overdue')->sum('grand_total'),
            ],
            'status_breakdown' => $statusBreakdown,
            'client_breakdown' => $clientBreakdown,
        ];
    }

    /**
     * Get payment report data.
     */
    public function getPaymentReport(int $companyId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $payments = Payment::whereHas('invoice', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['invoice.client'])
            ->get();

        $totalPayments = (float) $payments->sum('amount');
        $paymentCount = $payments->count();
        $averagePayment = $paymentCount > 0 ? $totalPayments / $paymentCount : 0;

        // Payment method breakdown
        $methodBreakdown = $payments->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ];
        });

        // Monthly breakdown
        $monthlyBreakdown = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthPayments = $payments->filter(function ($payment) use ($monthStart, $monthEnd) {
                return $payment->created_at >= $monthStart && $payment->created_at <= $monthEnd;
            });

            $monthlyBreakdown[] = [
                'month' => $monthStart->format('M Y'),
                'amount' => (float) $monthPayments->sum('amount'),
                'count' => $monthPayments->count(),
            ];

            $current->addMonth();
        }

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_payments' => $totalPayments,
                'payment_count' => $paymentCount,
                'average_payment' => $averagePayment,
            ],
            'payments' => $payments,
            'method_breakdown' => $methodBreakdown,
            'monthly_breakdown' => $monthlyBreakdown,
        ];
    }

    /**
     * Export invoices to CSV.
     */
    public function exportInvoicesToCsv(int $companyId, ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $status = null): string
    {
        $startDate = $startDate ?? Carbon::now()->startOfYear();
        $endDate = $endDate ?? Carbon::now()->endOfYear();

        $query = Invoice::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['client']);

        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->get();

        $filename = storage_path('app/exports/invoices_'.date('Y-m-d_His').'.csv');
        $directory = dirname($filename);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filename, 'w');

        // Headers
        fputcsv($file, [
            'Invoice Number',
            'Client',
            'Issue Date',
            'Due Date',
            'Status',
            'Subtotal',
            'VAT',
            'Platform Fee',
            'Total',
            'Created At',
        ]);

        // Data
        foreach ($invoices as $invoice) {
            fputcsv($file, [
                $invoice->invoice_number ?? $invoice->invoice_reference,
                $invoice->client->name ?? 'N/A',
                $invoice->issue_date?->format('Y-m-d') ?? '',
                $invoice->due_date?->format('Y-m-d') ?? '',
                $invoice->status,
                number_format($invoice->subtotal, 2),
                number_format($invoice->vat_amount, 2),
                number_format($invoice->platform_fee, 2),
                number_format($invoice->grand_total, 2),
                $invoice->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($file);

        return $filename;
    }

    /**
     * Get aging report data (outstanding invoices by age)
     */
    public function getAgingReport(int $companyId, ?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? Carbon::now();

        $invoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->with(['client', 'payments'])
            ->get();

        $agingBuckets = [
            'current' => ['min' => 0, 'max' => 30, 'label' => '0-30 Days', 'amount' => 0, 'count' => 0],
            'days_31_60' => ['min' => 31, 'max' => 60, 'label' => '31-60 Days', 'amount' => 0, 'count' => 0],
            'days_61_90' => ['min' => 61, 'max' => 90, 'label' => '61-90 Days', 'amount' => 0, 'count' => 0],
            'over_90' => ['min' => 91, 'max' => 9999, 'label' => 'Over 90 Days', 'amount' => 0, 'count' => 0],
        ];

        $agingDetails = [];

        foreach ($invoices as $invoice) {
            $totalPaid = (float) $invoice->payments->sum('amount');
            $outstanding = (float) $invoice->grand_total - $totalPaid;

            if ($outstanding <= 0) {
                continue; // Skip fully paid invoices
            }

            // Calculate days overdue
            $dueDate = $invoice->due_date ?? $invoice->issue_date;
            $daysOverdue = $asOfDate->diffInDays($dueDate, false);

            // Determine bucket
            $bucket = null;
            if ($daysOverdue <= 30) {
                $bucket = 'current';
            } elseif ($daysOverdue <= 60) {
                $bucket = 'days_31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = 'days_61_90';
            } else {
                $bucket = 'over_90';
            }

            $agingBuckets[$bucket]['amount'] += $outstanding;
            $agingBuckets[$bucket]['count'] += 1;

            $agingDetails[] = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->full_number ?? $invoice->invoice_reference,
                'client_name' => $invoice->client->name ?? 'N/A',
                'invoice_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $dueDate?->toDateString(),
                'invoice_total' => (float) $invoice->grand_total,
                'amount_paid' => $totalPaid,
                'outstanding' => $outstanding,
                'days_overdue' => max(0, $daysOverdue),
                'bucket' => $bucket,
            ];
        }

        $totalOutstanding = array_sum(array_column($agingBuckets, 'amount'));
        $totalCount = array_sum(array_column($agingBuckets, 'count'));

        return [
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'summary' => [
                'total_outstanding' => $totalOutstanding,
                'total_invoices' => $totalCount,
            ],
            'aging_buckets' => array_values($agingBuckets),
            'aging_details' => collect($agingDetails)->sortByDesc('days_overdue')->values()->toArray(),
        ];
    }

    /**
     * Get Profit & Loss (P&L) statement
     */
    public function getProfitLossStatement(int $companyId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfYear();
        $endDate = $endDate ?? Carbon::now()->endOfYear();

        // Revenue (from paid invoices)
        $revenueInvoices = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalRevenue = (float) $revenueInvoices->sum('grand_total');
        $totalVAT = (float) $revenueInvoices->sum('vat_amount');
        $totalPlatformFees = (float) $revenueInvoices->sum('platform_fee');
        $netRevenue = $totalRevenue - $totalPlatformFees; // Revenue after platform fees

        // Expenses
        $expenses = Expense::where('company_id', $companyId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        $totalExpenses = (float) $expenses->sum('amount');
        $taxDeductibleExpenses = (float) $expenses->where('is_tax_deductible', true)->sum('amount');
        $nonTaxDeductibleExpenses = $totalExpenses - $taxDeductibleExpenses;

        // Expense breakdown by category
        $expenseByCategory = $expenses->groupBy('expense_category_id')->map(function ($group) {
            $first = $group->first();

            return [
                'category_id' => $first->expense_category_id,
                'category_name' => $first->category->name ?? 'Uncategorized',
                'amount' => (float) $group->sum('amount'),
                'count' => $group->count(),
            ];
        })->sortByDesc('amount')->values();

        // Monthly breakdown
        $monthlyBreakdown = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthRevenue = (float) $revenueInvoices
                ->filter(function ($invoice) use ($monthStart, $monthEnd) {
                    return $invoice->created_at >= $monthStart && $invoice->created_at <= $monthEnd;
                })
                ->sum('grand_total');

            $monthExpenses = (float) $expenses
                ->filter(function ($expense) use ($monthStart, $monthEnd) {
                    return $expense->expense_date >= $monthStart && $expense->expense_date <= $monthEnd;
                })
                ->sum('amount');

            $monthlyBreakdown[] = [
                'month' => $monthStart->format('M Y'),
                'revenue' => $monthRevenue,
                'expenses' => $monthExpenses,
                'profit' => $monthRevenue - $monthExpenses,
            ];

            $current->addMonth();
        }

        // Calculate profit/loss
        $grossProfit = $netRevenue - $totalExpenses;
        $profitMargin = $netRevenue > 0 ? ($grossProfit / $netRevenue) * 100 : 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'revenue' => [
                'total_revenue' => $totalRevenue,
                'vat_collected' => $totalVAT,
                'platform_fees' => $totalPlatformFees,
                'net_revenue' => $netRevenue,
            ],
            'expenses' => [
                'total_expenses' => $totalExpenses,
                'tax_deductible' => $taxDeductibleExpenses,
                'non_tax_deductible' => $nonTaxDeductibleExpenses,
                'by_category' => $expenseByCategory,
            ],
            'profit_loss' => [
                'gross_profit' => $grossProfit,
                'profit_margin' => $profitMargin,
            ],
            'monthly_breakdown' => $monthlyBreakdown,
        ];
    }

    /**
     * Get expense breakdown report
     */
    public function getExpenseBreakdown(int $companyId, ?Carbon $startDate = null, ?Carbon $endDate = null, ?int $categoryId = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $query = Expense::where('company_id', $companyId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with(['category', 'client']);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $expenses = $query->get();

        // Summary
        $totalExpenses = (float) $expenses->sum('amount');
        $taxDeductibleTotal = (float) $expenses->where('is_tax_deductible', true)->sum('amount');
        $nonTaxDeductibleTotal = $totalExpenses - $taxDeductibleTotal;

        // By category
        $byCategory = $expenses->groupBy('expense_category_id')->map(function ($group) {
            $first = $group->first();

            return [
                'category_id' => $first->expense_category_id,
                'category_name' => $first->category->name ?? 'Uncategorized',
                'amount' => (float) $group->sum('amount'),
                'count' => $group->count(),
                'tax_deductible' => (float) $group->where('is_tax_deductible', true)->sum('amount'),
            ];
        })->sortByDesc('amount')->values();

        // By payment method
        $byPaymentMethod = $expenses->groupBy('payment_method')->map(function ($group) {
            return [
                'method' => $group->first()->payment_method ?? 'Other',
                'amount' => (float) $group->sum('amount'),
                'count' => $group->count(),
            ];
        })->sortByDesc('amount')->values();

        // By status
        $byStatus = $expenses->groupBy('status')->map(function ($group) {
            return [
                'status' => $group->first()->status,
                'amount' => (float) $group->sum('amount'),
                'count' => $group->count(),
            ];
        })->sortByDesc('amount')->values();

        // Monthly breakdown
        $monthlyBreakdown = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthExpenses = $expenses->filter(function ($expense) use ($monthStart, $monthEnd) {
                return $expense->expense_date >= $monthStart && $expense->expense_date <= $monthEnd;
            });

            $monthlyBreakdown[] = [
                'month' => $monthStart->format('M Y'),
                'amount' => (float) $monthExpenses->sum('amount'),
                'count' => $monthExpenses->count(),
            ];

            $current->addMonth();
        }

        // Top expenses
        $topExpenses = $expenses->sortByDesc('amount')->take(10)->map(function ($expense) {
            return [
                'id' => $expense->id,
                'expense_number' => $expense->expense_number ?? "EXP-{$expense->id}",
                'description' => $expense->description,
                'category' => $expense->category->name ?? 'Uncategorized',
                'amount' => (float) $expense->amount,
                'date' => $expense->expense_date->toDateString(),
                'status' => $expense->status,
            ];
        })->values();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_expenses' => $totalExpenses,
                'tax_deductible' => $taxDeductibleTotal,
                'non_tax_deductible' => $nonTaxDeductibleTotal,
                'expense_count' => $expenses->count(),
                'average_expense' => $expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0,
            ],
            'by_category' => $byCategory,
            'by_payment_method' => $byPaymentMethod,
            'by_status' => $byStatus,
            'monthly_breakdown' => $monthlyBreakdown,
            'top_expenses' => $topExpenses,
            'expenses' => $expenses,
        ];
    }
}
