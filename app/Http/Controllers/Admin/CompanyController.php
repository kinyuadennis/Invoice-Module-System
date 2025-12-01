<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount(['users', 'clients', 'invoices'])
            ->with('owner')
            ->latest()
            ->paginate(15)
            ->through(function ($company) {
                $revenue = $company->invoices()
                    ->where('status', 'paid')
                    ->sum('grand_total');

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo' => $company->logo,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'owner' => $company->owner ? [
                        'id' => $company->owner->id,
                        'name' => $company->owner->name,
                        'email' => $company->owner->email,
                    ] : null,
                    'users_count' => $company->users_count,
                    'clients_count' => $company->clients_count,
                    'invoices_count' => $company->invoices_count,
                    'revenue' => (float) $revenue,
                    'created_at' => $company->created_at,
                ];
            });

        return view('admin.companies.index', [
            'companies' => $companies,
        ]);
    }

    public function show($id)
    {
        $company = Company::with(['owner', 'users', 'clients', 'invoices'])
            ->withCount(['users', 'clients', 'invoices'])
            ->findOrFail($id);

        $stats = [
            'total_revenue' => (float) $company->invoices()->where('status', 'paid')->sum('grand_total'),
            'pending_revenue' => (float) $company->invoices()->whereIn('status', ['draft', 'sent'])->sum('grand_total'),
            'overdue_revenue' => (float) $company->invoices()->where('status', 'overdue')->sum('grand_total'),
            'total_payments' => (float) $company->payments()->sum('amount'),
            'platform_fees_collected' => (float) $company->platformFees()->where('fee_status', 'paid')->sum('fee_amount'),
        ];

        $recentInvoices = $company->invoices()
            ->with('client')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.companies.show', [
            'company' => $company,
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
        ]);
    }

    public function edit($id)
    {
        $company = Company::findOrFail($id);

        return view('admin.companies.edit', [
            'company' => $company,
        ]);
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        $company = Company::findOrFail($id);
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && Storage::exists($company->logo)) {
                Storage::delete($company->logo);
            }

            $validated['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($validated);

        return redirect()->route('admin.companies.show', $company->id)
            ->with('success', 'Company updated successfully.');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        // Safety checks
        if ($company->invoices()->count() > 0) {
            return back()->withErrors([
                'message' => 'Cannot delete company with existing invoices. Please delete invoices first.',
            ]);
        }

        // Delete logo if exists
        if ($company->logo && Storage::exists($company->logo)) {
            Storage::delete($company->logo);
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
