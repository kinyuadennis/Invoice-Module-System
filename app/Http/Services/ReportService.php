<?php

namespace App\Http\Services;

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
}
