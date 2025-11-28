<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Services\InvoiceService;
use App\Models\Client;
use App\Models\Invoice;
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
        // Scope to current user's invoices
        $query = Invoice::where('user_id', Auth::id())
            ->with('client')
            ->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
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
                    $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [$now->startOfMonth(), $now->endOfMonth()]);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereBetween('created_at', [$now->startOfYear(), $now->endOfYear()]);
                    break;
            }
        }

        $invoices = $query->paginate(15)->through(function (Invoice $invoice) {
            return $this->invoiceService->formatInvoiceForList($invoice);
        });

        return view('user.invoices.index', [
            'invoices' => $invoices,
            'stats' => $this->invoiceService->getInvoiceStats(Auth::id()),
            'filters' => $request->only(['search', 'status', 'dateRange']),
        ]);
    }

    public function create()
    {
        $clients = Client::where('user_id', Auth::id())
            ->select('id', 'name', 'email', 'phone', 'address')
            ->get();

        // Service library with suggested prices (KES)
        $services = [
            'Web Development Services' => 50000,
            'Mobile App Development' => 75000,
            'Digital Marketing Campaign' => 30000,
            'Consulting Services' => 25000,
            'Graphic Design Services' => 20000,
            'Content Writing Services' => 15000,
            'SEO Optimization' => 35000,
            'Cloud Infrastructure Setup' => 60000,
            'Software Maintenance' => 40000,
            'Data Analytics Services' => 45000,
        ];

        return view('user.invoices.create', [
            'clients' => $clients,
            'services' => $services,
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->createInvoice($request);

        return redirect()->route('user.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    public function show($id)
    {
        // Ensure user can only view their own invoices
        $invoice = Invoice::where('user_id', Auth::id())
            ->with(['client', 'invoiceItems', 'payments'])
            ->findOrFail($id);

        return view('user.invoices.show', [
            'invoice' => $this->invoiceService->formatInvoiceForShow($invoice),
        ]);
    }

    public function edit($id)
    {
        // Ensure user can only edit their own invoices
        $invoice = Invoice::where('user_id', Auth::id())
            ->with(['client', 'invoiceItems'])
            ->findOrFail($id);

        $clients = Client::select('id', 'name', 'email', 'phone', 'address')->get();

        return view('user.invoices.edit', [
            'invoice' => $this->invoiceService->formatInvoiceForEdit($invoice),
            'clients' => $clients,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, $id)
    {
        // Ensure user can only update their own invoices
        $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);

        $invoice->update($request->validated());

        return redirect()->route('user.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy($id)
    {
        // Ensure user can only delete their own invoices
        $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);

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
        $invoice = Invoice::where('user_id', Auth::id())
            ->with(['client', 'invoiceItems', 'platformFees'])
            ->findOrFail($id);

        // TODO: Implement PDF generation using DomPDF or similar
        // For now, return a placeholder response
        return response()->json([
            'message' => 'PDF generation will be implemented soon',
            'invoice_id' => $invoice->id,
        ]);
    }

    /**
     * Send invoice via email
     */
    public function sendEmail($id)
    {
        $invoice = Invoice::where('user_id', Auth::id())
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
        $invoice = Invoice::where('user_id', Auth::id())
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
