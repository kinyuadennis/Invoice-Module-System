# 6-Step Invoice Creation Flow - Implementation Plan

## Overview
Transform the current single-page invoice creation form into a modern, step-by-step wizard that guides users through: Client Selection → Invoice Details → Line Items → Summary → Payment Method → Save & Send.

---

## STEP 1: Fix Critical Issues First

### 1.1 Fix InvoiceService Import
**File:** `app/Http/Services/InvoiceService.php`
- Add missing import: `use App\Http\Services\PlatformFeeService;`
- Ensure constructor properly injects PlatformFeeService

### 1.2 Fix Field Name Mismatch
**Files to Update:**
- `resources/views/user/invoices/create.blade.php` - Change `rate` to `unit_price`
- `app/Http/Requests/StoreInvoiceRequest.php` - Update validation rules
- `app/Http/Services/InvoiceService.php` - Update item mapping

**Changes:**
- Form fields: `item.rate` → `item.unit_price`
- Validation: Add `items.*.total_price` rule
- Service: Calculate `total_price = quantity * unit_price` before saving

### 1.3 Update Currency Formatting
**File:** `resources/views/user/invoices/create.blade.php`
- Change `formatCurrency` from USD to KES
- Update number formatting to Kenyan locale

---

## STEP 2: Create Multi-Step Wizard Component

### 2.1 Main Wizard Component
**File:** `resources/views/components/invoice-wizard.blade.php`

**Structure:**
```blade
<div x-data="invoiceWizard()">
    <!-- Step Indicator -->
    <div class="step-indicator">...</div>
    
    <!-- Step Content -->
    <div class="step-content">
        <!-- Step 1: Client Selection -->
        <!-- Step 2: Invoice Details -->
        <!-- Step 3: Line Items -->
        <!-- Step 4: Summary -->
        <!-- Step 5: Payment Method -->
        <!-- Step 6: Save & Send -->
    </div>
    
    <!-- Navigation Buttons -->
    <div class="wizard-navigation">...</div>
</div>
```

**Alpine.js State:**
- `currentStep: 1-6`
- `formData: { client, details, items, paymentMethod }`
- `validationErrors: {}`
- Methods: `nextStep()`, `previousStep()`, `validateStep()`, `submitForm()`

---

## STEP 3: Step 1 - Client Selection

### 3.1 Client Selector Component
**File:** `resources/views/components/client-selector.blade.php`

**Features:**
- Search/filter existing clients
- "Add New Client" button
- Auto-fill client data when selected
- Display selected client info

**Implementation:**
- Dropdown with search (Alpine.js or Select2)
- Show client name, email, phone when selected
- Store `client_id` in wizard state

### 3.2 Client Create Modal
**File:** `resources/views/components/client-create-modal.blade.php`

**Fields:**
- Name (required)
- Email (optional, unique)
- Phone (optional)
- Address (optional)

**AJAX Endpoint:**
- Route: `POST /app/clients` (AJAX)
- Controller: `User\ClientController@store`
- Return: JSON with new client data
- Auto-select new client after creation

### 3.3 Update InvoiceController
**File:** `app/Http/Controllers/User/InvoiceController.php`
- Scope clients to current user: `Client::where('user_id', Auth::id())`
- Pass clients to view

### 3.4 Create ClientController
**File:** `app/Http/Controllers/User/ClientController.php`
- `store()` method for AJAX client creation
- Validation: name required, email unique per user
- Return JSON response

**Route:** Add to `routes/web.php`:
```php
Route::post('/app/clients', [ClientController::class, 'store'])->name('user.clients.store');
```

---

## STEP 4: Step 2 - Invoice Details

### 4.1 Invoice Details Form Component
**File:** `resources/views/components/invoice-details-form.blade.php`

