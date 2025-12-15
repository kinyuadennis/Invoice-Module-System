# Phase 0 Implementation Plan: Immutability & Lifecycle Lock-In

## Objective

Make it **impossible**—not discouraged, not unlikely, but **impossible**—for a finalized invoice to be altered in any financially meaningful way.

**Scope:** MODELS + DATABASE + GUARDS ONLY  
**Duration:** Single focused session  
**Exit Criteria:** All immutability tests pass

---

## 0.0 Mental Model: Financial Freeze Marker

**Decision:** `finalized` = financial truth boundary

**Documentation:**
- `finalized` is NOT the same as `sent`
- `finalized` means: calculations frozen, structure locked, totals immutable
- `sent` is a communication event (can happen multiple times)
- `finalized` is an accounting event (happens once)

**Status:** ✅ **DECIDED** - Now enforce it in code

---

## 0.1 Add Lifecycle Helper Methods to Invoice Model

**File:** `app/Models/Invoice.php`

**Add Methods:**
```php
public function isDraft(): bool
public function isFinalized(): bool
public function isPaid(): bool
public function isMutable(): bool
```

**Rules:**
- `isDraft()`: Returns `true` if status is `'draft'`
- `isFinalized()`: Returns `true` if status is `'finalized'`, `'sent'`, `'paid'`, or `'overdue'`
- `isPaid()`: Returns `true` if status is `'paid'`
- `isMutable()`: Returns `true` ONLY if `isDraft()` is `true`

**Implementation Notes:**
- These methods inspect state only, never change it
- They create shared language for the codebase
- All other code will depend on these

**Location:** Add after line 179 (after `scopeForCompany` method)

---

## 0.2 Add Model-Level Immutability Guards

**File:** `app/Models/Invoice.php`

**Enhance Boot Method:**
- Extend existing `updating` hook (currently at lines 79-88)
- Add immutability check BEFORE prefix field protection
- Throw `\DomainException` if attempting to modify finalized invoice

**Fields That Must Be Immutable When Finalized:**
- Financial: `subtotal`, `tax`, `vat_amount`, `platform_fee`, `total`, `grand_total`, `discount`
- Structural: `client_id`, `issue_date`, `due_date`, `invoice_number`, `invoice_reference`
- Configuration: `vat_registered`, `payment_method`, `payment_details`

**Fields That CAN Change When Finalized:**
- `status` (forward transitions only: finalized → sent → paid)
- `notes` (administrative updates)
- `terms_and_conditions` (administrative updates)

**Implementation:**
- Check `isFinalized()` in `updating` hook
- If finalized, check if any immutable field is dirty
- If immutable field is dirty, throw `\DomainException` with clear message
- Allow status transitions (with validation)
- Allow notes/terms updates

**Exception Message Format:**
```
"Cannot modify finalized invoice #{$this->invoice_number}. Field '{$field}' is immutable after finalization."
```

---

## 0.3 Lock Invoice Items at Model Level

**File:** `app/Models/InvoiceItem.php`

**Add Boot Method:**
- Add `saving` hook: Check if parent invoice is finalized
- Add `deleting` hook: Check if parent invoice is finalized
- If parent is finalized, throw `\DomainException`

**Implementation:**
- In `saving` hook: Load parent invoice, check `isFinalized()`
- In `deleting` hook: Load parent invoice, check `isFinalized()`
- Throw exception with clear message

**Exception Message Format:**
```
"Cannot modify invoice items on finalized invoice #{$this->invoice->invoice_number}. Items are immutable after finalization."
```

**Note:** Must handle both create and update in `saving` hook.

---

## 0.4 Add finalize() Method (Reserve Legal Doorway)

**File:** `app/Models/Invoice.php`

**Add Method:**
```php
public function finalize(): void
```

**Implementation:**
- Validate invoice can be finalized:
  - Must be in `'draft'` status
  - Must have `invoice_number`
  - Must have `client_id`
- If validation fails, throw `\DomainException`
- Set status to `'finalized'`
- Save invoice

**Validation Rules:**
- Throw if not draft: `"Invoice #{$this->invoice_number} cannot be finalized. Current status: {$this->status}"`
- Throw if no invoice_number: `"Invoice cannot be finalized without an invoice number."`
- Throw if no client_id: `"Invoice cannot be finalized without a client."`

