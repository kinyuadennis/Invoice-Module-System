<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\DashboardService;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        // Get admin-specific stats (all data)
        $stats = [
            'totalUsers' => User::count(),
            'totalClients' => Client::count(),
            'totalInvoices' => Invoice::count(),
            'totalRevenue' => Invoice::where('status', 'paid')->sum('total'),
            'pendingInvoices' => Invoice::where('status', 'sent')->count(),
            'overdueInvoices' => Invoice::where('status', 'overdue')->count(),
        ];

        $recentInvoices = Invoice::with('client', 'user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-'.str_pad($invoice->id, 3, '0', STR_PAD_LEFT),
                    'client' => ['name' => $invoice->client->name ?? 'Unknown'],
                    'user' => ['name' => $invoice->user->name ?? 'Unknown'],
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date,
                    'total' => $invoice->total,
                ];
            });

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
        ]);
    }
}
