<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyManagementController extends Controller
{
    /**
     * Display a listing of user's companies.
     */
    public function index()
    {
        $user = Auth::user();
        $companies = $user->ownedCompanies()->with('invoiceTemplate')->get();

        return view('user.companies.index', [
            'companies' => $companies,
            'activeCompany' => $user->getCurrentCompany(),
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        $templates = InvoiceTemplate::active()->ordered()->get();

        return view('user.companies.create', [
            'templates' => $templates,
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'kra_pin' => 'nullable|string|max:20',
            'currency' => 'required|string|size:3|default:KES',
            'timezone' => 'required|string|max:50|default:Africa/Nairobi',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'default_invoice_template_id' => 'nullable|exists:invoice_templates,id',
        ]);

        $data = $validated;
        $data['owner_user_id'] = $user->id;
        $data['currency'] = $validated['currency'] ?? 'KES';
        $data['timezone'] = $validated['timezone'] ?? 'Africa/Nairobi';
        $data['next_invoice_sequence'] = 1;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $path = $logo->store('companies/logos', 'public');
            $data['logo'] = $path;
        }

        $company = Company::create($data);

        // Set as active company if user doesn't have one
        if (! $user->active_company_id) {
            $user->update(['active_company_id' => $company->id]);
        }

        return redirect()->route('user.companies.index')
            ->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified company.
     */
    public function show($id)
    {
        $user = Auth::user();
        $company = $user->ownedCompanies()->findOrFail($id);

        return view('user.companies.show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $company = $user->ownedCompanies()->findOrFail($id);
        $templates = InvoiceTemplate::active()->ordered()->get();

        return view('user.companies.edit', [
            'company' => $company,
            'templates' => $templates,
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->ownedCompanies()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'kra_pin' => 'nullable|string|max:20',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'default_invoice_template_id' => 'nullable|exists:invoice_templates,id',
        ]);

        $data = $validated;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            $logo = $request->file('logo');
            $path = $logo->store('companies/logos', 'public');
            $data['logo'] = $path;
        }

        $company->update($data);

        return redirect()->route('user.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $company = $user->ownedCompanies()->findOrFail($id);

        // Prevent deleting if it's the only company
        $companyCount = $user->ownedCompanies()->count();
        if ($companyCount <= 1) {
            return redirect()->route('user.companies.index')
                ->with('error', 'You cannot delete your only company.');
        }

        // Delete logo if exists
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        // If this was the active company, switch to another
        if ($user->active_company_id === $company->id) {
            $newActiveCompany = $user->ownedCompanies()->where('id', '!=', $company->id)->first();
            if ($newActiveCompany) {
                $user->update(['active_company_id' => $newActiveCompany->id]);
            }
        }

        $company->delete();

        return redirect()->route('user.companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    /**
     * Switch the active company for the current user.
     */
    public function switchCompany(Request $request)
    {
        $user = Auth::user();
        $companyId = $request->input('company_id');

        $company = $user->ownedCompanies()->findOrFail($companyId);

        $user->update(['active_company_id' => $company->id]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo' => $company->logo ? Storage::url($company->logo) : null,
                    'currency' => $company->currency ?? 'KES',
                    'timezone' => $company->timezone ?? 'Africa/Nairobi',
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Company switched successfully.');
    }

    /**
     * Get company details for AJAX requests (for invoice form reload).
     */
    public function getCompanyDetails($id)
    {
        $user = Auth::user();
        $company = $user->ownedCompanies()->with('invoiceTemplate')->findOrFail($id);

        return response()->json([
            'id' => $company->id,
            'name' => $company->name,
            'logo' => $company->logo ? Storage::url($company->logo) : null,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'kra_pin' => $company->kra_pin,
            'currency' => $company->currency ?? 'KES',
            'timezone' => $company->timezone ?? 'Africa/Nairobi',
            'default_invoice_template_id' => $company->default_invoice_template_id ?? $company->invoice_template_id,
            'template' => $company->getActiveInvoiceTemplate(),
        ]);
    }
}
