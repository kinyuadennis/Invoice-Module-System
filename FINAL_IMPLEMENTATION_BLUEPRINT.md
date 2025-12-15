# InvoiceHub: Final Implementation Blueprint

## Executive Summary

This blueprint provides a comprehensive, section-by-section implementation strategy to transform InvoiceHub into a **production-ready, audit-compliant, credible invoicing platform** for Kenyan businesses.

**Guiding Documents:**
- IDE Operating Instructions (Mental Model & Core Rules)
- Optimization & Evolution Guide (Technical Architecture)
- Clarifications (Status Lifecycle, Legacy Handling, Audit Scope, Backward Compatibility)

**Implementation Philosophy:**
- Blueprint-focused: Strategies and approaches, not code
- Section-by-section: Organized by codebase layers
- Guideline-aligned: Every change references guide principles
- Business-critical: Preserves trust, performance, auditability

---

## Table of Contents

1. [Database Schema Blueprint](#database-schema-blueprint)
2. [Models Layer Blueprint](#models-layer-blueprint)
3. [Services Layer Blueprint](#services-layer-blueprint)
4. [Controllers Layer Blueprint](#controllers-layer-blueprint)
5. [Requests Layer Blueprint](#requests-layer-blueprint)
6. [Views/Resources Layer Blueprint](#viewsresources-layer-blueprint)
7. [Migration Strategy Blueprint](#migration-strategy-blueprint)
8. [Implementation Phases](#implementation-phases)

---

## Database Schema Blueprint

### Strategy Overview

Database changes establish the foundation for immutability, auditability, and company-level configuration. All changes must preserve existing data and enable backward compatibility.

### Section 1.1: Invoice Status Enum Enhancement

**What:** Add `finalized` status to invoice status enum

**Why:** Establishes explicit lifecycle boundary (draft → finalized → sent → paid)

**Approach:**
- Modify enum to include `finalized` between `draft` and `sent`
- Ensure migration is reversible (with data validation)
- Update all status validation rules

**Dependencies:** None (foundational change)

**Backward Compatibility:** Existing `sent`/`paid` invoices remain unchanged

---

### Section 1.2: Invoice Snapshot Table

**What:** Create `invoice_snapshots` table for immutable invoice state

**Why:** Enables PDF generation without DB queries, preserves historical accuracy

**Approach:**
- One-to-one relationship with invoices (unique constraint)
- Store complete invoice state as JSON (header, items, company, client, totals)
- Include `snapshot_taken_at` and `snapshot_taken_by` for audit
- Add `legacy_snapshot` boolean flag for retroactive snapshots
- Index on `invoice_id` for performance

**Data Structure Strategy:**
- Invoice header: All invoice fields at finalization time
- Items array: Complete line items with all attributes
- Company snapshot: Company state including branding, PDF settings
- Client snapshot: Client information at finalization
- Totals breakdown: Subtotal, VAT, platform fee, grand total with rates used
- Payment methods: Enabled payment methods at finalization
- Template data: Template configuration used

**Dependencies:** Invoice table must exist

**Backward Compatibility:** Legacy snapshots marked separately, existing invoices can be migrated

---

### Section 1.3: Invoice Audit Log Table

**What:** Create `invoice_audit_logs` table for complete audit trail

**Why:** Enables accountability, legal defensibility, compliance readiness

**Approach:**
- Foreign key to invoices (cascade delete)
- Foreign key to users (nullable for system actions)
- Action type field (enum: created, updated, finalized, sent, paid, voided, cancelled)
- Changes JSON field (what changed: old → new values)
- Metadata: IP address, user agent, timestamp
- Indexes on invoice_id, action_at, action type

**Logging Strategy:**
- Log lifecycle-relevant actions only
- Include user context (who, when, from where)
- Store change diffs for updates
- Do NOT log UI-only actions (previews, page views)

**Dependencies:** Invoices and users tables must exist

**Backward Compatibility:** No impact on existing data

---

### Section 1.4: Company VAT & Platform Fee Configuration

**What:** Add company-level VAT and platform fee rate fields

**Why:** Enables company-specific rates, removes hardcoding, supports Kenya market

**Approach:**
- `default_vat_rate`: Decimal field (percentage, e.g., 16.00)
- `vat_enabled`: Boolean flag (allows VAT-exempt companies)
- `platform_fee_rate`: Decimal field (as decimal, e.g., 0.0300 for 3%)
- Default values match current hardcoded values (16% VAT, 3% platform fee)
- Add to company fillable and casts

**Dependencies:** Companies table must exist

**Backward Compatibility:** Default values ensure existing companies work unchanged

---

### Section 1.5: Invoice Rate Storage Fields

**What:** Add fields to store rates used when invoice was created

**Why:** Enables invoice reproduction if company rates change, audit trail

**Approach:**
- `vat_rate_used`: Decimal (percentage stored, e.g., 16.00)
- `platform_fee_rate_used`: Decimal (as decimal, e.g., 0.0300)
- `tax_rate`: Decimal (calculated effective rate, e.g., 16.00)
- Nullable fields (for existing invoices)
- Populated at invoice creation/update time

**Dependencies:** Invoices table must exist

**Backward Compatibility:** Nullable fields, existing invoices remain valid

---

## Models Layer Blueprint

### Strategy Overview

Models enforce business rules at the data layer. They protect immutability, define lifecycle states, and establish relationships. All model changes must align with accounting system mental model.

### Section 2.1: Invoice Model - Lifecycle State Methods

**What:** Add methods to determine invoice lifecycle state and editability

**Why:** Enables consistent lifecycle enforcement across codebase

**Approach:**
- `isDraft()`: Returns true if status is 'draft'
- `isFinalized()`: Returns true if status is 'finalized', 'sent', 'paid', or 'overdue'
- `isIssued()`: Returns true if status is 'sent', 'paid', or 'overdue'
- `canEdit()`: Returns true only if draft
- `canRecalculateTotals()`: Returns true only if draft
- `canModifyItems()`: Returns true only if draft
- `finalize()`: Transitions invoice from draft to finalized (with validation)

**Validation Strategy:**
- `finalize()` requires invoice_number exists
- `finalize()` requires client_id exists
- `finalize()` throws exception if not in draft state

**Dependencies:** Status enum must include 'finalized'

**Backward Compatibility:** Existing invoices unaffected, new methods are additive

---

### Section 2.2: Invoice Model - Immutability Enforcement

**What:** Enhance boot method to prevent structural edits on finalized invoices

**Why:** Protects historical facts, prevents audit violations

**Approach:**
- In `updating` event: Check if invoice is finalized
- If finalized: Allow only status transitions and notes updates
- Block all financial fields (subtotal, VAT, totals, etc.)
- Block structural fields (client_id, dates, items, etc.)
- Validate status transitions (only allow forward progression)
- Throw descriptive exceptions with invoice number

**Allowed Updates on Finalized:**
- Status (finalized → sent → paid, with validation)
- Notes (administrative updates only)

**Forbidden Updates on Finalized:**
- All financial calculations
- All structural data
- Line items
- Client assignment
- Dates

**Dependencies:** Lifecycle state methods must exist

**Backward Compatibility:** Existing finalized invoices protected, drafts unaffected

---

### Section 2.3: Invoice Model - Deletion Protection

**What:** Prevent deletion of finalized invoices

**Why:** Maintains audit trail, prevents data loss

**Approach:**
- In `deleting` event: Check if invoice is finalized
- If finalized: Throw exception preventing deletion
- Only drafts can be deleted

**Dependencies:** Lifecycle state methods must exist

**Backward Compatibility:** Existing behavior preserved for drafts

---

### Section 2.4: Invoice Model - Relationships

**What:** Add relationships to snapshot and audit logs

**Why:** Enables easy access to immutable data and audit history

**Approach:**
- `snapshot()`: HasOne relationship to InvoiceSnapshot
- `auditLogs()`: HasMany relationship to InvoiceAuditLog (ordered by action_at desc)

**Dependencies:** Snapshot and audit log models must exist

**Backward Compatibility:** Additive changes, no breaking impact

---

### Section 2.5: Invoice Model - Fillable & Casts

**What:** Add new rate fields to fillable and casts

**Why:** Enables mass assignment and proper type casting

**Approach:**
- Add `vat_rate_used`, `platform_fee_rate_used`, `tax_rate` to fillable
- Add decimal casts for rate fields
- Maintain existing fillable fields

**Dependencies:** Database fields must exist

**Backward Compatibility:** Additive, no breaking changes

---

### Section 2.6: InvoiceItem Model - Immutability Protection

**What:** Prevent modification/deletion of items on finalized invoices

**Why:** Protects invoice structure, maintains audit integrity

**Approach:**
- In `saving` event: Check if parent invoice is finalized
- If finalized: Throw exception preventing modification
- In `deleting` event: Check if parent invoice is finalized
- If finalized: Throw exception preventing deletion

**Dependencies:** Invoice model lifecycle methods must exist

**Backward Compatibility:** Existing finalized invoices protected

---

### Section 2.7: Company Model - VAT & Platform Fee Methods

**What:** Add methods to retrieve company-specific rates

**Why:** Enables company-configurable rates, removes hardcoding

**Approach:**
- `getVatRate()`: Returns VAT rate as percentage (e.g., 16.00)
- `getVatRateDecimal()`: Returns VAT rate as decimal (e.g., 0.16)
- `isVatEnabled()`: Returns boolean for VAT enabled status
- `getPlatformFeeRate()`: Returns platform fee as decimal (e.g., 0.03)
- `getPlatformFeeRatePercentage()`: Returns platform fee as percentage (e.g., 3.00)
- All methods provide sensible defaults if fields are null

**Dependencies:** Database fields must exist

**Backward Compatibility:** Default values ensure existing code works

---

### Section 2.8: InvoiceSnapshot Model

**What:** Create new model for invoice snapshots

**Why:** Represents immutable invoice state at finalization

**Approach:**
- Define fillable fields (all snapshot data fields)
- Define casts (JSON fields as arrays, datetime fields)
- Relationships: invoice (belongsTo), takenBy user (belongsTo), template (belongsTo)
- No business logic in model (pure data container)

**Dependencies:** Snapshot table must exist

**Backward Compatibility:** New model, no impact on existing code

---

### Section 2.9: InvoiceAuditLog Model

**What:** Create new model for audit logging

**Why:** Represents audit trail entries

**Approach:**
- Define fillable fields (all audit log fields)
- Define casts (JSON changes as array, datetime)
- Relationships: invoice (belongsTo), user (belongsTo)
- No business logic in model (pure data container)

**Dependencies:** Audit log table must exist

**Backward Compatibility:** New model, no impact on existing code

---

## Services Layer Blueprint

### Strategy Overview

Services contain ALL business logic. They enforce lifecycle rules, perform calculations, create snapshots, and log audit actions. Controllers only validate, call services, and return responses.

### Section 3.1: InvoiceService - Lifecycle Enforcement in createInvoice()

**What:** Ensure new invoices follow lifecycle rules and use company rates

**Why:** Establishes correct invoice creation pattern, removes hardcoding

**Approach:**
- Load company to get VAT and platform fee rates
- Use company methods to get rates (not hardcoded values)
- Calculate VAT using company rate and VAT enabled flag
- Calculate platform fee using company rate
- Store rates used on invoice (vat_rate_used, platform_fee_rate_used)
- Calculate and store tax_rate
- Log audit action for invoice creation
- Return created invoice

**Rate Calculation Strategy:**
- VAT: Only if vat_registered AND company.isVatEnabled()
- Platform fee: Always calculated using company rate
- Rates stored as they were at creation time

**Dependencies:** Company model rate methods, audit logging service method

**Backward Compatibility:** Default company rates ensure existing behavior

---

### Section 3.2: InvoiceService - Lifecycle Enforcement in updateInvoice()

**What:** Prevent updates to finalized invoices, enforce draft-only editing

**Why:** Protects immutability, maintains audit integrity

**Approach:**
- First check: Verify invoice.canEdit() (throws exception if not draft)
- If items provided: Verify invoice.canModifyItems() (throws exception if not draft)
- Use company rates for recalculation (not hardcoded)
- Store rates used on invoice
- Log audit action with change diff
- Return updated invoice

**Update Strategy:**
- Only drafts can be updated
- All recalculations use company rates
- Rates stored on invoice
- Changes logged for audit

**Dependencies:** Invoice model lifecycle methods, company rate methods

**Backward Compatibility:** Existing drafts can still be updated

---

### Section 3.3: InvoiceService - Lifecycle Enforcement in updateTotals()

**What:** Prevent recalculation of totals on finalized invoices

**Why:** Protects financial truth, prevents historical data changes

**Approach:**
- First check: Verify invoice.canRecalculateTotals() (throws exception if not draft)
- Use company rates for calculations (not hardcoded)
- Store rates used on invoice
- Update platform fee record if exists
- Save invoice

**Calculation Strategy:**
- Only drafts can have totals recalculated
- Use company rates from invoice's company
- Store rates for audit trail

**Dependencies:** Invoice model lifecycle methods, company rate methods

**Backward Compatibility:** Existing drafts can still recalculate

---

### Section 3.4: InvoiceService - finalizeInvoice() Method

**What:** Create new method to finalize invoices

**Why:** Establishes explicit finalization step, creates snapshot

**Approach:**
- Validate invoice can be finalized (is draft, has invoice_number, has client_id)
- Create snapshot before finalization (see Section 3.5)
- Call invoice.finalize() to change status
- Log audit action for finalization
- Return finalized invoice

**Finalization Strategy:**
- Snapshot created first (immutable state preserved)
- Status changed to 'finalized'
- Audit logged
- Exception thrown if validation fails

**Dependencies:** Snapshot creation method, invoice finalize method, audit logging

**Backward Compatibility:** New method, existing invoices unaffected

---

### Section 3.5: InvoiceService - createSnapshot() Method

**What:** Create method to generate immutable invoice snapshot

**Why:** Enables PDF generation without DB queries, preserves historical accuracy

**Approach:**
- Load all necessary relationships (client, company, items, platform fees, template, payment methods)
- Extract invoice data (all fields needed for PDF)
- Extract items data (all line item attributes)
- Extract company data (including PDF settings, branding)
- Extract client data (all client attributes)
- Extract payment methods data (enabled methods only)
- Calculate totals breakdown (subtotal, VAT, platform fee, grand total with rates)
- Extract template data (template configuration)
- Create InvoiceSnapshot record with all data
- Return snapshot

**Snapshot Data Strategy:**
- Store complete state, not references
- Include all settings needed for PDF rendering
- Store rates used (for reproduction)
- Mark as legacy_snapshot if retroactive

**Dependencies:** InvoiceSnapshot model, invoice relationships

**Backward Compatibility:** New method, can be called for existing invoices

---

### Section 3.6: InvoiceService - formatInvoiceFromSnapshot() Method

**What:** Create method to format snapshot data for PDF rendering

**Why:** Enables PDF generation from immutable snapshot data

**Approach:**
- Extract all data from snapshot JSON fields
- Format to match existing formatInvoiceForShow() structure
- Include all fields needed by PDF templates
- Return formatted array

**Formatting Strategy:**
- Match existing format structure for template compatibility
- Use snapshot data only (no DB queries)
- Include all financial breakdowns
- Include company/client/items data

**Dependencies:** InvoiceSnapshot model

**Backward Compatibility:** New method, existing formatInvoiceForShow() unchanged

---

### Section 3.7: InvoiceService - calculatePreviewTotals() Method

**What:** Extract preview calculation logic from controllers to service

**Why:** Moves business logic to service layer, enables testing, removes duplication

**Approach:**
- Accept items array and options (vat_registered, discount, etc.)
- Load company to get rates
- Calculate subtotal from items
- Apply discount (fixed or percentage)
- Calculate VAT using company rate (if enabled and registered)
- Calculate platform fee using company rate
- Calculate grand total
- Return totals array with all breakdowns

**Calculation Strategy:**
- Use company rates (not hardcoded)
- Match calculation logic from createInvoice()
- Return structured data for preview rendering

**Dependencies:** Company rate methods

**Backward Compatibility:** New method, existing preview logic can be refactored to use it

---

### Section 3.8: InvoiceService - logInvoiceAction() Method

**What:** Create method to log invoice actions for audit trail

**Why:** Enables accountability, legal defensibility

**Approach:**
- Accept invoice, action type, changes array, optional notes
- Extract user context (auth user, IP, user agent)
- Create InvoiceAuditLog record
- Return audit log entry

**Logging Strategy:**
- Log lifecycle-relevant actions only
- Include user context
- Store change diffs for updates
- Include timestamp and metadata

**Dependencies:** InvoiceAuditLog model

**Backward Compatibility:** New method, existing code unaffected

---

### Section 3.9: PlatformFeeService - Use Company Rates

**What:** Update platform fee calculation to use company rates

**Why:** Removes hardcoding, enables company-specific rates

**Approach:**
- Remove hardcoded FEE_RATE constant
- Load company from invoice
- Use company.getPlatformFeeRate() for calculation
- Store fee_rate on platform fee record (as percentage)

**Calculation Strategy:**
- Always use company rate
- Store rate used for audit
- Maintain existing fee record structure

**Dependencies:** Company rate methods

**Backward Compatibility:** Default company rates ensure existing behavior

---

### Section 3.10: FormatsInvoiceData Trait - Remove Calculation Logic

**What:** Remove tax_rate calculation from formatting trait

**Why:** Tax rate should be pre-calculated and stored, not computed during formatting

**Approach:**
- Remove calculation logic from formatInvoiceWithDetails()
- Use stored tax_rate from invoice model
- Fallback to 0 if not set (for backward compatibility)

**Formatting Strategy:**
- Read stored values only
- No calculations during formatting
- Maintain backward compatibility with fallbacks

**Dependencies:** Invoice model tax_rate field

**Backward Compatibility:** Fallback ensures existing invoices work

---

## Controllers Layer Blueprint

### Strategy Overview

Controllers are thin HTTP layers. They validate requests, call services, return responses. No business logic, no calculations, no DB queries beyond service calls.

### Section 4.1: InvoiceController - update() Method Enhancement

**What:** Add lifecycle check before calling updateInvoice service

**Why:** Prevents invalid update attempts, provides user-friendly errors

**Approach:**
- Load invoice with relationships
- Check invoice.canEdit() before service call
- If not editable: Return error response with clear message
- If editable: Call service.updateInvoice()
- Clear dashboard cache
- Return success response

**Validation Strategy:**
- Check at controller level for early failure
- Provide user-friendly error messages
- Service layer also validates (defense in depth)

**Dependencies:** Invoice model lifecycle methods, InvoiceService

**Backward Compatibility:** Existing drafts can still be updated

---

### Section 4.2: InvoiceController - generatePdf() Method Refactor

**What:** Use snapshot data for finalized invoices, pre-resolve all PDF data

**Why:** Eliminates DB queries in PDF generation, ensures consistency

**Approach:**
- Load invoice with snapshot relationship
- Check if invoice is finalized and has snapshot
- If finalized with snapshot: Use formatInvoiceFromSnapshot()
- If draft: Use formatInvoiceForShow() (live data allowed)
- Pre-resolve PDF settings in controller (not in view)
- Pre-resolve logo path in controller
- Pass all pre-computed data to PDF view
- Generate PDF with DomPDF

**PDF Generation Strategy:**
- Finalized invoices: Always use snapshot
- Drafts: Can use live data
- All settings resolved before view rendering
- No DB queries in PDF views

**Dependencies:** Snapshot system, formatInvoiceFromSnapshot method

**Backward Compatibility:** Drafts continue to work, finalized invoices use snapshots

---

### Section 4.3: InvoiceController - preview() Method Refactor

**What:** Extract calculation logic to service, keep only formatting in controller

**Why:** Moves business logic to service layer, enables testing

**Approach:**
- Validate request data
- Call service.calculatePreviewTotals() with items and options
- Format invoice data structure (no calculations)
- Merge totals from service into invoice data
- Render preview template
- Return JSON response

**Refactoring Strategy:**
- Remove all calculation logic
- Call service for totals
- Controller only formats and renders
- Maintain existing response structure

**Dependencies:** InvoiceService calculatePreviewTotals method

**Backward Compatibility:** Response structure unchanged, calculations moved to service

---

### Section 4.4: InvoiceController - previewFrame() Method Refactor

**What:** Extract calculation logic to service (same as preview)

**Why:** Removes duplication, centralizes business logic

**Approach:**
- Same strategy as preview() method
- Use service.calculatePreviewTotals()
- Remove calculation logic
- Maintain existing response structure

**Dependencies:** InvoiceService calculatePreviewTotals method

**Backward Compatibility:** Response structure unchanged

---

### Section 4.5: InvoiceController - autosave() Method Enhancement

**What:** Ensure autosave respects lifecycle rules, uses company rates

**Why:** Prevents invalid updates, maintains consistency

**Approach:**
- Load invoice if draft_id provided
- Verify invoice.canEdit() before update
- Use service methods for calculations (not inline)
- Store rates used on invoice
- Return success response

**Autosave Strategy:**
- Only drafts can be autosaved
- Use service for all calculations
- Maintain existing autosave behavior

**Dependencies:** Invoice lifecycle methods, InvoiceService

**Backward Compatibility:** Existing drafts can still autosave

---

### Section 4.6: InvoiceController - finalize() Method (New)

**What:** Create new endpoint to finalize invoices

**Why:** Provides explicit finalization step in UI

**Approach:**
- Load invoice with relationships
- Verify invoice belongs to user's company
- Call service.finalizeInvoice()
- Return success response with finalized invoice data
- Handle exceptions with user-friendly messages

**Finalization Strategy:**
- Explicit user action required
- Service handles all validation and snapshot creation
- Clear success/error messaging

**Dependencies:** InvoiceService finalizeInvoice method

**Backward Compatibility:** New endpoint, existing code unaffected

---

## Requests Layer Blueprint

### Strategy Overview

Form Requests validate input at HTTP boundary. They ensure data integrity before it reaches services. Enhancements needed for lifecycle validation and rate handling.

### Section 5.1: UpdateInvoiceRequest - Lifecycle Validation

**What:** Add validation to prevent updating finalized invoices

**Why:** Early validation prevents invalid requests

**Approach:**
- Load invoice from route parameter
- Check invoice.canEdit() in authorize() or rules()
- Return validation error if invoice is finalized
- Maintain existing field validation rules

**Validation Strategy:**
- Check lifecycle state early
- Provide clear error messages
- Maintain existing field rules

**Dependencies:** Invoice model lifecycle methods

**Backward Compatibility:** Existing drafts can still be updated

---

### Section 5.2: StoreInvoiceRequest - Rate Validation

**What:** Ensure rate fields are not provided in request (calculated by service)

**Why:** Rates must come from company configuration, not user input

**Approach:**
- Do not accept vat_rate_used or platform_fee_rate_used in request
- These fields are service-calculated only
- Maintain existing validation rules

**Validation Strategy:**
- Reject rate fields if provided
- Rates always come from company configuration
- User cannot override rates

**Dependencies:** None

**Backward Compatibility:** No impact, rates were never accepted before

---

## Views/Resources Layer Blueprint

### Strategy Overview

Views are presentation-only. They render data, never query databases, never calculate, never make business decisions. PDF views must receive pre-computed data only.

### Section 6.1: PDF Views - Remove All Logic

**What:** Ensure PDF Blade files contain no business logic or DB queries

**Why:** PDFs are output devices only, performance and consistency critical

**Approach:**
- Audit all PDF template files
- Remove any calculation logic
- Remove any DB queries (Company::find(), etc.)
- Remove any conditional logic that alters business rules
- Keep only rendering logic (loops, conditionals for display)
- Ensure all data comes from controller-passed variables

**PDF View Strategy:**
- Receive pre-computed invoice data
- Receive pre-computed company data (including PDF settings)
- Receive pre-computed client data
- Render static content only
- No calculations, no queries, no business decisions

**Dependencies:** Controller pre-processing, snapshot system

**Backward Compatibility:** Existing PDFs continue to render, data source changes

---

### Section 6.2: PDF Partials - Pre-Resolve Settings

**What:** Remove getPdfSettings() calls from PDF partials

**Why:** Settings must be pre-resolved in controller, not in view

**Approach:**
- Remove $company->getPdfSettings() calls from header/footer partials
- Controller must pass pdf_settings in invoice data
- Views read from passed data only

**Partial Strategy:**
- All settings come from controller
- No method calls in views
- No DB access in views

**Dependencies:** Controller pre-processing

**Backward Compatibility:** PDFs continue to render, data source changes

---

### Section 6.3: Invoice Views - Lifecycle State Display

**What:** Update UI to reflect invoice lifecycle states

**Why:** Users need clear indication of invoice editability

**Approach:**
- Show "Finalized" status badge
- Disable edit buttons for finalized invoices
- Show "Finalize" button for drafts
- Display snapshot creation timestamp if finalized
- Show audit log link for finalized invoices

**UI Strategy:**
- Clear visual indicators of lifecycle state
- Prevent invalid actions (disable buttons)
- Provide audit trail access
- Maintain existing UI patterns

**Dependencies:** Invoice lifecycle methods, audit log system

**Backward Compatibility:** Existing UI continues to work, new states added

---

### Section 6.4: Invoice Views - Rate Display

**What:** Show rates used on invoice (for transparency)

**Why:** Users need to see what rates were applied

**Approach:**
- Display VAT rate used (if stored)
- Display platform fee rate used (if stored)
- Show in invoice detail view
- Show in PDF (from snapshot data)

**Display Strategy:**
- Show rates for transparency
- Use stored rates (not current company rates)
- Maintain existing display patterns

**Dependencies:** Invoice rate storage fields

**Backward Compatibility:** Additive display, no breaking changes

---

## Migration Strategy Blueprint

### Strategy Overview

Migration handles existing data safely. Legacy invoices are frozen, not fixed. New invoices follow strict rules. Backward compatibility is maintained.

### Section 7.1: Status Enum Migration

**What:** Add 'finalized' status to enum safely

**Why:** Establishes new lifecycle state without breaking existing data

**Approach:**
- Modify enum to include 'finalized'
- Existing invoices remain in current status
- No automatic status changes
- Migration is reversible (with validation)

**Migration Strategy:**
- Add status value to enum
- Existing data unchanged
- New invoices can use 'finalized'
- Old invoices remain 'sent'/'paid' as-is

**Dependencies:** None

**Backward Compatibility:** Existing invoices unaffected

---

### Section 7.2: Retroactive Snapshot Creation

**What:** Create snapshots for existing sent/paid invoices

**Why:** Enables PDF generation without DB queries, maintains audit trail

**Approach:**
- Create migration command or one-time script
- Find all invoices in 'sent' or 'paid' status
- For each invoice: Create snapshot using current stored values
- Mark snapshot as legacy_snapshot = true
- Do NOT recalculate totals or VAT
- Use stored values exactly as they are

**Snapshot Strategy:**
- Use current invoice data (not recomputed)
- Include all relationships (company, client, items)
- Mark as legacy for identification
- Preserve exact current state

**Dependencies:** Snapshot table and model must exist

**Backward Compatibility:** Existing invoices frozen as-is, no data changes

---

### Section 7.3: Company Rate Defaults Migration

**What:** Set default VAT and platform fee rates for existing companies

**Why:** Ensures existing companies have rates configured

**Approach:**
- Update all companies with null rates
- Set default_vat_rate to 16.00
- Set vat_enabled to true
- Set platform_fee_rate to 0.0300 (3%)
- Existing invoices unaffected (rates stored on invoice)

**Migration Strategy:**
- Set sensible defaults
- Existing invoices use stored rates
- New invoices use company rates
- No breaking changes

**Dependencies:** Company rate fields must exist

**Backward Compatibility:** Defaults match current hardcoded values

---

### Section 7.4: Invoice Rate Storage Migration

**What:** Populate rate fields for existing invoices (optional)

**Why:** Enables rate display and audit trail

**Approach:**
- For invoices with null rates: Calculate from stored values
- vat_rate_used: Calculate from vat_amount and subtotal
- platform_fee_rate_used: Calculate from platform_fee and total
- tax_rate: Calculate from tax and subtotal
- Mark as estimated if calculated (for audit clarity)

**Migration Strategy:**
- Calculate rates from existing data
- Mark as estimated for transparency
- New invoices store rates at creation
- Optional migration (can be skipped)

**Dependencies:** Invoice rate fields must exist

**Backward Compatibility:** Nullable fields, existing invoices work without rates

---

## Implementation Phases

### Phase 1: Foundation (Week 1-2)

**Objective:** Establish database and model foundation

**Deliverables:**
- Database migrations (status, snapshots, audit logs, rates)
- Model enhancements (lifecycle methods, immutability)
- Retroactive snapshot creation for existing invoices
- Company rate defaults migration

**Success Criteria:**
- All migrations run successfully
- Models enforce lifecycle rules
- Existing invoices have snapshots
- Zero data loss

---

### Phase 2: Business Logic (Week 3-4)

**Objective:** Centralize calculations and enforce company rates

**Deliverables:**
- InvoiceService refactored (company rates, lifecycle enforcement)
- PlatformFeeService updated (company rates)
- Snapshot creation integrated
- Audit logging integrated
- Preview calculations moved to service

**Success Criteria:**
- Zero hardcoded rates
- All calculations use company rates
- Snapshots created on finalization
- Audit logs created for all actions

---

### Phase 3: HTTP Layer (Week 4-5)

**Objective:** Enforce lifecycle rules at HTTP boundary

**Deliverables:**
- Controllers refactored (lifecycle checks, service calls only)
- PDF generation uses snapshots
- Preview methods use service calculations
- Request validation enhanced
- Finalize endpoint created

**Success Criteria:**
- No business logic in controllers
- PDFs use snapshots for finalized invoices
- All endpoints validated
- User-friendly error messages

---

### Phase 4: Presentation Layer (Week 5-6)

**Objective:** Ensure views are logic-free

**Deliverables:**
- PDF views audited and cleaned
- Settings pre-resolved in controllers
- UI reflects lifecycle states
- Rate display added

**Success Criteria:**
- Zero DB queries in PDF views
- All PDF data pre-computed
- UI shows correct states
- Performance maintained

---

### Phase 5: Audit & Polish (Week 6-7)

**Objective:** Complete audit trail and final validations

**Deliverables:**
- Audit logging for all lifecycle actions
- Company scoping verified
- Performance optimization
- Documentation updated

**Success Criteria:**
- All invoice actions logged
- Multi-tenancy verified
- Performance benchmarks met
- Documentation accurate

---

### Phase 6: Testing & Deployment (Week 7-8)

**Objective:** Validate all improvements and deploy

**Deliverables:**
- Comprehensive test suite
- Load testing completed
- Deployment plan executed
- Post-deployment monitoring

**Success Criteria:**
- 100% test coverage on critical paths
- Load tests pass
- Zero critical bugs
- Monitoring in place

---

## Success Metrics

### Technical Metrics

- Zero DB queries in PDF generation (after snapshot implementation)
- Zero hardcoded VAT/Platform fee rates
- 100% of finalized invoices have snapshots
- Zero structural edits possible on finalized invoices
- All queries scoped by company_id
- All invoice lifecycle actions logged

### Business Metrics

- Can claim "audit-ready" status
- Can claim "immutable invoice records"
- Can claim "Kenya-compliant VAT handling"
- Can demonstrate ETIMS readiness (without claiming compliance)
- Can pass accountant review
- Can defend invoice data in legal disputes

---

## Risk Mitigation

### Backward Compatibility Risks

**Risk:** Existing invoices break after changes

**Mitigation:**
- Legacy snapshots marked separately
- Existing invoices use stored values
- No recalculation of old data
- Default values match current behavior

### Performance Risks

**Risk:** Snapshot creation slows down finalization

**Mitigation:**
- Snapshot creation is one-time operation
- PDF generation becomes faster (no DB queries)
- Indexes on snapshot table
- Async snapshot creation (future optimization)

### Data Integrity Risks

**Risk:** Snapshots become out of sync

**Mitigation:**
- Snapshots created atomically with finalization
- One snapshot per invoice (unique constraint)
- No updates to snapshots after creation
- Validation ensures snapshot completeness

---

## Conclusion

This blueprint provides a comprehensive, section-by-section strategy for transforming InvoiceHub into a production-ready, audit-compliant platform. Every improvement aligns with the IDE Operating Instructions and Optimization & Evolution Guide principles.

**Key Transformation:**
- **Current:** Functional app with architectural risks
- **Target:** Credible, audit-ready, accounting-adjacent platform

**Implementation Philosophy:**
- Preserve trust, performance, auditability
- Maintain backward compatibility
- Enforce immutability after finalization
- Enable Kenya-market credibility

Following this blueprint will deliver a system that businesses can trust, accountants can review, and regulators can respect.

