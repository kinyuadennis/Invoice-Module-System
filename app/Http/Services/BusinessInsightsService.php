<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BusinessInsightsService
{
    /**
     * Calculate Days Sales Outstanding (DSO) for a company.
     *
     * DSO = (Accounts Receivable / Total Credit Sales) × Number of Days
     */
    public function calculateDSO(int $companyId, ?int $days = 30): float
    {
        $cacheKey = "dso_{$companyId}_{$days}";

        return Cache::remember($cacheKey, 300, function () use ($companyId, $days) {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays($days);

            // Get total credit sales (invoices sent) in the period
            $totalCreditSales = (float) Invoice::where('company_id', $companyId)
                ->where('status', '!=', 'draft')
                ->whereBetween('issue_date', [$startDate, $endDate])
                ->sum('grand_total');

            // Get accounts receivable (unpaid invoices)
            $accountsReceivable = (float) Invoice::where('company_id', $companyId)
                ->whereIn('status', ['sent', 'overdue'])
                ->sum('grand_total');

            if ($totalCreditSales <= 0) {
                return 0.0;
            }

            return round(($accountsReceivable / $totalCreditSales) * $days, 2);
        });
    }

    /**
     * Get invoice aging report.
     *
     * @return array{0-30: array{count: int, amount: float}, 31-60: array{count: int, amount: float}, 61-90: array{count: int, amount: float}, 90+: array{count: int, amount: float}}
     */
    public function getInvoiceAgingReport(int $companyId): array
    {
        $cacheKey = "invoice_aging_{$companyId}";

        return Cache::remember($cacheKey, 300, function () use ($companyId) {
            $today = Carbon::today();

            $unpaidInvoices = Invoice::where('company_id', $companyId)
                ->whereIn('status', ['sent', 'overdue'])
                ->whereNotNull('due_date')
                ->get();

            $aging = [
                '0-30' => ['count' => 0, 'amount' => 0.0],
                '31-60' => ['count' => 0, 'amount' => 0.0],
                '61-90' => ['count' => 0, 'amount' => 0.0],
                '90+' => ['count' => 0, 'amount' => 0.0],
            ];

            foreach ($unpaidInvoices as $invoice) {
                $daysPastDue = $today->diffInDays($invoice->due_date);
                $amount = (float) $invoice->grand_total;

                if ($daysPastDue <= 30) {
                    $aging['0-30']['count']++;
                    $aging['0-30']['amount'] += $amount;
                } elseif ($daysPastDue <= 60) {
                    $aging['31-60']['count']++;
                    $aging['31-60']['amount'] += $amount;
                } elseif ($daysPastDue <= 90) {
                    $aging['61-90']['count']++;
                    $aging['61-90']['amount'] += $amount;
                } else {
                    $aging['90+']['count']++;
                    $aging['90+']['amount'] += $amount;
                }
            }

            return $aging;
        });
    }

    /**
     * Get top clients by revenue.
     *
     * @return array<int, array{client_id: int, client_name: string, revenue: float, invoice_count: int, avg_payment_time: float|null}>
     */
    public function getTopClientsByRevenue(int $companyId, int $limit = 10): array
    {
        $cacheKey = "top_clients_{$companyId}_{$limit}";

        return Cache::remember($cacheKey, 300, function () use ($companyId, $limit) {
            $clients = Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->select('client_id')
                ->selectRaw('SUM(grand_total) as revenue')
                ->selectRaw('COUNT(*) as invoice_count')
                ->groupBy('client_id')
                ->orderByDesc('revenue')
                ->limit($limit)
                ->get();

            return $clients->map(function ($item) {
                $client = Client::find($item->client_id);
                $avgPaymentTime = $this->calculateAveragePaymentTime($item->client_id);

                return [
                    'client_id' => $item->client_id,
                    'client_name' => $client->name ?? 'Unknown',
                    'revenue' => (float) $item->revenue,
                    'invoice_count' => (int) $item->invoice_count,
                    'avg_payment_time' => $avgPaymentTime,
                ];
            })->toArray();
        });
    }

    /**
     * Calculate average payment time for a client (in days).
     */
    public function calculateAveragePaymentTime(?int $clientId): ?float
    {
        if (! $clientId) {
            return null;
        }

        $cacheKey = "avg_payment_time_client_{$clientId}";

        return Cache::remember($cacheKey, 300, function () use ($clientId) {
            $paidInvoices = Invoice::where('client_id', $clientId)
                ->where('status', 'paid')
                ->whereNotNull('issue_date')
                ->with('payments')
                ->get();

            if ($paidInvoices->isEmpty()) {
                return null;
            }

            $totalDays = 0;
            $count = 0;

            foreach ($paidInvoices as $invoice) {
                $issueDate = $invoice->issue_date;

                // Try to get payment date from Payment model
                $payment = $invoice->payments()->first();
                $paidDate = $payment?->paid_at ?? $payment?->payment_date ?? $invoice->updated_at;

                if ($paidDate && $issueDate) {
                    $days = Carbon::parse($issueDate)->diffInDays(Carbon::parse($paidDate));
                    $totalDays += $days;
                    $count++;
                }
            }

            return $count > 0 ? round($totalDays / $count, 1) : null;
        });
    }

    /**
     * Get average payment time for all clients of a company.
     */
    public function getAveragePaymentTimeForCompany(int $companyId): ?float
    {
        $cacheKey = "avg_payment_time_company_{$companyId}";

        return Cache::remember($cacheKey, 300, function () use ($companyId) {
            $paidInvoices = Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereNotNull('issue_date')
                ->with('payments')
                ->get();

            if ($paidInvoices->isEmpty()) {
                return null;
            }

            $totalDays = 0;
            $count = 0;

            foreach ($paidInvoices as $invoice) {
                $issueDate = $invoice->issue_date;

                // Try to get payment date from Payment model
                $payment = $invoice->payments()->first();
                $paidDate = $payment?->paid_at ?? $payment?->payment_date ?? $invoice->updated_at;

                if ($paidDate && $issueDate) {
                    $days = Carbon::parse($issueDate)->diffInDays(Carbon::parse($paidDate));
                    $totalDays += $days;
                    $count++;
                }
            }

            return $count > 0 ? round($totalDays / $count, 1) : null;
        });
    }

    /**
     * Get revenue trends by month.
     *
     * @return array<int, array{month: string, revenue: float, invoice_count: int}>
     */
    public function getRevenueTrends(int $companyId, int $months = 12): array
    {
        $cacheKey = "revenue_trends_{$companyId}_{$months}";

        return Cache::remember($cacheKey, 300, function () use ($companyId, $months) {
            $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();

            $invoices = Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->where('issue_date', '>=', $startDate)
                ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as month')
                ->selectRaw('SUM(grand_total) as revenue')
                ->selectRaw('COUNT(*) as invoice_count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $trends = [];
            $currentDate = $startDate->copy();

            for ($i = 0; $i < $months; $i++) {
                $monthKey = $currentDate->format('Y-m');
                $monthLabel = $currentDate->format('M Y');

                $invoiceData = $invoices->firstWhere('month', $monthKey);

                $trends[] = [
                    'month' => $monthLabel,
                    'month_key' => $monthKey,
                    'revenue' => $invoiceData ? (float) $invoiceData->revenue : 0.0,
                    'invoice_count' => $invoiceData ? (int) $invoiceData->invoice_count : 0,
                ];

                $currentDate->addMonth();
            }

            return $trends;
        });
    }

    /**
     * Get all insights for a company.
     *
     * @return array<string, mixed>
     */
    public function getAllInsights(int $companyId): array
    {
        return [
            'dso' => $this->calculateDSO($companyId),
            'dso_90' => $this->calculateDSO($companyId, 90),
            'invoice_aging' => $this->getInvoiceAgingReport($companyId),
            'top_clients' => $this->getTopClientsByRevenue($companyId, 10),
            'avg_payment_time' => $this->getAveragePaymentTimeForCompany($companyId),
            'revenue_trends' => $this->getRevenueTrends($companyId, 12),
        ];
    }
}
