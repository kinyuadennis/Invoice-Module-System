# Phase 2: Calculation Logic Inventory

## Purpose

This document maps ALL calculation logic in the codebase. This is a complete inventory before centralization.

**Rule:** Tag each location with its layer (Controller, Model, Service, View, PDF, Helper, Job)

---

## Subtotal Calculations

### Location 1: InvoiceController::preview()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 456-460  
**Classification:** ❌ **CONTROLLER**

```php
$subtotal = 0;
foreach ($validated['items'] as $item) {
    $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
}
```

---

### Location 2: InvoiceController::previewFrame()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 624-627  
**Classification:** ❌ **CONTROLLER**

Duplicate of Location 1.

---

### Location 3: InvoiceService::createInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 149-155  
**Classification:** ✅ **SERVICE** (but needs centralization)

```php
$subtotal = 0;
foreach ($items as $item) {
    $itemTotal = $item['total_price'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['rate'] ?? 0));
    $subtotal += $itemTotal;
}
```

---

### Location 4: InvoiceService::updateInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 325-331  
**Classification:** ✅ **SERVICE** (but needs centralization)

```php
$subtotal = 0;
foreach ($items as $item) {
    $itemTotal = $item['total_price'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['rate'] ?? 0));
    $subtotal += $itemTotal;
}
```

---

## Discount Calculations

### Location 1: InvoiceController::preview()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 462-473  
**Classification:** ❌ **CONTROLLER**

```php
$discount = $validated['discount'] ?? 0;
$discountType = $validated['discount_type'] ?? 'fixed';
$discountAmount = 0;
if ($discount > 0) {
    if ($discountType === 'percentage') {
        $discountAmount = $subtotal * ($discount / 100);
    } else {
        $discountAmount = $discount;
    }
}
$subtotalAfterDiscount = max(0, $subtotal - $discountAmount);
```

---

### Location 2: InvoiceController::previewFrame()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 624-641  
**Classification:** ❌ **CONTROLLER**

Duplicate of Location 1.

---

### Location 3: InvoiceService::createInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 157-168  
**Classification:** ✅ **SERVICE** (but needs centralization)

```php
$discount = $data['discount'] ?? 0;
$discountType = $data['discount_type'] ?? 'fixed';
$discountAmount = 0;
if ($discount > 0) {
    if ($discountType === 'percentage') {
        $discountAmount = $subtotal * ($discount / 100);
    } else {
        $discountAmount = $discount;
    }
}
$subtotalAfterDiscount = max(0, $subtotal - $discountAmount);
```

---

## VAT Calculations

### Location 1: InvoiceController::preview()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 478  
**Classification:** ❌ **CONTROLLER** + Hardcoded Rate

```php
$vatAmount = $subtotalAfterDiscount * 0.16; // 16% VAT
```

---

### Location 2: InvoiceController::previewFrame()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 646  
**Classification:** ❌ **CONTROLLER** + Hardcoded Rate

```php
$vatAmount = $subtotalAfterDiscount * 0.16;
```

---

### Location 3: InvoiceService::createInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 174  
**Classification:** ⚠️ **SERVICE** + Hardcoded Rate

```php
$vatAmount = $subtotalAfterDiscount * 0.16; // 16% VAT (Kenyan standard)
```

---

### Location 4: InvoiceService::updateInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 333  
**Classification:** ⚠️ **SERVICE** + Hardcoded Rate

```php
$vatAmount = $subtotal * 0.16;
```

---

### Location 5: InvoiceService::updateTotals()
**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 408  
**Classification:** ⚠️ **SERVICE** + Hardcoded Rate

```php
$vatAmount = $subtotal * 0.16; // 16% VAT (Kenyan standard)
```

---

### Location 6: InvoiceSnapshotBuilder::extractItemsData()
**File:** `app/Http/Services/InvoiceSnapshotBuilder.php`  
**Lines:** 159-168  
**Classification:** ⚠️ **SERVICE** (but in snapshot builder, should use calculation service)

```php
if ($invoice->vat_registered && $item->vat_rate) {
    if ($item->vat_included) {
        $itemVatAmount = $itemSubtotal * ($item->vat_rate / (100 + $item->vat_rate));
    } else {
        $itemVatAmount = $itemSubtotal * ($item->vat_rate / 100);
    }
}
```

---

### Location 7: HomeController (Public)
**File:** `app/Http/Controllers/Public/HomeController.php`  
**Line:** 280  
**Classification:** ❌ **CONTROLLER** + Hardcoded Rate

```php
$tax = $subtotal * 0.16; // 16% VAT
```

---

## Platform Fee Calculations

### Location 1: InvoiceController::preview()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 482  
**Classification:** ❌ **CONTROLLER** + Hardcoded Rate

```php
$platformFee = $totalBeforeFee * 0.03; // 3% platform fee
```

---

### Location 2: InvoiceController::previewFrame()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 650  
**Classification:** ❌ **CONTROLLER** + Hardcoded Rate

