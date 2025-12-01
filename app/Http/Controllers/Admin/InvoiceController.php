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
        $query = Invoice::with(['client', 'user', 'company'])->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('invoice_reference', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Company filter
        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        // User filter (admin can filter by user)
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $invoices = $query->paginate(15)->through(function (Invoice $invoice) {
            $formatted = $this->invoiceService->formatInvoiceForList($invoice);
            $formatted['company'] = $invoice->company ? [
                'id' => $invoice->company->id,
                'name' => $invoice->company->name,
            ] : null;

            return $formatted;
        });

        $companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'companies' => $companies,
            'filters' => $request->only(['search', 'status', 'user_id', 'company_id']),
        ]);
    }

    public function show($id)
    {
        // Admin can view any invoice
        $invoice = Invoice::with(['client', 'user', 'company', 'invoiceItems', 'payments'])
            ->findOrFail($id);

        $formatted = $this->invoiceService->formatInvoiceForShow($invoice);
        $formatted['company'] = $invoice->company ? [
            'id' => $invoice->company->id,
            'name' => $invoice->company->name,
            'logo' => $invoice->company->logo,
            'email' => $invoice->company->email,
            'phone' => $invoice->company->phone,
            'address' => $invoice->company->address,
            'kra_pin' => $invoice->company->kra_pin,
        ] : null;

        return view('admin.invoices.show', [
            'invoice' => $formatted,
        ]);
    }
}
