<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
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

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company = Company::create($data);

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

        return view('company.settings', [
            'company' => $company,
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

        $company = Company::findOrFail($user->company_id);
        $templates = config('invoice-templates.templates');
        $formatPatterns = config('invoice-templates.format_patterns');

        return view('company.invoice-customization', [
            'company' => $company,
            'templates' => $templates,
            'formatPatterns' => $formatPatterns,
        ]);
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
            'invoice_prefix' => 'nullable|string|max:20',
            'invoice_suffix' => 'nullable|string|max:20',
            'invoice_padding' => 'required|integer|min:1|max:10',
            'invoice_format' => 'required|string|in:{PREFIX}-{NUMBER},{PREFIX}-{YEAR}-{NUMBER},{YEAR}/{NUMBER},{PREFIX}/{NUMBER}/{SUFFIX},{NUMBER}',
        ]);

        $company->update($request->only([
            'invoice_prefix',
            'invoice_suffix',
            'invoice_padding',
            'invoice_format',
        ]));

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
            return back()->with('error', 'Only the company owner can update settings.');
        }

        $templates = config('invoice-templates.templates');
        $validTemplates = array_keys($templates);

        $request->validate([
            'invoice_template' => ['required', 'string', 'in:'.implode(',', $validTemplates)],
        ]);

        $company->update([
            'invoice_template' => $request->invoice_template,
        ]);

        return back()->with('success', 'Invoice template updated successfully!');
    }
}
