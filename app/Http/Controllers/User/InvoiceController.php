<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Services\InvoiceService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;
    protected \App\Services\InvoiceSnapshotService $snapshotService;
    protected \App\Services\PdfInvoiceRenderer $pdfRenderer;

    public function __construct(
        InvoiceService $invoiceService,
        \App\Services\InvoiceSnapshotService $snapshotService,
        \App\Services\PdfInvoiceRenderer $pdfRenderer
    ) {
        $this->invoiceService = $invoiceService;
        $this->snapshotService = $snapshotService;
        $this->pdfRenderer = $pdfRenderer;
    }

    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        // Scope to current user's active company invoices (from session)
        // Eager load all necessary relations to prevent N+1 queries
        $query = Invoice::where('company_id', $companyId)
            ->with(['client', 'company', 'invoiceItems'])
            ->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_reference', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
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
            $now = Carbon::now();

            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
                    break;
            }
        }

        $invoices = $query->paginate(15)->through(function (Invoice $invoice) {
            return $this->invoiceService->formatInvoiceForList($invoice);
        });

        return view('user.invoices.index', [
            'invoices' => $invoices,
            'stats' => $this->invoiceService->getInvoiceStats($companyId),
            'filters' => $request->only(['search', 'status', 'dateRange']),
        ]);
    }

    public function create()
    {
        // Always use session-based active company - never allow request parameter override
        // This ensures clients and invoices are scoped to the correct company
        $companyId = CurrentCompanyService::requireId();

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'address', 'kra_pin')
            ->get();

        // Get service library from invoice items (company-specific)
        $services = $this->invoiceService->getServiceLibrary($companyId);

        // Get company for invoice format settings
        $company = Company::findOrFail($companyId);

        // Get next invoice number preview
        $prefixService = app(\App\Services\InvoicePrefixService::class);
        $clientId = request()->input('client_id');

        // Check if client-specific numbering is enabled and client is provided
        if ($company->use_client_specific_numbering && $clientId) {
            // Get client-specific invoice number preview
            $client = Client::where('id', $clientId)
                ->where('company_id', $companyId)
                ->first();

            if ($client) {
                $nextInvoiceNumber = $prefixService->getNextClientInvoiceNumberPreview($company, $client);
            } else {
                $nextInvoiceNumber = 'Select a client to see invoice number';
            }
        } elseif ($company->use_client_specific_numbering && ! $clientId) {
            // Client-specific numbering enabled but no client selected
            $nextInvoiceNumber = 'Select a client to see invoice number';
        } else {
            // Global numbering (default)
            $nextInvoiceNumber = $prefixService->getNextInvoiceNumberPreview($company);
        }

        // Check if user wants one-page builder (default) or wizard
        $builderType = request()->get('builder', 'one-page'); // 'one-page' or 'wizard'

        // Only return JSON if explicitly requested via Accept header AND X-Requested-With header
        // This prevents regular browser requests from being treated as AJAX
        $isExplicitAjax = request()->wantsJson()
            && request()->hasHeader('X-Requested-With')
            && request()->header('X-Requested-With') === 'XMLHttpRequest';

        if ($isExplicitAjax) {
            return response()->json([
                'success' => true,
                'next_invoice_number' => $nextInvoiceNumber,
                'active_prefix' => $company->activeInvoicePrefix()?->prefix ?? $company->invoice_prefix ?? 'INV',
            ]);
        }

        if ($builderType === 'one-page') {
            return view('user.invoices.create-one-page', [
                'clients' => $clients,
                'services' => $services,
                'company' => $company,
                'nextInvoiceNumber' => $nextInvoiceNumber,
                'selectedCompanyId' => $companyId,
            ]);
        }

        return view('user.invoices.create', [
            'clients' => $clients,
            'services' => $services,
            'company' => $company,
            'nextInvoiceNumber' => $nextInvoiceNumber,
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $invoice = $this->invoiceService->createInvoice($request);

        // Clear dashboard cache when invoice is created
        Cache::forget("dashboard_data_{$companyId}");

        // If AJAX request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'invoice_id' => $invoice->id,
                'redirect' => route('user.invoices.show', $invoice->id),
            ]);
        }

        // Create initial draft snapshot
        $this->snapshotService->createSnapshot($invoice, 'draft');

        return redirect()->route('user.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'payments', 'company'])
            ->findOrFail($id);

        $paymentService = new \App\Http\Services\PaymentService(new \App\Http\Services\InvoiceStatusService);
        $paymentSummary = $paymentService->getPaymentSummary($invoice);

        return view('user.invoices.show', [
            'invoice' => $this->invoiceService->formatInvoiceForShow($invoice),
            'paymentSummary' => $paymentSummary,
        ]);
    }

    public function edit($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems'])
            ->findOrFail($id);

        // Restrict editing based on status
        // Draft: fully editable
        // Sent/Overdue: limited editing (redirect to show page with message)
        // Paid/Cancelled: not editable (redirect to show page)
        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return redirect()->route('user.invoices.show', $invoice->id)
                ->with('error', 'Paid and cancelled invoices cannot be edited.');
        }

        if (in_array($invoice->status, ['sent', 'overdue'])) {
            return redirect()->route('user.invoices.show', $invoice->id)
                ->with('warning', 'Sent invoices have limited editing to maintain bookkeeping integrity. Only draft invoices can be fully edited.');
        }

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'address')
            ->get();

        // Get service library from invoice items (company-specific)
        $services = $this->invoiceService->getServiceLibrary($companyId);

        return view('user.invoices.edit', [
            'invoice' => $this->invoiceService->formatInvoiceForEdit($invoice),
            'clients' => $clients,
            'services' => $services,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        // Eager load relations to prevent N+1 queries
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'company'])
            ->findOrFail($id);

        // Handle status updates separately with validation
        if ($request->has('status')) {
            $statusService = new \App\Http\Services\InvoiceStatusService;
            $newStatus = $request->input('status');

            if (! $statusService->updateStatus($invoice, $newStatus)) {
                return back()->withErrors([
                    'status' => 'Invalid status transition from '.$invoice->status.' to '.$newStatus.'.',
                ]);
            }

            // Refresh invoice to get updated status
            $invoice->refresh();

            // If marking as paid, also record payment if amount provided
            if ($newStatus === 'paid' && $request->has('payment_amount')) {
                $paymentService = new \App\Http\Services\PaymentService(new \App\Http\Services\InvoiceStatusService);
                $paymentService->recordPayment($invoice, $request);
            }

            // Create snapshot for status change (especially for paid)
            $this->snapshotService->createSnapshot($invoice, $newStatus);
        } else {
            // Restrict full editing based on status
            // Draft: fully editable
            // Sent/Overdue: allow minor adjustments only (notes, payment details)
            // Paid/Cancelled: no editing
            if (in_array($invoice->status, ['paid', 'cancelled'])) {
                return back()->withErrors([
                    'status' => 'Paid and cancelled invoices cannot be edited.',
                ]);
            }

            if (in_array($invoice->status, ['sent', 'overdue'])) {
                // Allow limited editing (notes, payment details) but prevent major changes
                $allowedFields = ['notes', 'terms_and_conditions', 'payment_details'];
                $requestData = $request->only($allowedFields);

                if ($request->hasAny(['client_id', 'items', 'subtotal', 'tax', 'discount'])) {
                    return back()->withErrors([
                        'message' => 'Sent invoices have limited editing. Only notes and payment details can be modified. To make major changes, cancel this invoice and create a new one.',
                    ]);
                }

                // Update only allowed fields
                $invoice->update($requestData);
            } else {
                // Draft: full editing allowed
                $this->invoiceService->updateInvoice($invoice, $request);
                
                // Create snapshot for draft update
                $invoice->refresh();
                $this->snapshotService->createSnapshot($invoice, 'draft');
            }
        }

        // Clear dashboard cache when invoice is updated
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        // Eager load relations to prevent N+1 queries
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'company'])
            ->findOrFail($id);

        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return back()->withErrors([
                'message' => 'Only draft invoices can be deleted.',
            ]);
        }

        $invoice->delete();

        // Clear dashboard cache when invoice is deleted
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Duplicate an existing invoice as a new draft.
     */
    public function duplicate($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        $originalInvoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'company'])
            ->findOrFail($id);

        // Create a new draft invoice with copied data
        $newInvoice = $originalInvoice->replicate();
        $newInvoice->status = 'draft';
        $newInvoice->invoice_reference = null; // Will be regenerated
        $newInvoice->invoice_number = null;
        $newInvoice->full_number = null;
        $newInvoice->prefix_used = null;
        $newInvoice->serial_number = null;
        $newInvoice->client_sequence = null;
        $newInvoice->issue_date = now()->toDateString();
        $newInvoice->due_date = now()->addDays(30)->toDateString();
        $newInvoice->save();

        // Copy invoice items
        foreach ($originalInvoice->invoiceItems as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $newInvoice->id;
            $newItem->save();
        }

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        // Redirect to edit the new invoice
        return redirect()->route('user.invoices.edit', $newInvoice->id)
            ->with('success', 'Invoice duplicated successfully. You can now edit the new draft.');
    }

    /**
     * Save current invoice as a template.
     */
    public function saveAsTemplate(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'template_data' => 'required|array',
            'is_favorite' => 'nullable|boolean',
        ]);

        $template = \App\Models\UserInvoiceTemplate::create([
            'user_id' => $user->id,
            'company_id' => $companyId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'template_data' => $validated['template_data'],
            'is_favorite' => $validated['is_favorite'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully.',
            'template' => $template,
        ]);
    }

    /**
     * Get all templates for the current company.
     */
    public function getTemplates(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        $templates = \App\Models\UserInvoiceTemplate::forCompany($companyId)
            ->where('user_id', $user->id)
            ->orderBy('is_favorite', 'desc')
            ->mostUsed()
            ->get();

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Load a template.
     */
    public function loadTemplate(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        $template = \App\Models\UserInvoiceTemplate::forCompany($companyId)
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Record usage
        $template->recordUsage();

        return response()->json([
            'success' => true,
            'template_data' => $template->template_data,
        ]);
    }

    /**
     * Delete a template.
     */
    public function deleteTemplate(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        $template = \App\Models\UserInvoiceTemplate::forCompany($companyId)
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully.',
        ]);
    }

    /**
     * Toggle favorite status of a template.
     */
    public function toggleFavorite(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        $template = \App\Models\UserInvoiceTemplate::forCompany($companyId)
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $template->update(['is_favorite' => ! $template->is_favorite]);

        return response()->json([
            'success' => true,
            'is_favorite' => $template->is_favorite,
        ]);
    }

    /**
     * Generate PDF for invoice
     */
    /**
     * Generate PDF for invoice
     */
    public function generatePdf($id)
    {
        // Increase execution time for PDF generation
        set_time_limit(60);

        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        $invoice = Invoice::where('company_id', $companyId)
            ->findOrFail($id);

        // Find existing snapshot or create one
        $snapshot = $this->snapshotService->findLatestSnapshot($invoice);

        if (! $snapshot) {
            // If no snapshot exists, create one based on current status
            // For draft, we create a temporary snapshot (or permanent if we want to track history)
            // For sent/paid, we backfill the snapshot
            $snapshot = $this->snapshotService->createSnapshot($invoice, $invoice->status);
        }

        // Render PDF from snapshot
        try {
            $pdfContent = $this->pdfRenderer->render($snapshot);
            
            // Generate filename
            $filename = 'invoice-'.($snapshot->snapshot_data['invoice_details']['full_number'] ?? $invoice->invoice_number ?? $invoice->id).'.pdf';

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');

        } catch (\Exception $e) {
            \Log::error('PDF generation error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate PDF. Please try again or contact support.');
        }
    }

    /**
     * Send invoice via email
     */
    public function sendEmail(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'company'])
            ->findOrFail($id);

        // Validate invoice can be sent
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send an invoice that is already paid.',
            ], 422);
        }

        if ($invoice->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send a cancelled invoice.',
            ], 422);
        }

        if (! $invoice->client) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice must have a client before sending.',
            ], 422);
        }

        if (! $invoice->client->email) {
            return response()->json([
                'success' => false,
                'message' => 'Client must have an email address to send invoice.',
            ], 422);
        }

        // Update status to 'sent' if it's currently 'draft'
        if ($invoice->status === 'draft') {
            $statusService = new \App\Http\Services\InvoiceStatusService;
            $statusService->markAsSent($invoice);
            $invoice->refresh();
        }

        // Create snapshot for the sent invoice
        $this->snapshotService->createSnapshot($invoice, 'sent');

        // Queue email job with PDF attachment
        \App\Jobs\SendInvoiceEmailJob::dispatch($invoice);

        return response()->json([
            'success' => true,
            'message' => 'Invoice email has been queued for sending.',
            'status' => $invoice->status,
        ]);
    }

    /**
     * Send invoice via WhatsApp
     */
    public function sendWhatsApp($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems'])
            ->findOrFail($id);

        // TODO: Implement WhatsApp sending
        // Queue WhatsApp job for later implementation
        return response()->json([
            'success' => true,
            'message' => 'WhatsApp message queued for sending',
        ]);
    }

    /**
     * Generate live preview of invoice (for one-page builder)
     */
    public function preview(Request $request)
    {
        try {
            $companyId = CurrentCompanyService::requireId();

            // Validate request data
            $validated = $request->validate([
                'client_id' => 'nullable|exists:clients,id',
                'client' => 'nullable|array',
                'issue_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'invoice_number' => 'nullable|string',
                'po_number' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'vat_registered' => 'nullable|boolean',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:fixed,percentage',
                'notes' => 'nullable|string',
                'terms_and_conditions' => 'nullable|string',
                'payment_method' => 'nullable|string',
                'payment_details' => 'nullable|string',
            ]);

            // Get company
            $company = Company::findOrFail($companyId);

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
            }

            // Apply discount
            $discount = $validated['discount'] ?? 0;
            $discountType = $validated['discount_type'] ?? 'fixed';
            $discountAmount = 0;
            if ($discount > 0) {
                if ($discountType === 'percentage') {
                    $discountAmount = $subtotal * ($discount / 100);
                } else {
                    $discountAmount = $discount;
                }
            }
            $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

            // Calculate VAT
            $vatAmount = 0;
            if ($validated['vat_registered'] ?? false) {
                $vatAmount = $subtotalAfterDiscount * 0.16; // 16% VAT
            }

            $totalBeforeFee = $subtotalAfterDiscount + $vatAmount;
            $platformFee = $totalBeforeFee * 0.03; // 3% platform fee
            $grandTotal = $totalBeforeFee + $platformFee;

            // Format invoice data for preview - include ALL fields
            $issueDate = $validated['issue_date'] ?? now()->toDateString();
            $invoiceData = [
                'id' => 'preview',
                'invoice_number' => $validated['invoice_number'] ?? $request->input('invoice_number', 'INV-0001'),
                'po_number' => $validated['po_number'] ?? null,
                'status' => 'draft',
                'issue_date' => $issueDate,
                'date' => $issueDate, // Some templates use 'date' instead of 'issue_date'
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'discount_type' => $discountType,
                'vat_registered' => $validated['vat_registered'] ?? false,
                'vat_amount' => $vatAmount,
                'platform_fee' => $platformFee,
                'total' => $totalBeforeFee,
                'grand_total' => $grandTotal,
                'notes' => $validated['notes'] ?? null,
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_details' => $validated['payment_details'] ?? null,
                'client' => $this->formatClientDataForPreview($validated['client'] ?? null, $validated['client_id'] ?? null),
                'client_id' => $validated['client_id'] ?? null,
                'items' => array_map(function ($item) {
                    $quantity = (float) ($item['quantity'] ?? 1);
                    $unitPrice = (float) ($item['unit_price'] ?? 0);
                    $totalPrice = $quantity * $unitPrice;

                    return [
                        'id' => null,
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'total' => $totalPrice, // Keep for backward compatibility
                    ];
                }, $validated['items']),
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'address' => $company->address,
                    'kra_pin' => $company->kra_pin,
                    'logo' => $company->logo,
                ],
            ];

            // Get template from company's selected template (database-driven)
            $template = $company->getActiveInvoiceTemplate();
            $templateView = $template->view_path ?? 'invoices.templates.modern-clean';

            // Check if view exists, fallback to default if not
            if (! view()->exists($templateView)) {
                $defaultTemplate = \App\Models\InvoiceTemplate::getDefault();
                $templateView = $defaultTemplate->view_path ?? 'invoices.templates.modern-clean';
            }

            // Convert logo to web URL for browser preview
            if ($company->logo) {
                $invoiceData['company']['logo'] = \Illuminate\Support\Facades\Storage::url($company->logo);
                $invoiceData['company']['logo_path'] = $company->logo; // Keep original path for PDF
            }

            // Add is_preview flag for template rendering
            $invoiceData['is_preview'] = true;

            // Render preview HTML using the selected template
            $html = view($templateView, [
                'invoice' => $invoiceData,
                'template' => $template,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'totals' => [
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'vat_amount' => $vatAmount,
                    'platform_fee' => $platformFee,
                    'total' => $totalBeforeFee,
                    'grand_total' => $grandTotal,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while generating preview: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show invoice preview in iframe (for draft/preview).
     * Handles both GET (query params) and POST requests.
     */
    public function previewFrame(Request $request)
    {
        try {
            // Use session-based active company
            $companyId = CurrentCompanyService::requireId();

            // Get company
            $company = Company::findOrFail($companyId);

            // Validate request data (works with both GET query params and POST body)
            $validated = $request->validate([
                'client_id' => 'nullable|exists:clients,id',
                'client' => 'nullable|array',
                'client.name' => 'nullable|string',
                'client.email' => 'nullable|email',
                'client.phone' => 'nullable|string',
                'client.address' => 'nullable|string',
                'client.kra_pin' => 'nullable|string',
                'issue_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'invoice_number' => 'nullable|string',
                'po_number' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'vat_registered' => 'nullable|boolean',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:fixed,percentage',
                'notes' => 'nullable|string',
                'terms_and_conditions' => 'nullable|string',
                'payment_method' => 'nullable|string',
                'payment_details' => 'nullable|string',
            ]);

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
            }

            // Apply discount
            $discount = $validated['discount'] ?? 0;
            $discountType = $validated['discount_type'] ?? 'fixed';
            $discountAmount = 0;
            if ($discount > 0) {
                if ($discountType === 'percentage') {
                    $discountAmount = $subtotal * ($discount / 100);
                } else {
                    $discountAmount = $discount;
                }
            }
            $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

            // Calculate VAT
            $vatAmount = 0;
            if ($validated['vat_registered'] ?? false) {
                $vatAmount = $subtotalAfterDiscount * 0.16;
            }

            $totalBeforeFee = $subtotalAfterDiscount + $vatAmount;
            $platformFee = $totalBeforeFee * 0.03;
            $grandTotal = $totalBeforeFee + $platformFee;

            // Format invoice data for preview - include ALL fields
            $issueDate = $validated['issue_date'] ?? now()->toDateString();
            $invoiceData = [
                'id' => 'preview',
                'invoice_number' => $validated['invoice_number'] ?? $request->input('invoice_number', 'INV-0001'),
                'po_number' => $validated['po_number'] ?? null,
                'status' => 'draft',
                'issue_date' => $issueDate,
                'date' => $issueDate,
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'discount_type' => $discountType,
                'vat_registered' => $validated['vat_registered'] ?? false,
                'vat_amount' => $vatAmount,
                'platform_fee' => $platformFee,
                'total' => $totalBeforeFee,
                'grand_total' => $grandTotal,
                'notes' => $validated['notes'] ?? null,
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_details' => $validated['payment_details'] ?? null,
                'client' => $this->formatClientDataForPreview($validated['client'] ?? null, $validated['client_id'] ?? null),
                'client_id' => $validated['client_id'] ?? null,
                'items' => array_map(function ($item) {
                    $quantity = (float) ($item['quantity'] ?? 1);
                    $unitPrice = (float) ($item['unit_price'] ?? 0);
                    $totalPrice = $quantity * $unitPrice;

                    return [
                        'id' => null,
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'total' => $totalPrice, // Keep for backward compatibility
                    ];
                }, $validated['items']),
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'address' => $company->address,
                    'kra_pin' => $company->kra_pin,
                    'logo' => $company->logo,
                ],
            ];

            // Get template from company's selected template (database-driven)
            $template = $company->getActiveInvoiceTemplate();
            $templateView = $template->view_path ?? 'invoices.templates.modern-clean';

            // Check if view exists, fallback to default if not
            if (! view()->exists($templateView)) {
                $defaultTemplate = \App\Models\InvoiceTemplate::getDefault();
                $templateView = $defaultTemplate->view_path ?? 'invoices.templates.modern-clean';
            }

            // Convert logo to web URL for browser preview
            if ($company->logo) {
                $invoiceData['company']['logo'] = \Illuminate\Support\Facades\Storage::url($company->logo);
                $invoiceData['company']['logo_path'] = $company->logo;
            }

            // Add is_preview flag for template rendering
            $invoiceData['is_preview'] = true;

            // Render preview HTML using the selected template
            return view($templateView, [
                'invoice' => $invoiceData,
                'template' => $template,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            abort(422, 'Invalid preview data: '.implode(', ', array_flatten($e->errors())));
        } catch (\Exception $e) {
            abort(500, 'Error generating preview: '.$e->getMessage());
        }
    }

    /**
     * Show invoice preview in iframe from existing invoice.
     */
    public function previewFrameFromInvoice($id)
    {
        // Use session-based active company
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'company'])
            ->findOrFail($id);

        // Format invoice data using the service
        $formattedInvoice = $this->invoiceService->formatInvoiceForShow($invoice);

        // Get template from invoice (if stored) or company's active template
        $template = $invoice->getInvoiceTemplate();
        $templateView = $template->view_path ?? 'invoices.templates.modern-clean';

        // Check if view exists, fallback to default if not
        if (! view()->exists($templateView)) {
            $defaultTemplate = \App\Models\InvoiceTemplate::getDefault();
            $templateView = $defaultTemplate->view_path ?? 'invoices.templates.modern-clean';
        }

        // Convert logo to web URL for browser preview
        if ($invoice->company && $invoice->company->logo) {
            $formattedInvoice['company']['logo'] = \Illuminate\Support\Facades\Storage::url($invoice->company->logo);
            $formattedInvoice['company']['logo_path'] = $invoice->company->logo;
        }

        // Add is_preview flag for template rendering
        $formattedInvoice['is_preview'] = true;

        // Render preview HTML using the selected template
        return view($templateView, [
            'invoice' => $formattedInvoice,
            'template' => $template,
        ]);
    }

    /**
     * Format client data for preview.
     */
    private function formatClientDataForPreview(?array $client, ?int $clientId): ?array
    {
        if ($client && is_array($client)) {
            return $client;
        }

        // If client_id is provided but client data is not, try to load it
        // Scope by company_id to prevent cross-company data access
        if ($clientId) {
            $companyId = CurrentCompanyService::requireId();
            $clientModel = \App\Models\Client::where('id', $clientId)
                ->where('company_id', $companyId)
                ->first();
            if ($clientModel) {
                return [
                    'id' => $clientModel->id,
                    'name' => $clientModel->name,
                    'email' => $clientModel->email,
                    'phone' => $clientModel->phone,
                    'address' => $clientModel->address,
                    'kra_pin' => $clientModel->kra_pin,
                ];
            }
        }

        return null;
    }

    /**
     * Autosave invoice draft
     */
    public function autosave(Request $request)
    {
        try {
            // Use session-based active company
            $companyId = CurrentCompanyService::requireId();

            $validated = $request->validate([
                'draft_id' => 'nullable|exists:invoices,id',
                'client_id' => 'nullable|exists:clients,id',
                'issue_date' => 'nullable|date',
                'due_date' => 'nullable|date',
                'items' => 'nullable|array',
                'notes' => 'nullable|string',
                'terms_and_conditions' => 'nullable|string',
                'po_number' => 'nullable|string|max:100',
                'vat_registered' => 'nullable|boolean',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:fixed,percentage',
            ]);

            // If draft_id exists, update existing draft
            if ($request->has('draft_id') && $request->draft_id) {
                $invoice = Invoice::where('company_id', $companyId)
                    ->where('id', $request->draft_id)
                    ->where('status', 'draft')
                    ->first();

                if (! $invoice) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Draft not found or not editable.',
                    ], 404);
                }

                // Update invoice (excluding prefix fields which are immutable)
                $updateData = array_intersect_key($validated, array_flip([
                    'client_id', 'issue_date', 'due_date', 'notes', 'terms_and_conditions',
                    'po_number', 'vat_registered', 'discount', 'discount_type',
                ]));
                $invoice->update($updateData);

                // Update items if provided
                if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
                    // Filter out empty items
                    $items = array_filter($request->input('items', []), function ($item) {
                        return ! empty($item['description']) && trim($item['description']) !== '';
                    });

                    if (count($items) > 0) {
                        $invoice->invoiceItems()->delete();
                        foreach ($items as $item) {
                            $invoice->invoiceItems()->create([
                                'company_id' => $companyId,
                                'description' => $item['description'] ?? '',
                                'quantity' => $item['quantity'] ?? 1,
                                'unit_price' => $item['unit_price'] ?? 0,
                                'vat_included' => $item['vat_included'] ?? false,
                                'vat_rate' => $item['vat_rate'] ?? 16.00,
                                'total_price' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                            ]);
                        }
                        // Recalculate totals
                        $this->invoiceService->updateTotals($invoice);
                    }
                }
            } else {
                // Create new draft using InvoiceService for proper invoice number generation
                // This ensures duplicate handling, client-specific numbering, and proper locking
                if (empty($request->input('items')) || count($request->input('items', [])) === 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'At least one item is required to create a draft.',
                    ], 422);
                }

                // Filter out empty items
                $items = array_filter($request->input('items', []), function ($item) {
                    return ! empty($item['description']) && trim($item['description']) !== '';
                });

                if (count($items) === 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'At least one valid item is required to create a draft.',
                    ], 422);
                }

                // Prepare request data for InvoiceService
                // Create a new request with merged data to ensure proper format
                $serviceRequest = Request::create(
                    $request->url(),
                    $request->method(),
                    array_merge($request->all(), [
                        'status' => 'draft',
                        'items' => array_values($items), // Re-index array
                    ])
                );
                $serviceRequest->setUserResolver($request->getUserResolver());
                $serviceRequest->headers->replace($request->headers->all());

                // Use InvoiceService to create invoice with proper duplicate handling
                // This method handles:
                // - Client-specific numbering if enabled
                // - Proper invoice number generation with database locking
                // - Duplicate prevention
                // - Item creation and totals calculation
                try {
                    $invoice = $this->invoiceService->createInvoice($serviceRequest);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle duplicate entry errors specifically
                    if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                        // Retry once with a fresh request (invoice number will be regenerated)
                        // The InvoiceService will generate a new invoice number on retry
                        $invoice = $this->invoiceService->createInvoice($serviceRequest);
                    } else {
                        throw $e;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'draft_id' => $invoice->id,
                'message' => 'Draft saved successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (including duplicate entry)
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'success' => false,
                    'error' => 'A duplicate invoice number was generated. Please try again.',
                ], 409);
            }

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while saving the draft: '.$e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while saving the draft: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record a payment for an invoice.
     */
    public function recordPayment(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'payments'])
            ->findOrFail($id);

        // Validate invoice can receive payment
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This invoice is already fully paid.',
            ], 422);
        }

        if ($invoice->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot record payment for a cancelled invoice.',
            ], 422);
        }

        $paymentService = new \App\Http\Services\PaymentService(new \App\Http\Services\InvoiceStatusService);
        $payment = $paymentService->recordPayment($invoice, $request);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'payment' => $payment,
            'payment_summary' => $paymentService->getPaymentSummary($invoice->fresh()),
            'invoice_status' => $invoice->fresh()->status,
        ]);
    }
}
