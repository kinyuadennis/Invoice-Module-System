<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\Invoice;
use App\Models\InvoiceAuditLog;
use Illuminate\Http\Request;

/**
 * AuditLogController (API)
 *
 * Provides read-only API access to audit logs.
 * All queries are company-scoped.
 */
class AuditLogController extends Controller
{
    /**
     * Get audit logs for a specific invoice.
     */
    public function invoiceLogs(Request $request, $invoiceId)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        // Ensure invoice belongs to company
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

        $perPage = min($request->input('per_page', 50), 100);
        $logs = $query->paginate($perPage);

        return AuditLogResource::collection($logs);
    }

    /**
     * Get all audit logs for company's invoices.
     */
    public function index(Request $request)
    {
        $companyId = $request->input('company_id'); // Set by middleware

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

        $perPage = min($request->input('per_page', 50), 100);
        $logs = $query->paginate($perPage);

        return AuditLogResource::collection($logs);
    }
}