**Note:** This method ONLY changes status. No snapshot creation yet (that's Phase 1).

---

## 0.5 Add Database Constraints

**File:** Create new migration: `add_finalized_status_to_invoices_table.php`

**Migration Actions:**
1. Modify `status` enum to include `'finalized'`
2. Ensure `status` column is `NOT NULL` (should already be, but verify)
3. Add check constraint if MySQL version supports it (optional)

**Migration Code:**
```php
// Add 'finalized' to enum
DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'finalized', 'sent', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft'");
```

**Rollback:**
```php
// Remove 'finalized' from enum (revert to original)
DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft'");
```

**Note:** This is minimal, strategic protection. Not enforcing business rules, just preventing undefined states.

---

## 0.6 Write One Brutal Test

**File:** `tests/Feature/InvoiceImmutabilityTest.php`

**Test Name:** `test_finalized_invoice_cannot_be_modified`

**Test Steps:**
1. Create a draft invoice with items
2. Set invoice_number and client_id
3. Call `finalize()` method
4. Attempt to modify financial field (e.g., `subtotal`)
5. Assert exception is thrown
6. Attempt to modify structural field (e.g., `client_id`)
7. Assert exception is thrown
8. Attempt to modify invoice items
9. Assert exception is thrown
10. Attempt to delete invoice
11. Assert exception is thrown (or deletion prevented)

**Assertions:**
- `expectException(\DomainException::class)` for all modification attempts
- Verify invoice status is `'finalized'` after finalization
- Verify original values are preserved

**Alternative:** If test infrastructure not ready, document manual tinker experiment:
```php
$invoice = Invoice::find(1);
$invoice->finalize();
$invoice->subtotal = 999; // Should throw DomainException
$invoice->save(); // Should fail
```

---

## Implementation Order

1. **0.1** - Add lifecycle helper methods (foundation)
2. **0.5** - Add database migration (infrastructure)
3. **0.2** - Add model immutability guards (enforcement)
4. **0.3** - Lock invoice items (complete protection)
5. **0.4** - Add finalize() method (legal doorway)
6. **0.6** - Write test (proof)

---

## Exit Criteria Validation

Before proceeding to Phase 1, verify ALL are true:

- [ ] A finalized invoice cannot be edited (test passes)
- [ ] Invoice items cannot be modified on finalized invoice (test passes)
- [ ] Totals cannot change on finalized invoice (test passes)
- [ ] Status transitions are explicit (finalize() method exists)
- [ ] All enforcement happens at model level (no controller/service checks needed)
- [ ] Database enum includes `'finalized'` status
- [ ] Test proves immutability works

**If ANY fail → STOP. Fix before proceeding.**

---

## Files to Modify

1. `app/Models/Invoice.php` - Add methods and guards
2. `app/Models/InvoiceItem.php` - Add immutability protection
3. `database/migrations/YYYY_MM_DD_HHMMSS_add_finalized_status_to_invoices_table.php` - New migration
4. `tests/Feature/InvoiceImmutabilityTest.php` - New test file

**Total Files:** 4 (3 modifications, 1 new)

---

## What NOT to Touch

- ❌ Services (InvoiceService, PlatformFeeService, etc.)
- ❌ Controllers (InvoiceController, etc.)
- ❌ Requests (UpdateInvoiceRequest, etc.)
- ❌ Views (PDF templates, etc.)
- ❌ VAT hardcoding (Phase 1)
- ❌ Platform fee inconsistency (Phase 1)
- ❌ Snapshot system (Phase 1)
- ❌ Audit logging (Phase 2)

**If you touch any of these → You're breaking the plan.**

---

## Success Metrics

**Phase 0 is successful if:**
1. Test passes: finalized invoice cannot be modified
2. Tinker experiment fails: `$invoice->finalize(); $invoice->subtotal = 999; $invoice->save();` throws exception
3. Invoice items cannot be created/updated/deleted on finalized invoice
4. Model boot method enforces immutability (not just controllers)

**Phase 0 is NOT successful if:**
- Controllers still need to check lifecycle
- Services still need to validate immutability
- Test passes but tinker experiment succeeds
- Any financial field can change after finalization

---

## Next Steps (After Phase 0)

Once Phase 0 exit criteria are met:
1. Update blueprint with Phase 0 completion
2. Proceed to Phase 1 (Services layer - company rates, snapshot system)
3. Do NOT proceed until Phase 0 is 100% complete

---

## Notes

- This phase is about **prevention**, not features
- We're building guardrails, not functionality
- Every line of code should make it harder to violate immutability
- Model-level enforcement is non-negotiable
- If someone runs `tinker` and tries to modify a finalized invoice, it MUST fail

**Restraint is the test. Pass it cleanly, and everything else becomes easier.**

