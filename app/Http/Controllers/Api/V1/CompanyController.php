<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

/**
 * CompanyController (API)
 *
 * Handles company information via API.
 * All queries are company-scoped.
 */
class CompanyController extends Controller
{
    /**
     * List companies for authenticated user.
     * Only returns companies the user has access to.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all companies user has access to
        $companies = Company::where('owner_user_id', $user->id)
            ->orWhere('id', $user->company_id) // Legacy support
            ->get();

        return CompanyResource::collection($companies);
    }

    /**
     * Get single company.
     * Must belong to authenticated user.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $companyId = $request->input('company_id'); // Active company from middleware

        // Ensure company belongs to user
        $company = Company::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhere('id', $user->company_id); // Legacy support
            })
            ->firstOrFail();

        return new CompanyResource($company);
    }
}
