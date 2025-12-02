@extends('layouts.user')

@section('title', 'Invoice Customization')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Invoice Customization</h1>
        <p class="mt-1 text-sm text-gray-600">Customize your invoice numbering format and visual template</p>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Number Format Section -->
            <x-card>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Invoice Number Format</h2>
                
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
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="INV"
                                />
                                <p class="mt-1 text-xs text-gray-500">e.g., INV, HUB, ACME</p>
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
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="KE"
                                />
                                <p class="mt-1 text-xs text-gray-500">e.g., KE, 2025</p>
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
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <label class="block text-sm font-medium text-blue-900 mb-2">Preview</label>
                            <div class="text-2xl font-bold text-blue-600" x-text="preview"></div>
                            <p class="mt-2 text-xs text-blue-700">This is how your next invoice number will look</p>
                        </div>

                        <div>
                            <x-button type="submit" variant="primary">
                                Save Format Settings
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-card>

            <!-- Invoice Template Selection Section -->
            <x-card>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Invoice Template</h2>
                
                <p class="text-sm text-gray-600 mb-6">Choose a visual template for your invoices. This will be applied to all future invoices and PDFs.</p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach($templates as $key => $template)
                        <div 
                            class="relative border-2 rounded-lg p-4 transition-all {{ $company->invoice_template === $key ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            @if($company->invoice_template === $key)
                                <div class="absolute top-2 right-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white">
                                        Active
                                    </span>
                                </div>
                            @endif
                            
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $template['name'] }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ $template['description'] }}</p>
                            
                            <form method="POST" action="{{ route('user.company.update-invoice-template') }}" class="inline">
                                @csrf
                                <input type="hidden" name="invoice_template" value="{{ $key }}">
                                <x-button 
                                    type="submit" 
                                    variant="{{ $company->invoice_template === $key ? 'primary' : 'default' }}"
                                    class="w-full"
                                >
                                    {{ $company->invoice_template === $key ? 'Currently Active' : 'Use This Template' }}
                                </x-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Card -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">About Invoice Customization</h2>
                <div class="space-y-3 text-sm text-gray-600">
                    <p><strong>Invoice Number Format:</strong> Customize how your invoice numbers are generated. The format will be applied automatically when creating new invoices.</p>
                    <p><strong>Invoice Template:</strong> Choose a visual style for your invoices. The selected template will be used for both online viewing and PDF generation.</p>
                    <p><strong>Changes:</strong> Format and template changes apply to all future invoices. Existing invoices will not be affected.</p>
                </div>
            </x-card>

            <!-- Quick Links -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
                <div class="space-y-2">
                    <a href="{{ route('user.company.settings') }}" class="block text-sm text-blue-600 hover:text-blue-700">
                        ← Back to Company Settings
                    </a>
                    <a href="{{ route('user.invoices.create') }}" class="block text-sm text-blue-600 hover:text-blue-700">
                        Create New Invoice →
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>

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
</script>
@endsection

