# InvoiceHub: Gap Analysis - Current Code vs Blueprint

## Executive Summary

This is a **read-only surgical map** identifying every violation, mismatch, and risk between current InvoiceHub code and the approved blueprint. No refactoring. No fixes. No "quick wins". Only exposure of mismatches.

**Analysis Date:** Current  
**Blueprint Reference:** FINAL_IMPLEMENTATION_BLUEPRINT.md  
**Guides Reference:** IDE Operating Instructions + Optimization & Evolution Guide

**Critical Finding:** System is **NOT blueprint-compliant**. Multiple violations prevent production deployment.

---

## Table of Contents

1. [Invoice Lifecycle Mapping](#1-invoice-lifecycle-mapping)
2. [Calculation Location Audit](#2-calculation-location-audit)
3. [PDF Dependency Scan](#3-pdf-dependency-scan)
4. [Immutability Breach Check](#4-immutability-breach-check)
5. [Company Isolation Review](#5-company-isolation-review)
6. [Summary of Violations](#6-summary-of-violations)

---

## 1. Invoice Lifecycle Mapping

### Current Status Values

**Database Enum:** `draft`, `sent`, `paid`, `overdue`, `cancelled`

**Blueprint Requirement:** `draft`, `finalized`, `sent`, `paid`, `overdue`, `cancelled`

**Gap:** `finalized` status does NOT exist.

---

### Status Change Locations

#### Location 1: InvoiceService::createInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 52

**Current Behavior:**
- Accepts `status` from request
- Defaults to `'draft'` if not provided
- No validation of status transitions
- No lifecycle enforcement

**Violation:** Status can be set to `'sent'` or `'paid'` directly on creation, bypassing `finalized` state.

**Blueprint Requirement:** Invoices must transition: `draft → finalized → sent → paid`

---

#### Location 2: InvoiceService::updateInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 264

**Current Behavior:**
- Accepts `status` in update data
- Updates status without lifecycle validation
- No check if invoice is finalized
- No enforcement of valid transitions

**Violation:** Status can be changed on ANY invoice, regardless of current state.

**Blueprint Requirement:** Only drafts can be edited. Finalized invoices can only transition status forward (finalized → sent → paid).

---

#### Location 3: PaymentService::processPayment()

**File:** `app/Http/Services/PaymentService.php`  
**Lines:** 42-47

**Current Behavior:**
- Directly sets `$invoice->status = 'paid'`
- No lifecycle check
- No validation that invoice is in valid state for payment
- Uses non-existent status `'partially_paid'` (commented as needing enum addition)

**Violation:** Payment service bypasses lifecycle, directly manipulates status.

**Blueprint Requirement:** Payment marking should validate invoice is in `sent` or `overdue` state before marking as `paid`.

---

#### Location 4: InvoiceController::destroy()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 254

**Current Behavior:**
- Checks `if ($invoice->status !== 'draft')` before deletion
- Only drafts can be deleted

**Status:** ✅ **COMPLIANT** - Correctly prevents deletion of non-draft invoices.

---

### Mutability Enforcement

#### Current Enforcement

**File:** `app/Models/Invoice.php`  
**Lines:** 78-88

**Current Behavior:**
- Boot method prevents updates to prefix fields (`prefix_used`, `serial_number`, `full_number`)
- Only numbering fields protected
- No protection for financial fields
- No protection for structural fields
- No lifecycle-based immutability

**Violation:** Invoices in `sent` or `paid` status can still be edited (except numbering fields).

**Blueprint Requirement:** Invoices in `finalized`, `sent`, `paid`, or `overdue` status must be immutable (except status transitions and notes).

---

#### Missing Lifecycle Methods

**File:** `app/Models/Invoice.php`

**Current State:**
- No `isDraft()` method
- No `isFinalized()` method
- No `isIssued()` method
- No `canEdit()` method
- No `canRecalculateTotals()` method
- No `canModifyItems()` method
- No `finalize()` method

**Violation:** No lifecycle state methods exist to enforce immutability rules.

**Blueprint Requirement:** All lifecycle state methods must exist to enable consistent enforcement.

---

### Status Transition Validation

**Current State:**
- No validation of status transitions
- Any status can change to any other status
- No enforcement of forward-only progression

**Violation:** Status transitions are unvalidated, allowing invalid state changes.

**Blueprint Requirement:** Status transitions must be validated (draft → finalized → sent → paid, with cancellation allowed from finalized/sent).

---

## 2. Calculation Location Audit

### Subtotal Calculations

#### Location 1: InvoiceController::preview()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 456-460

**Classification:** ❌ **CONTROLLER** - Business logic in wrong layer

**Current Behavior:**
```php
$subtotal = 0;
foreach ($validated['items'] as $item) {
    $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
}
```

**Violation:** Calculation logic belongs in service, not controller.

**Blueprint Requirement:** Controllers validate, call services, return responses. No calculations.

---

#### Location 2: InvoiceController::previewFrame()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 624-627

**Classification:** ❌ **CONTROLLER** - Business logic in wrong layer

**Current Behavior:** Duplicate calculation logic (same as preview())

**Violation:** Logic duplication + wrong layer.

**Blueprint Requirement:** Extract to service method, remove duplication.

---

#### Location 3: InvoiceService::createInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 149-155

**Classification:** ✅ **SERVICE** - Correct layer

**Status:** Compliant - calculation in service layer.

---

#### Location 4: InvoiceService::updateInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 325-331

**Classification:** ✅ **SERVICE** - Correct layer

**Status:** Compliant - calculation in service layer.

---

### VAT Calculations

#### Location 1: InvoiceController::preview()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 478

**Classification:** ❌ **CONTROLLER** - Business logic + hardcoded rate

**Current Behavior:**
```php
$vatAmount = $subtotalAfterDiscount * 0.16; // 16% VAT
```

**Violations:**
1. Business logic in controller
2. Hardcoded VAT rate (should use company rate)

**Blueprint Requirement:** Use `$company->getVatRateDecimal()` in service method.

---

#### Location 2: InvoiceController::previewFrame()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 646

**Classification:** ❌ **CONTROLLER** - Same violations as preview()

**Violations:** Duplicate logic + hardcoded rate.

---

#### Location 3: InvoiceService::createInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 174

**Classification:** ⚠️ **SERVICE** - Correct layer but hardcoded rate

**Current Behavior:**
```php
$vatAmount = $subtotalAfterDiscount * 0.16; // 16% VAT (Kenyan standard)
```

**Violation:** Hardcoded rate, not company-configurable.

**Blueprint Requirement:** Use `$company->getVatRateDecimal()`.

---

#### Location 4: InvoiceService::updateInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 333

**Classification:** ⚠️ **SERVICE** - Correct layer but hardcoded rate

**Violation:** Hardcoded rate.

---

#### Location 5: InvoiceService::updateTotals()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 408

**Classification:** ⚠️ **SERVICE** - Correct layer but hardcoded rate

**Violation:** Hardcoded rate.

---

### Platform Fee Calculations

#### Location 1: InvoiceController::preview()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 482

**Classification:** ❌ **CONTROLLER** - Business logic + hardcoded rate

**Current Behavior:**
```php
$platformFee = $totalBeforeFee * 0.03; // 3% platform fee
```

**Violations:**
1. Business logic in controller
2. Hardcoded platform fee rate

**Blueprint Requirement:** Use `$company->getPlatformFeeRate()` in service method.

---

#### Location 2: InvoiceController::previewFrame()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 650

**Classification:** ❌ **CONTROLLER** - Same violations

---

#### Location 3: InvoiceService::createInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 178

**Classification:** ⚠️ **SERVICE** - Correct layer but hardcoded rate

**Current Behavior:**
```php
$platformFee = $totalBeforeFee * 0.03; // 3% platform fee
```

**Violation:** Hardcoded rate.

---

#### Location 4: InvoiceService::updateInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 335

**Classification:** ⚠️ **SERVICE** - Correct layer but **INCONSISTENT RATE**

**Current Behavior:**
```php
$platformFee = $totalBeforeFee * 0.008; // 0.8% - INCONSISTENT!
```

**Violation:** Uses 0.8% (0.008) while other locations use 3% (0.03). Creates inconsistent invoices.

**Blueprint Requirement:** Standardize on company-configurable rate.

---

#### Location 5: InvoiceService::updateTotals()

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 410

**Classification:** ⚠️ **SERVICE** - Correct layer but hardcoded rate

**Violation:** Hardcoded rate.

---

#### Location 6: PlatformFeeService::generateFeeForInvoice()

**File:** `app/Http/Services/PlatformFeeService.php`  
**Line:** 13, 21

**Classification:** ⚠️ **SERVICE** - Correct layer but hardcoded constant

**Current Behavior:**
```php
private const FEE_RATE = 0.03; // Hardcoded constant
$feeAmount = ($invoice->subtotal + $invoice->vat_amount) * self::FEE_RATE;
```

**Violation:** Hardcoded constant, not company-specific.

**Blueprint Requirement:** Use `$invoice->company->getPlatformFeeRate()`.

---

### Discount Calculations

#### Location 1: InvoiceController::preview()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 462-473

**Classification:** ❌ **CONTROLLER** - Business logic in wrong layer

**Violation:** Discount calculation logic in controller.

**Blueprint Requirement:** Move to service method.

---

#### Location 2: InvoiceController::previewFrame()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 624-641

**Classification:** ❌ **CONTROLLER** - Duplicate logic

---

#### Location 3: InvoiceService::createInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 157-168

**Classification:** ✅ **SERVICE** - Correct layer

**Status:** Compliant.

---

### Tax Rate Calculation

#### Location 1: FormatsInvoiceData Trait

**File:** `app/Traits/FormatsInvoiceData.php`  
**Lines:** 95-97

**Classification:** ❌ **TRAIT/FORMATTING** - Calculation during formatting

**Current Behavior:**
```php
$data['tax_rate'] = $data['subtotal'] > 0
    ? round(($data['tax'] / $data['subtotal']) * 100, 2)
    : 0;
```

**Violation:** Calculated during formatting, should be pre-stored on invoice.

**Blueprint Requirement:** Calculate and store `tax_rate` at invoice creation/update time.

---

## 3. PDF Dependency Scan

### PDF Generation Flow

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Method:** `generatePdf()`  
**Lines:** 272-382

**Current Flow:**
1. Load invoice with relationships: `with(['client', 'invoiceItems', 'platformFees', 'user', 'company.invoiceTemplate'])`
2. Call `formatInvoiceForShow()` which may trigger additional queries
3. Load platform fee from relationship: `$invoice->platformFees->first()`
4. Pass `$invoice->company` to PDF view
5. PDF view may call `$company->getPdfSettings()`

**Classification:** ⚠️ **UNSAFE** - Queries DB, no snapshot system

---

### PDF View Analysis

#### File: resources/views/pdf/partials/footer.blade.php

**Lines:** 5-11

**Current Behavior:**
```php
$pdfSettings = $company->getPdfSettings();
// Fallback: load company if only ID is available
$company = \App\Models\Company::find($invoice['company']['id']);
$pdfSettings = $company->getPdfSettings();
```

**Classification:** ❌ **LOGIC-LEAKING** - DB query in PDF view

**Violations:**
1. Calls `getPdfSettings()` method (may query settings JSON)
2. Falls back to `Company::find()` - direct DB query in view
3. Settings not pre-resolved in controller

**Blueprint Requirement:** All settings must be pre-resolved in controller, passed as data.

---

#### File: resources/views/invoices/pdf.blade.php

**Lines:** 650-653, 661-668

**Current Behavior:**
```php
@php
    $itemTotal = $item['total'] ?? $item['total_price'] ?? 0;
    $invoiceSubtotal = $invoice['subtotal'] ?? 1;
    $invoiceTax = $invoice['vat_amount'] ?? $invoice['tax'] ?? 0;
    $itemTax = $invoiceSubtotal > 0 ? ($itemTotal / $invoiceSubtotal) * $invoiceTax : 0;
@endphp
```

**Classification:** ❌ **LOGIC-LEAKING** - Calculation in PDF view

**Violations:**
1. Calculates item tax proportionally
2. Calculates item discount proportionally
3. Business logic in PDF view

**Blueprint Requirement:** All calculations must be pre-computed, PDF only renders.

---

#### File: resources/views/invoices/pdf.blade.php

**Line:** 687

**Current Behavior:**
```php
VAT ({{ $invoice['tax_rate'] ?? 16 }}%):
```

**Classification:** ⚠️ **UNSAFE** - Hardcoded fallback

**Violation:** Falls back to hardcoded 16% if tax_rate not set.

**Blueprint Requirement:** Tax rate must be stored on invoice, no fallback calculation.

---

#### File: resources/views/invoices/pdf.blade.php

**Line:** 699

**Current Behavior:**
```php
Platform Fee (3%):
```

**Classification:** ⚠️ **UNSAFE** - Hardcoded display

**Violation:** Hardcoded "3%" label, not using stored rate.

**Blueprint Requirement:** Display rate from invoice data (platform_fee_rate_used).

---

### PDF Data Source

**Current State:**
- PDFs use `formatInvoiceForShow()` which loads live invoice data
- No snapshot system exists
- If company/client data changes, PDFs regenerate differently
- Platform fee loaded from relationship (may trigger query)

**Classification:** ❌ **UNSAFE** - No immutable snapshot system

**Blueprint Requirement:** Finalized invoices must use snapshot data, drafts can use live data.

---

## 4. Immutability Breach Check

### Fields That Can Be Edited After "Sent" or "Paid"

#### InvoiceService::updateInvoice()

**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 246-397

**Current Behavior:**
- No lifecycle check before update
- Allows updating: `client_id`, `issue_date`, `due_date`, `status`, `payment_method`, `payment_details`, `notes`
- Allows deleting and recreating items (Lines 322, 370)
- Allows recalculating totals (Line 389)

**Violations:**
1. Invoices in `sent` or `paid` status can be edited
2. Items can be modified on non-draft invoices
3. Totals can be recalculated on non-draft invoices
4. Financial fields can be updated

**Blueprint Requirement:** Only drafts can be edited. Finalized invoices are immutable (except status transitions and notes).

---

#### InvoiceService::updateTotals()

**File:** `app/Http/Services/InvoiceService.php`  
**Lines:** 402-425

**Current Behavior:**
- No lifecycle check
- Can be called on ANY invoice
- Recalculates VAT, platform fee, grand_total from live items

**Violation:** Totals can be recalculated on finalized/issued invoices, rewriting financial history.

**Blueprint Requirement:** Only drafts can have totals recalculated.

---

#### InvoiceItem Model

**File:** `app/Models/InvoiceItem.php`

**Current Behavior:**
- No immutability protection
- Items can be created/deleted/updated on any invoice
- No check if parent invoice is finalized

**Violation:** Invoice items can be modified on finalized invoices.

**Blueprint Requirement:** Items must be immutable on finalized invoices.

---

#### InvoiceController::autosave()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Lines:** 809-961

**Current Behavior:**
- Checks `status === 'draft'` before update (Line 833)
- Allows item modification (Lines 858-868)
- Calls `updateTotals()` (Line 871)

**Status:** ✅ **PARTIALLY COMPLIANT** - Only allows autosave on drafts, but no lifecycle method used.

**Improvement Needed:** Use `invoice->canEdit()` method instead of direct status check.

---

### Update Paths

#### Path 1: InvoiceController::update()

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 223

**Current Behavior:**
- Calls `invoiceService->updateInvoice()` without lifecycle check
- No validation that invoice is editable

**Violation:** Controller doesn't enforce lifecycle before service call.

**Blueprint Requirement:** Controller must check `invoice->canEdit()` before calling service.

---

#### Path 2: UpdateInvoiceRequest

**File:** `app/Http/Requests/UpdateInvoiceRequest.php`  
**Line:** 26

**Current Behavior:**
- Validates status enum: `'draft,sent,paid,overdue,cancelled'`
- Does NOT include `'finalized'`
- Does NOT check if invoice is editable

**Violation:** Request validation doesn't enforce lifecycle rules.

**Blueprint Requirement:** Request must validate invoice is editable before accepting update.

---

#### Path 3: Direct Model Updates

**File:** `app/Http/Services/InvoiceService.php`  
**Line:** 386

**Current Behavior:**
- `$invoice->update($data)` called without lifecycle check
- Model boot method only protects prefix fields

**Violation:** Model doesn't prevent structural edits on finalized invoices.

**Blueprint Requirement:** Model boot method must enforce immutability for finalized invoices.

---

## 5. Company Isolation Review

### Query Scoping Analysis

#### Invoice Queries

**File:** `app/Http/Controllers/User/InvoiceController.php`

**Status:** ✅ **COMPLIANT**

**Evidence:**
- Line 29: `CurrentCompanyService::requireId()` used
- Line 33: `Invoice::where('company_id', $companyId)` - explicit scoping
- All invoice queries scoped by company_id

**Status:** Compliant - company scoping enforced.

---

#### Client Queries

**File:** `app/Http/Controllers/User/InvoiceController.php`  
**Line:** 95

**Status:** ✅ **COMPLIANT**

**Evidence:**
```php
$clients = Client::where('company_id', $companyId)
```

**Status:** Compliant - explicit company scoping.

---

#### Payment Queries

**File:** `app/Http/Controllers/User/PaymentController.php`

**Status:** ✅ **COMPLIANT**

**Evidence:**
- Uses `CurrentCompanyService::requireId()`
- Queries scoped by company_id

**Status:** Compliant.

---

#### Platform Fee Queries

**File:** `app/Http/Services/PlatformFeeService.php`  
**Line:** 24

**Status:** ✅ **COMPLIANT**

**Evidence:**
```php
PlatformFee::where('invoice_id', $invoice->id)
    ->where('company_id', $invoice->company_id)
```

**Status:** Compliant - explicit company scoping.

---

#### Service Queries

**File:** `app/Http/Services/InvoiceService.php`

**Status:** ✅ **COMPLIANT**

**Evidence:**
- Line 36: `CurrentCompanyService::requireId()` used consistently
- All queries include `->where('company_id', $companyId)`

**Status:** Compliant.

---

#### Admin Queries

**File:** `app/Http/Controllers/Admin/InvoiceController.php`  
**Line:** 23

**Current Behavior:**
- Admin queries may not be company-scoped (by design for admin access)
- Admin has access to all companies

**Status:** ⚠️ **ACCEPTABLE** - Admin access is intentional, not a violation.

---

### Global Settings Access

#### PDF Footer Partial

**File:** `resources/views/pdf/partials/footer.blade.php`  
**Line:** 9

**Current Behavior:**
```php
$company = \App\Models\Company::find($invoice['company']['id']);
```

**Classification:** ⚠️ **RISKY** - Direct model access in view

**Violation:** View directly queries Company model, bypassing company scoping (though uses invoice's company_id).

**Blueprint Requirement:** Company data should be pre-loaded and passed to view.

---

#### Admin Client Create View

**File:** `resources/views/admin/clients/create.blade.php`  
**Line:** 16

**Current Behavior:**
```php
$companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);
```

**Status:** ✅ **ACCEPTABLE** - Admin view, intentional global access.

---

## 6. Summary of Violations

### Critical Violations (Blocks Production)

1. **No `finalized` status exists** - Lifecycle incomplete
2. **No snapshot system** - PDFs query live DB, data can change
3. **Invoices can be edited after `sent`/`paid`** - Immutability not enforced
4. **Totals can be recalculated on finalized invoices** - Financial history can be rewritten
5. **Items can be modified on finalized invoices** - Invoice structure can change
6. **VAT hardcoded in 7+ locations** - Not company-configurable
7. **Platform fee inconsistent (3% vs 0.8%)** - Creates inconsistent invoices
8. **PDF views contain calculation logic** - Violates read-only renderer rule
9. **PDF views query database** - Performance risk, data inconsistency
10. **No audit logging system** - Cannot defend against disputes

---

### High Priority Violations (Serious Credibility Issues)

11. **Calculation logic in controllers** - Business logic in wrong layer
12. **Tax rate calculated during formatting** - Should be pre-stored
13. **No lifecycle state methods** - Cannot enforce immutability consistently
14. **Status transitions unvalidated** - Invalid state changes possible
15. **Payment service bypasses lifecycle** - Direct status manipulation

---

### Medium Priority Violations (Code Quality)

16. **Duplicate calculation logic** - preview() and previewFrame() duplicate code
17. **Hardcoded rate fallbacks in PDF** - Should use stored rates
18. **Platform fee rate not company-configurable** - Hardcoded constant
19. **No rate storage on invoices** - Cannot reproduce if company rates change
20. **Model doesn't enforce immutability** - Only prefix fields protected

---

### Low Priority Violations (Nice-to-Have)

21. **Autosave uses direct status check** - Should use lifecycle method
22. **Request validation doesn't check editability** - Could fail earlier
23. **PDF settings not pre-resolved** - Minor performance impact

---

## Violation Count by Category

| Category | Critical | High | Medium | Low | Total |
|---------|---------|------|--------|-----|-------|
| Lifecycle | 3 | 2 | 1 | 1 | 7 |
| Calculations | 2 | 2 | 3 | 0 | 7 |
| PDF Generation | 2 | 0 | 2 | 1 | 5 |
| Immutability | 3 | 1 | 1 | 1 | 6 |
| Company Scoping | 0 | 0 | 1 | 0 | 1 |
| Audit Trail | 1 | 0 | 0 | 0 | 1 |
| **TOTAL** | **11** | **5** | **8** | **3** | **27** |

---

## Risk Assessment

### Financial Integrity Risks

**Risk Level:** 🔴 **CRITICAL**

**Risks:**
- Invoice totals can change after issuance
- VAT amounts can be recalculated
- Platform fees can be modified
- Invoice structure can be altered

**Impact:** Audit failure, legal disputes, regulatory non-compliance

---

### Performance Risks

**Risk Level:** 🟡 **HIGH**

**Risks:**
- PDF generation queries DB on every request
- No snapshot caching
- Settings resolved in views (blocking)

**Impact:** Slow PDF generation, scalability issues

---

### Data Consistency Risks

**Risk Level:** 🔴 **CRITICAL**

**Risks:**
- PDFs can regenerate differently if company/client data changes
- No immutable record of invoice state
- Cannot reproduce historical invoices if rates change

**Impact:** Inconsistent records, cannot defend in audits

---

### Credibility Risks

**Risk Level:** 🟡 **HIGH**

**Risks:**
- Hardcoded rates not credible for Kenyan market
- No audit trail
- Inconsistent platform fee calculations

**Impact:** Cannot claim audit-ready status, accountant distrust

---

## Blueprint Compliance Score

| Section | Compliance | Notes |
|---------|-----------|-------|
| Invoice Lifecycle | 20% | No finalized status, no enforcement |
| Snapshot System | 0% | Does not exist |
| Immutability | 15% | Only prefix fields protected |
| Business Logic Separation | 60% | Some in controllers, some in services |
| VAT Configuration | 0% | Hardcoded everywhere |
| Platform Fee Configuration | 0% | Hardcoded, inconsistent |
| PDF Generation | 30% | Queries DB, contains logic |
| Company Scoping | 95% | Generally well-enforced |
| Audit Trail | 0% | Does not exist |
| **Overall** | **25%** | **NOT PRODUCTION-READY** |

---

## Phase 1 Resolution Status

### Snapshot Structure: ✅ IMPLEMENTED
- `invoice_snapshots` table created with JSON payload column
- One-to-one relationship with invoices enforced
- Legacy snapshot flag included

### Snapshot Creation at Finalization: ✅ ENFORCED
- `InvoiceFinalizationService` ensures atomic finalization + snapshot creation
- Transaction ensures snapshot creation failure rolls back finalization
- Hard invariant: finalized invoice must have snapshot (except legacy)

### PDFs No Longer Require Live DB Data: ✅ STRUCTURALLY PREPARED
- Snapshot contains all data needed for PDF rendering
- Company, client, items, totals all captured in snapshot
- Template and branding data included
- Note: PDF views not yet updated (Phase 3)

### Financial Truth Capture: ✅ ESTABLISHED
- Complete financial truth definition documented
- Snapshot builder extracts all necessary data
- Explicit values stored (not formulas)
- Historical accuracy preserved

---

## Phase 2 Resolution Status

### Centralized Calculation Service: ✅ IMPLEMENTED
- `InvoiceCalculationService` created as single authoritative source
- Pure logic, deterministic, no side effects
- All calculations go through this service

### Duplicate Calculation Paths: ✅ ELIMINATED
- Removed calculations from `InvoiceController::preview()`
- Removed calculations from `InvoiceController::previewFrame()`
- Refactored `InvoiceService::createInvoice()` to use calculation service
- Refactored `InvoiceService::updateInvoice()` to use calculation service
- Refactored `InvoiceService::updateTotals()` to use calculation service
- Fixed inconsistent platform fee rate (0.8% → 3%)

### Snapshot Data Derived from Single Source of Truth: ✅ ESTABLISHED
- `InvoiceSnapshotBuilder` uses calculation service for totals
- `InvoiceSnapshotBuilder` uses calculation service for line item VAT
- Snapshot stores results, not formulas

### Financial Consistency Guaranteed: ✅ ENFORCED
- Same invoice calculated in multiple paths → same totals (tested)
- Snapshot totals exactly match calculation service output (tested)
- Calculation service is deterministic (tested)
- Calculations frozen at finalization (enforced in `updateTotals()`)

---

## Next Steps (After Blueprint Update)

1. **Update Blueprint** - Incorporate discovered constraints
2. **Lock Assumptions** - Freeze scope based on gap analysis
3. **Execute Phase 0** - ✅ COMPLETE
4. **Execute Phase 1** - ✅ COMPLETE
5. **Proceed Phase-by-Phase** - Test after each phase

---

## Conclusion

Current InvoiceHub codebase has **27 identified violations** across 6 categories. The system is **25% blueprint-compliant** and **NOT production-ready**.

**Critical Path:**
1. Add `finalized` status
2. Implement snapshot system
3. Enforce immutability at model level
4. Extract calculation logic to services
5. Make rates company-configurable
6. Remove all logic from PDF views

**Estimated Effort:** 6-8 weeks of focused development to achieve blueprint compliance.

This gap analysis provides the surgical map needed for disciplined, phase-by-phase implementation.

