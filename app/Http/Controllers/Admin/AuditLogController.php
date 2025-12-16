<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->latest();

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $auditLogs = $query->paginate(50);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
        $actions = AuditLog::distinct()->pluck('action')->sort();
        $modelTypes = AuditLog::distinct()->pluck('model_type')->sort();

        return view('admin.audit-logs.index', [
            'auditLogs' => $auditLogs,
            'users' => $users,
            'actions' => $actions,
            'modelTypes' => $modelTypes,
            'filters' => $request->only(['action', 'model_type', 'user_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display the specified audit log.
     */
    public function show($id)
    {
        $auditLog = AuditLog::with('user')->findOrFail($id);

        return view('admin.audit-logs.show', [
            'auditLog' => $auditLog,
        ]);
    }
}
