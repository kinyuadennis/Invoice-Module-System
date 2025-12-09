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
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        // Scope to current user's active company invoices
        $query = Invoice::where('company_id', $companyId)
            ->with('client')
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
        $user = Auth::user();
        $companies = $user->ownedCompanies()->get();

        // Get company ID from request or use active company
        $companyId = request()->input('company_id', CurrentCompanyService::id());

        if (! $companyId) {
            if ($companies->count() === 0) {
                return redirect()->route('company.setup')
                    ->with('error', 'Please create a company first.');
            }
            $companyId = $companies->first()->id;
        }

        // Ensure user owns this company
        if (! $companies->pluck('id')->contains($companyId)) {
            $companyId = CurrentCompanyService::requireId();
        }

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'address', 'kra_pin')
            ->get();

        // Get service library from invoice items (company-specific)
        $services = $this->invoiceService->getServiceLibrary($companyId);

        // Get company for invoice format settings
        $company = Company::findOrFail($companyId);

        // Get next invoice number preview
        $prefixService = app(\App\Services\InvoicePrefixService::class);
        $nextInvoiceNumber = $prefixService->getNextInvoiceNumberPreview($company);

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
                'companies' => $companies,
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
        $invoice = $this->invoiceService->createInvoice($request);

        // If AJAX request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'invoice_id' => $invoice->id,
                'redirect' => route('user.invoices.show', $invoice->id),
            ]);
        }

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

        return view('user.invoices.show', [
            'invoice' => $this->invoiceService->formatInvoiceForShow($invoice),
        ]);
    }

    public function edit($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems'])
            ->findOrFail($id);

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
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);

        // Update invoice using service
        $this->invoiceService->updateInvoice($invoice, $request);

        return redirect()->route('user.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);

        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return back()->withErrors([
                'message' => 'Only draft invoices can be deleted.',
            ]);
        }

        $invoice->delete();

        return redirect()->route('user.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePdf($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'platformFees', 'user', 'company.invoiceTemplate'])
            ->findOrFail($id);

        // Format invoice data for PDF
        $formattedInvoice = $this->invoiceService->formatInvoiceForShow($invoice);

        // Add platform fee if exists
        $platformFee = $invoice->platformFees->first();
        if ($platformFee) {
            $formattedInvoice['platform_fee'] = (float) $platformFee->fee_amount;
        }

        // Company data is already included in formatInvoiceForShow via the trait

        // Get template from invoice (if stored) or company's active template
        $template = $invoice->getInvoiceTemplate();

        // Fallback to modern-clean if template view doesn't exist
        $templateView = $template->view_path;
        if (! view()->exists($templateView)) {
            // Fallback to default template
            $templateView = 'invoices.templates.modern-clean';
            \Log::warning("Template view not found: {$template->view_path}, using fallback: {$templateView}", [
                'template_id' => $template->id,
                'invoice_id' => $invoice->id,
            ]);
        }

        // Generate PDF using selected template
        $pdf = Pdf::loadView($templateView, [
            'invoice' => $formattedInvoice,
            'template' => $template, // Pass template object for CSS file path
        ]);

        // Set PDF options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);

        // If template has custom CSS, ensure it's accessible
        if ($template->css_file) {
            $cssPath = public_path("css/invoice-templates/{$template->css_file}");
            if (file_exists($cssPath)) {
                $pdf->setOption('chroot', public_path());
            }
        }

        // Generate filename
        $filename = 'invoice-'.($formattedInvoice['invoice_number'] ?? $invoice->id).'.pdf';

        // Return PDF download
        return $pdf->download($filename);
    }

    /**
     * Send invoice via email
     */
    public function sendEmail($id)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems'])
            ->findOrFail($id);

        // TODO: Implement email sending
        // Queue email job for later implementation
        return response()->json([
            'success' => true,
            'message' => 'Email queued for sending',
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
            $companyId = Auth::user()->company_id;

            if (! $companyId) {
                abort(403, 'You must belong to a company.');
            }

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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            abort(403, 'You must belong to a company.');
        }

        // Ensure invoice belongs to user's company
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
        if ($clientId) {
            $clientModel = \App\Models\Client::find($clientId);
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
            $user = Auth::user();
            $companyId = $user->company_id;

            if (! $companyId) {
                return response()->json(['success' => false, 'error' => 'You must belong to a company.'], 403);
            }

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

            // If draft_id exists, update it; otherwise create new draft
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

                // Update invoice (excluding prefix fields)
                $updateData = array_intersect_key($validated, array_flip([
                    'client_id', 'issue_date', 'due_date', 'notes', 'terms_and_conditions',
                    'po_number', 'vat_registered', 'discount', 'discount_type',
                ]));
                $invoice->update($updateData);

                // Update items if provided
                if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
                    $invoice->invoiceItems()->delete();
                    foreach ($request->items as $item) {
                        if (! empty($item['description'])) {
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
                    }
                    // Recalculate totals
                    $this->invoiceService->updateTotals($invoice);
                }
            } else {
                // Create new draft - client_id is optional for drafts, but items are required
                if (empty($request->input('items')) || count($request->input('items', [])) === 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'At least one item is required to create a draft.',
                    ], 422);
                }

                // If no client_id, we can still create a draft but need to handle it
                if (empty($validated['client_id'])) {
                    // Allow draft without client - will be required when finalizing
                    $validated['client_id'] = null;
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

                // Get company
                $company = Company::findOrFail($companyId);

                // Prepare invoice data
                $invoiceData = array_merge($validated, [
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'status' => 'draft',
                ]);

                // Ensure client_id is set (can be null for drafts)
                if (! isset($invoiceData['client_id'])) {
                    $invoiceData['client_id'] = null;
                }

                // Generate invoice number if not provided
                if (empty($invoiceData['invoice_reference'])) {
                    $prefixService = app(\App\Services\InvoicePrefixService::class);
                    $prefix = $prefixService->getActivePrefix($company);
                    $serialNumber = $prefixService->generateNextSerialNumber($company, $prefix);
                    $fullNumber = $prefixService->generateFullNumber($company, $prefix, $serialNumber);

                    $invoiceData['prefix_used'] = $prefix->prefix;
                    $invoiceData['serial_number'] = $serialNumber;
                    $invoiceData['full_number'] = $fullNumber;
                    $invoiceData['invoice_reference'] = $fullNumber;
                }

                // Set default issue_date if not provided
                if (empty($invoiceData['issue_date'])) {
                    $invoiceData['issue_date'] = now()->toDateString();
                }

                // Calculate totals
                $subtotal = 0;
                foreach ($items as $item) {
                    $itemTotal = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
                    $subtotal += $itemTotal;
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

                $invoiceData['subtotal'] = $subtotal;
                $invoiceData['discount'] = $discountAmount;
                $invoiceData['tax'] = $vatAmount;
                $invoiceData['vat_amount'] = $vatAmount;
                $invoiceData['platform_fee'] = $platformFee;
                $invoiceData['total'] = $totalBeforeFee;
                $invoiceData['grand_total'] = $grandTotal;

                // Create invoice
                $invoice = Invoice::create($invoiceData);

                // Add invoice items
                foreach ($items as $item) {
                    $invoice->invoiceItems()->create([
                        'company_id' => $companyId,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'] ?? 1,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'vat_included' => $item['vat_included'] ?? false,
                        'vat_rate' => $item['vat_rate'] ?? 16.00,
                        'total_price' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                    ]);
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while saving the draft: '.$e->getMessage(),
            ], 500);
        }
    }
}
