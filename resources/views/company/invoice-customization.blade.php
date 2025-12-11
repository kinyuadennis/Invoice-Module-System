@extends('layouts.user')

@section('title', 'Invoice Customization')

@section('content')
<div 
    x-data="invoiceCustomizationManager({{ json_encode($company) }}, {{ json_encode($templates) }}, {{ json_encode($selectedTemplate) }}, {{ json_encode($formatPatterns) }})"
    class="min-h-screen"
>
    <!-- Header -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 sm:p-8 text-white shadow-xl">
            <h1 class="text-2xl sm:text-3xl font-bold mb-2">Invoice Customization</h1>
            <p class="text-blue-100 text-base sm:text-lg">Customize your invoice numbering format, branding, and visual template</p>
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

    <!-- Two-Column Layout Container -->
    <div class="w-full max-w-[1400px] mx-auto">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-8">
            <!-- LEFT COLUMN - SETTINGS PANEL -->
            <div class="flex-1 lg:max-w-[520px] xl:max-w-[550px] space-y-6">
                <!-- Invoice Numbering Section -->
                <x-card class="shadow-lg border border-gray-200" padding="lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Invoice Numbering</h2>
                            <p class="text-sm text-gray-500 mt-0.5">Configure invoice number format</p>
                        </div>
                    </div>
                    <button 
                        @click="resetNumberingDefaults()"
                        class="text-xs text-blue-600 hover:text-blue-700 font-medium transition-colors"
                    >
                        Reset
                    </button>
                </div>

                <form method="POST" action="{{ route('user.company.update-invoice-format') }}" @submit.prevent="saveNumberingSettings()">
                    @csrf
                    
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="invoice_prefix" class="block text-sm font-medium text-gray-700 mb-2">
                                    Prefix
                                </label>
                                <input
                                    type="text"
                                    id="invoice_prefix"
                                    name="invoice_prefix"
                                    x-model="settings.numbering.prefix"
                                    @input="updatePreview()"
                                    maxlength="20"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                                    placeholder="INV"
                                />
                                <p class="mt-1.5 text-xs text-gray-500">e.g., INV, HUB</p>
                            </div>

                            <div>
                                <label for="invoice_suffix" class="block text-sm font-medium text-gray-700 mb-2">
                                    Suffix (Optional)
                                </label>
                                <input
                                    type="text"
                                    id="invoice_suffix"
                                    name="invoice_suffix"
                                    x-model="settings.numbering.suffix"
                                    @input="updatePreview()"
                                    maxlength="20"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                                    placeholder="KE"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="invoice_padding" class="block text-sm font-medium text-gray-700 mb-2">
                                Number Padding
                            </label>
                            <select
                                id="invoice_padding"
                                name="invoice_padding"
                                x-model="settings.numbering.padding"
                                @change="updatePreview()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                            >
                                <option value="3">3 digits (001)</option>
                                <option value="4">4 digits (0001)</option>
                                <option value="5">5 digits (00001)</option>
                                <option value="6">6 digits (000001)</option>
                            </select>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                            <label class="block text-sm font-semibold text-blue-900 mb-2">Next Invoice #:</label>
                            <div class="text-2xl font-bold text-blue-600" x-text="numberingPreview"></div>
                        </div>

                        <x-button type="submit" variant="primary" class="w-full">
                            Save Numbering Settings
                        </x-button>
                    </div>
                </form>
            </x-card>

            <!-- Client-Specific Numbering Section -->
            <x-card class="shadow-lg border border-gray-200" padding="lg">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2.5 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Client-Specific Numbering</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Enable per-client sequences</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.company.update-invoice-format') }}" @submit.prevent="saveClientNumbering()">
                    @csrf
                    
                    <div class="space-y-6">
                        <div class="flex items-center justify-between p-5 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label for="use_client_specific_numbering" class="block text-sm font-semibold text-gray-900 mb-1">
                                    Enable Client-Specific Numbering
                                </label>
                                <p class="text-xs text-gray-600">Each client has independent sequence</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    id="use_client_specific_numbering"
                                    name="use_client_specific_numbering"
                                    x-model="settings.clientNumbering.enabled"
                                    @change="updatePreview()"
                                    value="1"
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div x-show="settings.clientNumbering.enabled" x-transition class="space-y-4">
                            <div>
                                <label for="client_invoice_format" class="block text-sm font-medium text-gray-700 mb-2">
                                    Format Pattern
                                </label>
                                <select
                                    id="client_invoice_format"
                                    name="client_invoice_format"
                                    x-model="settings.clientNumbering.format"
                                    @change="updatePreview()"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                                >
                                    <option value="{PREFIX}-{CLIENTSEQ}">INV-001</option>
                                    <option value="{PREFIX}-{CLIENTSEQ}-{YEAR}">INV-001-2025</option>
                                    <option value="{YEAR}/{CLIENTSEQ}">2025/001</option>
                                    <option value="{PREFIX}/{CLIENTSEQ}">INV/001</option>
                                </select>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                                <p class="text-sm font-semibold text-blue-900 mb-2">Example:</p>
                                <ul class="text-xs text-blue-800 space-y-1 list-disc list-inside">
                                    <li>Client A: INV-001, INV-002, INV-003</li>
                                    <li>Client B: INV-001, INV-002 (independent)</li>
                                </ul>
                            </div>
                        </div>

                        <x-button type="submit" variant="primary" class="w-full">
                            Save Client Numbering
                        </x-button>
                    </div>
                </form>
            </x-card>

            <!-- Branding & Template Settings -->
            <x-card class="shadow-lg border border-gray-200" padding="lg">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2.5 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Branding & Template</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Customize colors and fonts</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.company.update-branding') }}" @submit.prevent="saveBranding()" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">
                            Primary Color
                        </label>
                        <div class="flex gap-3">
                            <input
                                type="color"
                                x-model="settings.branding.primaryColor"
                                @input="updatePreview()"
                                class="h-11 w-20 rounded-lg border border-gray-300 cursor-pointer"
                            />
                            <input
                                type="text"
                                id="primary_color"
                                name="primary_color"
                                x-model="settings.branding.primaryColor"
                                @input="updatePreview()"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                                placeholder="#2B6EF6"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-2">
                            Secondary Color (Optional)
                        </label>
                        <div class="flex gap-3">
                            <input
                                type="color"
                                x-model="settings.branding.secondaryColor"
                                @input="updatePreview()"
                                class="h-11 w-20 rounded-lg border border-gray-300 cursor-pointer"
                            />
                            <input
                                type="text"
                                id="secondary_color"
                                name="secondary_color"
                                x-model="settings.branding.secondaryColor"
                                @input="updatePreview()"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                                placeholder="#7C3AED"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="font_family" class="block text-sm font-medium text-gray-700 mb-2">
                            Font Family
                        </label>
                        <select
                            id="font_family"
                            name="font_family"
                            x-model="settings.branding.fontFamily"
                            @change="updatePreview()"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                        >
                            <option value="Inter">Inter</option>
                            <option value="Roboto">Roboto</option>
                            <option value="Open Sans">Open Sans</option>
                            <option value="System">System</option>
                        </select>
                    </div>

                    <x-button type="submit" variant="primary" class="w-full">
                        Save Branding
                    </x-button>
                </form>
            </x-card>

            <!-- Template Selection Grid -->
            @if(isset($templates) && $templates->isNotEmpty())
                <x-card class="shadow-lg border border-gray-200" padding="lg">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2.5 bg-indigo-100 rounded-lg">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Template Selection</h2>
                            <p class="text-sm text-gray-500 mt-0.5">Choose your invoice layout</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($templates as $template)
                            <button
                                @click="selectTemplate({{ $template->id }})"
                                :class="settings.templateId === {{ $template->id }} 
                                    ? 'border-2 border-blue-500 bg-blue-50 ring-2 ring-blue-200' 
                                    : 'border border-gray-200 hover:border-blue-300'"
                                class="p-5 rounded-lg text-left transition-all cursor-pointer bg-white hover:shadow-md"
                            >
                                <div class="font-semibold text-gray-900 mb-2">{{ $template->name }}</div>
                                @if($template->description)
                                    <p class="text-xs text-gray-600 mb-3 leading-relaxed">{{ Str::limit($template->description, 60) }}</p>
                                @endif
                                <div class="text-xs text-gray-500">Prefix: <strong class="text-gray-700">{{ $template->prefix }}</strong></div>
                            </button>
                        @endforeach
                    </div>
                </x-card>
            @endif

            <!-- Advanced Styling (Collapsible) -->
            <x-card class="shadow-lg border border-gray-200" padding="lg">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Advanced Styling</h2>
                            <p class="text-sm text-gray-500 mt-0.5">Fine-tune layout and appearance</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            x-model="settings.advancedStyling.enabled"
                            @change="updatePreview()"
                            class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div x-show="settings.advancedStyling.enabled" x-transition class="space-y-6">
                    <form method="POST" action="{{ route('user.company.update-advanced-styling') }}" @submit.prevent="saveAdvancedStyling()">
                        @csrf
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Table Border Style</label>
                                <select
                                    x-model="settings.advancedStyling.tableBorders"
                                    @change="updatePreview()"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"
                                >
                                    <option value="none">None</option>
                                    <option value="thin">Thin</option>
                                    <option value="medium">Medium</option>
                                    <option value="thick">Thick</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Custom CSS</label>
                                <textarea
                                    x-model="settings.advancedStyling.customCss"
                                    @input="updatePreview()"
                                    rows="6"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs px-4 py-3"
                                    placeholder="/* Your custom CSS here */"
                                ></textarea>
                            </div>

                            <x-button type="submit" variant="primary" class="w-full">
                                Save Advanced Settings
                            </x-button>
                        </div>
                    </form>
                </div>
            </x-card>
        </div>

        <!-- RIGHT COLUMN - LIVE PREVIEW -->
        <div class="flex-1 lg:min-w-[600px] lg:max-w-[700px] xl:max-w-[800px]">
            <x-card class="shadow-lg border border-gray-200 sticky top-6" padding="lg">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Live Preview</h2>
                        <div class="flex items-center gap-3">
                        <!-- View Tabs -->
                        <div class="flex gap-1 bg-gray-100 rounded-lg p-1">
                            <button
                                @click="previewView = 'desktop'"
                                :class="previewView === 'desktop' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'"
                                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all"
                            >
                                Desktop
                            </button>
                            <button
                                @click="previewView = 'mobile'"
                                :class="previewView === 'mobile' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'"
                                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all"
                            >
                                Mobile
                            </button>
                            <button
                                @click="previewView = 'print'"
                                :class="previewView === 'print' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'"
                                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all"
                            >
                                Print
                            </button>
                        </div>
                        <!-- Zoom Controls -->
                        <div class="flex items-center gap-1 bg-gray-100 rounded-lg px-2 py-1">
                            <button
                                @click="previewZoom = Math.max(50, previewZoom - 25)"
                                class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-200 rounded transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span class="text-xs text-gray-600 min-w-[3rem] text-center font-medium" x-text="previewZoom + '%'"></span>
                            <button
                                @click="previewZoom = Math.min(200, previewZoom + 25)"
                                class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-200 rounded transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Preview Container -->
                <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="min-height: 600px;">
                    <div x-show="previewLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 z-10 rounded-lg">
                        <div class="text-center">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-gray-600 font-medium">Loading preview...</p>
                        </div>
                    </div>

                    <div 
                        x-show="!previewLoading && previewHtml"
                        class="overflow-auto bg-white rounded-lg"
                        :style="`max-height: 800px; transform: scale(${previewZoom / 100}); transform-origin: top left; width: ${100 / (previewZoom / 100)}%`"
                    >
                        <iframe
                            :srcdoc="previewHtml"
                            class="w-full border-0"
                            style="min-height: 800px;"
                        ></iframe>
                    </div>

                    <div x-show="!previewLoading && !previewHtml" class="flex items-center justify-center h-96 text-gray-400">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm font-medium">Select a template to preview</p>
                        </div>
                    </div>
                </div>
            </x-card>
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
        previewView: 'desktop',
        previewZoom: 100,
        previewLoading: false,
        previewHtml: null,
        numberingPreview: '',
        previewDebounceTimer: null,

        init() {
            this.updateNumberingPreview();
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
            
            // Debounce preview updates
            clearTimeout(this.previewDebounceTimer);
            this.previewDebounceTimer = setTimeout(() => {
                this.loadPreview();
            }, 300);
        },

        async loadPreview() {
            if (!this.settings.templateId) {
                console.warn('No template selected, skipping preview');
                this.previewHtml = null;
                return;
            }

            this.previewLoading = true;
            this.previewHtml = null;

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
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || data.message || `HTTP ${response.status}: ${response.statusText}`);
                }

                if (data.success && data.html) {
                    this.previewHtml = data.html;
                } else {
                    throw new Error(data.error || data.message || 'Failed to generate preview');
                }
            } catch (error) {
                console.error('Preview error:', error);
                // Don't show alert, just log - the UI will show the error state
                this.previewHtml = null;
            } finally {
                this.previewLoading = false;
            }
        },

        async selectTemplate(templateId) {
            this.settings.templateId = templateId;
            await this.updatePreview();
            
            // Save template selection
            try {
                const response = await fetch('{{ route("user.company.update-invoice-template") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({ invoice_template_id: templateId }),
                });
            } catch (error) {
                console.error('Error saving template:', error);
            }
        },

        async saveNumberingSettings() {
            const form = event.target.closest('form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                const contentType = response.headers.get('content-type');
                if (response.ok && contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'success',
                                message: data.message || 'Settings saved successfully!',
                            }
                        }));
                    }
                } else {
                    const text = await response.text();
                    throw new Error(text || 'Failed to save settings');
                }
            } catch (error) {
                console.error('Error saving:', error);
                if (window.dispatchEvent) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'error',
                            message: error.message || 'Error saving settings. Please try again.',
                        }
                    }));
                }
            }
        },

        async saveClientNumbering() {
            const form = event.target.closest('form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                const contentType = response.headers.get('content-type');
                if (response.ok && contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'success',
                                message: data.message || 'Settings saved successfully!',
                            }
                        }));
                    }
                } else {
                    const text = await response.text();
                    throw new Error(text || 'Failed to save settings');
                }
            } catch (error) {
                console.error('Error saving:', error);
                if (window.dispatchEvent) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'error',
                            message: error.message || 'Error saving settings. Please try again.',
                        }
                    }));
                }
            }
        },

        async saveBranding() {
            const form = event.target.closest('form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                const contentType = response.headers.get('content-type');
                if (response.ok && contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'success',
                                message: data.message || 'Settings saved successfully!',
                            }
                        }));
                    }
                } else {
                    const text = await response.text();
                    throw new Error(text || 'Failed to save settings');
                }
            } catch (error) {
                console.error('Error saving:', error);
                if (window.dispatchEvent) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'error',
                            message: error.message || 'Error saving settings. Please try again.',
                        }
                    }));
                }
            }
        },

        async saveAdvancedStyling() {
            const form = event.target.closest('form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                const contentType = response.headers.get('content-type');
                if (response.ok && contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'success',
                                message: data.message || 'Settings saved successfully!',
                            }
                        }));
                    }
                } else {
                    const text = await response.text();
                    throw new Error(text || 'Failed to save settings');
                }
            } catch (error) {
                console.error('Error saving:', error);
                if (window.dispatchEvent) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'error',
                            message: error.message || 'Error saving settings. Please try again.',
                        }
                    }));
                }
            }
        },

        resetNumberingDefaults() {
            this.settings.numbering = {
                prefix: 'INV',
                suffix: '',
                padding: 3,
                format: '{PREFIX}-{NUMBER}'
            };
            this.updatePreview();
        }
    }
}
</script>
@endsection
