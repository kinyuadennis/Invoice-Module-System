<?php

namespace App\Http\Services;

use App\Models\InvoiceSnapshot;
use Illuminate\Support\Arr;

/**
 * InvoiceEtimsExportService
 *
 * Transforms invoice snapshot data into ETIMS-compliant export format.
 * This service is read-only - it never modifies snapshots or invoices.
 *
 * Rules:
 * - Only reads from snapshot_data (immutable source)
 * - Generates JSON/XML export format
 * - Validates data before export
 * - Handles missing optional fields gracefully
 */
class InvoiceEtimsExportService
{
    protected InvoiceSnapshotValidator $snapshotValidator;

    protected KraPinValidator $kraPinValidator;

    public function __construct(
        InvoiceSnapshotValidator $snapshotValidator,
        KraPinValidator $kraPinValidator
    ) {
        $this->snapshotValidator = $snapshotValidator;
        $this->kraPinValidator = $kraPinValidator;
    }

    /**
     * Export invoice snapshot to ETIMS-compliant JSON format.
     *
     * @param  InvoiceSnapshot  $snapshot  Invoice snapshot to export
     * @return array ETIMS-compliant JSON structure
     *
     * @throws \RuntimeException If required fields are missing
     */
    public function exportToJson(InvoiceSnapshot $snapshot): array
    {
        // Validate snapshot has required fields
        $validation = $this->snapshotValidator->validateSnapshotForEtims($snapshot);
        if (! $validation['valid']) {
            throw new \RuntimeException(
                'Snapshot validation failed: '.implode(' ', $validation['errors'])
            );
        }

        $data = $snapshot->snapshot_data;
        $mappings = config('etims.field_mappings');
        $defaults = config('etims.defaults');
        $dateFormat = config('etims.export.date_format');

        // Helper to get value from snapshot using dot notation
        $get = function (string $path, $default = null) use ($data) {
            return Arr::get($data, $path, $default);
        };

        // Build ETIMS-compliant structure
        $etimsData = [
            'invoiceNumber' => $get($mappings['invoiceNumber']),
            'issueDate' => $this->formatDate($get($mappings['issueDate']), $dateFormat),
            'dueDate' => $this->formatDate($get($mappings['dueDate']), $dateFormat),
            'currency' => $get($mappings['currency'], $defaults['currency']),
            'poNumber' => $get($mappings['poNumber']),

            'seller' => [
                'kraPin' => $this->kraPinValidator->sanitize($get($mappings['seller.kraPin'])),
                'name' => $get($mappings['seller.name']),
                'email' => $get($mappings['seller.email']),
                'phone' => $get($mappings['seller.phone']),
                'address' => $get($mappings['seller.address']),
                'registrationNumber' => $get($mappings['seller.registrationNumber']),
            ],

            'buyer' => [
                'kraPin' => $get($mappings['buyer.kraPin']) ? $this->kraPinValidator->sanitize($get($mappings['buyer.kraPin'])) : null,
                'name' => $get($mappings['buyer.name']),
                'email' => $get($mappings['buyer.email']),
                'phone' => $get($mappings['buyer.phone']),
                'address' => $get($mappings['buyer.address']),
            ],

            'items' => $this->formatItems($get('items', [])),

            'totals' => [
                'subtotal' => $this->formatDecimal($get($mappings['totals.subtotal'], 0)),
                'discount' => $this->formatDecimal($get($mappings['totals.discount'], 0)),
                'vatAmount' => $this->formatDecimal($get($mappings['totals.vatAmount'], 0)),
                'platformFee' => $this->formatDecimal($get($mappings['totals.platformFee'], 0)),
                'total' => $this->formatDecimal($get($mappings['totals.total'], 0)),
            ],

            'metadata' => [
                'exportedAt' => now()->toIso8601String(),
                'exportFormat' => 'ETIMS',
                'exportVersion' => '1.0',
            ],
        ];

        // Remove null values for cleaner JSON
        return $this->removeNullValues($etimsData);
    }

    /**
     * Export invoice snapshot to ETIMS-compliant XML format.
     *
     * @param  InvoiceSnapshot  $snapshot  Invoice snapshot to export
     * @return string ETIMS-compliant XML string
     *
     * @throws \RuntimeException If required fields are missing
     */
    public function exportToXml(InvoiceSnapshot $snapshot): string
    {
        $jsonData = $this->exportToJson($snapshot);

        // Convert JSON structure to XML
        $xml = new \SimpleXMLElement('<invoice></invoice>');
        $this->arrayToXml($jsonData, $xml);

        return $xml->asXML();
    }

    /**
     * Validate export data before generating export.
     *
     * @param  InvoiceSnapshot  $snapshot  Snapshot to validate
     * @return array Validation result with 'valid', 'errors', 'warnings'
     */
    public function validateExportData(InvoiceSnapshot $snapshot): array
    {
        return $this->snapshotValidator->validateSnapshotForEtims($snapshot);
    }

    /**
     * Format line items for ETIMS export.
     *
     * @param  array  $items  Line items from snapshot
     * @return array Formatted items array
     */
    protected function formatItems(array $items): array
    {
        $formatted = [];

        foreach ($items as $item) {
            $formatted[] = [
                'description' => $item['description'] ?? 'Item',
                'quantity' => $this->formatDecimal($item['quantity'] ?? 1),
                'unitPrice' => $this->formatDecimal($item['unit_price'] ?? 0),
                'totalPrice' => $this->formatDecimal($item['total_price'] ?? 0),
                'vatIncluded' => (bool) ($item['vat_included'] ?? false),
                'vatRate' => $this->formatDecimal($item['vat_rate'] ?? 0),
                'vatAmount' => $this->formatDecimal($item['vat_amount'] ?? 0),
            ];
        }

        return $formatted;
    }

    /**
     * Format date string to ETIMS format.
     *
     * @param  string|null  $date  Date string
     * @param  string  $format  Date format
     * @return string|null Formatted date or null
     */
    protected function formatDate(?string $date, string $format): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format decimal to ETIMS standard (2 decimal places).
     *
     * @param  float|string|null  $value  Value to format
     * @return float Formatted decimal
     */
    protected function formatDecimal($value): float
    {
        $decimalPlaces = config('etims.export.decimal_places', 2);

        return round((float) ($value ?? 0), $decimalPlaces);
    }

    /**
     * Remove null values from array recursively.
     *
     * @param  array  $array  Array to clean
     * @return array Array without null values
     */
    protected function removeNullValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->removeNullValues($value);
                // Remove empty arrays
                if (empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif ($value === null) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Convert array to XML recursively.
     *
     * @param  array  $data  Data array
     * @param  \SimpleXMLElement  $xml  XML element
     */
    protected function arrayToXml(array $data, \SimpleXMLElement &$xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}
