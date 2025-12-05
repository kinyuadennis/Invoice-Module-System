@extends('layouts.user')

@section('title', 'Invoice Customization')

@section('content')
<!-- Header -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-white shadow-xl">
        <h1 class="text-3xl font-bold mb-2">Invoice Customization</h1>
        <p class="text-blue-100 text-lg">Customize your invoice numbering format and visual template</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6">
        <x-alert type="success">{{ session('success') }}</x-alert>
    </div>
@endif

@if(session('error'))
    <div class="mb-6">
        <x-alert type="error">{{ session('error') }}</x-alert>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Main Content - Left Side (8 columns) -->
    <div class="lg:col-span-8 space-y-6">
        <!-- Invoice Number Format Section -->
        <x-card class="shadow-lg border border-gray-200">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                <div class="p-2.5 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Invoice Number Format</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Configure how your invoice numbers are generated</p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('user.company.update-invoice-format') }}" x-data="invoiceFormatForm({{ json_encode($company) }}, {{ json_encode($formatPatterns) }})">
                @csrf
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Prefix -->
                        <div>
                            <label for="invoice_prefix" class="block text-sm font-medium text-gray-700 mb-2">
                                Prefix
                            </label>
                            <input
                                type="text"
                                id="invoice_prefix"
                                name="invoice_prefix"
                                x-model="formData.prefix"
                                @input="updatePreview()"
                                maxlength="20"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                placeholder="INV"
                            />
                            <p class="mt-1.5 text-xs text-gray-500">e.g., INV, HUB, ACME</p>
                        </div>

                        <!-- Suffix -->
                        <div>
                            <label for="invoice_suffix" class="block text-sm font-medium text-gray-700 mb-2">
                                Suffix (Optional)
                            </label>
                            <input
                                type="text"
                                id="invoice_suffix"
                                name="invoice_suffix"
                                x-model="formData.suffix"
                                @input="updatePreview()"
                                maxlength="20"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                placeholder="KE"
                            />
                            <p class="mt-1.5 text-xs text-gray-500">e.g., KE, 2025</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Padding -->
                        <div>
                            <label for="invoice_padding" class="block text-sm font-medium text-gray-700 mb-2">
                                Number Padding
                            </label>
                            <select
                                id="invoice_padding"
                                name="invoice_padding"
                                x-model="formData.padding"
                                @change="updatePreview()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                            >
                                <option value="3">3 digits (001)</option>
                                <option value="4">4 digits (0001)</option>
                                <option value="5">5 digits (00001)</option>
                                <option value="6">6 digits (000001)</option>
                            </select>
                        </div>

                        <!-- Format Pattern -->
                        <div>
                            <label for="invoice_format" class="block text-sm font-medium text-gray-700 mb-2">
                                Format Pattern
                            </label>
                            <select
                                id="invoice_format"
                                name="invoice_format"
                                x-model="formData.format"
                                @change="updatePreview()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                            >
                                @foreach($formatPatterns as $pattern => $example)
                                    <option value="{{ $pattern }}" {{ $company->invoice_format === $pattern ? 'selected' : '' }}>
                                        {{ $example }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Live Preview -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-6 shadow-sm">
                        <label class="block text-sm font-semibold text-blue-900 mb-3">Live Preview</label>
                        <div class="text-3xl font-bold text-blue-600 mb-2" x-text="preview"></div>
                        <p class="text-xs text-blue-700">This is how your next invoice number will look</p>
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary" class="w-full sm:w-auto min-w-[200px]">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Format Settings
                        </x-button>
                    </div>
                </div>
            </form>
        </x-card>

        <!-- Invoice Template Selection - Moved to main content area -->
        @if(isset($templates) && $templates->isNotEmpty())
            <x-card class="shadow-lg border border-gray-200">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                    <div class="p-2.5 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Invoice Template</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Choose a visual template for your invoices</p>
                    </div>
                </div>
                
                <x-invoice-template-selector 
                    :templates="$templates" 
                    :selectedTemplate="$selectedTemplate ?? null"
                    :company="$company"
                />
            </x-card>
        @endif
    </div>

    <!-- Sidebar - Right Side (4 columns) -->
    <div class="lg:col-span-4 space-y-6">
        <!-- Info Card -->
        <x-card class="shadow-lg border border-gray-200 bg-gradient-to-br from-blue-50 to-indigo-50">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-blue-200">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900">About Invoice Customization</h2>
            </div>
            <div class="space-y-4 text-sm text-gray-700">
                <div>
                    <p class="font-semibold text-gray-900 mb-1.5">Invoice Number Format</p>
                    <p class="text-gray-600 leading-relaxed">Customize how your invoice numbers are generated. The format will be applied automatically when creating new invoices.</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 mb-1.5">Invoice Template</p>
                    <p class="text-gray-600 leading-relaxed">Choose a visual style for your invoices. Each template has its own layout, styling, and invoice prefix.</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 mb-1.5">Template Prefix</p>
                    <p class="text-gray-600 leading-relaxed">Each template comes with a default prefix (e.g., INV, FACT, BILL). When you select a template, the invoice prefix will automatically update to match.</p>
                </div>
                <div class="pt-3 border-t border-blue-200">
                    <p class="font-semibold text-gray-900 mb-1.5">Important Note</p>
                    <p class="text-gray-600 leading-relaxed">Format and template changes apply to all future invoices. Existing invoices will not be affected.</p>
                </div>
            </div>
        </x-card>

        <!-- Template Preview - Compact version in sidebar -->
        @if(isset($templates) && $templates->isNotEmpty())
            <x-card class="shadow-lg border border-gray-200" x-data="templatePreview()" @load-template-preview.window="loadPreview($event.detail.templateId)">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                    <div class="p-1.5 bg-green-100 rounded-lg">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Template Preview</h2>
                </div>
                <p class="text-sm text-gray-600 mb-4">See how your invoice will look</p>
                
                <!-- Preview Loading State -->
                <div x-show="loading" class="text-center py-12" x-cloak>
                    <div class="inline-flex flex-col items-center gap-2 text-blue-600">
                        <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-medium">Loading...</span>
                    </div>
                </div>

                <!-- Preview Error State -->
                <div x-show="error" class="bg-red-50 border-2 border-red-200 rounded-lg p-3" x-cloak>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-xs text-red-800 font-medium" x-text="error"></p>
                    </div>
                </div>

                <!-- Preview Content - Compact with proper scaling -->
                <div x-show="!loading && !error && previewHtml" class="border-2 border-gray-200 rounded-lg overflow-hidden bg-white shadow-inner" x-cloak>
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-3 py-2 border-b border-gray-200">
                        <p class="text-xs font-semibold text-gray-700">
                            <span class="text-blue-600" x-text="templateName"></span>
                        </p>
                    </div>
                    <div class="preview-container" style="max-height: 500px; overflow: auto;">
                        <div class="preview-content" style="transform: scale(0.7); transform-origin: top left; width: 142.86%; padding: 8px;" x-html="previewHtml"></div>
                    </div>
                </div>

                <!-- Initial State -->
                <div x-show="!loading && !error && !previewHtml" class="text-center py-10 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                    <svg class="mx-auto h-10 w-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-xs text-gray-500 font-medium mb-1">No preview</p>
                    <p class="text-xs text-gray-400">Select a template to preview</p>
                </div>
            </x-card>
        @endif

        <!-- Quick Links -->
        <x-card class="shadow-lg border border-gray-200">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900">Quick Links</h2>
            </div>
            <div class="space-y-3">
                <a href="{{ route('user.invoices.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Back to Invoices</span>
                </a>
                <a href="{{ route('user.invoices.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Create New Invoice</span>
                </a>
            </div>
        </x-card>
    </div>
</div>

@php
    $templateId = isset($selectedTemplate) && $selectedTemplate ? $selectedTemplate->id : null;
@endphp

<script>
function invoiceFormatForm(company, formatPatterns) {
    return {
        formData: {
            prefix: company.invoice_prefix || 'INV',
            suffix: company.invoice_suffix || '',
            padding: company.invoice_padding || 4,
            format: company.invoice_format || '{PREFIX}-{NUMBER}'
        },
        preview: '',
        
        init() {
            this.updatePreview();
        },
        
        updatePreview() {
            const paddedNumber = String(1).padStart(parseInt(this.formData.padding), '0');
            const year = new Date().getFullYear();
            
            let preview = this.formData.format;
            preview = preview.replace('{PREFIX}', this.formData.prefix || 'INV');
            preview = preview.replace('{NUMBER}', paddedNumber);
            preview = preview.replace('{YEAR}', year);
            preview = preview.replace('{SUFFIX}', this.formData.suffix || '');
            
            this.preview = preview;
        }
    }
}

function templatePreview() {
    var INITIAL_TEMPLATE_ID = <?php echo json_encode($templateId); ?>;
    
    return {
        loading: false,
        error: null,
        previewHtml: null,
        templateName: null,
        currentTemplateId: INITIAL_TEMPLATE_ID,

        init() {
            // Load preview for initially selected template
            if (this.currentTemplateId) {
                this.loadPreview({ templateId: this.currentTemplateId });
            }
        },

        async loadPreview(detail) {
            const templateId = detail.templateId;
            
            if (!templateId) {
                this.error = 'No template selected';
                return;
            }

            this.loading = true;
            this.error = null;
            this.previewHtml = null;
            this.templateName = null;

            try {
                const response = await fetch(`{{ route('user.company.invoice-template.preview') }}?template_id=${templateId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ error: 'Failed to load preview' }));
                    throw new Error(errorData.error || 'Failed to load preview');
                }

                const data = await response.json();

                if (data.success && data.html) {
                    this.previewHtml = data.html;
                    this.templateName = data.template?.name || 'Template';
                } else {
                    throw new Error(data.error || 'Failed to generate preview');
                }
            } catch (error) {
                console.error('Error loading preview:', error);
                this.error = error.message || 'Failed to load preview. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
