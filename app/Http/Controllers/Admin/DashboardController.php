<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\DashboardService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\PlatformFee;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        // Get admin-specific stats (all data)
        $stats = [
            'totalCompanies' => Company::count(),
            'totalUsers' => User::count(),
            'totalClients' => Client::count(),
            'totalInvoices' => Invoice::count(),
            'totalRevenue' => Invoice::where('status', 'paid')->sum('grand_total'),
            'pendingInvoices' => Invoice::where('status', 'sent')->count(),
            'overdueInvoices' => Invoice::where('status', 'overdue')->count(),
            'platformFeesCollected' => PlatformFee::where('fee_status', 'paid')->sum('fee_amount'),
        ];

        // Top companies by revenue
        $topCompanies = Company::with('owner')
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
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

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
            'stats' => $stats,
            'topCompanies' => $topCompanies,
            'recentCompanies' => $recentCompanies,
            'recentInvoices' => $recentInvoices,
        ]);
    }
}
