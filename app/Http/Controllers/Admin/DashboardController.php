<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\AdminDashboardService;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\PlatformFee;

class DashboardController extends Controller
{
    public function __invoke(AdminDashboardService $adminDashboardService)
    {
        // Get comprehensive dashboard statistics
        $dashboardData = $adminDashboardService->getDashboardStats();

        // Top companies by revenue
        $topCompanies = $adminDashboardService->getTopCompanies(5);

        // Recent companies
        $recentCompanies = Company::with('owner')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo' => $company->logo,
                    'owner' => $company->owner ? $company->owner->name : 'Unknown',
                    'created_at' => $company->created_at,
                ];
            });

        // Recent invoices
        $recentInvoices = Invoice::with(['client', 'user', 'company'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_reference' => $invoice->invoice_reference,
                    'invoice_number' => $invoice->invoice_reference ?? 'INV-'.str_pad($invoice->id, 3, '0', STR_PAD_LEFT),
                    'client' => ['name' => $invoice->client->name ?? 'Unknown'],
                    'user' => ['name' => $invoice->user->name ?? 'Unknown'],
                    'company' => $invoice->company ? [
                        'id' => $invoice->company->id,
                        'name' => $invoice->company->name,
                    ] : null,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date,
                    'total' => $invoice->grand_total ?? $invoice->total,
                ];
            });

        return view('admin.dashboard.index', [
            'stats' => $dashboardData['overview'],
            'revenue' => $dashboardData['revenue'],
            'invoices' => $dashboardData['invoices'],
            'companies' => $dashboardData['companies'],
            'users' => $dashboardData['users'],
            'monthlyTrends' => $dashboardData['monthlyTrends'],
            'invoiceStatusDistribution' => $dashboardData['invoiceStatusDistribution'],
            'topCompanies' => $topCompanies,
            'recentCompanies' => $recentCompanies,
            'recentInvoices' => $recentInvoices,
            'platformFeesCollected' => PlatformFee::where('fee_status', 'paid')->sum('fee_amount'),
        ]);
    }
}
