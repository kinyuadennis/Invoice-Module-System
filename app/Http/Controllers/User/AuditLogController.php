<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceAuditLog;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * AuditLogController
 *
 * Handles audit log viewing in the UI.
 * Only authorized users can view audit logs.
 */
class AuditLogController extends Controller
{
    /**
     * Show audit logs for a specific invoice.
     */
    public function showInvoiceLogs(Request $request, $invoiceId)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->findOrFail($invoiceId);

        // Get audit logs for this invoice
        $query = InvoiceAuditLog::where('invoice_id', $invoiceId)
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Filter by action type if provided
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(50);

        return Inertia::render('User/AuditLogs/InvoiceLogs', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
            ],
            'logs' => $logs->through(function ($log) {
                return [
                    'id' => $log->id,
                    'action_type' => $log->action_type,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                    'ip_address' => $log->ip_address,
                    'source' => $log->source,
                    'metadata' => $log->metadata,
                    'old_data' => $log->old_data,
                    'new_data' => $log->new_data,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            }),
            'filters' => [
                'action_type' => $request->action_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }

    /**
     * Show all audit logs (filtered by user, date range, etc.).
     */
    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        // Get all invoices for this company
        $invoiceIds = Invoice::where('company_id', $companyId)->pluck('id');

        // Get audit logs for company's invoices
        $query = InvoiceAuditLog::whereIn('invoice_id', $invoiceIds)
            ->with(['user', 'invoice'])
            ->orderBy('created_at', 'desc');

        // Filter by user if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action type if provided
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(50);

        return Inertia::render('User/AuditLogs/Index', [
            'logs' => $logs->through(function ($log) {
                return [
                    'id' => $log->id,
                    'action_type' => $log->action_type,
                    'invoice' => $log->invoice ? [
                        'id' => $log->invoice->id,
                        'invoice_number' => $log->invoice->invoice_number,
                    ] : null,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                    'ip_address' => $log->ip_address,
                    'source' => $log->source,
                    'metadata' => $log->metadata,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            }),
            'filters' => [
                'user_id' => $request->user_id,
                'action_type' => $request->action_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }
}
