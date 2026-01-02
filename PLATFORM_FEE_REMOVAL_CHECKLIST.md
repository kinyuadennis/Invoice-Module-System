# Platform Fee Removal Checklist

This document tracks all platform fee references that need to be removed as part of the migration to a subscription-based payment model.

## Status Legend
- ✅ Completed
- ⏳ In Progress
- ⏸️ Blocked (requires blueprint)
- ⏭️ Deferred (can be handled later)

---

## 1. Database Schema Changes

### 1.1 Migrations to Create
- [ ] `remove_platform_fee_from_invoices_table.php` - Drop `platform_fee` column from `invoices` table
- [ ] `remove_platform_fee_from_estimates_table.php` - Drop `platform_fee` column from `estimates` table
- [ ] `remove_platform_fee_from_credit_notes_table.php` - Drop `platform_fee` column from `credit_notes` table
- [ ] `drop_platform_fees_table.php` - Drop entire `platform_fees` table (including all related migrations)
- [ ] `remove_platform_fee_rate_from_system_settings.php` - Remove `platform_fee_rate` from system settings cache/config

### 1.2 Tables Affected
- `invoices` - Remove `platform_fee` column (decimal 15,2)
- `estimates` - Remove `platform_fee` column (decimal 15,2)
- `credit_notes` - Remove `platform_fee` column (decimal 15,2)
- `platform_fees` - Drop entire table (id, company_id, invoice_id, fee_amount, fee_rate, fee_status, timestamps)
- System settings cache - Remove `platform_fee_rate` key

---

## 2. Models

### 2.1 Model Files to Update
- [ ] `app/Models/Invoice.php`
  - Remove `platform_fee` from `$fillable`
  - Remove `platform_fee` from `casts()`
  - Remove `platformFees()` relationship method
  
- [ ] `app/Models/Estimate.php`
  - Remove `platform_fee` from `$fillable`
  - Remove `platform_fee` from `casts()`
  
- [ ] `app/Models/CreditNote.php`
  - Remove `platform_fee` from `$fillable`
  - Remove `platform_fee` from `casts()`
  
- [ ] `app/Models/Company.php`
  - Remove `platformFees()` relationship method

### 2.2 Models to Delete
- [ ] `app/Models/PlatformFee.php` - Delete entire file
- [ ] `database/factories/PlatformFeeFactory.php` - Delete entire file

---

## 3. Services

### 3.1 Service Files to Update
- [ ] `app/Http/Services/InvoiceService.php`
  - Remove `PlatformFeeService` dependency from constructor
  - Remove all platform fee calculation logic from methods:
    - `createInvoice()` - Remove platform fee calculation and generation
    - `updateInvoice()` - Remove platform fee updates
    - `updateTotals()` - Remove platform fee from calculations
    - `sendInvoice()` - Remove platform fee generation calls
  
- [ ] `app/Http/Services/DashboardService.php`
  - Remove `PlatformFee` model import
  - Remove `totalPlatformFees` from dashboard data
  - Remove platform fee queries from `getDashboardData()`
  
- [ ] `app/Http/Services/EstimateService.php`
  - Remove `PlatformFeeService` dependency from constructor
  - Remove platform fee calculations from:
    - `createEstimate()`
    - `updateEstimate()`
    - `convertToInvoice()`
  
- [ ] `app/Http/Services/CreditNoteService.php`
  - Remove platform fee handling from credit note calculations
  
- [ ] `app/Http/Services/ReportService.php`
  - Remove platform fee revenue calculations
  - Remove platform fee line items from reports
  - Update net revenue calculations (currently subtracts platform fees)
  
- [ ] `app/Services/InvoiceSnapshotService.php`
  - Remove `platform_fee` from snapshot data

### 3.2 Service Files to Delete
- [ ] `app/Http/Services/PlatformFeeService.php` - Delete entire file

---

## 4. Controllers

### 4.1 Controllers to Update
- [ ] `app/Http/Controllers/User/InvoiceController.php`
  - Remove platform fee calculations from:
    - `store()`
    - `update()`
    - `quickCreate()`
  
- [ ] `app/Http/Controllers/Admin/SystemSettingsController.php`
  - Remove `platform_fee_rate` validation rule
  - Remove `platform_fee_rate` from cache handling
  
- [ ] `app/Http/Controllers/Admin/DashboardController.php`
  - Remove `PlatformFee` import
  - Remove `platformFeesCollected` from dashboard stats
  
- [ ] `app/Http/Controllers/Public/HomeController.php`
  - Remove `PlatformFeeService` dependency
  - Remove platform fee from invoice preview data
  - Remove platform fee from `calculatePreview()` method

