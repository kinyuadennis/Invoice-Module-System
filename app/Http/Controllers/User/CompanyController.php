<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\InvoiceTemplate;
use App\Services\InvoicePrefixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Show company setup form (for new users)
     */
    public function setup()
    {
        $user = Auth::user();

        // If user already has a company, redirect to settings
        if ($user->company_id) {
            return redirect()->route('company.settings');
        }

        return view('company.setup');
    }

    /**
     * Store newly created company
     */
    public function store(StoreCompanyRequest $request)
    {
        $user = Auth::user();

        // Prevent creating multiple companies
        if ($user->company_id) {
            return redirect()->route('company.settings')
                ->with('error', 'You already have a company.');
        }

        $data = $request->validated();
        $data['owner_user_id'] = $user->id;
        $data['invoice_prefix'] = $data['invoice_prefix'] ?? 'INV';

        // Normalize phone number to E.164 format
        if (isset($data['phone']) && ! empty($data['phone'])) {
            $phoneService = app(\App\Services\PhoneNumberService::class);
            $data['phone'] = $phoneService->normalize($data['phone']);
        }

        // Normalize KRA PIN to uppercase
        if (isset($data['kra_pin']) && ! empty($data['kra_pin'])) {
            $data['kra_pin'] = strtoupper($data['kra_pin']);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company = Company::create($data);

        // Create initial invoice prefix
        $prefixService = app(InvoicePrefixService::class);
        $prefixService->createDefaultPrefix($company, $user->id);

        // Update user with company_id
        $user->update(['company_id' => $company->id]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Company created successfully!');
    }

    /**
     * Show company settings page
     */
    public function settings()
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return redirect()->route('company.setup');
        }

        $company = Company::findOrFail($user->company_id);
        $prefixService = app(InvoicePrefixService::class);

        // Get prefix history
        $prefixHistory = $prefixService->getPrefixHistory($company);
        $activePrefix = $company->activeInvoicePrefix();

        // Get payment methods with display name
        $paymentMethods = $company->paymentMethods()->ordered()->get()->map(function ($method) {
            return array_merge($method->toArray(), [
                'display_name' => $method->display_name,
                'account_identifier' => $method->account_identifier,
                'clearing_time_description' => $method->clearing_time_description,
            ]);
        });

        return view('company.settings', [
            'company' => $company,
            'prefixHistory' => $prefixHistory,
            'activePrefix' => $activePrefix,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Update company settings
     */
    public function update(UpdateCompanyRequest $request)
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return redirect()->route('company.setup');
        }

        $company = Company::findOrFail($user->company_id);

        // Only owner can update company
        if ($company->owner_user_id !== $user->id) {
            return back()->with('error', 'Only the company owner can update settings.');
        }

        $data = $request->validated();

        // Normalize phone number to E.164 format
        if (isset($data['phone']) && ! empty($data['phone'])) {
            $phoneService = app(\App\Services\PhoneNumberService::class);
            $data['phone'] = $phoneService->normalize($data['phone']);
        }

        // Normalize KRA PIN to uppercase
        if (isset($data['kra_pin']) && ! empty($data['kra_pin'])) {
            $data['kra_pin'] = strtoupper($data['kra_pin']);
        }

        // Handle prefix change separately using InvoicePrefixService
        $prefixService = app(InvoicePrefixService::class);
        $newPrefix = $request->input('invoice_prefix');

        if ($newPrefix !== null) {
            $currentActivePrefix = $company->activeInvoicePrefix();

            // Only change prefix if it's different from current active prefix
            if (! $currentActivePrefix || $currentActivePrefix->prefix !== $newPrefix) {
                $prefixService->changePrefix($company, $newPrefix, $user->id);
            }

            // Remove from data array to prevent direct update
            unset($data['invoice_prefix']);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($data);

        return back()->with('success', 'Company settings updated successfully!');
    }

    /**
     * Show invoice customization page
     */
    public function invoiceCustomization()
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return redirect()->route('company.setup');
        }

        $company = Company::with('invoiceTemplate')->findOrFail($user->company_id);
        $prefixService = app(InvoicePrefixService::class);

        // Get prefix history
        $prefixHistory = $prefixService->getPrefixHistory($company);
        $activePrefix = $company->activeInvoicePrefix();

        // Get all active templates for selection (new database-driven system)
        $templates = InvoiceTemplate::active()->ordered()->get();

        // Get default template if none selected
        $selectedTemplate = $company->invoiceTemplate ?? InvoiceTemplate::getDefault();

        // Ensure we have a selected template
        if (! $selectedTemplate && $templates->isNotEmpty()) {
            $selectedTemplate = $templates->first();
        }

        // Legacy config-based templates for backward compatibility (if needed)
        $legacyTemplates = config('invoice-templates.templates', []);
        $formatPatterns = config('invoice-templates.format_patterns', []);

        return view('company.invoice-customization', [
            'company' => $company,
            'templates' => $templates, // New database templates
            'selectedTemplate' => $selectedTemplate,
            'legacyTemplates' => $legacyTemplates, // Keep for backward compatibility
            'formatPatterns' => $formatPatterns,
            'prefixHistory' => $prefixHistory,
            'activePrefix' => $activePrefix,
        ]);
    }

    /**
     * Preview invoice template with sample data
     */
    public function previewTemplate()
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        $company = Company::with('invoiceTemplate')->findOrFail($user->company_id);
        $templateId = request()->input('template_id');

        // Get template (selected or default)
        if ($templateId) {
            $template = InvoiceTemplate::find($templateId);
        } else {
            $template = $company->getActiveInvoiceTemplate();
        }

        if (! $template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        // Generate sample invoice data for preview
        $sampleInvoice = [
            'id' => 999,
            'invoice_number' => $template->prefix.'-0001',
            'status' => 'sent',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 10000.00,
            'tax' => 1600.00,
            'vat_amount' => 1600.00,
            'platform_fee' => 300.00,
            'grand_total' => 11900.00,
            'total' => 11900.00,
            'company' => [
                'id' => $company->id,
                'name' => $company->name ?? 'Your Company Name',
                'logo' => $company->logo,
                'email' => $company->email ?? 'info@yourcompany.com',
                'phone' => $company->phone ?? '+254 700 000 000',
                'address' => $company->address ?? 'Nairobi, Kenya',
                'kra_pin' => $company->kra_pin ?? 'P000000000A',
            ],
            'client' => [
                'id' => 1,
                'name' => 'Sample Client Company',
                'email' => 'client@example.com',
                'phone' => '+254 700 111 111',
                'address' => '123 Client Street, Nairobi, Kenya',
            ],
            'items' => [
                [
                    'id' => 1,
                    'description' => 'Web Development Services',
                    'quantity' => 10,
                    'unit_price' => 1000.00,
                    'total' => 10000.00,
                ],
            ],
            'notes' => 'Thank you for your business! Payment is due within 30 days.',
        ];

        // Check if view exists
        if (! view()->exists($template->view_path)) {
            return response()->json(['error' => 'Template view not found'], 404);
        }

        // Render the template view
        try {
            $html = view($template->view_path, [
                'invoice' => $sampleInvoice,
                'template' => $template,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to render preview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update invoice format settings
     */
    public function updateInvoiceFormat(Request $request)
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return redirect()->route('company.setup');
        }

        $company = Company::findOrFail($user->company_id);

        if ($company->owner_user_id !== $user->id) {
            return back()->with('error', 'Only the company owner can update settings.');
        }

        $request->validate([
            'invoice_prefix' => ['nullable', 'string', 'max:50', function ($attribute, $value, $fail) {
                if ($value && ! preg_match('/^[A-Za-z0-9\-_%]{1,50}$/', $value)) {
                    $fail('The prefix must be alphanumeric with optional hyphens, underscores, and placeholders (like %YYYY%), max 50 characters.');
                }
            }],
            'invoice_suffix' => 'nullable|string|max:20',
            'invoice_padding' => 'nullable|integer|min:1|max:10',
            'invoice_format' => 'nullable|string|in:{PREFIX}-{NUMBER},{PREFIX}-{YEAR}-{NUMBER},{YEAR}/{NUMBER},{PREFIX}/{NUMBER}/{SUFFIX},{NUMBER}',
        ]);

        $prefixService = app(InvoicePrefixService::class);
        $newPrefix = $request->input('invoice_prefix', $company->invoice_prefix ?? 'INV');
        $currentActivePrefix = $company->activeInvoicePrefix();

        // Only change prefix if it's different from current active prefix
        if (! $currentActivePrefix || $currentActivePrefix->prefix !== $newPrefix) {
            $prefixService->changePrefix($company, $newPrefix, $user->id);
        }

        // Update other format settings (suffix, padding, format)
        $updateData = [];
        if ($request->has('invoice_suffix')) {
            $updateData['invoice_suffix'] = $request->input('invoice_suffix');
        }
        if ($request->has('invoice_padding')) {
            $updateData['invoice_padding'] = $request->input('invoice_padding');
        }
        if ($request->has('invoice_format')) {
            $updateData['invoice_format'] = $request->input('invoice_format');
        }

        if (! empty($updateData)) {
            $company->update($updateData);
        }

        // Refresh company to get updated data
        $company->refresh();

        // If AJAX request, return JSON with updated invoice number
        if ($request->wantsJson() || $request->ajax()) {
            $nextInvoiceNumber = $prefixService->getNextInvoiceNumberPreview($company);

            return response()->json([
                'success' => true,
                'message' => 'Invoice format updated successfully!',
                'next_invoice_number' => $nextInvoiceNumber,
                'active_prefix' => $company->activeInvoicePrefix()?->prefix ?? $newPrefix,
            ]);
        }

        return back()->with('success', 'Invoice format updated successfully!');
    }

    /**
     * Update invoice template
     */
    public function updateInvoiceTemplate(Request $request)
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return redirect()->route('company.setup');
        }

        $company = Company::findOrFail($user->company_id);

        if ($company->owner_user_id !== $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Only the company owner can update settings.'], 403);
            }

            return back()->with('error', 'Only the company owner can update settings.');
        }

        $request->validate([
            'invoice_template_id' => ['required', 'exists:invoice_templates,id'],
        ]);

        $template = InvoiceTemplate::findOrFail($request->invoice_template_id);

        // Only allow active templates
        if (! $template->is_active) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Selected template is not available.'], 400);
            }

            return back()->with('error', 'Selected template is not available.');
        }

        $company->update([
            'invoice_template_id' => $template->id,
        ]);

        // If template has a different prefix, update the invoice prefix
        $currentPrefix = $company->activeInvoicePrefix();
        if ($template->prefix !== ($currentPrefix?->prefix ?? 'INV')) {
            $prefixService = app(InvoicePrefixService::class);
            $prefixService->changePrefix($company, $template->prefix, $user->id);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice template updated successfully!',
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'prefix' => $template->prefix,
                    'preview_image' => $template->preview_image_url,
                ],
            ]);
        }

        return back()->with('success', 'Invoice template updated successfully!');
    }
}
