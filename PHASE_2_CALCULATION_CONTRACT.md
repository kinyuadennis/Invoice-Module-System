# Phase 2: Authoritative Calculation Contract

## Purpose

This document defines the contract for the single, authoritative calculation service. All financial calculations must go through this service.

---

## Service Name

`InvoiceCalculationService`

---

## Input Contract

### Required Inputs

1. **Line Items** (array)
   ```php
   [
       [
           'quantity' => float,
           'unit_price' => float,
           'vat_included' => bool,
           'vat_rate' => float|null, // Percentage (e.g., 16.00)
       ],
       // ... more items
   ]
   ```

2. **Company Tax Configuration**
   ```php
   [
       'vat_enabled' => bool,
       'vat_rate' => float, // Percentage (e.g., 16.00)
   ]
   ```

3. **Platform Fee Configuration**
   ```php
   [
       'platform_fee_enabled' => bool,
       'platform_fee_rate' => float, // Decimal (e.g., 0.03 for 3%)
   ]
   ```

4. **Discount Configuration** (optional)
   ```php
   [
       'discount' => float,
       'discount_type' => 'fixed'|'percentage'|null,
   ]
   ```

5. **VAT Registration Status** (optional)
   ```php
   'vat_registered' => bool // Whether invoice is VAT registered
   ```

---

## Output Contract

### Return Structure

```php
[
    'items' => [
        [
            'quantity' => float,
            'unit_price' => float,
            'total_price' => float, // quantity * unit_price
            'vat_included' => bool,
            'vat_rate' => float,
            'vat_amount' => float, // Explicit VAT for this line
            'line_total' => float, // total_price + vat_amount (if not included)
        ],
        // ... more items
    ],
    'subtotal' => float, // Sum of all line totals before discount
    'discount' => float,
    'discount_type' => 'fixed'|'percentage'|null,
    'subtotal_after_discount' => float,
    'vat_amount' => float, // Total VAT (sum of line VAT amounts)
    'total' => float, // subtotal_after_discount + vat_amount
    'platform_fee' => float,
    'platform_fee_calculation_base' => float, // What fee was calculated on
    'grand_total' => float, // total + platform_fee
]
```

---

## Rules

1. **Pure Logic:** No DB writes, no status checks, no side effects
2. **Deterministic:** Same input → same output (always)
3. **Numbers Only:** Returns floats/decimals, not formatted strings
4. **No Currency:** No currency symbols, no formatting
5. **Explicit Values:** Stores calculated results, not formulas
6. **Rounding:** All monetary values rounded to 2 decimal places

---

## Calculation Order

1. Calculate line item totals (quantity * unit_price)
2. Calculate line item VAT (if applicable)
3. Sum line totals → subtotal
4. Apply discount (if any)
5. Calculate total VAT (sum of line VAT amounts)
6. Calculate total (subtotal_after_discount + vat_amount)
7. Calculate platform fee (on total)
8. Calculate grand total (total + platform_fee)

---

## VAT Calculation Rules

### If VAT is Included in Price
```php
vat_amount = line_total * (vat_rate / (100 + vat_rate))
```

### If VAT is Added to Price
```php
vat_amount = line_total * (vat_rate / 100)
```

### VAT Only Applied If
- `vat_enabled` is true (company setting)
- `vat_registered` is true (invoice setting)
- Item has `vat_rate` set

---

## Platform Fee Calculation Rules

```php
platform_fee = total * platform_fee_rate
```

### Platform Fee Only Applied If
- `platform_fee_enabled` is true (company setting)

---

## Discount Calculation Rules

### Percentage Discount
```php
discount_amount = subtotal * (discount / 100)
subtotal_after_discount = subtotal - discount_amount
```

### Fixed Discount
```php
discount_amount = discount
subtotal_after_discount = subtotal - discount_amount
```

### Minimum
```php
subtotal_after_discount = max(0, subtotal_after_discount)
```

---

## Error Handling

- Invalid input → throw `\InvalidArgumentException`
- Missing required input → throw `\InvalidArgumentException`
- Division by zero → return 0 (for safety)

---

## Usage Example

```php
$calculationService = new InvoiceCalculationService();

$result = $calculationService->calculate([
    'items' => [
        [
            'quantity' => 10,
            'unit_price' => 100.00,
            'vat_included' => false,
            'vat_rate' => 16.00,
        ],
    ],
    'vat_enabled' => true,
    'vat_rate' => 16.00,
    'vat_registered' => true,
    'platform_fee_enabled' => true,
    'platform_fee_rate' => 0.03,
    'discount' => 0,
    'discount_type' => null,
]);

// $result['grand_total'] = 1160.00 + 34.80 = 1194.80
```

---

## Integration Points

1. **InvoiceService::createInvoice()** - Use for new invoices
2. **InvoiceService::updateInvoice()** - Use for draft updates
3. **InvoiceService::updateTotals()** - Use for recalculation
4. **InvoiceController::preview()** - Use for preview calculations
5. **InvoiceController::previewFrame()** - Use for preview calculations
6. **InvoiceSnapshotBuilder** - Use to get totals for snapshot

---

## What This Service Does NOT Do

- ❌ Format currency
- ❌ Access database
- ❌ Check invoice status
- ❌ Create/update records
- ❌ Handle business rules (beyond calculations)
- ❌ Validate invoice data

---

## What This Service DOES Do

- ✅ Calculate line item totals
- ✅ Calculate VAT amounts
- ✅ Calculate discounts
- ✅ Calculate platform fees
- ✅ Calculate grand totals
- ✅ Return explicit, rounded values

