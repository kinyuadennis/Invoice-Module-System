<?php

namespace App\Http\Services;

use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeFacade;

class EtimsService
{
    /**
     * Generate eTIMS-compliant invoice data for KRA submission.
     */
    public function generateInvoiceData(Invoice $invoice): array
    {
        $company = $invoice->company;
        $client = $invoice->client;

        if (! $company->kra_pin) {
            throw new \Exception('Company KRA PIN is required for eTIMS compliance');
        }

        // Build invoice data according to KRA eTIMS format
        $invoiceData = [
            'seller' => [
                'pin' => $company->kra_pin,
                'name' => $company->name,
                'address' => $company->address ?? '',
                'email' => $company->email ?? '',
                'phone' => $company->phone ?? '',
            ],
            'buyer' => [
                'pin' => $client->kra_pin ?? '',
                'name' => $client->name,
                'address' => $client->address ?? '',
                'email' => $client->email ?? '',
                'phone' => $client->phone ?? '',
            ],
            'invoice' => [
                'number' => $invoice->invoice_number ?? $invoice->invoice_reference,
                'date' => $invoice->issue_date ? date('Y-m-d', strtotime($invoice->issue_date)) : date('Y-m-d'),
                'due_date' => $invoice->due_date ? date('Y-m-d', strtotime($invoice->due_date)) : null,
                'currency' => strtoupper($company->currency ?? 'KES'),
            ],
            'items' => [],
            'totals' => [
                'subtotal' => (float) $invoice->subtotal,
                'tax' => (float) ($invoice->tax ?? $invoice->vat_amount ?? 0),
                'total' => (float) $invoice->grand_total,
            ],
        ];

        // Add line items
        $items = $invoice->items ?? [];
        foreach ($items as $item) {
            $invoiceData['items'][] = [
                'description' => $item['description'] ?? '',
                'quantity' => (float) ($item['quantity'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'tax_rate' => (float) ($item['tax_rate'] ?? 0),
                'tax_amount' => (float) ($item['tax'] ?? $item['tax_amount'] ?? 0),
                'total' => (float) ($item['total_price'] ?? $item['total'] ?? 0),
            ];
        }

        return $invoiceData;
    }

    /**
     * Submit invoice to KRA eTIMS API (if API integration is enabled).
     */
    public function submitToEtims(Invoice $invoice): array
    {
        $apiUrl = config('services.etims.api_url');
        $apiKey = config('services.etims.api_key');

        if (! $apiUrl || ! $apiKey) {
            // If API is not configured, generate QR code locally
            return $this->generateLocalQrCode($invoice);
        }

        try {
            $invoiceData = $this->generateInvoiceData($invoice);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl.'/invoices', $invoiceData);

            if ($response->successful()) {
                $result = $response->json();

                // Update invoice with eTIMS data
                $invoice->update([
                    'etims_control_number' => $result['control_number'] ?? null,
                    'etims_qr_code' => $result['qr_code'] ?? null,
                    'etims_submitted_at' => now(),
                    'etims_metadata' => $result,
                ]);

                return [
                    'success' => true,
                    'control_number' => $result['control_number'] ?? null,
                    'qr_code' => $result['qr_code'] ?? null,
                    'message' => 'Invoice submitted to eTIMS successfully',
                ];
            }

            throw new \Exception('eTIMS API error: '.$response->body());
        } catch (\Exception $e) {
            Log::error('eTIMS submission failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback to local QR code generation
            return $this->generateLocalQrCode($invoice);
        }
    }

    /**
     * Generate QR code locally (for PDF-based eTIMS integration).
     */
    public function generateLocalQrCode(Invoice $invoice): array
    {
        $company = $invoice->company;

        if (! $company->kra_pin) {
            return [
                'success' => false,
                'error' => 'Company KRA PIN is required',
            ];
        }

        // Generate QR code data string (KRA format)
        $qrData = $this->buildQrCodeString($invoice);

        // Generate QR code image
        try {
            $qrCodeSvg = QrCodeFacade::size(200)
                ->generate($qrData);

            // Update invoice with QR code data
            $invoice->update([
                'etims_qr_code' => $qrData,
                'etims_submitted_at' => now(),
                'etims_metadata' => [
                    'generated_locally' => true,
                    'qr_data' => $qrData,
                ],
            ]);

            return [
                'success' => true,
                'qr_code' => $qrData,
                'qr_svg' => $qrCodeSvg,
                'message' => 'QR code generated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('QR code generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate QR code: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Build QR code string in KRA eTIMS format.
     */
    protected function buildQrCodeString(Invoice $invoice): string
    {
        $company = $invoice->company;
        $client = $invoice->client;

        // KRA eTIMS QR code format
        $data = [
            'seller_pin' => $company->kra_pin,
            'seller_name' => $company->name,
            'invoice_number' => $invoice->invoice_number ?? $invoice->invoice_reference,
            'invoice_date' => $invoice->issue_date ? date('Ymd', strtotime($invoice->issue_date)) : date('Ymd'),
            'invoice_amount' => number_format($invoice->grand_total, 2, '.', ''),
            'buyer_pin' => $client->kra_pin ?? '',
            'buyer_name' => $client->name ?? '',
        ];

        // Build pipe-delimited string (KRA format)
        return implode('|', [
            $data['seller_pin'],
            $data['seller_name'],
            $data['invoice_number'],
            $data['invoice_date'],
            $data['invoice_amount'],
            $data['buyer_pin'],
            $data['buyer_name'],
        ]);
    }

    /**
     * Export invoice data in eTIMS-compatible format (JSON/XML).
     */
    public function exportForEtims(Invoice $invoice, string $format = 'json'): string
    {
        $invoiceData = $this->generateInvoiceData($invoice);

        if ($format === 'xml') {
            return $this->arrayToXml($invoiceData);
        }

        return json_encode($invoiceData, JSON_PRETTY_PRINT);
    }

    /**
     * Convert array to XML format.
     */
    protected function arrayToXml(array $data, string $rootElement = 'invoice'): string
    {
        $xml = new \SimpleXMLElement('<'.$rootElement.'/>');
        $this->arrayToXmlRecursive($data, $xml);

        return $xml->asXML();
    }

    /**
     * Recursively convert array to XML.
     */
    protected function arrayToXmlRecursive(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }

    /**
     * Check if invoice is eTIMS compliant.
     */
    public function isCompliant(Invoice $invoice): bool
    {
        $company = $invoice->company;

        // Check if company has KRA PIN
        if (! $company->kra_pin) {
            return false;
        }

        // Check if invoice has required fields
        if (! $invoice->invoice_number && ! $invoice->invoice_reference) {
            return false;
        }

        if (! $invoice->issue_date) {
            return false;
        }

        return true;
    }

    /**
     * Pre-validate invoice data before eTIMS submission.
     * Returns validation errors if any, empty array if valid.
     */
    public function preValidateInvoice(Invoice $invoice): array
    {
        $errors = [];
        $company = $invoice->company;
        $client = $invoice->client;

        // Company validation
        if (! $company->kra_pin) {
            $errors[] = 'Company KRA PIN is required for eTIMS submission.';
        } elseif (! $this->isValidKraPin($company->kra_pin)) {
            $errors[] = 'Company KRA PIN format is invalid.';
        }

        if (empty($company->name)) {
            $errors[] = 'Company name is required.';
        }

        if (empty($company->address)) {
            $errors[] = 'Company address is required for eTIMS compliance.';
        }

        // Invoice validation
        if (! $invoice->invoice_number && ! $invoice->invoice_reference) {
            $errors[] = 'Invoice number or reference is required.';
        }

        if (! $invoice->issue_date) {
            $errors[] = 'Invoice issue date is required.';
        } elseif ($invoice->issue_date->isFuture()) {
            $errors[] = 'Invoice issue date cannot be in the future.';
        }

        if ($invoice->grand_total <= 0) {
            $errors[] = 'Invoice total must be greater than zero.';
        }

        // Client validation
        if (! $client) {
            $errors[] = 'Invoice must have a client.';
        } else {
            if (empty($client->name)) {
                $errors[] = 'Client name is required.';
            }

            // Buyer PIN is optional but if provided, must be valid
            if ($client->kra_pin && ! $this->isValidKraPin($client->kra_pin)) {
                $errors[] = 'Client KRA PIN format is invalid.';
            }
        }

        // Items validation
        $items = $invoice->invoiceItems ?? $invoice->items ?? [];
        if (empty($items) || count($items) === 0) {
            $errors[] = 'Invoice must have at least one item.';
        } else {
            foreach ($items as $index => $item) {
                $itemNum = $index + 1;
                if (empty($item['description'] ?? $item->description ?? '')) {
                    $errors[] = "Item #{$itemNum}: Description is required.";
                }
                if (($item['quantity'] ?? $item->quantity ?? 0) <= 0) {
                    $errors[] = "Item #{$itemNum}: Quantity must be greater than zero.";
                }
                if (($item['unit_price'] ?? $item->unit_price ?? 0) < 0) {
                    $errors[] = "Item #{$itemNum}: Unit price cannot be negative.";
                }
            }
        }

        // Totals validation
        $calculatedSubtotal = collect($items)->sum(function ($item) {
            return ($item['quantity'] ?? $item->quantity ?? 0) * ($item['unit_price'] ?? $item->unit_price ?? 0);
        });

        $tolerance = 0.01; // Allow small rounding differences
        if (abs($calculatedSubtotal - (float) $invoice->subtotal) > $tolerance) {
            $errors[] = 'Invoice subtotal does not match sum of line items.';
        }

        return $errors;
    }

    /**
     * Validate KRA PIN format (basic validation).
     */
    protected function isValidKraPin(?string $pin): bool
    {
        if (empty($pin)) {
            return false;
        }

        // KRA PIN format: P + 10 digits (e.g., P123456789A)
        // Or: A + 9 digits (e.g., A123456789)
        return preg_match('/^[PA]\d{9}[A-Z]?$/', strtoupper(trim($pin))) === 1;
    }

    /**
     * Submit reverse invoice (credit note) to eTIMS.
     */
    public function submitReverseInvoice(CreditNote $creditNote): array
    {
        $apiUrl = config('services.etims.api_url');
        $apiKey = config('services.etims.api_key');

        if (! $apiUrl || ! $apiKey) {
            // If API is not configured, generate reversal data locally
            return $this->generateLocalReversalData($creditNote);
        }

        try {
            // Pre-validate credit note
            $validationErrors = $this->preValidateCreditNote($creditNote);
            if (! empty($validationErrors)) {
                return [
                    'success' => false,
                    'errors' => $validationErrors,
                    'message' => 'Credit note validation failed',
                ];
            }

            $reversalData = $this->generateReversalData($creditNote);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl.'/reversals', $reversalData);

            if ($response->successful()) {
                $result = $response->json();

                // Update credit note with eTIMS data
                $creditNote->update([
                    'etims_control_number' => $result['control_number'] ?? null,
                    'etims_qr_code' => $result['qr_code'] ?? null,
                    'etims_submitted_at' => now(),
                    'etims_status' => 'approved',
                    'etims_metadata' => array_merge($creditNote->etims_metadata ?? [], [
                        'reversal_type' => 'credit_note',
                        'original_invoice' => $creditNote->invoice->etims_control_number,
                        'submitted_at' => now()->toIso8601String(),
                        'api_response' => $result,
                    ]),
                ]);

                return [
                    'success' => true,
                    'control_number' => $result['control_number'] ?? null,
                    'qr_code' => $result['qr_code'] ?? null,
                    'message' => 'Credit note submitted to eTIMS successfully',
                ];
            }

            $errorBody = $response->json() ?? ['message' => $response->body()];
            throw new \Exception('eTIMS API error: '.json_encode($errorBody));
        } catch (\Exception $e) {
            Log::error('eTIMS reversal submission failed', [
                'credit_note_id' => $creditNote->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback to local generation
            return $this->generateLocalReversalData($creditNote);
        }
    }

    /**
     * Pre-validate credit note data before eTIMS submission.
     */
    public function preValidateCreditNote(CreditNote $creditNote): array
    {
        $errors = [];
        $company = $creditNote->company;
        $invoice = $creditNote->invoice;

        // Company validation
        if (! $company->kra_pin) {
            $errors[] = 'Company KRA PIN is required for eTIMS submission.';
        } elseif (! $this->isValidKraPin($company->kra_pin)) {
            $errors[] = 'Company KRA PIN format is invalid.';
        }

        // Invoice validation (must have eTIMS control number for reversal)
        if (! $invoice->etims_control_number) {
            $errors[] = 'Original invoice must have an eTIMS control number for reversal.';
        }

        // Credit note validation
        if (! $creditNote->credit_note_reference && ! $creditNote->full_number) {
            $errors[] = 'Credit note number is required.';
        }

        if (! $creditNote->issue_date) {
            $errors[] = 'Credit note issue date is required.';
        } elseif ($creditNote->issue_date->isFuture()) {
            $errors[] = 'Credit note issue date cannot be in the future.';
        }

        if ($creditNote->total_credit <= 0) {
            $errors[] = 'Credit note total must be greater than zero.';
        }

        // Items validation
        $items = $creditNote->items ?? [];
        if (empty($items) || count($items) === 0) {
            $errors[] = 'Credit note must have at least one item.';
        }

        return $errors;
    }

    /**
     * Generate reversal data for eTIMS submission.
     */
    protected function generateReversalData(CreditNote $creditNote): array
    {
        $company = $creditNote->company;
        $invoice = $creditNote->invoice;
        $client = $creditNote->client ?? $invoice->client;

        return [
            'reversal_type' => 'credit_note',
            'original_invoice_control_number' => $invoice->etims_control_number,
            'seller' => [
                'pin' => $company->kra_pin,
                'name' => $company->name,
                'address' => $company->address ?? '',
            ],
            'buyer' => [
                'pin' => $client->kra_pin ?? '',
                'name' => $client->name ?? '',
            ],
            'credit_note' => [
                'number' => $creditNote->full_number ?? $creditNote->credit_note_reference,
                'date' => $creditNote->issue_date->format('Y-m-d'),
                'reason' => $creditNote->reason ?? 'other',
            ],
            'items' => $creditNote->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'tax_rate' => (float) ($item->vat_rate ?? 16),
                    'tax_amount' => (float) ($item->vat_rate ?? 16) * (float) $item->total_price / (100 + ($item->vat_rate ?? 16)),
                    'total' => (float) $item->total_price,
                ];
            })->toArray(),
            'totals' => [
                'subtotal' => (float) $creditNote->subtotal,
                'tax' => (float) $creditNote->vat_amount,
                'total' => (float) $creditNote->total_credit,
            ],
        ];
    }

    /**
     * Generate local reversal data (when API is not available).
     */
    protected function generateLocalReversalData(CreditNote $creditNote): array
    {
        $company = $creditNote->company;

        if (! $company->kra_pin) {
            return [
                'success' => false,
                'error' => 'Company KRA PIN is required',
            ];
        }

        // Generate reversal reference
        $reversalReference = 'REV-'.strtoupper(uniqid());

        // Generate QR code data for reversal
        $qrData = $this->buildReversalQrCodeString($creditNote);

        try {
            $qrCodeSvg = QrCodeFacade::size(200)
                ->generate($qrData);

            // Update credit note with reversal data
            $creditNote->update([
                'etims_control_number' => $reversalReference,
                'etims_qr_code' => $qrData,
                'etims_submitted_at' => now(),
                'etims_status' => 'submitted',
                'etims_metadata' => array_merge($creditNote->etims_metadata ?? [], [
                    'generated_locally' => true,
                    'reversal_type' => 'credit_note',
                    'original_invoice' => $creditNote->invoice->etims_control_number,
                    'qr_data' => $qrData,
                ]),
            ]);

            return [
                'success' => true,
                'control_number' => $reversalReference,
                'qr_code' => $qrData,
                'qr_svg' => $qrCodeSvg,
                'message' => 'Reversal data generated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Reversal QR code generation failed', [
                'credit_note_id' => $creditNote->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate reversal QR code: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Build QR code string for credit note reversal.
     */
    protected function buildReversalQrCodeString(CreditNote $creditNote): string
    {
        $company = $creditNote->company;
        $client = $creditNote->client ?? $creditNote->invoice->client;

        $data = [
            'reversal_type' => 'CN',
            'seller_pin' => $company->kra_pin,
            'seller_name' => $company->name,
            'credit_note_number' => $creditNote->full_number ?? $creditNote->credit_note_reference,
            'credit_note_date' => $creditNote->issue_date->format('Ymd'),
            'credit_amount' => number_format($creditNote->total_credit, 2, '.', ''),
            'original_invoice' => $creditNote->invoice->etims_control_number ?? $creditNote->invoice->invoice_number,
            'buyer_pin' => $client->kra_pin ?? '',
            'buyer_name' => $client->name ?? '',
        ];

        return implode('|', [
            $data['reversal_type'],
            $data['seller_pin'],
            $data['seller_name'],
            $data['credit_note_number'],
            $data['credit_note_date'],
            $data['credit_amount'],
            $data['original_invoice'],
            $data['buyer_pin'],
            $data['buyer_name'],
        ]);
    }

    /**
     * Enhanced invoice submission with pre-validation.
     */
    public function submitToEtimsWithValidation(Invoice $invoice): array
    {
        // Pre-validate before submission
        $validationErrors = $this->preValidateInvoice($invoice);
        if (! empty($validationErrors)) {
            return [
                'success' => false,
                'errors' => $validationErrors,
                'message' => 'Invoice validation failed. Please fix the errors before submission.',
            ];
        }

        // Proceed with submission
        return $this->submitToEtims($invoice);
    }
}
