<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoicePrefix;
use Illuminate\Support\Facades\DB;

class InvoicePrefixService
{
    /**
     * Get the currently active prefix for a company.
     */
    public function getActivePrefix(Company $company): InvoicePrefix
    {
        $prefix = $company->activeInvoicePrefix();

        // If no active prefix exists, create a default one
        if (! $prefix) {
            $prefix = $this->createDefaultPrefix($company);
        }

        return $prefix;
    }

    /**
     * Get prefix history for a company (all prefixes, ordered by started_at desc).
     */
    public function getPrefixHistory(Company $company): \Illuminate\Database\Eloquent\Collection
    {
        return $company->invoicePrefixes()
            ->orderBy('started_at', 'desc')
            ->with('creator:id,name,email')
            ->get();
    }

    /**
     * Get the next invoice number preview for a company.
     * Note: This is a read-only preview that doesn't reserve or consume the serial number.
     * The actual number is generated when the invoice is created.
     */
    public function getNextInvoiceNumberPreview(Company $company): string
    {
        $prefix = $this->getActivePrefix($company);

        // Get the next serial number without locking (read-only preview)
        // Need to match by processed prefix since prefix might have placeholders
        $processedPrefix = $this->processPrefixPlaceholders($prefix->prefix);

        $lastInvoice = Invoice::where('company_id', $company->id)
            ->where('prefix_used', $prefix->prefix)
            ->whereNotNull('serial_number')
            ->orderBy('serial_number', 'desc')
            ->first();

        $nextSerial = 1;
        if ($lastInvoice && $lastInvoice->serial_number) {
            $nextSerial = $lastInvoice->serial_number + 1;
        }

        $fullNumber = $this->generateFullNumber($company, $prefix, $nextSerial);

        return $fullNumber;
    }

    /**
     * Create a default prefix for a company using company settings.
     */
    public function createDefaultPrefix(Company $company, ?int $createdBy = null): InvoicePrefix
    {
        $prefixValue = $company->invoice_prefix ?? 'INV';

        return InvoicePrefix::create([
            'company_id' => $company->id,
            'prefix' => $prefixValue,
            'started_at' => now(),
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Generate the next serial number for a given prefix (transactional with proper locking).
     */
    public function generateNextSerialNumber(Company $company, InvoicePrefix $prefix): int
    {
        return DB::transaction(function () use ($company, $prefix) {
            // Use SELECT FOR UPDATE to lock rows and prevent race conditions
            // This ensures two simultaneous invoice creations don't get the same serial number
            $lastInvoice = Invoice::where('company_id', $company->id)
                ->where('prefix_used', $prefix->prefix)
                ->whereNotNull('serial_number')
                ->lockForUpdate()
                ->orderBy('serial_number', 'desc')
                ->first();

            $nextSerial = 1;
            if ($lastInvoice && $lastInvoice->serial_number) {
                $nextSerial = $lastInvoice->serial_number + 1;
            }

            return $nextSerial;
        }, 5); // Retry up to 5 times if deadlock occurs
    }

    /**
     * Generate full invoice number from prefix and serial.
     */
    public function generateFullNumber(Company $company, InvoicePrefix $prefix, int $serialNumber): string
    {
        $suffix = $company->invoice_suffix ?? '';
        $padding = $company->invoice_padding ?? 4;
        $format = $company->invoice_format ?? '{PREFIX}-{NUMBER}';

        $paddedNumber = str_pad($serialNumber, $padding, '0', STR_PAD_LEFT);

        // Process prefix with dynamic placeholders
        $processedPrefix = $this->processPrefixPlaceholders($prefix->prefix);

        $fullNumber = $format;
        $fullNumber = str_replace('{PREFIX}', $processedPrefix, $fullNumber);
        $fullNumber = str_replace('{NUMBER}', $paddedNumber, $fullNumber);
        $fullNumber = str_replace('{YEAR}', date('Y'), $fullNumber);
        $fullNumber = str_replace('{SUFFIX}', $suffix, $fullNumber);

        return $fullNumber;
    }

    /**
     * Process prefix placeholders like %YYYY%, %MM%, %DD%, etc.
     */
    protected function processPrefixPlaceholders(string $prefix): string
    {
        $now = now();

        // Replace placeholders
        $processed = $prefix;
        $processed = str_replace('%YYYY%', $now->format('Y'), $processed); // Full year (2025)
        $processed = str_replace('%YY%', $now->format('y'), $processed); // 2-digit year (25)
        $processed = str_replace('%MM%', $now->format('m'), $processed); // Month with leading zero (01-12)
        $processed = str_replace('%M%', $now->format('n'), $processed); // Month without leading zero (1-12)
        $processed = str_replace('%DD%', $now->format('d'), $processed); // Day with leading zero (01-31)
        $processed = str_replace('%D%', $now->format('j'), $processed); // Day without leading zero (1-31)
        $processed = str_replace('%MMMM%', $now->format('F'), $processed); // Full month name (January)
        $processed = str_replace('%MMM%', $now->format('M'), $processed); // Short month name (Jan)

        return $processed;
    }

    /**
     * Change the active prefix for a company.
     * Ends the current active prefix and creates a new one.
     * Historical invoices remain unchanged - only new invoices use the new prefix.
     */
    public function changePrefix(Company $company, string $newPrefix, int $createdBy): InvoicePrefix
    {
        return DB::transaction(function () use ($company, $newPrefix, $createdBy) {
            // Validate prefix format (alphanumeric, hyphens, underscores, and placeholders like %YYYY%, %MM%, etc.)
            // Allow placeholders: %YYYY%, %YY%, %MM%, %M%, %DD%, %D%, %MMMM%, %MMM%
            // Max length increased to 50 to accommodate placeholders
            if (! preg_match('/^[A-Za-z0-9\-_%]{1,50}$/', $newPrefix)) {
                throw new \InvalidArgumentException('Prefix must be alphanumeric with optional hyphens, underscores, and placeholders (like %YYYY%), max 50 characters.');
            }

            // Validate that placeholders are properly formatted if present
            if (preg_match('/%/', $newPrefix)) {
                // Check for valid placeholder patterns
                $validPlaceholders = ['%YYYY%', '%YY%', '%MM%', '%M%', '%DD%', '%D%', '%MMMM%', '%MMM%'];
                $foundPlaceholders = [];
                preg_match_all('/%[A-Z]+%/', $newPrefix, $foundPlaceholders);

                foreach ($foundPlaceholders[0] ?? [] as $placeholder) {
                    if (! in_array($placeholder, $validPlaceholders)) {
                        throw new \InvalidArgumentException("Invalid placeholder: {$placeholder}. Valid placeholders are: ".implode(', ', $validPlaceholders));
                    }
                }
            }

            // End the current active prefix
            $currentPrefix = $company->activeInvoicePrefix();
            if ($currentPrefix) {
                $currentPrefix->end();
            }

            // Create new prefix record
            $newPrefixRecord = InvoicePrefix::create([
                'company_id' => $company->id,
                'prefix' => $newPrefix,
                'started_at' => now(),
                'created_by' => $createdBy,
            ]);

            // Update company's invoice_prefix for backward compatibility
            // This is just for display - actual prefix logic uses invoice_prefixes table
            $company->update(['invoice_prefix' => $newPrefix]);

            return $newPrefixRecord;
        });
    }
}
