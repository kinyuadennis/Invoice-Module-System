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

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
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
        // Eager load relations to prevent N+1 queries
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'company'])
            ->findOrFail($id);

        // Update invoice using service
        $this->invoiceService->updateInvoice($invoice, $request);

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
     * Generate PDF for invoice
     */
    public function generatePdf($id)
    {
        // Increase execution time for PDF generation
        set_time_limit(60); // 60 seconds should be enough

        $companyId = CurrentCompanyService::requireId();

        // Ensure invoice belongs to user's active company (from session)
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

        // CRITICAL: Use invoice's company, NOT user's active company
        // The PDF must show the company that created the invoice, not the currently selected company
        if (! $invoice->company) {
            throw new \RuntimeException('Invoice company not found. Cannot generate PDF.');
        }

        // Get template from invoice (if stored) or invoice's company's active template
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

        // Prepare logo path for PDF (resolve before passing to view to avoid blocking)
        $logoPath = null;
        if ($invoice->company->logo) {
            $logoPath = $invoice->company->logo;
            // Convert storage path to absolute file path
            if (! str_starts_with($logoPath, 'http://') && ! str_starts_with($logoPath, 'https://')) {
                $fullPath = public_path('storage/'.$logoPath);
                if (file_exists($fullPath)) {
                    $logoPath = $fullPath;
                } else {
                    $logoPath = null; // Logo file doesn't exist, skip it
                }
            }
        }

        // Add resolved logo path to formatted invoice
        if ($logoPath) {
            $formattedInvoice['company']['logo_path'] = $logoPath;
        }

        // Generate PDF using selected template
        // CRITICAL: Pass invoice's company, NOT activeCompany() or getCurrentCompany()
        try {
            $pdf = Pdf::loadView($templateView, [
                'invoice' => $formattedInvoice,
                'template' => $template, // Pass template object for CSS file path
                'company' => $invoice->company, // Pass invoice's company for PDF settings
            ]);

            // Set PDF options
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('enable-php', true); // Enable PHP for page numbering
            $pdf->setOption('isRemoteEnabled', false); // Disable remote to prevent timeouts
            $pdf->setOption('isHtml5ParserEnabled', true);

            // Font options to prevent font errors
            // Disable font subsetting to avoid glyph bbox errors
            $pdf->setOption('enable_font_subsetting', false);

            // Suppress font warnings to prevent glyph bbox errors from breaking PDF generation
            $pdf->setOption('show_warnings', false);

            // Performance options
            $pdf->setOption('dpi', 96); // Lower DPI for faster rendering
        } catch (\Exception $e) {
            \Log::error('PDF generation error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate PDF. Please try again or contact support.');
        }

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

            // Calculate totals using service (authoritative source)
            $calculationResult = $this->invoiceService->calculatePreviewTotals($validated['items'], [
                'vat_registered' => $validated['vat_registered'] ?? false,
                'discount' => $validated['discount'] ?? 0,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
            ]);

            // Extract values for backward compatibility
            $subtotal = $calculationResult['subtotal'];
            $discountAmount = $calculationResult['discount'];
            $subtotalAfterDiscount = $calculationResult['subtotal_after_discount'];
            $vatAmount = $calculationResult['vat_amount'];
            $totalBeforeFee = $calculationResult['total'];
            $platformFee = $calculationResult['platform_fee'];
            $grandTotal = $calculationResult['grand_total'];

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

            // Calculate totals using service (authoritative source)
            $calculationResult = $this->invoiceService->calculatePreviewTotals($validated['items'], [
                'vat_registered' => $validated['vat_registered'] ?? false,
                'discount' => $validated['discount'] ?? 0,
                'discount_type' => $validated['discount_type'] ?? 'fixed',
            ]);

            // Extract values for backward compatibility
            $subtotal = $calculationResult['subtotal'];
            $discountAmount = $calculationResult['discount'];
            $discountType = $calculationResult['discount_type'];
            $subtotalAfterDiscount = $calculationResult['subtotal_after_discount'];
            $vatAmount = $calculationResult['vat_amount'];
            $totalBeforeFee = $calculationResult['total'];
            $platformFee = $calculationResult['platform_fee'];
            $grandTotal = $calculationResult['grand_total'];

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
}
