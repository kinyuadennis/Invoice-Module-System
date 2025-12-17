@extends('layouts.user')

@section('title', 'Invoice Customization')

@section('content')
<div
    x-data="invoiceCustomizationManager({{ json_encode($company) }}, {{ json_encode($templates) }}, {{ json_encode($selectedTemplate) }}, {{ json_encode($formatPatterns) }})"
    class="min-h-screen pb-12">
    <!-- Header -->
    <div class="mb-8 border-b border-gray-200 pb-6">
        <h1 class="text-2xl font-bold text-gray-900">Invoice Customization</h1>
        <p class="mt-2 text-gray-600">Design professional invoices that match your brand identity.</p>
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

    <!-- Two-Column Layout -->
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- LEFT COLUMN - SETTINGS -->
        <div class="flex-1 lg:max-w-[500px] xl:max-w-[550px] space-y-8">

            <!-- 1. Branding Section -->
            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                        Branding & Style
                    </h2>
                </div>

                <div class="p-6 space-y-6">
                    <form method="POST" action="{{ route('user.company.update-branding') }}" @submit.prevent="saveBranding()">
                        @csrf

                        <!-- Quick Palettes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Quick Palettes</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="preset in presets" :key="preset.name">
                                    <button
                                        type="button"
                                        @click="applyPreset(preset)"
                                        class="w-8 h-8 rounded-full border border-gray-200 shadow-sm hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        :style="`background: linear-gradient(135deg, ${preset.primary} 50%, ${preset.secondary} 50%)`"
                                        :title="preset.name"></button>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="primary_color" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                                    Primary Color
                                </label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        x-model="settings.branding.primaryColor"
                                        @input="updatePreview()"
                                        class="h-9 w-9 rounded border border-gray-300 cursor-pointer p-0.5" />
                                    <input
                                        type="text"
                                        id="primary_color"
                                        name="primary_color"
                                        x-model="settings.branding.primaryColor"
                                        @input="updatePreview()"
                                        class="flex-1 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                            </div>
                            <div>
                                <label for="secondary_color" class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                                    Secondary Color
                                </label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        x-model="settings.branding.secondaryColor"
                                        @input="updatePreview()"
                                        class="h-9 w-9 rounded border border-gray-300 cursor-pointer p-0.5" />
                                    <input
                                        type="text"
                                        id="secondary_color"
                                        name="secondary_color"
                                        x-model="settings.branding.secondaryColor"
                                        @input="updatePreview()"
                                        class="flex-1 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="font_family" class="block text-sm font-medium text-gray-700 mb-2">
                                Typography
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <template x-for="font in fonts" :key="font">
                                    <button
                                        type="button"
                                        @click="settings.branding.fontFamily = font; updatePreview()"
                                        :class="settings.branding.fontFamily === font ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-700'"
                                        class="px-4 py-2 border rounded-lg text-sm transition-colors text-left"
                                        :style="`font-family: ${font}, sans-serif`">
                                        <span x-text="font"></span>
                                        <span class="block text-xs opacity-60">123.45</span>
                                    </button>
                                </template>
                            </div>
                            <!-- Hidden select for form submission -->
                            <input type="hidden" name="font_family" x-model="settings.branding.fontFamily">
                        </div>

                        <div class="flex justify-end">
                            <x-button type="submit" variant="secondary" size="sm">
                                Save Branding
                            </x-button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- 2. Template Selection -->
            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                        Template Layout
                    </h2>
                </div>

                @if(isset($templates) && $templates->isNotEmpty())
                <div class="p-6 grid grid-cols-2 gap-4">
                    @foreach($templates as $template)
                    <button
                        @click="selectTemplate({{ $template->id }})"
                        :class="settings.templateId === {{ $template->id }} 
                                    ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50/50' 
                                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                        class="relative p-4 rounded-xl border text-left transition-all group">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-900">{{ $template->name }}</span>
                            <div x-show="settings.templateId === {{ $template->id }}" class="text-blue-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div class="space-y-1.5 opacity-60">
                            <div class="h-2 w-3/4 bg-gray-300 rounded-full"></div>
                            <div class="h-2 w-1/2 bg-gray-300 rounded-full"></div>
                            <div class="mt-2 h-8 w-full border border-gray-200 rounded"></div>
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif
            </section>

            <!-- 3. Numbering Settings -->
            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                        </svg>
                        Invoice Numbering
                    </h2>
                    <button @click="resetNumberingDefaults()" class="text-xs text-gray-500 hover:text-gray-900 font-medium">Reset</button>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('user.company.update-invoice-format') }}" @submit.prevent="saveNumberingSettings()">
                        @csrf
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Prefix</label>
                                <input type="text" name="invoice_prefix" x-model="settings.numbering.prefix" @input="updatePreview()" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="INV">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Padding</label>
                                <select name="invoice_padding" x-model="settings.numbering.padding" @change="updatePreview()" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="3">001</option>
                                    <option value="4">0001</option>
                                    <option value="5">00001</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Suffix</label>
                                <input type="text" name="invoice_suffix" x-model="settings.numbering.suffix" @input="updatePreview()" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="">
                            </div>
                        </div>

                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200 mb-4">
                            <span class="text-sm text-gray-600">Next Invoice:</span>
                            <span class="font-mono font-bold text-gray-900" x-text="numberingPreview"></span>
                        </div>

                        <!-- Client Specific Toggle -->
                        <div class="border-t border-gray-100 pt-4 mt-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <label for="use_client_specific_numbering" class="text-sm font-medium text-gray-900">Client-Specific Sequences</label>
                                    <p class="text-xs text-gray-500">Maintain separate counters for each client</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="use_client_specific_numbering" name="use_client_specific_numbering" x-model="settings.clientNumbering.enabled" @change="updatePreview()" value="1" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div x-show="settings.clientNumbering.enabled" x-transition>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Format Pattern</label>
                                <select name="client_invoice_format" x-model="settings.clientNumbering.format" @change="updatePreview()" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="{PREFIX}-{CLIENTSEQ}">INV-001</option>
                                    <option value="{PREFIX}-{CLIENTSEQ}-{YEAR}">INV-001-2025</option>
                                    <option value="{YEAR}/{CLIENTSEQ}">2025/001</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <x-button type="submit" variant="secondary" size="sm">Save Numbering</x-button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- 4. Advanced Styling -->
            <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between cursor-pointer" @click="settings.advancedStyling.enabled = !settings.advancedStyling.enabled">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        Advanced Styling
                    </h2>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="settings.advancedStyling.enabled ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <div x-show="settings.advancedStyling.enabled" x-transition class="p-6 border-t border-gray-100">
                    <form method="POST" action="{{ route('user.company.update-advanced-styling') }}" @submit.prevent="saveAdvancedStyling()">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Table Borders</label>
                                <select x-model="settings.advancedStyling.tableBorders" @change="updatePreview()" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="none">None</option>
                                    <option value="thin">Thin</option>
                                    <option value="medium">Medium</option>
                                    <option value="thick">Thick</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Custom CSS</label>
                                <textarea x-model="settings.advancedStyling.customCss" @input="updatePreview()" rows="4" class="w-full rounded-md border-gray-300 text-xs font-mono focus:border-blue-500 focus:ring-blue-500" placeholder="/* Custom CSS */"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <x-button type="submit" variant="secondary" size="sm">Save Advanced</x-button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

        </div>

        <!-- RIGHT COLUMN - PREVIEW -->
        <div class="flex-1 lg:min-w-[600px]">
            <div class="sticky top-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                    <!-- Preview Toolbar -->
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">Live Preview</span>
                        <div class="flex items-center gap-2">
                            <div class="flex bg-white rounded-lg border border-gray-200 p-0.5">
                                <button @click="previewZoom = Math.max(50, previewZoom - 10)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg></button>
                                <span class="px-2 text-xs font-mono flex items-center text-gray-600" x-text="previewZoom + '%'"></span>
                                <button @click="previewZoom = Math.min(150, previewZoom + 10)" class="p-1 hover:bg-gray-100 rounded text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg></button>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Canvas -->
                    <div class="relative bg-gray-100/50 p-8 overflow-hidden flex justify-center" style="min-height: 800px;">

                        <!-- Loading State -->
                        <div x-show="previewLoading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 backdrop-blur-sm">
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-8 w-8 text-blue-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-600">Generating Preview...</span>
                            </div>
                        </div>

                        <!-- Iframe Container -->
                        <div
                            x-show="!previewLoading && previewHtml"
                            class="bg-white shadow-2xl transition-transform origin-top duration-200"
                            :style="`width: 210mm; min-height: 297mm; transform: scale(${previewZoom / 100}); margin-bottom: -${(100 - previewZoom) * 10}px`">
                            <iframe
                                :srcdoc="previewHtml"
                                class="w-full h-full border-0"
                                style="min-height: 297mm;"
                                scrolling="no"></iframe>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!previewLoading && !previewHtml" class="flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">Select a template to preview</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function invoiceCustomizationManager(company, templates, selectedTemplate, formatPatterns) {
        return {
            settings: {
                numbering: {
                    prefix: company.invoice_prefix || 'INV',
                    suffix: company.invoice_suffix || '',
                    padding: company.invoice_padding || 3,
                    format: company.invoice_format || '{PREFIX}-{NUMBER}'
                },
                clientNumbering: {
                    enabled: company.use_client_specific_numbering || false,
                    format: company.client_invoice_format || '{PREFIX}-{CLIENTSEQ}'
                },
                branding: {
                    primaryColor: company.settings?.branding?.primary_color || '#2B6EF6',
                    secondaryColor: company.settings?.branding?.secondary_color || '',
                    fontFamily: company.settings?.branding?.font_family || 'Inter'
                },
                templateId: selectedTemplate?.id || templates[0]?.id || null,
                advancedStyling: {
                    enabled: company.settings?.advanced_styling?.enabled || false,
                    tableBorders: company.settings?.advanced_styling?.table_borders || 'thin',
                    customCss: company.settings?.advanced_styling?.custom_css || ''
                }
            },

            // New Features
            presets: [{
                    name: 'Professional Blue',
                    primary: '#2B6EF6',
                    secondary: '#1E40AF'
                },
                {
                    name: 'Modern Dark',
                    primary: '#111827',
                    secondary: '#374151'
                },
                {
                    name: 'Nature Green',
                    primary: '#059669',
                    secondary: '#047857'
                },
                {
                    name: 'Warm Orange',
                    primary: '#EA580C',
                    secondary: '#C2410C'
                },
                {
                    name: 'Classic Red',
                    primary: '#DC2626',
                    secondary: '#991B1B'
                },
                {
                    name: 'Elegant Purple',
                    primary: '#7C3AED',
                    secondary: '#5B21B6'
                },
            ],
            fonts: ['Inter', 'Roboto', 'Open Sans', 'Helvetica', 'Georgia'],

            previewZoom: 85,
            previewLoading: false,
            previewHtml: null,
            numberingPreview: '',
            previewDebounceTimer: null,

            init() {
                this.updateNumberingPreview();
                this.updatePreview();
            },

            applyPreset(preset) {
                this.settings.branding.primaryColor = preset.primary;
                this.settings.branding.secondaryColor = preset.secondary;
                this.updatePreview();
            },

            updateNumberingPreview() {
                const paddedNumber = String(1).padStart(parseInt(this.settings.numbering.padding), '0');
                const year = new Date().getFullYear();

                let preview = this.settings.numbering.format;
                preview = preview.replace('{PREFIX}', this.settings.numbering.prefix || 'INV');
                preview = preview.replace('{NUMBER}', paddedNumber);
                preview = preview.replace('{YEAR}', year);
                preview = preview.replace('{SUFFIX}', this.settings.numbering.suffix || '');

                this.numberingPreview = preview;
            },

            updatePreview() {
                this.updateNumberingPreview();

                clearTimeout(this.previewDebounceTimer);
                this.previewDebounceTimer = setTimeout(() => {
                    this.loadPreview();
                }, 300);
            },

            async loadPreview() {
                if (!this.settings.templateId) return;

                this.previewLoading = true;

                try {
                    const params = new URLSearchParams({
                        template_id: this.settings.templateId,
                        branding: JSON.stringify({
                            primary_color: this.settings.branding.primaryColor,
                            secondary_color: this.settings.branding.secondaryColor,
                            font_family: this.settings.branding.fontFamily
                        }),
                        advanced_styling: JSON.stringify({
                            enabled: this.settings.advancedStyling.enabled,
                            table_borders: this.settings.advancedStyling.tableBorders,
                            custom_css: this.settings.advancedStyling.customCss
                        })
                    });

                    const response = await fetch(`{{ route('user.company.invoice-template.preview') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    if (data.success && data.html) {
                        this.previewHtml = data.html;
                    }
                } catch (error) {
                    console.error('Preview error:', error);
                } finally {
                    this.previewLoading = false;
                }
            },

            async selectTemplate(templateId) {
                this.settings.templateId = templateId;
                await this.updatePreview();

                // Auto-save template selection
                try {
                    await fetch('{{ route("user.company.update-invoice-template") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        },
                        body: JSON.stringify({
                            invoice_template_id: templateId
                        }),
                    });
                } catch (error) {
                    console.error('Error saving template:', error);
                }
            },

            async saveBranding() {
                await this.submitForm(event.target);
            },

            async saveNumberingSettings() {
                await this.submitForm(event.target);
            },

            async saveAdvancedStyling() {
                await this.submitForm(event.target);
            },

            async submitForm(form) {
                const formData = new FormData(form);
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'success',
                                message: data.message || 'Settings saved!'
                            }
                        }));
                    } else {
                        throw new Error(data.message || 'Failed to save');
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'error',
                            message: error.message
                        }
                    }));
                }
            }
        }
    }
</script>
@endsection