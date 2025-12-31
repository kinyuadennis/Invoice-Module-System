<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstimateRequest;
use App\Http\Requests\UpdateEstimateRequest;
use App\Http\Services\EstimateService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Estimate;
use App\Services\CurrentCompanyService;
use App\Services\InvoicePrefixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EstimateController extends Controller
{
    protected EstimateService $estimateService;

    public function __construct(EstimateService $estimateService)
    {
        $this->estimateService = $estimateService;
    }

    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $query = Estimate::where('company_id', $companyId)
            ->with(['client', 'company', 'items'])
            ->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('estimate_reference', 'like', "%{$search}%")
                    ->orWhere('full_number', 'like', "%{$search}%")
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
            $now = now();

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

        $estimates = $query->paginate(15)->through(function (Estimate $estimate) {
            return $this->estimateService->formatEstimateForList($estimate);
        });

        return view('user.estimates.index', [
            'estimates' => $estimates,
            'stats' => $this->estimateService->getEstimateStats($companyId),
            'filters' => $request->only(['search', 'status', 'dateRange']),
        ]);
    }

    public function create()
    {
        $companyId = CurrentCompanyService::requireId();

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'address', 'kra_pin')
            ->get();

        $services = $this->estimateService->getServiceLibrary($companyId);

        $company = Company::findOrFail($companyId);

        // Get next estimate number preview
        $prefixService = app(InvoicePrefixService::class);
        $clientId = request()->input('client_id');

        if ($company->use_client_specific_numbering && $clientId) {
            $client = Client::where('id', $clientId)
                ->where('company_id', $companyId)
                ->first();

            if ($client) {
                $nextEstimateNumber = $prefixService->getNextClientInvoiceNumberPreview($company, $client);
            } else {
                $nextEstimateNumber = 'Select a client to see estimate number';
            }
        } elseif ($company->use_client_specific_numbering && ! $clientId) {
            $nextEstimateNumber = 'Select a client to see estimate number';
        } else {
            $nextEstimateNumber = $prefixService->getNextInvoiceNumberPreview($company);
        }

        $builderType = request()->get('builder', 'one-page');

        return view('user.estimates.create-one-page', [
            'clients' => $clients,
            'services' => $services,
            'company' => $company,
            'nextEstimateNumber' => $nextEstimateNumber,
            'selectedCompanyId' => $companyId,
        ]);
    }

    public function store(StoreEstimateRequest $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $estimate = $this->estimateService->createEstimate($request);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Estimate created successfully.',
                'estimate_id' => $estimate->id,
                'redirect' => route('user.estimates.show', $estimate->id),
            ]);
        }

        return redirect()->route('user.estimates.show', $estimate->id)
            ->with('success', 'Estimate created successfully.');
    }

    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $estimate = Estimate::where('company_id', $companyId)
            ->with(['client', 'items', 'company', 'convertedInvoice'])
            ->findOrFail($id);

        return view('user.estimates.show', [
            'estimate' => $this->estimateService->formatEstimateForShow($estimate),
        ]);
    }

    public function edit($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $estimate = Estimate::where('company_id', $companyId)
            ->with(['client', 'items'])
            ->findOrFail($id);

        // Restrict editing based on status
        if (in_array($estimate->status, ['converted', 'accepted'])) {
            return redirect()->route('user.estimates.show', $estimate->id)
                ->with('error', 'Converted and accepted estimates cannot be edited.');
        }

        if ($estimate->status === 'sent') {
            return redirect()->route('user.estimates.show', $estimate->id)
                ->with('warning', 'Sent estimates have limited editing. Only draft estimates can be fully edited.');
        }

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'address')
            ->get();

        $services = $this->estimateService->getServiceLibrary($companyId);

        return view('user.estimates.edit', [
            'estimate' => $this->estimateService->formatEstimateForEdit($estimate),
            'clients' => $clients,
            'services' => $services,
        ]);
    }

    public function update(UpdateEstimateRequest $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $estimate = Estimate::where('company_id', $companyId)
            ->with(['client', 'items', 'company'])
            ->findOrFail($id);

        // Restrict editing based on status
        if (in_array($estimate->status, ['converted', 'accepted'])) {
            return back()->withErrors([
                'status' => 'Converted and accepted estimates cannot be edited.',
            ]);
        }

        if ($estimate->status === 'sent') {
            // Allow limited editing
            $allowedFields = ['notes', 'terms_and_conditions'];
            $requestData = $request->only($allowedFields);

            if ($request->hasAny(['client_id', 'items', 'subtotal'])) {
                return back()->withErrors([
                    'message' => 'Sent estimates have limited editing. Only notes and terms can be modified.',
                ]);
            }

            $estimate->update($requestData);
        } else {
            // Draft: full editing allowed
            $this->estimateService->updateEstimate($estimate, $request);
        }

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.estimates.show', $estimate->id)
            ->with('success', 'Estimate updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $estimate = Estimate::where('company_id', $companyId)
            ->with(['client', 'items', 'company'])
            ->findOrFail($id);

        // Only allow deletion of draft estimates
        if ($estimate->status !== 'draft') {
            return back()->withErrors([
                'message' => 'Only draft estimates can be deleted.',
            ]);
        }

        $estimate->delete();

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.estimates.index')
            ->with('success', 'Estimate deleted successfully.');
    }

    /**
     * Convert estimate to invoice
     */
    public function convert($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $estimate = Estimate::where('company_id', $companyId)
            ->with(['client', 'items'])
            ->findOrFail($id);

        if ($estimate->isConverted()) {
            return redirect()->route('user.estimates.show', $estimate->id)
                ->with('error', 'This estimate has already been converted to an invoice.');
        }

        try {
            $invoice = $this->estimateService->convertToInvoice($estimate);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return redirect()->route('user.invoices.show', $invoice->id)
                ->with('success', 'Estimate converted to invoice successfully.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'Failed to convert estimate: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Send estimate to client
     */
    public function send($id, Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $estimate = Estimate::where('company_id', $companyId)
            ->with(['client', 'items', 'company'])
            ->findOrFail($id);

        if (! $estimate->client) {
            return back()->withErrors([
                'message' => 'Cannot send estimate without a client.',
            ]);
        }

        if (! $estimate->client->email) {
            return back()->withErrors([
                'message' => 'Client must have an email address to send estimate.',
            ]);
        }

        try {
            // Generate access token for customer portal
            $tokenService = app(\App\Http\Services\EstimateAccessTokenService::class);
            $accessToken = $tokenService->generateToken($estimate, 30); // 30 days expiry
            $accessUrl = $tokenService->getAccessUrl($accessToken);

            // Generate PDF
            $pdfRenderer = app(\App\Services\PdfEstimateRenderer::class);
            $pdfContent = $pdfRenderer->render($estimate);

            // Save PDF to temporary file
            $tempPath = storage_path('app/temp/estimate-'.$estimate->id.'-'.time().'.pdf');
            if (! is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            file_put_contents($tempPath, $pdfContent);

            // Send email with PDF attachment and approval link
            \Illuminate\Support\Facades\Mail::to($estimate->client->email)
                ->send(new \App\Mail\EstimateSentMail($estimate, $tempPath, $accessUrl));

            // Clean up temporary PDF file after sending
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            // Update status to 'sent' after successful email delivery
            $estimate->update(['status' => 'sent']);

            // Log client activity
            $activityService = app(\App\Http\Services\ClientActivityService::class);
            $activityService->logEstimateSent($estimate->client, $estimate->id, $estimate->full_number ?? $estimate->estimate_number);

            return back()->with('success', 'Estimate sent to client successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to send estimate email', [
                'estimate_id' => $estimate->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'message' => 'Failed to send estimate email: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Generate PDF for estimate
     */
    public function pdf($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $estimate = Estimate::where('company_id', $companyId)
            ->with(['client', 'items', 'company'])
            ->findOrFail($id);

        try {
            $pdfRenderer = app(\App\Services\PdfEstimateRenderer::class);
            $pdfContent = $pdfRenderer->render($estimate);

            $filename = 'estimate-'.($estimate->full_number ?? $estimate->estimate_number ?? $estimate->estimate_reference ?? $estimate->id).'.pdf';

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        } catch (\Exception $e) {
            \Log::error('Estimate PDF generation error', [
                'estimate_id' => $estimate->id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to generate PDF');
        }
    }
}
