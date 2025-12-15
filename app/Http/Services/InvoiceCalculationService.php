<?php

namespace App\Http\Services;

/**
 * InvoiceCalculationService
 *
 * The single, authoritative source for all invoice financial calculations.
 *
 * Rules:
 * - Pure logic (no DB writes, no status checks, no side effects)
 * - Deterministic (same input → same output, always)
 * - Numbers only (returns floats/decimals, not formatted strings)
 * - No currency symbols, no formatting
 * - Explicit values (stores calculated results, not formulas)
 *
 * This service becomes the only legal calculator in the system.
 */
class InvoiceCalculationService
{
    /**
     * Calculate invoice totals from line items and configuration.
     *
     * @param  array  $items  Line items with quantity, unit_price, vat_included, vat_rate
     * @param  array  $config  Configuration (vat_enabled, vat_rate, platform_fee_enabled, platform_fee_rate, discount, discount_type, vat_registered)
     * @return array Complete calculation results
     *
     * @throws \InvalidArgumentException If input is invalid
     */
    public function calculate(array $items, array $config): array
    {
        // Validate inputs
        $this->validateInputs($items, $config);

        // Extract configuration with defaults
        $vatEnabled = $config['vat_enabled'] ?? false;
        $vatRate = (float) ($config['vat_rate'] ?? 16.00);
        $vatRegistered = $config['vat_registered'] ?? false;
        $platformFeeEnabled = $config['platform_fee_enabled'] ?? true;
        $platformFeeRate = (float) ($config['platform_fee_rate'] ?? 0.03);
        $discount = (float) ($config['discount'] ?? 0);
        $discountType = $config['discount_type'] ?? null;

        // Step 1: Calculate line items with VAT
        $calculatedItems = $this->calculateLineItems($items, $vatEnabled, $vatRegistered, $vatRate);

        // Step 2: Calculate subtotal (sum of all line totals)
        $subtotal = $this->calculateSubtotal($calculatedItems);

        // Step 3: Apply discount
        $discountResult = $this->calculateDiscount($subtotal, $discount, $discountType);
        $subtotalAfterDiscount = $discountResult['subtotal_after_discount'];

        // Step 4: Calculate total VAT (sum of line VAT amounts)
        $vatAmount = $this->calculateTotalVat($calculatedItems);

        // Step 5: Calculate total (subtotal_after_discount + vat_amount)
        $total = $subtotalAfterDiscount + $vatAmount;

        // Step 6: Calculate platform fee
        $platformFee = $this->calculatePlatformFee($total, $platformFeeEnabled, $platformFeeRate);

        // Step 7: Calculate grand total
        $grandTotal = $total + $platformFee;

        return [
            'items' => $calculatedItems,
            'subtotal' => round($subtotal, 2),
            'discount' => $discountResult['discount_amount'],
            'discount_type' => $discountType,
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
            'vat_amount' => round($vatAmount, 2),
            'total' => round($total, 2),
            'platform_fee' => round($platformFee, 2),
            'platform_fee_calculation_base' => round($total, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Validate inputs.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateInputs(array $items, array $config): void
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('Items array cannot be empty.');
        }

        foreach ($items as $index => $item) {
            if (! isset($item['quantity']) || ! isset($item['unit_price'])) {
                throw new \InvalidArgumentException("Item at index {$index} must have quantity and unit_price.");
            }

            if ($item['quantity'] < 0) {
                throw new \InvalidArgumentException("Item at index {$index} quantity cannot be negative.");
            }

            if ($item['unit_price'] < 0) {
                throw new \InvalidArgumentException("Item at index {$index} unit_price cannot be negative.");
            }
        }
    }

    /**
     * Calculate line items with VAT.
     */
    protected function calculateLineItems(array $items, bool $vatEnabled, bool $vatRegistered, float $vatRate): array
    {
        $calculatedItems = [];

        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $totalPrice = $quantity * $unitPrice;

            $vatIncluded = $item['vat_included'] ?? false;
            $itemVatRate = (float) ($item['vat_rate'] ?? $vatRate);
            $itemVatAmount = 0.00;

            // Calculate VAT for this line item
            if ($vatEnabled && $vatRegistered && $itemVatRate > 0) {
                if ($vatIncluded) {
                    // VAT included in price
                    $itemVatAmount = $totalPrice * ($itemVatRate / (100 + $itemVatRate));
                } else {
                    // VAT added to price
                    $itemVatAmount = $totalPrice * ($itemVatRate / 100);
                }
            }

            $lineTotal = $vatIncluded ? $totalPrice : ($totalPrice + $itemVatAmount);

            $calculatedItems[] = [
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'total_price' => round($totalPrice, 2),
                'vat_included' => $vatIncluded,
                'vat_rate' => $itemVatRate,
                'vat_amount' => round($itemVatAmount, 2),
                'line_total' => round($lineTotal, 2),
            ];
        }

        return $calculatedItems;
    }

    /**
     * Calculate subtotal (sum of all line totals).
     */
    protected function calculateSubtotal(array $calculatedItems): float
    {
        $subtotal = 0.00;

        foreach ($calculatedItems as $item) {
            $subtotal += $item['line_total'];
        }

        return $subtotal;
    }

    /**
     * Calculate discount.
     */
    protected function calculateDiscount(float $subtotal, float $discount, ?string $discountType): array
    {
        if ($discount <= 0) {
            return [
                'discount_amount' => 0.00,
                'subtotal_after_discount' => $subtotal,
            ];
        }

        $discountAmount = 0.00;

        if ($discountType === 'percentage') {
            $discountAmount = $subtotal * ($discount / 100);
        } else {
            $discountAmount = $discount;
        }

        $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

        return [
            'discount_amount' => round($discountAmount, 2),
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
        ];
    }

    /**
     * Calculate total VAT (sum of line VAT amounts).
     */
    protected function calculateTotalVat(array $calculatedItems): float
    {
        $totalVat = 0.00;

        foreach ($calculatedItems as $item) {
            $totalVat += $item['vat_amount'];
        }

        return $totalVat;
    }

    /**
     * Calculate platform fee.
     */
    protected function calculatePlatformFee(float $total, bool $platformFeeEnabled, float $platformFeeRate): float
    {
        if (! $platformFeeEnabled || $platformFeeRate <= 0) {
            return 0.00;
        }

        return $total * $platformFeeRate;
    }
}
