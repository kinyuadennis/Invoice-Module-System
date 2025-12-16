<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\ReportService;
use App\Models\Client;
use App\Services\CurrentCompanyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Display reports index page.
     */
    public function index()
    {
        return view('user.reports.index');
    }

    /**
     * Display revenue report.
     */
    public function revenue(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $report = $this->reportService->getRevenueReport($companyId, $startDate, $endDate);

        return view('user.reports.revenue', [
            'report' => $report,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    /**
     * Display invoice report.
     */
    public function invoices(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();
        $status = $request->get('status');
        $clientId = $request->filled('client_id') ? (int) $request->client_id : null;

        $report = $this->reportService->getInvoiceReport($companyId, $startDate, $endDate, $status, $clientId);
        $clients = Client::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('user.reports.invoices', [
            'report' => $report,
            'clients' => $clients,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'client_id']),
        ]);
    }

    /**
     * Display payment report.
     */
    public function payments(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $report = $this->reportService->getPaymentReport($companyId, $startDate, $endDate);

        return view('user.reports.payments', [
            'report' => $report,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    /**
     * Export invoices to CSV.
     */
    public function exportInvoicesCsv(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfYear();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfYear();
        $status = $request->get('status');

        $filename = $this->reportService->exportInvoicesToCsv($companyId, $startDate, $endDate, $status);

        return Response::download($filename, 'invoices_'.date('Y-m-d').'.csv')->deleteFileAfterSend(true);
    }

    /**
     * Export revenue report to CSV.
     */
    public function exportRevenueCsv(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $report = $this->reportService->getRevenueReport($companyId, $startDate, $endDate);

        $filename = storage_path('app/exports/revenue_'.date('Y-m-d_His').'.csv');
        $directory = dirname($filename);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filename, 'w');

        // Summary
        fputcsv($file, ['Revenue Report']);
        fputcsv($file, ['Period', $report['period']['start'].' to '.$report['period']['end']]);
        fputcsv($file, []);
        fputcsv($file, ['Total Revenue', number_format($report['summary']['total_revenue'], 2)]);
        fputcsv($file, ['Total Invoices', $report['summary']['total_invoices']]);
        fputcsv($file, ['Average Invoice Value', number_format($report['summary']['average_invoice_value'], 2)]);
        fputcsv($file, []);

        // Monthly breakdown
        fputcsv($file, ['Monthly Breakdown']);
        fputcsv($file, ['Month', 'Revenue', 'Invoice Count']);
        foreach ($report['monthly_breakdown'] as $month) {
            fputcsv($file, [$month['month'], number_format($month['revenue'], 2), $month['count']]);
        }
        fputcsv($file, []);

        // Top clients
        fputcsv($file, ['Top Clients']);
        fputcsv($file, ['Client', 'Revenue', 'Invoice Count']);
        foreach ($report['top_clients'] as $client) {
            fputcsv($file, [$client->name, number_format($client->total_revenue, 2), $client->invoice_count]);
        }

        fclose($file);

        return Response::download($filename, 'revenue_'.date('Y-m-d').'.csv')->deleteFileAfterSend(true);
    }
}