**Fields:**
- **Issue Date:** Date picker (default: today)
- **Due Date:** Date picker (required, must be >= issue date)
- **Reference Number:** Text input (auto-generated but editable)
  - Format: `INV-YYYY-XXXX` (e.g., INV-2024-0001)
  - Auto-generate on load, allow editing
  - Validate uniqueness
- **Notes:** Textarea (optional)

**Auto-Generate Reference:**
- Use trait: `FormatsInvoiceNumber`
- Format: `INV-{YEAR}-{SEQUENTIAL}`
- Check uniqueness before saving

### 4.2 Database Migration
**File:** `database/migrations/YYYY_MM_DD_add_invoice_reference_to_invoices_table.php`

```php
Schema::table('invoices', function (Blueprint $table) {
    $table->string('invoice_reference')->nullable()->unique()->after('id');
});
```

### 4.3 Update Invoice Model
**File:** `app/Models/Invoice.php`
- Add `invoice_reference` to `$fillable`

---

## STEP 5: Step 3 - Line Items

### 5.1 Line Items Editor Component
**File:** `resources/views/components/line-items-editor.blade.php`

**Features:**
- **Add from Service Library:** Dropdown with predefined services
- **Add Custom Item:** Manual entry
- **Edit Price:** Inline editing
- **VAT Toggle:** Per-item VAT inclusion (default: 16%)
- **Automatic Totals:** Real-time calculation

**Service Library (Hardcoded Array):**
```php
$services = [
    'Web Development Services',
    'Mobile App Development',
    'Digital Marketing',
    'Consulting Services',
    'Graphic Design',
    'Content Writing',
    'SEO Optimization',
    'Cloud Infrastructure',
    'Software Maintenance',
    'Data Analytics',
];
```

**Line Item Structure:**
- Description (from library or custom)
- Quantity
- Unit Price
- VAT Toggle (on/off, default: on)
- VAT Rate (16% default)
- Total Price (calculated: quantity × unit_price × (1 + VAT if enabled))

**Alpine.js Features:**
- Add item from library (pre-fills description and suggested price)
- Add custom item (empty form)
- Remove item
- Toggle VAT per item
- Auto-calculate totals
- Real-time summary update

### 5.2 Update InvoiceItem Model
**File:** `app/Models/InvoiceItem.php`
- Add `vat_rate` and `vat_included` to `$fillable` (optional, can calculate from totals)

**Alternative:** Calculate VAT from totals in service layer if not storing separately.

---

## STEP 6: Step 4 - Summary

### 6.1 Invoice Summary Component
**File:** `resources/views/components/invoice-summary.blade.php`

**Display:**
- **Subtotal:** Sum of all line items (before VAT)
- **VAT (16%):** Total VAT amount
- **Platform Fee (0.8%):** Calculated on (subtotal + VAT)
- **Grand Total:** Subtotal + VAT + Platform Fee

**Real-Time Updates:**
- Reactive to line item changes
- Updates as user modifies items
- Shows breakdown clearly

**Calculation Logic:**
```javascript
subtotal = sum of (quantity × unit_price) for all items
vat = subtotal × 0.16
totalBeforeFee = subtotal + vat
platformFee = totalBeforeFee × 0.008
grandTotal = totalBeforeFee + platformFee
```

**Visual Design:**
- Clean breakdown table
- Highlight grand total
- Show platform fee with tooltip explanation
- KES currency formatting

---

## STEP 7: Step 5 - Payment Method

### 7.1 Payment Method Selector Component
**File:** `resources/views/components/payment-method-selector.blade.php`

**Options:**
- **M-Pesa:** Show M-Pesa number field (optional)
- **Bank Transfer:** Show bank account details field (optional)
- **Cash:** No additional fields

**Implementation:**
- Radio buttons or card selection
- Conditional fields based on selection
- Store `payment_method` in invoice

### 7.2 Database Migration
**File:** `database/migrations/YYYY_MM_DD_add_payment_method_to_invoices_table.php`

