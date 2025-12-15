<?php

namespace App\Http\Services;

use App\Models\InvoiceSnapshot;

/**
 * InvoiceSnapshotValidator Service
 *
 * Validates that invoice snapshots contain all required data for ETIMS export.
 * This ensures export can proceed without missing critical fields.
 */
class InvoiceSnapshotValidator
{
    protected InvoiceNumberValidator $invoiceNumberValidator;

    protected KraPinValidator $kraPinValidator;

    public function __construct(
        InvoiceNumberValidator $invoiceNumberValidator,
        KraPinValidator $kraPinValidator
    ) {
        $this->invoiceNumberValidator = $invoiceNumberValidator;
        $this->kraPinValidator = $kraPinValidator;
    }

    /**
     * Validate snapshot contains all required ETIMS fields.
     *
     * Required fields:
     * - Invoice number
     * - Issue date
     * - Company KRA PIN
     * - Client KRA PIN (if client exists)
     * - Line items (at least one)
     * - Totals (subtotal, VAT, grand total)
     *
     * @param  InvoiceSnapshot  $snapshot  Snapshot to validate
     * @return array Validation result with 'valid', 'errors', and 'warnings'
     */
    public function validateSnapshotForEtims(InvoiceSnapshot $snapshot): array
    {
        $data = $snapshot->snapshot_data;
        $errors = [];
        $warnings = [];

        // Required: Invoice number
        $invoiceNumber = $data['invoice']['invoice_number'] ?? null;
        if (empty($invoiceNumber)) {
            $errors[] = 'Invoice number is missing from snapshot.';
        } else {
            $invoiceValidation = $this->invoiceNumberValidator->validateFormat($invoiceNumber);
            if (! $invoiceValidation) {
                $errors[] = 'Invoice number format is invalid for ETIMS.';
            }
        }

        // Required: Issue date
        if (empty($data['invoice']['issue_date'] ?? null)) {
            $errors[] = 'Issue date is missing from snapshot.';
        }

        // Required: Company KRA PIN
        $companyKraPin = $data['company']['kra_pin'] ?? null;
        if (empty($companyKraPin)) {
            $errors[] = 'Company KRA PIN is missing. Required for ETIMS export.';
        } else {
            $companyKraValidation = $this->kraPinValidator->validate($companyKraPin);
            if (! $companyKraValidation['valid']) {
                $errors[] = 'Company KRA PIN is invalid: '.implode(', ', $companyKraValidation['errors']);
            }
        }

        // Required: Client KRA PIN (if client exists)
        if (! empty($data['client']['id'] ?? null)) {
            $clientKraPin = $data['client']['kra_pin'] ?? null;
            if (empty($clientKraPin)) {
                $warnings[] = 'Client KRA PIN is missing. Some ETIMS scenarios may require it.';
            } else {
                $clientKraValidation = $this->kraPinValidator->validate($clientKraPin);
                if (! $clientKraValidation['valid']) {
                    $warnings[] = 'Client KRA PIN format is invalid: '.implode(', ', $clientKraValidation['errors']);
                }
            }
        }

        // Required: At least one line item
        $items = $data['items'] ?? [];
        if (empty($items)) {
            $errors[] = 'Invoice must have at least one line item for ETIMS export.';
        }

        // Required: Totals
        $totals = $data['totals'] ?? [];
        if (empty($totals['subtotal'] ?? null)) {
            $errors[] = 'Subtotal is missing from snapshot totals.';
        }
        if (empty($totals['grand_total'] ?? null)) {
            $errors[] = 'Grand total is missing from snapshot totals.';
        }

        // Optional but recommended: VAT breakdown
        if (empty($totals['vat_amount'] ?? null)) {
            $warnings[] = 'VAT amount is missing. ETIMS may require VAT breakdown.';
        }

        // Optional: Currency (defaults to KES if missing)
        if (empty($data['invoice']['currency'] ?? $data['company']['currency'] ?? null)) {
            $warnings[] = 'Currency is missing. Will default to KES for ETIMS export.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get list of missing required fields.
     *
     * @param  InvoiceSnapshot  $snapshot  Snapshot to check
     * @return array List of missing required field paths
     */
    public function getMissingFields(InvoiceSnapshot $snapshot): array
    {
        $validation = $this->validateSnapshotForEtims($snapshot);

        return $validation['errors'];
    }
}
