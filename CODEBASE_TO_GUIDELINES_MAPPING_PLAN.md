# InvoiceHub Codebase to Guidelines Mapping Plan

## Purpose

This document maps every critical section of the InvoiceHub codebase to the Optimization & Evolution Guide principles, identifies violations, and provides specific improvement paths for real-world deployment readiness.

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Invoice Lifecycle Architecture](#invoice-lifecycle-architecture)
3. [PDF Generation System](#pdf-generation-system)
4. [Business Logic & Service Layer](#business-logic--service-layer)
5. [VAT & Platform Fee Management](#vat--platform-fee-management)
6. [Company Scoping & Multi-Tenancy](#company-scoping--multi-tenancy)
7. [Data Immutability & Snapshot System](#data-immutability--snapshot-system)
8. [Invoice Numbering & Audit Trail](#invoice-numbering--audit-trail)
9. [Payment Processing](#payment-processing)
10. [Implementation Roadmap](#implementation-roadmap)

---

## Executive Summary

### Current State Analysis

**Strengths:**
- Service layer exists (`InvoiceService`, `PlatformFeeService`)
- Company scoping generally enforced via `CurrentCompanyService`
- Invoice numbering has some immutability protection (prefix fields)
- PDF generation uses DomPDF with proper options

**Critical Violations:**
- No explicit invoice lifecycle (Draft → Finalized → Issued → Paid)
- No data snapshot system for finalized invoices
- VAT hardcoded to 16% (not company-configurable)
- Platform fee calculation varies (3% vs 0.8% in code)
- Totals can be recalculated after invoice issuance
- PDF generation may trigger DB queries via `formatInvoiceForShow()`
- No immutability enforcement for finalized invoices

**Impact on Real-World Deployment:**
- Risk of invoice data changing after issuance (audit failure)
- Cannot claim credible accounting system status
- Potential KRA/ETIMS compliance issues
- Performance risk from PDF generation queries

---

## 1. Invoice Lifecycle Architecture

### Current Implementation

**Status Values:** `draft`, `sent`, `paid`, `overdue`, `cancelled`

**Location:** `app/Models/Invoice.php` (status field)

**Current Behavior:**
- Draft invoices can be edited/deleted
- Non-draft invoices can still be edited (only numbering fields protected)
- No explicit "finalized" state
- No locking mechanism

### Guideline Requirement

**Required Lifecycle:**
1. **Draft** - Fully editable, no invoice number required
2. **Finalized** - Invoice number locked, totals frozen, structural edits forbidden
3. **Issued** - PDF generated, sent to client, immutable
4. **Paid** - Payment recorded, invoice remains immutable
5. **Archived** - Historical record

### Violations Identified

**File:** `app/Http/Services/InvoiceService.php`
```php
// Line 246-391: updateInvoice() allows updating non-draft invoices
// Only prevents numbering field changes if invoice_number exists
// BUT allows recalculation of totals, items, VAT, platform fees
```

**File:** `app/Http/Controllers/User/InvoiceController.php`
```php
// Line 223-241: update() method calls updateInvoice() without lifecycle check
// No verification that invoice is in editable state
```

**File:** `app/Http/Services/InvoiceService.php`
```php
// Line 402-425: updateTotals() can be called on ANY invoice
// Recalculates VAT, platform fee, grand_total from live items
// Violates immutability for finalized/issued invoices
```

### Required Improvements

#### 1.1 Add Finalized Status

**Action:** Introduce `finalized` status between `draft` and `sent`

**Changes:**
- Migration: Add `finalized` to status enum
- Model: Add `isFinalized()`, `isIssued()`, `canEdit()` helper methods
- Service: Enforce lifecycle rules in `updateInvoice()`

**Implementation:**
```php
// app/Models/Invoice.php
public function isFinalized(): bool
{
    return in_array($this->status, ['finalized', 'sent', 'paid', 'overdue']);
}

public function canEdit(): bool
{
    return $this->status === 'draft';
}

public function canRecalculateTotals(): bool
{
    return $this->status === 'draft';
}
```

#### 1.2 Enforce Lifecycle Rules in Service

**File:** `app/Http/Services/InvoiceService.php`

**Change:** Modify `updateInvoice()` to check lifecycle stage

```php
public function updateInvoice(Invoice $invoice, Request $request): Invoice
{
    // ENFORCE: Only drafts can be structurally edited
    if (!$invoice->canEdit()) {
        throw new \RuntimeException(
            'Invoice #' . $invoice->invoice_number . ' is finalized and cannot be edited.'
        );
    }
    
    // ... existing code ...
}
```

#### 1.3 Lock Totals at Finalization

**Action:** Prevent `updateTotals()` on finalized invoices

**File:** `app/Http/Services/InvoiceService.php`

```php
public function updateTotals(Invoice $invoice): void
{
    // ENFORCE: No recalculation after finalization
    if (!$invoice->canRecalculateTotals()) {
        throw new \RuntimeException(
            'Cannot recalculate totals for finalized invoice #' . $invoice->invoice_number
        );
    }
    
    // ... existing calculation logic ...
}
```

### Business Impact

**Before:** Invoices can change after issuance → Audit risk, legal issues

**After:** Historical immutability → Audit-ready, credible accounting system

---

## 2. PDF Generation System

### Current Implementation

**File:** `app/Http/Controllers/User/InvoiceController.php` (Line 272-382)

**Current Flow:**
1. Load invoice with relationships: `with(['client', 'invoiceItems', 'platformFees', 'user', 'company.invoiceTemplate'])`
2. Call `formatInvoiceForShow()` which formats data
3. Load template from invoice/company
4. Generate PDF via DomPDF

**PDF Views:**
- `resources/views/invoices/pdf.blade.php`
- `resources/views/invoices/templates/*.blade.php`
- `resources/views/pdf/partials/header.blade.php`
- `resources/views/pdf/partials/footer.blade.php`

### Guideline Requirement

**PDFs must:**
- Receive pre-computed, immutable data
- Never query database
- Never calculate totals
- Never resolve VAT or fees
- Only render static data

### Violations Identified

**File:** `resources/views/pdf/partials/footer.blade.php`
```php
// Line 5-11: Calls $company->getPdfSettings()
// This method may query settings JSON column
// Should be pre-resolved in controller/service
```

**File:** `app/Http/Controllers/User/InvoiceController.php`
```php
// Line 285: formatInvoiceForShow() loads relationships
// May trigger additional queries if not eager-loaded
// Should use snapshot data instead
```

**File:** `app/Traits/FormatsInvoiceData.php`
```php
// Line 95-97: Calculates tax_rate in formatInvoiceWithDetails()
// Formula: ($tax / $subtotal) * 100
// This is recalculation during formatting - should be pre-stored
```

### Required Improvements

#### 2.1 Create Invoice Snapshot System

**Action:** Store immutable snapshot when invoice is finalized

**New Migration:**
```php
// database/migrations/YYYY_MM_DD_create_invoice_snapshots_table.php
Schema::create('invoice_snapshots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
    $table->json('invoice_data'); // Complete invoice state
    $table->json('items_data'); // All line items
    $table->json('company_data'); // Company state at finalization
    $table->json('client_data'); // Client state at finalization
    $table->json('totals_breakdown'); // Subtotal, VAT, platform fee, grand_total
    $table->json('payment_methods_data'); // Payment methods at finalization
    $table->timestamp('snapshot_taken_at');
    $table->timestamps();
    
    $table->unique('invoice_id'); // One snapshot per invoice
});
```

**New Service Method:**
```php
// app/Http/Services/InvoiceService.php
public function createSnapshot(Invoice $invoice): InvoiceSnapshot
{
    // Called when invoice status changes to 'finalized'
    // Store complete immutable state
    return InvoiceSnapshot::create([
        'invoice_id' => $invoice->id,
        'invoice_data' => [...],
        'items_data' => $invoice->invoiceItems->toArray(),
        'company_data' => $invoice->company->toArray(),
        // ... all immutable data
    ]);
}
```

#### 2.2 Modify PDF Generation to Use Snapshots

**File:** `app/Http/Controllers/User/InvoiceController.php`

**Change:** Load snapshot for finalized invoices

```php
public function generatePdf($id)
{
    $companyId = CurrentCompanyService::requireId();
    $invoice = Invoice::where('company_id', $companyId)->findOrFail($id);
    
    // ENFORCE: Use snapshot for finalized invoices
    if ($invoice->isFinalized() && $invoice->snapshot) {
        $formattedInvoice = $this->invoiceService->formatInvoiceFromSnapshot($invoice->snapshot);
    } else {
        // Only drafts can use live data
        $formattedInvoice = $this->invoiceService->formatInvoiceForShow($invoice);
    }
    
    // ... PDF generation continues with formattedInvoice ...
}
```

#### 2.3 Pre-Resolve All PDF Data

**Action:** Extract `getPdfSettings()` call from PDF views

**File:** `app/Http/Controllers/User/InvoiceController.php`

**Before (in PDF view):**
```php
$pdfSettings = $company->getPdfSettings();
```

**After (in controller):**
```php
$pdfSettings = $company->getPdfSettings(); // Called once
$formattedInvoice['company']['pdf_settings'] = $pdfSettings; // Pass to view
```

**PDF view receives:**
```php
$pdfSettings = $invoice['company']['pdf_settings']; // Already resolved
```

### Business Impact

**Before:** PDF generation queries DB → Performance risk, data inconsistency

**After:** PDF renders pre-computed snapshot → Fast, reliable, audit-ready

---

## 3. Business Logic & Service Layer

### Current Implementation

**Services:**
- `InvoiceService` - Invoice CRUD, calculations
- `PlatformFeeService` - Platform fee calculations
- `DashboardService` - Dashboard aggregations
- `InvoicePrefixService` - Invoice numbering

**Controllers:**
- `InvoiceController` - HTTP layer, calls services

### Guideline Requirement

**Separation:**
- **Controllers:** Validate requests, call services, return responses
- **Services:** Business logic, calculations, decisions
- **Views:** Render data only, no logic

### Violations Identified

**File:** `app/Http/Controllers/User/InvoiceController.php`
```php
// Line 427-583: preview() method contains calculation logic
// Lines 456-483: Subtotal, discount, VAT, platform fee calculations
// This logic belongs in InvoiceService
```

**File:** `app/Http/Controllers/User/InvoiceController.php`
```php
// Line 589-680: previewFrame() duplicates calculation logic
// Same violations as preview()
```

**File:** `app/Traits/FormatsInvoiceData.php`
```php
// Line 95-97: Tax rate calculation in formatting trait
// Should be pre-calculated and stored
```

### Required Improvements

#### 3.1 Extract Calculation Logic to Service

**Action:** Move preview calculations to `InvoiceService`

**File:** `app/Http/Services/InvoiceService.php`

**New Method:**
```php
public function calculatePreviewTotals(array $items, array $options = []): array
{
    // Extract all calculation logic from controller
    // Returns: ['subtotal', 'discount', 'vat_amount', 'platform_fee', 'grand_total']
    // Use company's VAT rate and platform fee rate (when configurable)
}
```

**Controller Change:**
```php
// app/Http/Controllers/User/InvoiceController.php
public function preview(Request $request)
{
    $validated = $request->validate([...]);
    
    // ENFORCE: Business logic in service
    $totals = $this->invoiceService->calculatePreviewTotals(
        $validated['items'],
        [
            'vat_registered' => $validated['vat_registered'] ?? false,
            'discount' => $validated['discount'] ?? 0,
            'discount_type' => $validated['discount_type'] ?? 'fixed',
        ]
    );
    
    // Format data (no calculations)
    $invoiceData = [...];
    $invoiceData = array_merge($invoiceData, $totals);
    
    return response()->json(['success' => true, 'html' => $html]);
}
```

#### 3.2 Remove Logic from Formatting Trait

**File:** `app/Traits/FormatsInvoiceData.php`

**Current:**
```php
$data['tax_rate'] = $data['subtotal'] > 0
    ? round(($data['tax'] / $data['subtotal']) * 100, 2)
    : 0;
```

**Improvement:** Store `tax_rate` on invoice model at creation/update time

**Migration:**
```php
$table->decimal('tax_rate', 5, 2)->nullable(); // Store percentage (e.g., 16.00)
```

**Service:**
```php
// Calculate and store tax_rate when creating/updating invoice
$invoice->tax_rate = $subtotal > 0 
    ? round(($vatAmount / $subtotal) * 100, 2) 
    : 0;
```

### Business Impact

**Before:** Logic scattered → Hard to test, inconsistent calculations

**After:** Centralized logic → Testable, maintainable, consistent

---

## 4. VAT & Platform Fee Management

### Current Implementation

**VAT:**
- Hardcoded to 16% in multiple places
- Stored per item in `invoice_items.vat_rate`
- Company-level configuration missing

**Platform Fee:**
- Hardcoded to 3% in `PlatformFeeService::FEE_RATE`
- Some calculations use 0.8% (inconsistent)
- Company-level configuration missing

### Guideline Requirement

**Kenya-Specific Credibility:**
- VAT must be configurable per company
- Platform fee must be configurable per company
- Rates must be frozen at invoice finalization
- No dynamic calculation at render time

### Violations Identified

**File:** `app/Http/Services/InvoiceService.php`
```php
// Line 174: $vatAmount = $subtotalAfterDiscount * 0.16; // Hardcoded
// Line 178: $platformFee = $totalBeforeFee * 0.03; // Hardcoded
// Line 333: $vatAmount = $subtotal * 0.16; // Hardcoded
// Line 335: $platformFee = $totalBeforeFee * 0.008; // Inconsistent!
// Line 408: $vatAmount = $subtotal * 0.16; // Hardcoded
// Line 410: $platformFee = $totalBeforeFee * 0.03; // Hardcoded
```

**File:** `app/Http/Services/PlatformFeeService.php`
```php
// Line 13: private const FEE_RATE = 0.03; // Hardcoded, not company-specific
```

**File:** `app/Http/Controllers/User/InvoiceController.php`
```php
// Line 478: $vatAmount = $subtotalAfterDiscount * 0.16; // Hardcoded
// Line 482: $platformFee = $totalBeforeFee * 0.03; // Hardcoded
```

### Required Improvements

#### 4.1 Add Company-Level VAT Configuration

**Migration:**
```php
// database/migrations/YYYY_MM_DD_add_vat_configuration_to_companies.php
Schema::table('companies', function (Blueprint $table) {
    $table->decimal('default_vat_rate', 5, 2)->default(16.00)->after('currency');
    $table->boolean('vat_enabled')->default(true)->after('default_vat_rate');
});
```

**Model:**
```php
// app/Models/Company.php
public function getVatRate(): float
{
    return (float) ($this->default_vat_rate ?? 16.00);
}

public function isVatEnabled(): bool
{
    return (bool) ($this->vat_enabled ?? true);
}
```

#### 4.2 Add Company-Level Platform Fee Configuration

**Migration:**
```php
Schema::table('companies', function (Blueprint $table) {
    $table->decimal('platform_fee_rate', 5, 4)->default(0.0300)->after('default_vat_rate');
    // Store as decimal: 0.0300 = 3%, 0.0080 = 0.8%
});
```

**Model:**
```php
// app/Models/Company.php
public function getPlatformFeeRate(): float
{
    return (float) ($this->platform_fee_rate ?? 0.0300);
}
```

#### 4.3 Update Services to Use Company Rates

**File:** `app/Http/Services/InvoiceService.php`

**Change all calculation methods:**
```php
// BEFORE:
$vatAmount = $subtotalAfterDiscount * 0.16;

// AFTER:
$company = Company::findOrFail($companyId);
$vatRate = $company->getVatRate() / 100; // Convert percentage to decimal
$vatAmount = $company->isVatEnabled() 
    ? $subtotalAfterDiscount * $vatRate 
    : 0;
```

**File:** `app/Http/Services/PlatformFeeService.php`

**Change:**
```php
public function generateFeeForInvoice(Invoice $invoice): PlatformFee
{
    $company = $invoice->company;
    $feeRate = $company->getPlatformFeeRate(); // Company-specific rate
    
    $feeAmount = ($invoice->subtotal + $invoice->vat_amount) * $feeRate;
    
    // ... rest of method ...
}

// Remove hardcoded FEE_RATE constant
```

#### 4.4 Store Rates on Invoice at Finalization

**Migration:**
```php
Schema::table('invoices', function (Blueprint $table) {
    $table->decimal('vat_rate_used', 5, 2)->nullable()->after('vat_amount');
    $table->decimal('platform_fee_rate_used', 5, 4)->nullable()->after('platform_fee');
});
```

**Service:**
```php
// When finalizing invoice, store rates used
$invoice->vat_rate_used = $company->getVatRate();
$invoice->platform_fee_rate_used = $company->getPlatformFeeRate();
$invoice->save();
```

### Business Impact

**Before:** Hardcoded rates → Not credible for Kenyan businesses, inflexible

**After:** Company-configurable rates → Credible, flexible, audit-ready

---

## 5. Company Scoping & Multi-Tenancy

### Current Implementation

**Service:** `app/Services/CurrentCompanyService.php`

**Usage:** Controllers use `CurrentCompanyService::requireId()` for scoping

### Guideline Requirement

**Company Context Is Sacred:**
- Every operation must be scoped by `company_id`
- No global queries
- No default company assumptions

### Violations Identified

**File:** `resources/views/admin/clients/create.blade.php`
```php
// Line 16: $companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);
// Admin view - acceptable, but verify scope
```

**File:** `resources/views/user/invoices/create-one-page.blade.php`
```php
// Line 23: $userCompanies = $user->ownedCompanies()->get();
// User-scoped, acceptable
```

**Status:** ✅ Generally compliant - CurrentCompanyService is used consistently

### Required Improvements

#### 5.1 Audit All Queries for Company Scoping

**Action:** Review all database queries to ensure company_id filtering

**Checklist:**
- [ ] All Invoice queries scope by company_id
- [ ] All Client queries scope by company_id
- [ ] All Payment queries scope by company_id
- [ ] All Item queries scope by company_id
- [ ] All PlatformFee queries scope by company_id

**Status:** Appears mostly compliant, verify edge cases

#### 5.2 Add Query Scoping Helpers

**Model Scopes:**
```php
// app/Models/Invoice.php
public function scopeForCompany($query, int $companyId)
{
    return $query->where('company_id', $companyId);
}

// Usage in services:
Invoice::forCompany($companyId)->where('status', 'paid')->get();
```

### Business Impact

**Before:** Potential data leakage risk

**After:** Enforced multi-tenancy → Secure, compliant

---

## 6. Data Immutability & Snapshot System

### Current Implementation

**Partial Protection:**
- Invoice numbering fields are protected (`prefix_used`, `serial_number`, `full_number`)
- Model boot method prevents updates to immutable prefix fields

**Missing:**
- No snapshot system
- Totals can be recalculated
- Items can be modified
- Company/client data can change

### Guideline Requirement

**At Finalization:**
- Freeze all invoice data
- Store immutable snapshot
- Prevent structural edits
- PDFs read from snapshot only

### Required Improvements

#### 6.1 Create Snapshot System (See Section 2.1)

#### 6.2 Enforce Immutability at Model Level

**File:** `app/Models/Invoice.php`

**Enhance boot method:**
```php
protected static function boot(): void
{
    parent::boot();
    
    // Prevent updates to finalized invoices
    static::updating(function ($invoice) {
        if ($invoice->isFinalized()) {
            // Allow only non-structural fields
            $allowedFields = ['status', 'notes']; // Status can change (sent → paid)
            
            foreach ($invoice->getDirty() as $field => $value) {
                if (!in_array($field, $allowedFields)) {
                    throw new \RuntimeException(
                        "Cannot update field '{$field}' on finalized invoice #{$invoice->invoice_number}"
                    );
                }
            }
        }
    });
    
    // Prevent deletion of finalized invoices
    static::deleting(function ($invoice) {
        if ($invoice->isFinalized()) {
            throw new \RuntimeException(
                "Cannot delete finalized invoice #{$invoice->invoice_number}"
            );
        }
    });
}
```

#### 6.3 Prevent Item Modification for Finalized Invoices

**File:** `app/Models/InvoiceItem.php`

**Add:**
```php
protected static function boot(): void
{
    parent::boot();
    
    static::saving(function ($item) {
        if ($item->invoice && $item->invoice->isFinalized()) {
            throw new \RuntimeException(
                "Cannot modify items on finalized invoice #{$item->invoice->invoice_number}"
            );
        }
    });
}
```

### Business Impact

**Before:** Data can change → Audit failure, legal risk

**After:** Immutable records → Audit-ready, legally defensible

---

## 7. Invoice Numbering & Audit Trail

### Current Implementation

**Service:** `app/Services/InvoicePrefixService.php`

**Features:**
- Prefix management
- Client-specific numbering
- Row locking for concurrency

**Protection:**
- Prefix fields are immutable after creation

### Guideline Requirement

**Invoice Numbers:**
- Must be traceable
- Never regenerated
- Structured and sequential
- Audit-ready

### Status: ✅ Mostly Compliant

**Minor Improvements:**

#### 7.1 Add Audit Log Table

**Migration:**
```php
Schema::create('invoice_audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action'); // 'created', 'updated', 'finalized', 'issued', 'paid'
    $table->json('changes')->nullable(); // What changed
    $table->text('notes')->nullable();
    $table->timestamp('action_at');
    $table->timestamps();
    
    $table->index('invoice_id');
    $table->index('action_at');
});
```

**Service Method:**
```php
public function logInvoiceAction(Invoice $invoice, string $action, array $changes = []): void
{
    InvoiceAuditLog::create([
        'invoice_id' => $invoice->id,
        'user_id' => auth()->id(),
        'action' => $action,
        'changes' => $changes,
        'action_at' => now(),
    ]);
}
```

### Business Impact

**Before:** No audit trail → Cannot defend against disputes

**After:** Complete audit trail → Legal protection, accountability

---

## 8. Payment Processing

### Current Implementation

**Model:** `app/Models/Payment.php`

**Controller:** `app/Http/Controllers/User/PaymentController.php`

### Guideline Requirement

**Payments:**
- Belong to companies
- Mark invoices as paid
- Flow through services
- No manual DB toggles

### Status: ✅ Compliant

**Verification Needed:**
- Ensure payments update invoice status via service
- Verify no direct status manipulation

---

## 9. Implementation Roadmap

### Phase 1: Critical Foundation (Week 1-2)

**Priority: HIGH - Blocks real-world deployment**

1. **Invoice Lifecycle**
   - Add `finalized` status
   - Implement `canEdit()`, `isFinalized()` methods
   - Enforce lifecycle rules in `updateInvoice()`
   - Lock `updateTotals()` for finalized invoices

2. **Snapshot System**
   - Create `invoice_snapshots` table
   - Implement `createSnapshot()` service method
   - Modify PDF generation to use snapshots
   - Auto-create snapshot on finalization

3. **Immutability Enforcement**
   - Enhance model boot methods
   - Prevent structural edits on finalized invoices
   - Prevent item modification
   - Add validation exceptions

**Files to Modify:**
- `app/Models/Invoice.php`
- `app/Http/Services/InvoiceService.php`
- `app/Http/Controllers/User/InvoiceController.php`
- Database migrations

### Phase 2: Credibility Layer (Week 3-4)

**Priority: HIGH - Required for Kenya market**

1. **VAT Configuration**
   - Add `default_vat_rate` to companies
   - Update all calculation methods
   - Store `vat_rate_used` on invoices
   - Remove hardcoded 16%

2. **Platform Fee Configuration**
   - Add `platform_fee_rate` to companies
   - Update `PlatformFeeService`
   - Store `platform_fee_rate_used` on invoices
   - Standardize rate (decide 3% vs 0.8%)

3. **Business Logic Extraction**
   - Move preview calculations to service
   - Remove logic from formatting trait
   - Store `tax_rate` on invoice

**Files to Modify:**
- `app/Models/Company.php`
- `app/Http/Services/InvoiceService.php`
- `app/Http/Services/PlatformFeeService.php`
- `app/Http/Controllers/User/InvoiceController.php`

### Phase 3: Polish & Audit (Week 5-6)

**Priority: MEDIUM - Enhances credibility**

1. **Audit Trail**
   - Create `invoice_audit_logs` table
   - Log all invoice actions
   - Add audit log views

2. **PDF Optimization**
   - Pre-resolve all PDF data in controller
   - Remove `getPdfSettings()` calls from views
   - Verify no DB queries in PDF rendering

3. **Company Scoping Audit**
   - Verify all queries are scoped
   - Add query helper scopes
   - Test multi-company isolation

**Files to Modify:**
- Database migrations
- `app/Http/Services/InvoiceService.php`
- `app/Http/Controllers/User/InvoiceController.php`
- PDF views

### Phase 4: Testing & Documentation (Week 7-8)

**Priority: HIGH - Required before launch**

1. **Unit Tests**
   - Test lifecycle enforcement
   - Test snapshot creation
   - Test immutability rules
   - Test VAT/Platform fee calculations

2. **Feature Tests**
   - Test invoice finalization flow
   - Test PDF generation with snapshots
   - Test payment processing
   - Test multi-company isolation

3. **Documentation**
   - Update API documentation
   - Document lifecycle rules
   - Document snapshot system
   - Document VAT/Platform fee configuration

---

## 10. Key Metrics for Success

### Technical Metrics

- ✅ Zero DB queries in PDF generation (after snapshot implementation)
- ✅ Zero hardcoded VAT/Platform fee rates
- ✅ 100% of finalized invoices have snapshots
- ✅ Zero structural edits possible on finalized invoices
- ✅ All queries scoped by company_id

### Business Metrics

- ✅ Can claim "audit-ready" status
- ✅ Can claim "immutable invoice records"
- ✅ Can claim "Kenya-compliant VAT handling"
- ✅ Can demonstrate ETIMS readiness (without claiming compliance)
- ✅ Can pass accountant review

---

## Conclusion

This plan provides a clear roadmap from current state to production-ready, guideline-compliant InvoiceHub. The implementation prioritizes:

1. **Immutability** - Historical fact preservation
2. **Performance** - Snapshot-based PDF generation
3. **Credibility** - Company-configurable rates, audit trails
4. **Maintainability** - Clear service boundaries, testable logic

Following this plan will transform InvoiceHub from a functional invoicing app into a credible, audit-ready, Kenya-market-ready accounting-adjacent platform.