### 4.2 Controllers to Delete
- [ ] `app/Http/Controllers/Admin/PlatformFeeController.php` - Delete entire file

---

## 5. Views & Templates

### 5.1 Blade Templates to Update
- [ ] `resources/views/components/one-page-invoice-builder.blade.php`
  - Remove `platform_fee` from Alpine.js data
  - Remove platform fee from summary section display
  - Remove platform fee from calculations (`calculateTotals()`)
  - Remove platform fee from form data initialization
  
- [ ] `resources/views/pdf/invoice.blade.php`
  - Remove platform fee row from totals section
  
- [ ] `resources/views/pdf/estimate.blade.php`
  - Remove platform fee row from totals section
  
- [ ] `resources/views/invoices/pdf.blade.php`
  - Remove platform fee from totals display
  
- [ ] `resources/views/user/invoices/show.blade.php`
  - Remove platform fee display section
  
- [ ] `resources/views/user/estimates/show.blade.php`
  - Remove platform fee display section
  
- [ ] `resources/views/user/reports/profit-loss.blade.php`
  - Remove platform fees revenue card/display
  
- [ ] `resources/views/admin/system-settings/index.blade.php`
  - Remove platform fee rate input field and label
  
- [ ] `resources/views/components/hero-enhanced.blade.php`
  - Remove "3% platform fee" text from hero section
  
- [ ] `resources/views/layouts/admin.blade.php`
  - Remove "Platform Fees" navigation menu items (2 instances)

### 5.2 View Directories to Delete
- [ ] `resources/views/admin/platform-fees/` - Delete entire directory (if exists)

---

## 6. Routes

### 6.1 Routes to Remove
- [ ] `routes/web.php`
  - Remove `PlatformFeeController` import
  - Remove admin platform fees routes:
    ```php
    Route::get('/platform-fees', [PlatformFeeController::class, 'index'])->name('platform-fees.index');
    ```

---

## 7. Traits & Helpers

### 7.1 Files to Update
- [ ] `app/Traits/FormatsInvoiceData.php`
  - Remove `platform_fee` from formatted invoice data array

---

## 8. PDF Renderers

### 8.1 Files to Update
- [ ] `app/Services/PdfEstimateRenderer.php`
  - Remove `platform_fee` from estimate data array

---

## 9. Tests

### 9.1 Test Files to Update
- [ ] Update all invoice creation tests to remove platform fee assertions
- [ ] Update all estimate creation tests to remove platform fee assertions
- [ ] Update dashboard tests to remove platform fee stats
- [ ] Update report tests to remove platform fee calculations
- [ ] Update PDF rendering tests to remove platform fee assertions

### 9.2 Test Files to Delete
- [ ] Delete any `PlatformFeeTest.php` or related test files

---

## 10. Configuration & Cache

### 10.1 Cache Keys to Remove
- [ ] `system_setting_platform_fee_rate` - Remove from cache/clear

### 10.2 Environment Variables
- [ ] Review `.env.example` - Remove any platform fee related env vars (if any)

---

## 11. Documentation

### 11.1 Files to Update
- [ ] `README.md` - Remove platform fee references
- [ ] Any API documentation - Remove platform fee parameters/responses
- [ ] Update any user guides or help documentation

---

## 12. Database Seeding

### 12.1 Seeders to Update
- [ ] Review and update any seeders that create platform fees
- [ ] Remove platform fee data from demo/test data

---

## 13. Code Comments & Documentation

### 13.1 Comments to Update
- [ ] Remove or update code comments that reference platform fees
- [ ] Update PHPDoc blocks that mention platform fees

---

## Notes

1. **Financial Immutability**: Historical invoices with platform fees should remain untouched in the database. Only remove the columns from new records going forward. Consider a data migration strategy for existing data if needed.

2. **Backward Compatibility**: PDF snapshots may still contain platform fee data. Decide if this should be preserved for historical accuracy or removed.

3. **Testing Strategy**: 
   - Run full test suite after each major section
   - Focus on invoice/estimate creation and calculation tests
   - Verify PDF rendering still works correctly
   - Check dashboard and reports display correctly

4. **Migration Order**:
   1. Remove business logic first (services, controllers)
   2. Remove UI elements (views)
   3. Remove model relationships and attributes
   4. Create and run database migrations
   5. Delete obsolete files (models, services, controllers)

---

## Estimated Impact

- **Files to Modify**: ~30-40 files
- **Files to Delete**: ~5-7 files
- **Database Tables**: 1 table to drop, 3 columns to remove
- **Lines of Code**: ~500-700 lines to remove/modify
- **Estimated Time**: 4-6 hours (excluding subscription module implementation)

