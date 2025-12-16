<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\RecurringInvoiceService;
use App\Models\Client;
use App\Models\RecurringInvoice;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class RecurringInvoiceController extends Controller
{
    public function __construct(
        private RecurringInvoiceService $recurringInvoiceService
    ) {}

    /**
     * Display a listing of recurring invoices.
     */
    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $query = RecurringInvoice::where('company_id', $companyId)
            ->with(['client', 'user'])
            ->latest();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $recurringInvoices = $query->paginate(15);

        return view('user.recurring-invoices.index', [
            'recurringInvoices' => $recurringInvoices,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Show the form for creating a new recurring invoice.
     */
    public function create()
    {
        $companyId = CurrentCompanyService::requireId();

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('user.recurring-invoices.create', [
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created recurring invoice.
     */
    public function store(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'interval' => 'required|integer|min:1|max:12',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_occurrences' => 'nullable|integer|min:1',
            'auto_send' => 'boolean',
            'send_reminders' => 'boolean',
            'invoice_data' => 'required|array',
            'invoice_data.line_items' => 'required|array|min:1',
            'invoice_data.line_items.*.description' => 'required|string',
            'invoice_data.line_items.*.quantity' => 'required|numeric|min:0',
            'invoice_data.line_items.*.unit_price' => 'required|numeric|min:0',
            'invoice_data.line_items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'invoice_data.notes' => 'nullable|string',
            'invoice_data.terms_and_conditions' => 'nullable|string',
            'invoice_data.payment_terms' => 'nullable|integer|min:1',
        ]);

        // Verify client belongs to company
        $client = Client::where('id', $validated['client_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Calculate next run date
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $nextRunDate = $startDate;

        $recurringInvoice = RecurringInvoice::create([
            'company_id' => $companyId,
            'client_id' => $validated['client_id'],
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'frequency' => $validated['frequency'],
            'interval' => $validated['interval'],
            'start_date' => $startDate,
            'end_date' => $validated['end_date'] ? \Carbon\Carbon::parse($validated['end_date']) : null,
            'next_run_date' => $nextRunDate,
            'status' => 'active',
            'invoice_data' => $validated['invoice_data'],
            'auto_send' => $validated['auto_send'] ?? false,
            'send_reminders' => $validated['send_reminders'] ?? true,
            'max_occurrences' => $validated['max_occurrences'] ?? null,
        ]);

        return redirect()->route('user.recurring-invoices.index')
            ->with('success', 'Recurring invoice created successfully.');
    }

    /**
     * Display the specified recurring invoice.
     */
    public function show(RecurringInvoice $recurringInvoice)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify ownership
        if ($recurringInvoice->company_id !== $companyId) {
            abort(403);
        }

        $recurringInvoice->load(['client', 'user', 'generatedInvoices']);

        return view('user.recurring-invoices.show', [
            'recurringInvoice' => $recurringInvoice,
        ]);
    }

    /**
     * Show the form for editing the specified recurring invoice.
     */
    public function edit(RecurringInvoice $recurringInvoice)
    {
        $companyId = CurrentCompanyService::requireId();

        if ($recurringInvoice->company_id !== $companyId) {
            abort(403);
        }

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('user.recurring-invoices.edit', [
            'recurringInvoice' => $recurringInvoice,
            'clients' => $clients,
        ]);
    }

    /**
     * Update the specified recurring invoice.
     */
    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
        $companyId = CurrentCompanyService::requireId();

        if ($recurringInvoice->company_id !== $companyId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'interval' => 'required|integer|min:1|max:12',
            'end_date' => 'nullable|date|after:start_date',
            'max_occurrences' => 'nullable|integer|min:1',
            'auto_send' => 'boolean',
            'send_reminders' => 'boolean',
            'invoice_data' => 'required|array',
            'invoice_data.line_items' => 'required|array|min:1',
        ]);

        $recurringInvoice->update($validated);

        return redirect()->route('user.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice updated successfully.');
    }

    /**
     * Pause a recurring invoice.
     */
    public function pause(RecurringInvoice $recurringInvoice)
    {
        $companyId = CurrentCompanyService::requireId();

        if ($recurringInvoice->company_id !== $companyId) {
            abort(403);
        }

        if ($recurringInvoice->status === 'active') {
            $recurringInvoice->update(['status' => 'paused']);
        }

        return redirect()->back()->with('success', 'Recurring invoice paused.');
    }

    /**
     * Resume a paused recurring invoice.
     */
    public function resume(RecurringInvoice $recurringInvoice)
    {
        $companyId = CurrentCompanyService::requireId();

        if ($recurringInvoice->company_id !== $companyId) {
            abort(403);
        }

        if ($recurringInvoice->status === 'paused') {
            $recurringInvoice->update(['status' => 'active']);
        }

        return redirect()->back()->with('success', 'Recurring invoice resumed.');
    }

    /**
     * Cancel a recurring invoice.
     */
    public function cancel(RecurringInvoice $recurringInvoice)
    {
        $companyId = CurrentCompanyService::requireId();

        if ($recurringInvoice->company_id !== $companyId) {
            abort(403);
        }

        $recurringInvoice->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Recurring invoice cancelled.');
    }

    /**
     * Manually generate an invoice from a recurring template.
     */
    public function generate(RecurringInvoice $recurringInvoice)
    {
        $companyId = CurrentCompanyService::requireId();

        if ($recurringInvoice->company_id !== $companyId) {
            abort(403);
        }

        if ($recurringInvoice->status !== 'active') {
            return redirect()->back()->with('error', 'Only active recurring invoices can generate invoices.');
        }

        $invoice = $this->recurringInvoiceService->generateInvoice($recurringInvoice);

        if ($invoice) {
            return redirect()->route('user.invoices.show', $invoice)
                ->with('success', 'Invoice generated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to generate invoice.');
    }
}
