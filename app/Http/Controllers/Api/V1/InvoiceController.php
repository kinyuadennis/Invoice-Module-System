<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Http\Services\InvoiceEtimsExportService;
use App\Http\Services\InvoiceFinalizationService;
use App\Http\Services\InvoiceService;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * InvoiceController (API)
 *
 * Handles invoice CRUD operations via API.
 * Critical rules:
 * - All queries scoped by company_id
 * - PATCH/UPDATE only allowed for draft invoices
 * - Finalized invoices read from snapshots
 * - Never touches calculation service or modifies snapshots
 */
class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    protected InvoiceFinalizationService $finalizationService;

    protected InvoiceEtimsExportService $etimsExportService;

    public function __construct(
        InvoiceService $invoiceService,
        InvoiceFinalizationService $finalizationService,
        InvoiceEtimsExportService $etimsExportService
    ) {
        $this->invoiceService = $invoiceService;
        $this->finalizationService = $finalizationService;
        $this->etimsExportService = $etimsExportService;
    }

    /**
     * List invoices (paginated).
     * Company-scoped automatically via middleware.
     */
    public function index(Request $request)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $query = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'snapshot']);

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100); // Max 100 per page
        $invoices = $query->latest()->paginate($perPage);

        return InvoiceResource::collection($invoices);
    }

    /**
     * Get single invoice.
     * For finalized invoices, includes snapshot indicator.
     */
    public function show(Request $request, $id)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'company', 'invoiceItems', 'snapshot'])
            ->findOrFail($id);

        return new InvoiceResource($invoice);
    }

    /**
     * Create new invoice (draft only).
     * Uses InvoiceService to handle business logic.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'po_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'vat_registered' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
        ]);

        $companyId = $request->input('company_id'); // Set by middleware

        // Ensure client belongs to company (if provided)
        if ($request->has('client_id')) {
            $client = \App\Models\Client::where('id', $request->client_id)
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        // Create invoice using service
        $invoice = $this->invoiceService->createInvoice($request);

        // Load relationships for response
        $invoice->load(['client', 'company', 'invoiceItems']);

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update invoice (draft only).
     * Finalized invoices cannot be updated.
     */
    public function update(Request $request, $id)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $invoice = Invoice::where('company_id', $companyId)
            ->findOrFail($id);

        // CRITICAL: Only draft invoices can be updated
        if (! $invoice->isDraft()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft invoices can be updated. This invoice is '.$invoice->status.'.',
            ], 403);
        }

        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'po_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'vat_registered' => 'nullable|boolean',
            'items' => 'nullable|array|min:1',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
        ]);

        // Ensure client belongs to company (if provided)
        if ($request->has('client_id')) {
            $client = \App\Models\Client::where('id', $request->client_id)
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        // Update invoice using service
        $invoice = $this->invoiceService->updateInvoice($request, $invoice);

        // Load relationships for response
        $invoice->load(['client', 'company', 'invoiceItems']);

        return new InvoiceResource($invoice);
    }

    /**
     * Finalize invoice.
     * Creates snapshot atomically.
     */
    public function finalize(Request $request, $id)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems'])
            ->findOrFail($id);

        // Only draft invoices can be finalized
        if (! $invoice->isDraft()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft invoices can be finalized. This invoice is '.$invoice->status.'.',
            ], 403);
        }

        try {
            // Finalize invoice and create snapshot atomically
            $finalizedInvoice = $this->finalizationService->finalizeInvoice($invoice);

            // Load relationships for response
            $finalizedInvoice->load(['client', 'company', 'invoiceItems', 'snapshot']);

            return (new InvoiceResource($finalizedInvoice))
                ->response()
                ->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to finalize invoice: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get PDF for invoice.
     * For finalized invoices, uses snapshot data.
     */
    public function pdf(Request $request, $id)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['snapshot'])
            ->findOrFail($id);

        // Redirect to web PDF endpoint (PDF generation is complex, reuse existing)
        $pdfUrl = route('user.invoices.pdf', $id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'pdf_url' => $pdfUrl,
                'message' => 'PDF generation available via web endpoint. Use this URL to download PDF.',
            ],
        ]);
    }

    /**
     * Export invoice to ETIMS format.
     * Only available for finalized invoices with snapshots.
     */
    public function exportEtims(Request $request, $id)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $invoice = Invoice::where('company_id', $companyId)
            ->with('snapshot')
            ->findOrFail($id);

        // Only finalized invoices with snapshots can be exported
        if (! $invoice->isFinalized()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice must be finalized to export for ETIMS.',
            ], 403);
        }

        if (! $invoice->snapshot) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice snapshot not found. Cannot export for ETIMS.',
            ], 404);
        }

        try {
            // Generate ETIMS-compliant JSON export
            $etimsData = $this->etimsExportService->exportToJson($invoice->snapshot);

            return response()->json([
                'status' => 'success',
                'data' => $etimsData,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'ETIMS export failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
