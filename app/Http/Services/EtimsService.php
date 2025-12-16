<?php

namespace App\Http\Services;

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
}
