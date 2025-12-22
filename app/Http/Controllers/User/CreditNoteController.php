<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreditNoteRequest;
use App\Http\Requests\UpdateCreditNoteRequest;
use App\Http\Services\CreditNoteService;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CreditNoteController extends Controller
{
    protected CreditNoteService $creditNoteService;

    public function __construct(CreditNoteService $creditNoteService)
    {
        $this->creditNoteService = $creditNoteService;
    }

    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $query = CreditNote::where('company_id', $companyId)
            ->with(['invoice', 'client', 'items'])
            ->latest('issue_date');

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('credit_note_reference', 'like', "%{$search}%")
                    ->orWhere('full_number', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($q) use ($search) {
                        $q->where('invoice_reference', 'like', "%{$search}%");
                    })
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('dateRange') && $request->dateRange) {
            $dateRange = $request->dateRange;
            $now = now();

            switch ($dateRange) {
                case 'today':
                    $query->whereDate('issue_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('issue_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('issue_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                    break;
                case 'quarter':
                    $query->whereBetween('issue_date', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereBetween('issue_date', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
                    break;
            }
        }

        $creditNotes = $query->paginate(15)->through(function (CreditNote $creditNote) {
            return $this->creditNoteService->formatCreditNoteForList($creditNote);
        });

        return view('user.credit-notes.index', [
            'creditNotes' => $creditNotes,
            'stats' => $this->creditNoteService->getCreditNoteStats($companyId),
            'filters' => $request->only(['search', 'status', 'dateRange']),
        ]);
    }

    public function create(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        // Get invoice ID if provided
        $invoiceId = $request->input('invoice_id');

        if ($invoiceId) {
            $invoice = Invoice::where('company_id', $companyId)
                ->with(['invoiceItems', 'client'])
                ->findOrFail($invoiceId);

            return view('user.credit-notes.create', [
                'invoice' => $invoice,
            ]);
        }

        // Show invoice selection
        $invoices = Invoice::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->with('client')
            ->latest()
            ->limit(50)
            ->get();

        return view('user.credit-notes.select-invoice', [
            'invoices' => $invoices,
        ]);
    }

    public function store(StoreCreditNoteRequest $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['invoiceItems', 'client'])
            ->findOrFail($request->input('invoice_id'));

        $creditNote = $this->creditNoteService->createCreditNoteFromInvoice($invoice, $request);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Credit note created successfully.',
                'credit_note_id' => $creditNote->id,
                'redirect' => route('user.credit-notes.show', $creditNote->id),
            ]);
        }

        return redirect()->route('user.credit-notes.show', $creditNote->id)
            ->with('success', 'Credit note created successfully.');
    }

    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->with(['invoice', 'client', 'items', 'appliedToInvoice'])
            ->findOrFail($id);

        // Get available invoices to apply credit to
        $availableInvoices = [];
        if ($creditNote->canApplyToInvoice()) {
            $availableInvoices = Invoice::where('company_id', $companyId)
                ->where('client_id', $creditNote->client_id)
                ->where('id', '!=', $creditNote->invoice_id)
                ->where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->with('client')
                ->get()
                ->map(function ($invoice) {
                    $totalPaid = $invoice->payments->sum('amount');
                    $remaining = $invoice->grand_total - $totalPaid;

                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->full_number ?? $invoice->invoice_reference,
                        'grand_total' => (float) $invoice->grand_total,
                        'remaining' => max(0, $remaining),
                    ];
                });
        }

        return view('user.credit-notes.show', [
            'creditNote' => $this->creditNoteService->formatCreditNoteForShow($creditNote),
            'availableInvoices' => $availableInvoices,
        ]);
    }

    public function edit($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->with(['invoice', 'client', 'items'])
            ->findOrFail($id);

        // Only allow editing draft credit notes
        if ($creditNote->status !== 'draft') {
            return redirect()->route('user.credit-notes.show', $creditNote->id)
                ->with('error', 'Only draft credit notes can be edited.');
        }

        return view('user.credit-notes.edit', [
            'creditNote' => $this->creditNoteService->formatCreditNoteForEdit($creditNote),
        ]);
    }

    public function update(UpdateCreditNoteRequest $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->findOrFail($id);

        // Only allow editing draft credit notes
        if ($creditNote->status !== 'draft') {
            return back()->withErrors([
                'status' => 'Only draft credit notes can be edited.',
            ]);
        }

        $creditNote->update($request->only([
            'reason',
            'reason_details',
            'notes',
            'terms_and_conditions',
        ]));

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.credit-notes.show', $creditNote->id)
            ->with('success', 'Credit note updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->findOrFail($id);

        // Only allow deletion of draft credit notes
        if ($creditNote->status !== 'draft') {
            return back()->withErrors([
                'message' => 'Only draft credit notes can be deleted.',
            ]);
        }

        $creditNote->delete();

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.credit-notes.index')
            ->with('success', 'Credit note deleted successfully.');
    }

    /**
     * Issue credit note (change status from draft to issued)
     */
    public function issue($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->findOrFail($id);

        if ($creditNote->status !== 'draft') {
            return back()->withErrors([
                'message' => 'Only draft credit notes can be issued.',
            ]);
        }

        $creditNote->update(['status' => 'issued']);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return back()->with('success', 'Credit note issued successfully.');
    }

    /**
     * Apply credit note to an invoice
     */
    public function applyToInvoice($id, Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->findOrFail($id);

        $invoiceId = $request->input('invoice_id');
        $invoice = Invoice::where('company_id', $companyId)
            ->with('payments')
            ->findOrFail($invoiceId);

        try {
            $this->creditNoteService->applyToInvoice($creditNote, $invoice);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return redirect()->route('user.credit-notes.show', $creditNote->id)
                ->with('success', 'Credit note applied to invoice successfully.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Submit credit note to eTIMS
     */
    public function submitToEtims($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->with('invoice')
            ->findOrFail($id);

        if ($creditNote->status !== 'issued') {
            return back()->withErrors([
                'message' => 'Only issued credit notes can be submitted to eTIMS.',
            ]);
        }

        try {
            $this->creditNoteService->submitToEtims($creditNote);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return back()->with('success', 'Credit note submitted to eTIMS successfully.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'Failed to submit to eTIMS: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Generate PDF for credit note
     */
    public function pdf($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $creditNote = CreditNote::where('company_id', $companyId)
            ->with(['invoice', 'client', 'items', 'company'])
            ->findOrFail($id);

        // TODO: Implement PDF generation
        // Similar to invoice PDF generation

        return response()->json([
            'message' => 'PDF generation not yet implemented',
        ], 501);
    }
}
