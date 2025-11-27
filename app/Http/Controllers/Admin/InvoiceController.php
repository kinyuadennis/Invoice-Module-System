<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\InvoiceService;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        // Admin sees ALL invoices from all users
        $query = Invoice::with(['client', 'user'])->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // User filter (admin can filter by user)
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $invoices = $query->paginate(15)->through(function (Invoice $invoice) {
            return $this->invoiceService->formatInvoiceForList($invoice);
        });

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status', 'user_id']),
        ]);
    }

    public function show($id)
    {
        // Admin can view any invoice
        $invoice = Invoice::with(['client', 'user', 'invoiceItems', 'payments'])
            ->findOrFail($id);

        return view('admin.invoices.show', [
            'invoice' => $this->invoiceService->formatInvoiceForShow($invoice),
        ]);
    }
}
