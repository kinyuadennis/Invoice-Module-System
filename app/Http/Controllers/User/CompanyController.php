<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
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
}
