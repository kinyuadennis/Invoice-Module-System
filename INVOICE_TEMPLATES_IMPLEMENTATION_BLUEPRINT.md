# Invoice Templates System — Complete Implementation Blueprint

**Version:** 1.0  
**Date:** 2025-12-03  
**Status:** Ready for Implementation  
**Target:** Laravel 12 + DomPDF + Blade Templates

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Database Schema](#database-schema)
3. [Models](#models)
4. [Migrations](#migrations)
5. [Controllers](#controllers)
6. [Services](#services)
7. [Views & UI Components](#views--ui-components)
8. [PDF Generation](#pdf-generation)
9. [Template System](#template-system)
10. [Testing Checklist](#testing-checklist)
11. [Migration Path](#migration-path)

---

## 🎯 Overview

### **Current State**
- Company model stores `invoice_template` as a string (config-based)
- Templates are defined in `config/invoice-templates.php`
- PDF generation uses template view paths from config
- No database persistence for templates
- No template preview functionality
- No template management UI

### **Target State**
- Database-driven template system with `invoice_templates` table
- Company settings store `invoice_template_id` (foreign key)
- Template preview thumbnails in UI
- Dynamic prefix generation per template
- Template-specific CSS files
- Admin/manageable template library
- Fallback to default template

### **Key Features**
✅ Template selection with visual previews  
✅ Template-specific invoice prefixes (INV-, FACT-, etc.)  
✅ Template-specific CSS styling  
✅ Template management (CRUD)  
✅ Default template fallback  
✅ Template activation/deactivation  
✅ Preview thumbnails in selection UI  

---

## 🗄️ Database Schema

### **1. `invoice_templates` Table**

```sql
CREATE TABLE invoice_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Template display name (e.g., "Modern Clean", "Classic Professional")',
    slug VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL-friendly identifier (e.g., "modern-clean", "classic-professional")',
    prefix VARCHAR(50) NOT NULL DEFAULT 'INV' COMMENT 'Invoice prefix for this template (e.g., INV-, FACT-, BILL-)',
    description TEXT NULL COMMENT 'Template description for admin/users',
    layout_class VARCHAR(100) NULL COMMENT 'CSS class for layout (e.g., "template-modern", "template-classic")',
    css_file VARCHAR(255) NULL COMMENT 'Path to template-specific CSS file (e.g., "templates/modern-clean.css")',
    view_path VARCHAR(255) NOT NULL COMMENT 'Blade view path (e.g., "invoices.templates.modern-clean")',
    preview_image VARCHAR(255) NULL COMMENT 'Path to preview thumbnail image',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether template is available for selection',
    is_default BOOLEAN DEFAULT FALSE COMMENT 'Default template for new companies',
    display_order INT DEFAULT 0 COMMENT 'Order in selection dropdown',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_active (is_active),
    INDEX idx_default (is_default),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **2. Update `companies` Table**

```sql
-- Add foreign key column (nullable for migration compatibility)
ALTER TABLE companies 
ADD COLUMN invoice_template_id BIGINT UNSIGNED NULL AFTER invoice_template,
ADD CONSTRAINT fk_companies_invoice_template 
    FOREIGN KEY (invoice_template_id) 
    REFERENCES invoice_templates(id) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE,
ADD INDEX idx_invoice_template_id (invoice_template_id);

-- Migrate existing invoice_template string values to foreign keys
-- (See migration script below)
```

---

## 📦 Models

### **1. InvoiceTemplate Model**

**File:** `app/Models/InvoiceTemplate.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'prefix',
        'description',
        'layout_class',
        'css_file',
        'view_path',
        'preview_image',
        'is_active',
        'is_default',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Companies using this template.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default template.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get the full path to the CSS file.
     */
    public function getCssFilePathAttribute(): ?string
    {
        if (!$this->css_file) {
            return null;
        }

        return public_path("css/invoice-templates/{$this->css_file}");
    }

    /**
     * Get the preview image URL.
     */
    public function getPreviewImageUrlAttribute(): ?string
    {
        if (!$this->preview_image) {
            return null;
        }

        return asset("images/invoice-templates/{$this->preview_image}");
    }

    /**
     * Get the default template.
     */
    public static function getDefault(): ?self
    {
        return static::default()->first() ?? static::active()->first();
    }
}
```

### **2. Update Company Model**

**File:** `app/Models/Company.php`

Add to existing model:

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Add to $fillable array:
'invoice_template_id',

/**
 * The invoice template this company uses.
 */
public function invoiceTemplate(): BelongsTo
{
    return $this->belongsTo(InvoiceTemplate::class);
}

/**
 * Get the active invoice template for this company.
 */
public function getActiveInvoiceTemplate(): InvoiceTemplate
{
    return $this->invoiceTemplate ?? InvoiceTemplate::getDefault();
}

/**
 * Get the invoice prefix from the active template.
 */
public function getInvoicePrefixFromTemplate(): string
{
    $template = $this->getActiveInvoiceTemplate();
    return $template->prefix ?? 'INV';
}
```

---

## 🔄 Migrations

### **1. Create Invoice Templates Table**

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_invoice_templates_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('prefix', 50)->default('INV');
            $table->text('description')->nullable();
            $table->string('layout_class', 100)->nullable();
            $table->string('css_file', 255)->nullable();
            $table->string('view_path', 255);
            $table->string('preview_image', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_default');
            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
```

### **2. Add Template ID to Companies**

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_invoice_template_id_to_companies_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('invoice_template_id')
                ->nullable()
                ->after('invoice_template')
                ->constrained('invoice_templates')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Migrate existing invoice_template string values to foreign keys
        $this->migrateExistingTemplates();
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['invoice_template_id']);
            $table->dropColumn('invoice_template_id');
        });
    }

    /**
     * Migrate existing invoice_template string values to foreign keys.
     */
    private function migrateExistingTemplates(): void
    {
        // Get all unique invoice_template values from companies
        $templates = DB::table('companies')
            ->whereNotNull('invoice_template')
            ->distinct()
            ->pluck('invoice_template');

        foreach ($templates as $templateName) {
            // Find or create template by slug
            $slug = \Illuminate\Support\Str::slug($templateName);
            
            $template = DB::table('invoice_templates')
                ->where('slug', $slug)
                ->first();

            if (!$template) {
                // Create template if it doesn't exist
                $templateId = DB::table('invoice_templates')->insertGetId([
                    'name' => ucwords(str_replace(['-', '_'], ' ', $templateName)),
                    'slug' => $slug,
                    'prefix' => 'INV', // Default prefix
                    'view_path' => "invoices.templates.{$slug}",
                    'is_active' => true,
                    'is_default' => false,
                    'display_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $templateId = $template->id;
            }

            // Update companies with this template name to use the foreign key
            DB::table('companies')
                ->where('invoice_template', $templateName)
                ->update([
                    'invoice_template_id' => $templateId,
                    'invoice_template' => null, // Keep for backward compatibility, can remove later
                ]);
        }
    }
};
```

### **3. Seed Default Templates**

**File:** `database/seeders/InvoiceTemplateSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\InvoiceTemplate;
use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Modern Clean',
                'slug' => 'modern-clean',
                'prefix' => 'INV',
                'description' => 'Clean, modern design with ample white space and professional typography.',
                'layout_class' => 'template-modern-clean',
                'css_file' => 'modern-clean.css',
                'view_path' => 'invoices.templates.modern-clean',
                'preview_image' => 'modern-clean-preview.png',
                'is_active' => true,
                'is_default' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Classic Professional',
                'slug' => 'classic-professional',
                'prefix' => 'FACT',
                'description' => 'Traditional invoice layout with formal styling and structured sections.',
                'layout_class' => 'template-classic-professional',
                'css_file' => 'classic-professional.css',
                'view_path' => 'invoices.templates.classic-professional',
                'preview_image' => 'classic-professional-preview.png',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 2,
            ],
            [
                'name' => 'Minimalist',
                'slug' => 'minimalist',
                'prefix' => 'BILL',
                'description' => 'Ultra-minimal design focusing on essential information only.',
                'layout_class' => 'template-minimalist',
                'css_file' => 'minimalist.css',
                'view_path' => 'invoices.templates.minimalist',
                'preview_image' => 'minimalist-preview.png',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 3,
            ],
            [
                'name' => 'Bold Modern',
                'slug' => 'bold-modern',
                'prefix' => 'INV',
                'description' => 'Bold colors and modern design elements for standout invoices.',
                'layout_class' => 'template-bold-modern',
                'css_file' => 'bold-modern.css',
                'view_path' => 'invoices.templates.bold-modern',
                'preview_image' => 'bold-modern-preview.png',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 4,
            ],
        ];

        foreach ($templates as $template) {
            InvoiceTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
```

---

## 🎮 Controllers

### **1. Update CompanyController**

**File:** `app/Http/Controllers/User/CompanyController.php`

Add/update methods:

```php
use App\Models\InvoiceTemplate;

/**
 * Show company settings page with template selection
 */
public function settings()
{
    $user = Auth::user();

    if (!$user->company_id) {
        return redirect()->route('company.setup');
    }

    $company = Company::with('invoiceTemplate')->findOrFail($user->company_id);
    
    // Get all active templates for selection
    $templates = InvoiceTemplate::active()->ordered()->get();
    
    // Get default template if none selected
    $selectedTemplate = $company->invoiceTemplate ?? InvoiceTemplate::getDefault();
    
    // ... existing code for prefix history, payment methods, etc.

    return view('company.settings', [
        'company' => $company,
        'templates' => $templates,
        'selectedTemplate' => $selectedTemplate,
        'prefixHistory' => $prefixHistory,
        'activePrefix' => $activePrefix,
        'paymentMethods' => $paymentMethods,
    ]);
}

/**
 * Update invoice template selection
 */
public function updateInvoiceTemplate(Request $request)
{
    $user = Auth::user();

    if (!$user->company_id) {
        return redirect()->route('company.setup');
    }

    $company = Company::findOrFail($user->company_id);

    if ($company->owner_user_id !== $user->id) {
        return back()->with('error', 'Only the company owner can update settings.');
    }

    $request->validate([
        'invoice_template_id' => ['required', 'exists:invoice_templates,id'],
    ]);

    $template = InvoiceTemplate::findOrFail($request->invoice_template_id);

    // Only allow active templates
    if (!$template->is_active) {
        return back()->with('error', 'Selected template is not available.');
    }

    $company->update([
        'invoice_template_id' => $template->id,
    ]);

    // If template has a different prefix, update the invoice prefix
    if ($template->prefix !== $company->activeInvoicePrefix()?->prefix) {
        $prefixService = app(InvoicePrefixService::class);
        $prefixService->changePrefix($company, $template->prefix, $user->id);
    }

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Invoice template updated successfully!',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'prefix' => $template->prefix,
                'preview_image' => $template->preview_image_url,
            ],
        ]);
    }

    return back()->with('success', 'Invoice template updated successfully!');
}
```

### **2. Create InvoiceTemplateController (Admin)**

**File:** `app/Http/Controllers/Admin/InvoiceTemplateController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceTemplateController extends Controller
{
    public function index()
    {
        $templates = InvoiceTemplate::ordered()->get();

        return view('admin.invoice-templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return view('admin.invoice-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:invoice_templates,slug',
            'prefix' => 'required|string|max:50',
            'description' => 'nullable|string',
            'layout_class' => 'nullable|string|max:100',
            'css_file' => 'nullable|string|max:255',
            'view_path' => 'required|string|max:255',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            $validated['preview_image'] = $request->file('preview_image')
                ->store('invoice-templates/previews', 'public');
        }

        InvoiceTemplate::create($validated);

        return redirect()->route('admin.invoice-templates.index')
            ->with('success', 'Template created successfully!');
    }

    public function edit(InvoiceTemplate $invoiceTemplate)
    {
        return view('admin.invoice-templates.edit', [
            'template' => $invoiceTemplate,
        ]);
    }

    public function update(Request $request, InvoiceTemplate $invoiceTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:invoice_templates,slug,' . $invoiceTemplate->id,
            'prefix' => 'required|string|max:50',
            'description' => 'nullable|string',
            'layout_class' => 'nullable|string|max:100',
            'css_file' => 'nullable|string|max:255',
            'view_path' => 'required|string|max:255',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'display_order' => 'integer|min:0',
        ]);

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            // Delete old image
            if ($invoiceTemplate->preview_image) {
                Storage::disk('public')->delete($invoiceTemplate->preview_image);
            }
            $validated['preview_image'] = $request->file('preview_image')
                ->store('invoice-templates/previews', 'public');
        }

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            InvoiceTemplate::where('id', '!=', $invoiceTemplate->id)
                ->update(['is_default' => false]);
        }

        $invoiceTemplate->update($validated);

        return redirect()->route('admin.invoice-templates.index')
            ->with('success', 'Template updated successfully!');
    }

    public function destroy(InvoiceTemplate $invoiceTemplate)
    {
        // Prevent deletion if it's the only active template
        $activeCount = InvoiceTemplate::active()->count();
        if ($invoiceTemplate->is_active && $activeCount <= 1) {
            return back()->with('error', 'Cannot delete the last active template.');
        }

        // Delete preview image
        if ($invoiceTemplate->preview_image) {
            Storage::disk('public')->delete($invoiceTemplate->preview_image);
        }

        $invoiceTemplate->delete();

        return redirect()->route('admin.invoice-templates.index')
            ->with('success', 'Template deleted successfully!');
    }
}
```

---

## 🔧 Services

### **Update InvoiceService**

**File:** `app/Http/Services/InvoiceService.php`

Add method to get template for invoice:

```php
use App\Models\InvoiceTemplate;

/**
 * Get the invoice template for a company.
 */
public function getInvoiceTemplate(Company $company): InvoiceTemplate
{
    return $company->getActiveInvoiceTemplate();
}

/**
 * Get the invoice prefix from template.
 */
public function getInvoicePrefixFromTemplate(Company $company): string
{
    return $company->getInvoicePrefixFromTemplate();
}
```

---

## 🎨 Views & UI Components

### **1. Template Selection Component**

**File:** `resources/views/components/invoice-template-selector.blade.php`

```blade
@props(['templates', 'selectedTemplate', 'company'])

<div class="space-y-4">
    <div>
        <label class="block text-sm font-semibold text-slate-900 mb-2">
            Invoice Template
        </label>
        <p class="text-sm text-slate-600 mb-4">
            Choose a template style for your invoices. The template determines the layout, styling, and invoice prefix.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" 
         x-data="invoiceTemplateSelector({{ $selectedTemplate->id ?? null }})">
        
        @foreach($templates as $template)
            <div 
                class="relative border-2 rounded-lg p-4 cursor-pointer transition-all hover:shadow-lg"
                :class="selectedTemplateId === {{ $template->id }} 
                    ? 'border-blue-500 bg-blue-50' 
                    : 'border-slate-200 hover:border-slate-300'"
                @click="selectTemplate({{ $template->id }}, '{{ route('company.update-invoice-template') }}')"
            >
                <!-- Selected Badge -->
                <div 
                    x-show="selectedTemplateId === {{ $template->id }}"
                    class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full"
                >
                    Selected
                </div>

                <!-- Preview Image -->
                @if($template->preview_image_url)
                    <img 
                        src="{{ $template->preview_image_url }}" 
                        alt="{{ $template->name }}"
                        class="w-full h-32 object-cover rounded mb-3"
                    >
                @else
                    <div class="w-full h-32 bg-slate-100 rounded mb-3 flex items-center justify-center">
                        <span class="text-slate-400 text-sm">No Preview</span>
                    </div>
                @endif

                <!-- Template Info -->
                <div>
                    <h3 class="font-semibold text-slate-900 mb-1">
                        {{ $template->name }}
                    </h3>
                    @if($template->description)
                        <p class="text-xs text-slate-600 mb-2">
                            {{ Str::limit($template->description, 80) }}
                        </p>
                    @endif
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-slate-500">
                            Prefix: <strong>{{ $template->prefix }}</strong>
                        </span>
                        @if($template->is_default)
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                Default
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Loading Indicator -->
    <div x-show="processing" class="text-center py-4">
        <div class="inline-flex items-center gap-2 text-blue-600">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Updating template...</span>
        </div>
    </div>
</div>

<script>
function invoiceTemplateSelector(initialTemplateId) {
    return {
        selectedTemplateId: initialTemplateId,
        processing: false,

        async selectTemplate(templateId, updateUrl) {
            if (this.processing || this.selectedTemplateId === templateId) {
                return;
            }

            this.processing = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        invoice_template_id: templateId,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.selectedTemplateId = templateId;
                    
                    // Show success message
                    this.$dispatch('notify', {
                        type: 'success',
                        message: data.message || 'Template updated successfully!',
                    });

                    // Optionally reload page to reflect changes
                    // window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to update template');
                }
            } catch (error) {
                console.error('Error updating template:', error);
                this.$dispatch('notify', {
                    type: 'error',
                    message: error.message || 'Failed to update template. Please try again.',
                });
            } finally {
                this.processing = false;
            }
        },
    };
}
</script>
```

### **2. Update Company Settings View**

**File:** `resources/views/company/settings.blade.php`

Add template selector section:

```blade
@extends('layouts.user')

@section('title', 'Company Settings')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Company Settings</h1>

    <div class="space-y-6">
        <!-- Invoice Template Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Invoice Template</h2>
            <x-invoice-template-selector 
                :templates="$templates" 
                :selectedTemplate="$selectedTemplate"
                :company="$company"
            />
        </div>

        <!-- Existing sections: Company Info, Invoice Format, Payment Methods, etc. -->
        <!-- ... -->
    </div>
</div>
@endsection
```

---

## 📄 PDF Generation

### **Update InvoiceController::generatePdf**

**File:** `app/Http/Controllers/User/InvoiceController.php`

```php
public function generatePdf($id)
{
    $companyId = Auth::user()->company_id;

    if (!$companyId) {
        abort(403, 'You must belong to a company to generate PDFs.');
    }

    $invoice = Invoice::where('company_id', $companyId)
        ->with(['client', 'invoiceItems', 'platformFees', 'user', 'company.invoiceTemplate'])
        ->findOrFail($id);

    $formattedInvoice = $this->invoiceService->formatInvoiceForShow($invoice);

    // Add platform fee if exists
    $platformFee = $invoice->platformFees->first();
    if ($platformFee) {
        $formattedInvoice['platform_fee'] = (float) $platformFee->fee_amount;
    }

    // Get template from company relationship
    $template = $invoice->company->getActiveInvoiceTemplate();
    $templateView = $template->view_path;

    // Generate PDF with template-specific CSS
    $pdf = Pdf::loadView($templateView, [
        'invoice' => $formattedInvoice,
        'template' => $template, // Pass template object for CSS file path
    ]);

    // Set PDF options
    $pdf->setPaper('a4', 'portrait');
    $pdf->setOption('enable-local-file-access', true);

    // If template has custom CSS, load it
    if ($template->css_file) {
        $cssPath = public_path("css/invoice-templates/{$template->css_file}");
        if (file_exists($cssPath)) {
            $pdf->setOption('chroot', public_path());
        }
    }

    $filename = 'invoice-'.($formattedInvoice['invoice_number'] ?? $invoice->id).'.pdf';

    return $pdf->download($filename);
}
```

### **Update Invoice Template Views**

**File:** `resources/views/invoices/templates/modern-clean.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice['invoice_number'] ?? 'N/A' }}</title>
    
    @if(isset($template) && $template->css_file)
        <link rel="stylesheet" href="{{ asset("css/invoice-templates/{$template->css_file}") }}">
    @else
        <link rel="stylesheet" href="{{ asset('css/invoice-templates/default.css') }}">
    @endif
    
    <style>
        /* Inline styles for PDF generation */
        body {
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
        }
        /* ... template-specific styles ... */
    </style>
</head>
<body class="{{ $template->layout_class ?? 'template-default' }}">
    <!-- Invoice content -->
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <h1>Invoice {{ $invoice['invoice_number'] ?? 'N/A' }}</h1>
            <!-- ... -->
        </div>
        
        <!-- Invoice body -->
        <!-- ... -->
    </div>
</body>
</html>
```

---

## 🎨 Template System

### **1. Create Template CSS Files**

**Directory:** `public/css/invoice-templates/`

**File:** `public/css/invoice-templates/modern-clean.css`

```css
/* Modern Clean Template Styles */
.template-modern-clean {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #1a1a1a;
    line-height: 1.6;
}

.template-modern-clean .invoice-header {
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 2rem;
    margin-bottom: 2rem;
}

.template-modern-clean .invoice-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.template-modern-clean .invoice-number {
    font-size: 1.25rem;
    color: #6b7280;
    font-weight: 500;
}

/* ... more styles ... */
```

**File:** `public/css/invoice-templates/classic-professional.css`

```css
/* Classic Professional Template Styles */
.template-classic-professional {
    font-family: 'Times New Roman', serif;
    color: #000;
}

.template-classic-professional .invoice-header {
    border: 2px solid #000;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

/* ... more styles ... */
```

### **2. Create Preview Images**

**Directory:** `public/images/invoice-templates/`

- `modern-clean-preview.png` (300x200px)
- `classic-professional-preview.png` (300x200px)
- `minimalist-preview.png` (300x200px)
- `bold-modern-preview.png` (300x200px)

---

## ✅ Testing Checklist

### **Database & Migrations**
- [ ] Run migrations successfully
- [ ] Seed default templates
- [ ] Verify foreign key constraints
- [ ] Test migration of existing data

### **Template Selection**
- [ ] Display all active templates in UI
- [ ] Show preview thumbnails
- [ ] Select template updates company settings
- [ ] Selected template persists on page reload
- [ ] Template prefix updates invoice prefix
- [ ] Default template fallback works

### **PDF Generation**
- [ ] PDF uses selected template view
- [ ] Template CSS loads correctly
- [ ] Template layout class applies
- [ ] Invoice prefix from template is used
- [ ] PDF renders correctly for all templates

### **Edge Cases**
- [ ] Company with no template uses default
- [ ] Deactivated template not shown in selection
- [ ] Cannot delete last active template
- [ ] Template deletion sets company template to null
- [ ] Preview image upload works
- [ ] CSS file path validation

### **Admin Management**
- [ ] Create new template
- [ ] Edit existing template
- [ ] Delete template (with safety checks)
- [ ] Set default template
- [ ] Activate/deactivate template
- [ ] Reorder templates

---

## 🔄 Migration Path

### **Step 1: Run Migrations**
```bash
php artisan migrate
php artisan db:seed --class=InvoiceTemplateSeeder
```

### **Step 2: Update Existing Code**
1. Update `CompanyController::settings()` to load templates
2. Update `CompanyController::updateInvoiceTemplate()`
3. Update `InvoiceController::generatePdf()`
4. Add template selector component to settings view

### **Step 3: Create Template Assets**
1. Create CSS files in `public/css/invoice-templates/`
2. Create preview images in `public/images/invoice-templates/`
3. Update existing template views to use template object

### **Step 4: Test & Deploy**
1. Test template selection
2. Test PDF generation with each template
3. Verify existing invoices still work
4. Deploy to production

---

## 📝 Additional Notes

### **Template Prefix Logic**
- Template prefix is used when creating new invoice prefixes
- If company changes template, new invoices use new prefix
- Existing invoices keep their original prefix
- Prefix history is maintained via `InvoicePrefix` model

### **CSS File Organization**
- Store CSS files in `public/css/invoice-templates/`
- Use descriptive filenames matching template slug
- Include print media queries for PDF generation
- Use relative paths for assets

### **Preview Images**
- Recommended size: 300x200px
- Format: PNG or JPG
- Store in `public/images/invoice-templates/`
- Use descriptive filenames matching template slug

### **Performance Considerations**
- Cache template queries if needed
- Lazy load preview images
- Optimize CSS files for PDF generation
- Consider CDN for template assets

---

## 🎯 Success Criteria

✅ Companies can select from multiple invoice templates  
✅ Template selection persists in database  
✅ PDF generation uses selected template  
✅ Template-specific prefixes work correctly  
✅ Preview thumbnails display in UI  
✅ Default template fallback works  
✅ Admin can manage templates  
✅ Existing invoices continue to work  

---

**End of Blueprint**