```php
$platformFee = $totalBeforeFee * 0.03;
```

---

### Location 3: InvoiceService::createInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 178  
**Classification:** ⚠️ **SERVICE** + Hardcoded Rate

```php
$platformFee = $totalBeforeFee * 0.03; // 3% platform fee
```

---

### Location 4: InvoiceService::updateInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 335  
**Classification:** ⚠️ **SERVICE** + **INCONSISTENT RATE**

```php
$platformFee = $totalBeforeFee * 0.008; // 0.8% - INCONSISTENT!
```

**CRITICAL:** This uses 0.8% while others use 3%. Creates inconsistent invoices.

---

### Location 5: InvoiceService::updateTotals()
**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 410  
**Classification:** ⚠️ **SERVICE** + Hardcoded Rate

```php
$platformFee = $totalBeforeFee * 0.03; // 3% platform fee
```

---

### Location 6: PlatformFeeService::generateFeeForInvoice()
**File:** `app/Http/Services/PlatformFeeService.php`  
**Lines:** 13, 21  
**Classification:** ⚠️ **SERVICE** + Hardcoded Constant

```php
private const FEE_RATE = 0.03;
$feeAmount = ($invoice->subtotal + $invoice->vat_amount) * self::FEE_RATE;
```

---

## Grand Total Calculations

### Location 1: InvoiceController::preview()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 481-483  
**Classification:** ❌ **CONTROLLER**

```php
$totalBeforeFee = $subtotalAfterDiscount + $vatAmount;
$platformFee = $totalBeforeFee * 0.03;
$grandTotal = $totalBeforeFee + $platformFee;
```

---

### Location 2: InvoiceController::previewFrame()
**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 649-651  
**Classification:** ❌ **CONTROLLER**

Duplicate of Location 1.

---

### Location 3: InvoiceService::createInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 175-180  
**Classification:** ✅ **SERVICE** (but needs centralization)

```php
$totalBeforeFee = $subtotalAfterDiscount + $vatAmount;
$platformFee = $totalBeforeFee * 0.03;
$grandTotal = $totalBeforeFee + $platformFee;
```

---

### Location 4: InvoiceService::updateInvoice()
**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 334-336  
**Classification:** ✅ **SERVICE** (but needs centralization + inconsistent rate)

```php
$totalBeforeFee = $subtotal + $vatAmount;
$platformFee = $totalBeforeFee * 0.008; // INCONSISTENT!
$grandTotal = $totalBeforeFee + $platformFee;
```

---

### Location 5: InvoiceService::updateTotals()
**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 409-412  
**Classification:** ✅ **SERVICE** (but needs centralization)

```php
$totalBeforeFee = $subtotal + $vatAmount;
$platformFee = $totalBeforeFee * 0.03;
$grandTotal = $totalBeforeFee + $platformFee;
```

---

## Line Item VAT Calculations

### Location 1: InvoiceSnapshotBuilder::extractItemsData()
**File:** `app/Http/Services/InvoiceSnapshotBuilder.php`  
**Lines:** 159-168  
**Classification:** ⚠️ **SERVICE** (but in snapshot builder)

Calculates VAT per line item (included vs excluded).

---

## Rounding Operations

### Location 1: InvoiceSnapshotBuilder::extractItemsData()
**File:** `app/Http/Services/InvoiceSnapshotBuilder.php`  
**Line:** 179  
**Classification:** ⚠️ **SERVICE**

```php
'vat_amount' => round($itemVatAmount, 2),
```

---

### Location 2: InvoiceSnapshotBuilder::extractTotalsData()
**File:** `app/Http/Services/InvoiceSnapshotBuilder.php`  
**Line:** 215  
**Classification:** ⚠️ **SERVICE**

```php
'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
```

---

## Summary by Layer

| Layer | Count | Status |
|-------|-------|--------|
| **Controller** | 8 | ❌ Must remove |
| **Service** | 12 | ⚠️ Must centralize |
| **Snapshot Builder** | 3 | ⚠️ Must use calculation service |
| **PDF/View** | 0 | ✅ Not touched (Phase 3) |
| **Model** | 0 | ✅ None found |
| **Helper** | 0 | ✅ None found |
| **Job** | 0 | ✅ None found |

---

## Critical Issues

1. **Inconsistent Platform Fee Rate:** `updateInvoice()` uses 0.8% while others use 3%
2. **Hardcoded VAT Rate:** 16% hardcoded in 7 locations
3. **Hardcoded Platform Fee Rate:** 3% hardcoded in 5 locations (0.8% in 1 location)
4. **Duplicate Logic:** Controllers duplicate service calculations
5. **Snapshot Builder Calculations:** Should use calculation service, not calculate itself

---

## Next Steps

1. Create single `InvoiceCalculationService`
2. Move all calculations to service
3. Remove calculations from controllers
4. Update snapshot builder to use calculation service
5. Ensure company rates are used (not hardcoded)

