<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CurrentCompanyService
{
    /**
     * Cached company instance for the current request.
     */
    private static ?Company $cachedCompany = null;

    /**
     * Cached company ID for the current request.
     */
    private static ?int $cachedCompanyId = null;

    /**
     * Get the currently active company for the authenticated user from session.
     * Uses request-level static caching to prevent multiple DB queries.
     */
    public static function get(): ?Company
    {
        // Return cached if available
        if (self::$cachedCompany !== null) {
            return self::$cachedCompany;
        }

        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // First try to get from session
        $companyId = Session::get('active_company_id');

        if ($companyId) {
            // Validate user owns the company
            $company = $user->ownedCompanies()->find($companyId);
            if ($company) {
                // Cache for this request
                self::$cachedCompany = $company;
                self::$cachedCompanyId = $company->id;

                return $company;
            }
            // If session company is invalid, clear it
            Session::forget('active_company_id');
        }

        // Fallback: try to get from User model (for migration period)
        if ($user->active_company_id) {
            $company = $user->ownedCompanies()->find($user->active_company_id);
            if ($company) {
                // Sync to session for future requests
                Session::put('active_company_id', $company->id);
                // Cache for this request
                self::$cachedCompany = $company;
                self::$cachedCompanyId = $company->id;

                return $company;
            }
        }

        // Final fallback: get first owned company
        $firstCompany = $user->ownedCompanies()->first();
        if ($firstCompany) {
            // Set in session and User model
            Session::put('active_company_id', $firstCompany->id);
            if (! $user->active_company_id) {
                $user->update(['active_company_id' => $firstCompany->id]);
                // Refresh the user model to ensure in-memory object has updated value
                $user->refresh();
            }
            // Cache for this request
            self::$cachedCompany = $firstCompany;
            self::$cachedCompanyId = $firstCompany->id;

            return $firstCompany;
        }

        return null;
    }

    /**
     * Get the currently active company ID from session.
     * Uses request-level static caching to prevent multiple DB queries.
     */
    public static function id(): ?int
    {
        // Return cached if available
        if (self::$cachedCompanyId !== null) {
            return self::$cachedCompanyId;
        }

        $company = self::get();
        self::$cachedCompanyId = $company?->id;

        return self::$cachedCompanyId;
    }

    /**
     * Ensure user has an active company, throw exception if not.
     */
    public static function require(): Company
    {
        $company = self::get();

        if (! $company) {
            throw new \RuntimeException('No active company found. Please select a company first.');
        }

        return $company;
    }

    /**
     * Get company ID, throwing exception if not found.
     */
    public static function requireId(): int
    {
        return self::require()->id;
    }

    /**
     * Clear the request-level cache.
     * Call this after switching companies to ensure fresh data.
     */
    public static function clearCache(): void
    {
        self::$cachedCompany = null;
        self::$cachedCompanyId = null;
    }
}
