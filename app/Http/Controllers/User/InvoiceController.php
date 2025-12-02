<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Services\InvoiceService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to view invoices.');
        }

        // Scope to current user's company invoices
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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to create invoices.');
        }

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'address')
            ->get();

        // Get service library from invoice items (company-specific)
        $services = $this->invoiceService->getServiceLibrary($companyId);

        // Get company for invoice format settings
        $company = Company::findOrFail($companyId);

        return view('user.invoices.create', [
            'clients' => $clients,
            'services' => $services,
            'company' => $company,
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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to view invoices.');
        }

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'payments', 'company'])
            ->findOrFail($id);

        return view('user.invoices.show', [
            'invoice' => $this->invoiceService->formatInvoiceForShow($invoice),
        ]);
    }

    public function edit($id)
    {
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to edit invoices.');
        }

        // Ensure invoice belongs to user's company
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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to update invoices.');
        }

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);

        // Update invoice using service
        $this->invoiceService->updateInvoice($invoice, $request);

        return redirect()->route('user.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to delete invoices.');
        }

        // Ensure invoice belongs to user's company
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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            abort(403, 'You must belong to a company to generate PDFs.');
        }

        // Ensure invoice belongs to user's company
        $invoice = Invoice::where('company_id', $companyId)
            ->with(['client', 'invoiceItems', 'platformFees', 'user', 'company'])
            ->findOrFail($id);

        // Format invoice data for PDF
        $formattedInvoice = $this->invoiceService->formatInvoiceForShow($invoice);

        // Add platform fee if exists
        $platformFee = $invoice->platformFees->first();
        if ($platformFee) {
            $formattedInvoice['platform_fee'] = (float) $platformFee->fee_amount;
        }

        // Company data is already included in formatInvoiceForShow via the trait

        // Get template from company settings
        $company = $invoice->company;
        $templateName = $company->invoice_template ?? 'modern_clean';
        $templates = config('invoice-templates.templates');

        // Get template view path
        $templateView = $templates[$templateName]['view'] ?? 'invoices.templates.modern-clean';

        // Generate PDF using selected template
        $pdf = Pdf::loadView($templateView, [
            'invoice' => $formattedInvoice,
        ]);

        // Set PDF options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);

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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return response()->json(['error' => 'You must belong to a company.'], 403);
        }

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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return response()->json(['error' => 'You must belong to a company.'], 403);
        }

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
}