```php
Schema::table('invoices', function (Blueprint $table) {
    $table->string('payment_method')->nullable()->after('status');
    $table->text('payment_details')->nullable()->after('payment_method');
});
```

### 7.3 Update Invoice Model
**File:** `app/Models/Invoice.php`
- Add `payment_method` and `payment_details` to `$fillable`

---

## STEP 8: Step 6 - Save & Send

### 8.1 Invoice Actions Component
**File:** `resources/views/components/invoice-actions.blade.php`

**Actions:**
1. **Save Draft:** Save invoice with status='draft'
2. **Generate PDF:** Download PDF (implement later)
3. **Send Email:** Queue email sending (implement later)
4. **Send WhatsApp:** Queue WhatsApp message (implement later)
5. **Send via M-PESA:** Placeholder (disabled, "Coming Soon" badge)

**Implementation:**
- Action buttons with icons
- Each action calls different endpoint
- Show loading states
- Success/error feedback

### 8.2 Update InvoiceController
**File:** `app/Http/Controllers/User/InvoiceController.php`

**New Methods:**
- `generatePdf($id)` - Generate and download PDF
- `sendEmail($id)` - Queue email sending
- `sendWhatsApp($id)` - Queue WhatsApp sending

**Routes:**
```php
Route::get('/app/invoices/{id}/pdf', [InvoiceController::class, 'generatePdf'])->name('user.invoices.pdf');
Route::post('/app/invoices/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('user.invoices.send-email');
Route::post('/app/invoices/{id}/send-whatsapp', [InvoiceController::class, 'sendWhatsApp'])->name('user.invoices.send-whatsapp');
```

### 8.3 PDF Generation
**Package:** Install `barryvdh/laravel-dompdf` or use Laravel's built-in PDF
- Create PDF view: `resources/views/invoices/pdf.blade.php`
- Generate PDF in controller
- Return download response

---

## STEP 9: Update Validation & Backend

### 9.1 Update StoreInvoiceRequest
**File:** `app/Http/Requests/StoreInvoiceRequest.php`

**New Rules:**
```php
'invoice_reference' => 'nullable|string|max:50|unique:invoices,invoice_reference',
'issue_date' => 'required|date',
'due_date' => 'required|date|after_or_equal:issue_date',
'payment_method' => 'nullable|in:mpesa,bank_transfer,cash',
'payment_details' => 'nullable|string|max:500',
'notes' => 'nullable|string|max:1000',
'items.*.unit_price' => 'required|numeric|min:0',
'items.*.total_price' => 'required|numeric|min:0',
```

### 9.2 Update InvoiceService
**File:** `app/Http/Services/InvoiceService.php`

**Update createInvoice():**
- Handle `invoice_reference` (generate if not provided)
- Handle `payment_method` and `payment_details`
- Handle `notes`
- Calculate totals correctly with VAT
- Generate platform fee

**Reference Generation:**
```php
if (empty($data['invoice_reference'])) {
    $year = date('Y');
    $lastInvoice = Invoice::whereYear('created_at', $year)
        ->orderBy('id', 'desc')
        ->first();
    $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_reference, -4) + 1 : 1;
    $data['invoice_reference'] = "INV-{$year}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
}
```

---

## STEP 10: Update Create View

### 10.1 Replace Create View
**File:** `resources/views/user/invoices/create.blade.php`

**Replace entire content with:**
```blade
@extends('layouts.user')

@section('title', 'Create Invoice')

@section('content')
    <x-invoice-wizard
        :clients="$clients"
        :services="$services"
    />
@endsection
```

**Controller Update:**
```php
public function create()
{
    $clients = Client::where('user_id', Auth::id())
        ->select('id', 'name', 'email', 'phone', 'address')
        ->get();
    
    $services = [
        'Web Development Services' => 50000,
        'Mobile App Development' => 75000,
        // ... more services with suggested prices
    ];
    
    return view('user.invoices.create', [
        'clients' => $clients,
        'services' => $services,
    ]);
}
```

