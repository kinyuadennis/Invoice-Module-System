<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\InvoiceTemplate;
use App\Services\CurrentCompanyService;
use App\Services\InvoicePrefixService;
use App\Services\InvoicePreviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Show company setup form (for new users)
     */
    public function setup()
    {
        $user = Auth::user();

        // If user already has companies, redirect to company management
        if ($user->ownedCompanies()->count() > 0) {
            return redirect()->route('user.companies.index');
        }

        return view('company.setup');
    }

    /**
     * Store newly created company
     */
    public function store(StoreCompanyRequest $request)
    {
        $user = Auth::user();

        // Allow creating multiple companies - no restriction

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

        // Set as active company in session
        Session::put('active_company_id', $company->id);

        // Set as active company if user doesn't have one
        if (! $user->active_company_id) {
            $user->update(['active_company_id' => $company->id]);
        }

        // Legacy: also update company_id for backward compatibility
        if (! $user->company_id) {
            $user->update(['company_id' => $company->id]);
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Company created successfully!');
    }

    /**
     * Show company settings page
     */
    public function settings()
    {
        $user = Auth::user();

        // require() throws exception if no company found, so no null check needed
        $company = CurrentCompanyService::require();

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
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Update company settings
     */
    public function update(UpdateCompanyRequest $request)
    {
        $user = Auth::user();

        // require() throws exception if no company found, so no null check needed
        $company = CurrentCompanyService::require();

        // Only owner can update company
        if ($company->owner_user_id !== $user->id) {
            return back()->with('error', 'Only the company owner can update settings.');
        }

        // Ensure we're updating the active company
        $activeCompanyId = Session::get('active_company_id', $user->active_company_id);
        if ($company->id !== $activeCompanyId) {
            return back()->with('error', 'You can only update your active company.');
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

        // Clear dashboard cache for this company since settings may affect dashboard display
        Cache::forget("dashboard_data_{$company->id}");

        return back()->with('success', 'Company settings updated successfully!');
    }

    /**
     * Show invoice customization page
     */
    public function invoiceCustomization()
    {
        $company = CurrentCompanyService::require();

        // Get all active templates for selection
        $templates = InvoiceTemplate::active()->ordered()->get();

        // Get default template if none selected
        $selectedTemplate = $company->getActiveInvoiceTemplate();

        // Ensure we have a selected template
        if (! $selectedTemplate && $templates->isNotEmpty()) {
            $selectedTemplate = $templates->first();
        }

        // Legacy config-based templates for backward compatibility (if needed)
        $legacyTemplates = config('invoice-templates.templates', []);
        $formatPatterns = config('invoice-templates.format_patterns', []);

        return view('company.invoice-customization', [
            'company' => $company,
            'templates' => $templates,
            'selectedTemplate' => $selectedTemplate,
            'legacyTemplates' => $legacyTemplates,
            'formatPatterns' => $formatPatterns,
        ]);
    }

    /**
     * Preview invoice template with sample data, branding, and advanced styling
     */
    public function previewTemplate(Request $request)
    {
        $company = CurrentCompanyService::require();

        $templateId = $request->input('template_id');
        // Convert to integer if provided, null otherwise
        $templateId = $templateId ? (int) $templateId : null;

        // Decode JSON strings if they're sent as strings
        $branding = $request->input('branding', []);
        if (is_string($branding)) {
            $branding = json_decode($branding, true) ?? [];
        }
        if (! is_array($branding)) {
            $branding = [];
        }

        $advancedStyling = $request->input('advanced_styling', []);
        if (is_string($advancedStyling)) {
            $advancedStyling = json_decode($advancedStyling, true) ?? [];
        }
        if (! is_array($advancedStyling)) {
            $advancedStyling = [];
        }

        try {
            $previewService = app(InvoicePreviewService::class);
            $html = $previewService->generatePreview($templateId, $company, $branding, $advancedStyling);

            // Apply advanced styling if enabled
            if (! empty($advancedStyling['enabled'])) {
                $html = $previewService->applyAdvancedStyling($html, $advancedStyling);
            }

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            \Log::error('Preview generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $templateId,
            ]);

            return response()->json([
                'success' => false,
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

        // require() throws exception if no company found, so no null check needed
        $company = CurrentCompanyService::require();

        if ($company->owner_user_id !== $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Only the company owner can update settings.'], 403);
            }

            return back()->with('error', 'Only the company owner can update settings.');
        }

        try {
            $validated = $request->validate([
                'invoice_prefix' => ['nullable', 'string', 'max:50', function ($attribute, $value, $fail) {
                    if ($value && ! preg_match('/^[A-Za-z0-9\-_%]{1,50}$/', $value)) {
                        $fail('The prefix must be alphanumeric with optional hyphens, underscores, and placeholders (like %YYYY%), max 50 characters.');
                    }
                }],
                'invoice_suffix' => 'nullable|string|max:20',
                'invoice_padding' => 'nullable|integer|min:1|max:10',
                'invoice_format' => 'nullable|string|in:{PREFIX}-{NUMBER},{PREFIX}-{YEAR}-{NUMBER},{YEAR}/{NUMBER},{PREFIX}/{NUMBER}/{SUFFIX},{NUMBER}',
                'use_client_specific_numbering' => 'nullable|boolean',
                'client_invoice_format' => 'nullable|string|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $prefixService = app(InvoicePrefixService::class);
        $newPrefix = $request->input('invoice_prefix', $company->invoice_prefix ?? 'INV');
        $currentActivePrefix = $company->activeInvoicePrefix();

        // Only change prefix if it's different from current active prefix
        if (! $currentActivePrefix || $currentActivePrefix->prefix !== $newPrefix) {
            $prefixService->changePrefix($company, $newPrefix, $user->id);
        }

        // Update other format settings (suffix, padding, format, client-specific)
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
        if ($request->has('use_client_specific_numbering')) {
            $updateData['use_client_specific_numbering'] = $request->boolean('use_client_specific_numbering');
        }
        if ($request->has('client_invoice_format')) {
            $updateData['client_invoice_format'] = $request->input('client_invoice_format');
        }

        if (! empty($updateData)) {
            $company->update($updateData);
        }

        // Refresh company to get updated data
        $company->refresh();

        // Clear dashboard cache for this company since invoice format may affect dashboard
        Cache::forget("dashboard_data_{$company->id}");

        // Only return JSON if explicitly requested via Accept header AND X-Requested-With header
        // This prevents regular form submissions from being treated as AJAX
        $isExplicitAjax = $request->wantsJson()
            && $request->hasHeader('X-Requested-With')
            && $request->header('X-Requested-With') === 'XMLHttpRequest';

        if ($isExplicitAjax) {
            $nextInvoiceNumber = $prefixService->getNextInvoiceNumberPreview($company);

            return response()->json([
                'success' => true,
                'message' => 'Invoice format updated successfully!',
                'next_invoice_number' => $nextInvoiceNumber,
                'active_prefix' => $company->activeInvoicePrefix()?->prefix ?? $newPrefix,
            ]);
        }

        // Always redirect for regular form submissions
        return redirect()->route('user.company.invoice-customization')
            ->with('success', 'Invoice format updated successfully!');
    }

    /**
     * Update invoice template
     */
    public function updateInvoiceTemplate(Request $request)
    {
        $user = Auth::user();

        // require() throws exception if no company found, so no null check needed
        $company = CurrentCompanyService::require();

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

        // Clear dashboard cache for this company since template may affect dashboard
        Cache::forget("dashboard_data_{$company->id}");

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

    /**
     * Update branding settings
     */
    public function updateBranding(Request $request)
    {
        $user = Auth::user();
        $company = CurrentCompanyService::require();

        if ($company->owner_user_id !== $user->id) {
            return response()->json(['error' => 'Only the company owner can update settings.'], 403);
        }

        $request->validate([
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'nullable|string|in:Inter,Roboto,Open Sans,System',
        ]);

        $brandingData = [];
        if ($request->has('primary_color')) {
            $brandingData['primary_color'] = $request->input('primary_color');
        }
        if ($request->has('secondary_color')) {
            $brandingData['secondary_color'] = $request->input('secondary_color');
        }
        if ($request->has('font_family')) {
            $brandingData['font_family'] = $request->input('font_family');
        }

        if (! empty($brandingData)) {
            $company->updateBrandingSettings($brandingData);
            $company->save();
        }

        // Clear dashboard cache for this company since branding may affect dashboard
        Cache::forget("dashboard_data_{$company->id}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Branding settings updated successfully!',
                'branding' => $company->getBrandingSettings(),
            ]);
        }

        return back()->with('success', 'Branding settings updated successfully!');
    }

    /**
     * Update advanced styling settings
     */
    public function updateAdvancedStyling(Request $request)
    {
        $user = Auth::user();
        $company = CurrentCompanyService::require();

        if ($company->owner_user_id !== $user->id) {
            return response()->json(['error' => 'Only the company owner can update settings.'], 403);
        }

        $request->validate([
            'enabled' => 'nullable|boolean',
            'column_widths' => 'nullable|array',
            'column_widths.description' => 'nullable|integer|min:10|max:100',
            'column_widths.quantity' => 'nullable|integer|min:5|max:100',
            'column_widths.price' => 'nullable|integer|min:5|max:100',
            'column_widths.total' => 'nullable|integer|min:5|max:100',
            'table_borders' => 'nullable|string|in:none,thin,medium,thick',
            'spacing' => 'nullable|array',
            'spacing.padding' => 'nullable|integer|min:0|max:50',
            'spacing.margin' => 'nullable|integer|min:0|max:100',
            'header_text' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:500',
            'watermark_enabled' => 'nullable|boolean',
            'custom_css' => 'nullable|string|max:5000',
        ]);

        $stylingData = [];
        if ($request->has('enabled')) {
            $stylingData['enabled'] = $request->boolean('enabled');
        }
        if ($request->has('column_widths')) {
            $stylingData['column_widths'] = $request->input('column_widths');
        }
        if ($request->has('table_borders')) {
            $stylingData['table_borders'] = $request->input('table_borders');
        }
        if ($request->has('spacing')) {
            $stylingData['spacing'] = $request->input('spacing');
        }
        if ($request->has('header_text')) {
            $stylingData['header_text'] = $request->input('header_text');
        }
        if ($request->has('footer_text')) {
            $stylingData['footer_text'] = $request->input('footer_text');
        }
        if ($request->has('watermark_enabled')) {
            $stylingData['watermark_enabled'] = $request->boolean('watermark_enabled');
        }
        if ($request->has('custom_css')) {
            $stylingData['custom_css'] = $request->input('custom_css');
        }

        if (! empty($stylingData)) {
            $company->updateAdvancedStylingSettings($stylingData);
            $company->save();
        }

        // Clear dashboard cache for this company since styling may affect dashboard
        Cache::forget("dashboard_data_{$company->id}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Advanced styling settings updated successfully!',
                'advanced_styling' => $company->getAdvancedStylingSettings(),
            ]);
        }

        return back()->with('success', 'Advanced styling settings updated successfully!');
    }
}
