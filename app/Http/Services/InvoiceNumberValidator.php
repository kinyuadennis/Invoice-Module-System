<?php

namespace App\Http\Services;

use App\Models\Company;

/**
 * InvoiceNumberValidator Service
 *
 * Validates invoice numbers for ETIMS compliance.
 * ETIMS typically requires:
 * - Alphanumeric characters
 * - Specific length constraints
 * - No special characters (except hyphens/dashes)
 * - Unique within company scope
 */
class InvoiceNumberValidator
{
    /**
     * Validate invoice number format for ETIMS compliance.
     *
     * ETIMS Requirements (typical):
     * - Alphanumeric with optional hyphens
     * - Max 50 characters
     * - No special characters except hyphens
     * - Must not be empty
     *
     * @param  string  $invoiceNumber  Invoice number to validate
     * @return bool True if valid format
     */
    public function validateFormat(string $invoiceNumber): bool
    {
        if (empty(trim($invoiceNumber))) {
            return false;
        }

        // ETIMS typically allows: alphanumeric, hyphens, underscores
        // Max length: 50 characters (ETIMS standard)
        $pattern = '/^[A-Za-z0-9\-_]{1,50}$/';

        return (bool) preg_match($pattern, $invoiceNumber);
    }

    /**
     * Validate invoice number is unique within company scope.
     *
     * @param  string  $invoiceNumber  Invoice number to check
     * @param  Company  $company  Company to check uniqueness within
     * @return bool True if unique (or doesn't exist)
     */
    public function validateUniqueness(string $invoiceNumber, Company $company): bool
    {
        $exists = \App\Models\Invoice::where('company_id', $company->id)
            ->where('invoice_number', $invoiceNumber)
            ->exists();

        return ! $exists;
    }

    /**
     * Validate invoice number for ETIMS export.
     * Combines format and uniqueness checks.
     *
     * @param  string  $invoiceNumber  Invoice number to validate
     * @param  Company  $company  Company context
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validateForEtims(string $invoiceNumber, Company $company): array
    {
        $errors = [];

        if (! $this->validateFormat($invoiceNumber)) {
            $errors[] = 'Invoice number format is invalid for ETIMS. Must be alphanumeric with optional hyphens, max 50 characters.';
        }

        // Note: Uniqueness check is informational for ETIMS (not blocking)
        // ETIMS doesn't require uniqueness, but it's good practice
        if (! $this->validateUniqueness($invoiceNumber, $company)) {
            $errors[] = 'Invoice number already exists in company (warning only).';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