---

## STEP 11: Additional Components Needed

### 11.1 Step Indicator Component
**File:** `resources/views/components/step-indicator.blade.php`
- Visual progress bar
- Step numbers with labels
- Active/complete states

### 11.2 Service Library Dropdown
**File:** `resources/views/components/service-library-dropdown.blade.php`
- Searchable dropdown
- Predefined services with suggested prices
- "Add Custom" option

---

## STEP 12: Testing & Validation

### 12.1 Test Scenarios
1. Create invoice with existing client
2. Create invoice with new client (modal)
3. Add items from service library
4. Add custom items
5. Toggle VAT on/off per item
6. Verify totals calculation
7. Verify platform fee calculation
8. Save as draft
9. Generate PDF
10. Send email (queue test)

### 12.2 Edge Cases
- Empty line items (prevent submission)
- Invalid dates (due date before issue date)
- Duplicate invoice reference
- Client creation validation errors
- Network errors during AJAX calls

---

## Implementation Order

1. **Fix critical issues** (Step 1) - Must do first
2. **Create wizard structure** (Step 2) - Foundation
3. **Client selection** (Step 3) - First user interaction
4. **Invoice details** (Step 4) - Basic info
5. **Line items** (Step 5) - Core functionality
6. **Summary** (Step 6) - Validation & totals
7. **Payment method** (Step 7) - Additional info
8. **Save & Send** (Step 8) - Final actions
9. **Backend updates** (Step 9-10) - Data persistence
11. **Testing** (Step 12) - Validation

---

## Technical Notes

### Alpine.js State Management
```javascript
x-data="{
    currentStep: 1,
    formData: {
        client_id: null,
        issue_date: '',
        due_date: '',
        invoice_reference: '',
        notes: '',
        items: [],
        payment_method: null,
        payment_details: ''
    },
    validationErrors: {},
    services: @json($services),
    clients: @json($clients)
}"
```

### Form Submission
- Collect all step data
- Validate all steps
- Submit via AJAX or traditional form
- Handle errors per step
- Redirect on success

### Responsive Design
- Mobile: Stack steps vertically
- Tablet: 2-column layout where appropriate
- Desktop: Full wizard experience

---

## Files Summary

### New Files to Create:
1. `resources/views/components/invoice-wizard.blade.php`
2. `resources/views/components/client-selector.blade.php`
3. `resources/views/components/client-create-modal.blade.php`
4. `resources/views/components/invoice-details-form.blade.php`
5. `resources/views/components/line-items-editor.blade.php`
6. `resources/views/components/invoice-summary.blade.php`
7. `resources/views/components/payment-method-selector.blade.php`
8. `resources/views/components/invoice-actions.blade.php`
9. `resources/views/components/step-indicator.blade.php`
10. `resources/views/components/service-library-dropdown.blade.php`
11. `app/Http/Controllers/User/ClientController.php`
12. `database/migrations/YYYY_MM_DD_add_invoice_reference_to_invoices_table.php`
13. `database/migrations/YYYY_MM_DD_add_payment_method_to_invoices_table.php`

### Files to Modify:
1. `app/Http/Services/InvoiceService.php`
2. `app/Http/Requests/StoreInvoiceRequest.php`
3. `app/Http/Controllers/User/InvoiceController.php`
4. `app/Models/Invoice.php`
5. `app/Models/InvoiceItem.php`
6. `resources/views/user/invoices/create.blade.php`
7. `routes/web.php`

---

## Success Criteria

- User can complete invoice creation in 6 clear steps
- All data persists correctly
- Platform fee calculates accurately (0.8%)
- VAT calculations are correct (16%)
- Client can be added inline without page reload
- Service library speeds up item entry
- PDF generation works
- Email/WhatsApp actions queue properly
- Mobile responsive
- Accessible (keyboard navigation, screen readers)

---

**Ready for implementation!**

