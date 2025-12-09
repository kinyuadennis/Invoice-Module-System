<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CurrentCompanyService
{
    /**
     * Get the currently active company for the authenticated user.
     */
    public static function get(): ?Company
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return $user->getCurrentCompany();
    }

    /**
     * Get the currently active company ID.
     */
    public static function id(): ?int
    {
        $company = self::get();

        return $company?->id;
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
}
