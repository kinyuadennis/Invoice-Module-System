@props(['clients', 'services', 'company', 'nextInvoiceNumber'])

<div 
    x-data="onePageInvoiceBuilder({{ json_encode($clients) }}, {{ json_encode($services) }}, {{ json_encode($company) }}, {{ json_encode($nextInvoiceNumber) }})"
    class="space-y-6"
    @keydown.ctrl.s.prevent="saveDraft()"
    @keydown.meta.s.prevent="saveDraft()"
>
    <!-- Enhanced Header with Quick Actions -->
    <div class="flex items-center justify-between mb-6 bg-white rounded-lg border border-gray-200 shadow-sm p-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">New Invoice</h1>
            <p class="text-sm text-gray-500 mt-1">Create and send professional invoices in minutes</p>
        </div>
        <div class="flex items-center gap-4">
            <!-- Enhanced Autosave Status -->
            <div class="flex items-center gap-2">
                <span x-show="autosaveStatus === 'saving'" class="text-sm text-gray-500 flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-lg">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                    Saving draft...
            </span>
                <span x-show="autosaveStatus === 'saved'" class="text-sm text-green-600 flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-lg">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Draft saved
                </span>
                <span x-show="autosaveStatus === 'idle' && draftId" class="text-xs text-gray-400 px-3 py-1.5">
                    Draft #<span x-text="draftId"></span>
                </span>
            </div>
            
            <!-- Quick Actions Toolbar -->
            <div class="flex items-center gap-2 border-l border-gray-200 pl-4">
                <button 
                    @click="openTemplateLibrary()" 
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    title="Load Template"
                >
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Templates
                </button>
                <button 
                    @click="saveDraft()" 
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    title="Save Draft (Ctrl+S / Cmd+S)"
                >
                    Save Draft
                </button>
                <button 
                    @click="showPreview = !showPreview; if (showPreview) updatePreview()" 
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    title="Toggle Preview (Ctrl+P / Cmd+P)"
                >
                    <span x-show="!showPreview">Preview</span>
                    <span x-show="showPreview">Hide Preview</span>
                </button>
                <button 
                    @click="openSaveTemplateModal()" 
                    class="px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                    title="Save as Template"
                >
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Template
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-6 space-y-6">
            <!-- Form Content -->

    <!-- Main Content Area -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-6 space-y-6">
            <!-- A. Client Section -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Name *</h2>
                
                <!-- Client Search/Select -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select or add a customer
                        <span class="text-xs text-gray-500 font-normal">(Optional for drafts, required when sending)</span>
                    </label>
                    <div class="relative" x-data="{ open: false }" @click.outside="showClientDropdown = false">
                        <input 
                            type="text"
                            x-model="clientSearch"
                            @input.debounce.300ms="searchClients()"
                            @focus="showClientDropdown = true; if (clientSearchResults.length === 0) searchClients();"
                            @click="showClientDropdown = true; if (clientSearchResults.length === 0) searchClients();"
                            placeholder="Search clients or create new..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <div 
                            x-show="showClientDropdown && (clientSearchResults.length > 0 || clientSearch.length >= 0)"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            x-cloak
                        >
                            <template x-for="client in clientSearchResults" :key="client.id">
                                <div 
                                    @click.stop="selectClient(client); showClientDropdown = false"
                                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0"
                                >
                                    <div class="font-medium" x-text="client ? client.name : ''"></div>
                                    <div class="text-sm text-gray-500" x-text="client ? (client.email || client.phone || '') : ''"></div>
                                </div>
                            </template>
                            <div 
                                @click.stop="$dispatch('open-create-client-modal'); showClientDropdown = false"
                                class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-blue-600 font-medium border-t border-gray-200"
                            >
                                + Create New Client
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Client Info -->
                <div x-show="formData.client != null" class="p-4 bg-gray-50 rounded-lg" x-cloak>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium">Name:</span>
                            <span x-text="formData.client ? formData.client.name : 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-medium">Email:</span>
                            <span x-text="formData.client ? (formData.client.email || 'N/A') : 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-medium">Phone:</span>
                            <span x-text="formData.client ? (formData.client.phone || 'N/A') : 'N/A'"></span>
                        </div>
                        <div>
                            <span class="font-medium">Address:</span>
                            <span x-text="formData.client ? (formData.client.address || 'N/A') : 'N/A'"></span>
                        </div>
                    </div>
                    <button 
                        @click="formData.client = null; formData.client_id = null; refreshInvoiceNumber()"
                        class="mt-2 text-sm text-red-600 hover:text-red-700"
                    >
                        Change Client
                    </button>
                </div>
            </div>

            <!-- B. Invoice Metadata -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date *</label>
                        <input 
                            type="date"
                            x-model="formData.issue_date"
                            @change="if (showPreview) updatePreview()"
                            :value="formData.issue_date || '{{ date('Y-m-d') }}'"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
                        <input 
                            type="date"
                            x-model="formData.due_date"
                            @change="triggerAutosave(); if (showPreview) updatePreview()"
                            :min="formData.issue_date"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Invoice# *</label>
                            <button 
                                type="button"
                                @click="$dispatch('open-invoice-config')"
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                            >
                                Configure
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                            <input 
                                type="text"
                                x-model="formData.invoice_number"
                                :placeholder="!formData.invoice_number ? (formData.client_id ? 'Generating invoice number...' : '{{ $nextInvoiceNumber }}') : ''"
                                readonly
                                    class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm pr-10"
                            >
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" x-show="formData.invoice_number">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <button 
                                type="button"
                                @click="$dispatch('open-invoice-config')"
                                class="px-3 py-2 text-gray-600 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                title="Configure Invoice Number"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-1 flex items-center justify-between">
                            <p class="text-xs text-gray-500">
                                <span x-show="formData.invoice_number" class="text-green-600 font-medium">Preview: <span x-text="formData.invoice_number"></span></span>
                                <span x-show="!formData.invoice_number" class="text-gray-500">Auto-generated when finalized</span>
                            </p>
                            <button 
                                type="button"
                                @click="refreshInvoiceNumber()"
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                                title="Refresh invoice number"
                            >
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                        <input 
                            type="text"
                            x-model="formData.po_number"
                            @change="if (showPreview) updatePreview()"
                            placeholder="Optional"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div class="col-span-2">
                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox"
                                x-model="formData.vat_registered"
                                @change="if (showPreview) updatePreview()"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm font-medium text-gray-700">VAT Registered Business</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- C. Line Items Table -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Item Table</h2>
                    <button 
                        @click="addLineItem()"
                        class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                    >
                        + Add Item
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-8"></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Line Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(item, index) in formData.items" :key="index">
                                <tr 
                                    draggable="true"
                                    @dragstart="draggedItemIndex = index"
                                    @dragover.prevent="handleDragOver($event, index)"
                                    @drop.prevent="handleDrop($event, index)"
                                    @dragend="draggedItemIndex = null"
                                    :class="{ 'opacity-50': draggedItemIndex === index }"
                                    class="cursor-move hover:bg-gray-50 transition-colors"
                                >
                                    <td class="px-4 py-3 text-gray-400 cursor-grab active:cursor-grabbing">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                        </svg>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <input 
                                                type="text"
                                                x-model="item.description"
                                                @input.debounce.300ms="searchItems(index, $event.target.value); calculateItemTotal(index); if (showPreview) updatePreview()"
                                                @focus="if (item.description && item.description.length >= 2) searchItems(index, item.description)"
                                                @click.outside="itemSearchResults = { ...itemSearchResults, [index]: [] }"
                                                placeholder="Item description"
                                                class="w-full border-0 focus:ring-0 p-0 text-sm"
                                                required
                                            >
                                            <!-- Autocomplete Dropdown -->
                                            <div 
                                                x-show="getItemSearchResults(index).length > 0"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                                x-cloak
                                            >
                                                <template x-for="suggestion in getItemSearchResults(index)" :key="suggestion.id">
                                                    <div 
                                                        @click.stop="selectItem(index, suggestion)"
                                                        class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0"
                                                    >
                                                        <div class="font-medium" x-text="suggestion.name"></div>
                                                        <div class="text-sm text-gray-500" x-text="formatCurrency(suggestion.unit_price)"></div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div 
                                                x-show="isItemSearchLoading(index)"
                                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4 text-center text-sm text-gray-500"
                                                x-cloak
                                            >
                                                Searching...
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="number"
                                            x-model="item.quantity"
                                            @input="calculateItemTotal(index); triggerAutosave(); if (showPreview) updatePreview()"
                                            min="0.01"
                                            step="0.01"
                                            class="w-20 border-0 focus:ring-0 p-0 text-sm text-right"
                                            required
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="number"
                                            x-model="item.unit_price"
                                            @input="calculateItemTotal(index); triggerAutosave(); if (showPreview) updatePreview()"
                                            min="0"
                                            step="0.01"
                                            class="w-24 border-0 focus:ring-0 p-0 text-sm text-right"
                                            required
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium" x-text="formatCurrency(item.total)"></td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button 
                                                @click="duplicateItem(index)"
                                                class="text-blue-600 hover:text-blue-700 text-sm"
                                                title="Duplicate"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                            <button 
                                                @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-700 text-sm"
                                                title="Delete"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p x-show="formData.items.length === 0" class="text-center text-gray-500 py-4">No items added yet. Click "Add Item" to get started.</p>
            </div>

            <!-- D. Totals & Tax Summary -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Summary</h2>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium" x-text="formatCurrency(totals.subtotal)"></span>
                    </div>
                    <div x-show="totals.discount > 0" class="flex justify-between text-sm" x-cloak>
                        <span class="text-gray-600">Discount</span>
                        <span class="font-medium text-red-600" x-text="'-' + formatCurrency(totals.discount)"></span>
                    </div>
                    <div x-show="formData.vat_registered" class="flex justify-between text-sm" x-cloak>
                        <span class="text-gray-600">VAT (16%)</span>
                        <span class="font-medium" x-text="formatCurrency(totals.vat_amount)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Platform Fee (3%)</span>
                        <span class="font-medium" x-text="formatCurrency(totals.platform_fee)"></span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 flex justify-between text-lg font-bold">
                        <span>Total Payable</span>
                        <span class="text-blue-600" x-text="formatCurrency(totals.grand_total)"></span>
                    </div>
                </div>
            </div>

            <!-- E. Notes & Terms -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes to Client</label>
                        <textarea 
                            x-model="formData.notes"
                            @change="if (showPreview) updatePreview()"
                            rows="3"
                            placeholder="Any additional notes or terms..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                        <textarea 
                            x-model="formData.terms_and_conditions"
                            @change="if (showPreview) updatePreview()"
                            rows="3"
                            placeholder="Payment terms, conditions, etc..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button 
                    @click="togglePreview()" 
                    :disabled="processing || !canShowPreview()"
                    class="px-4 py-2 border border-blue-300 bg-white text-blue-600 rounded-lg hover:bg-blue-50 font-medium disabled:opacity-50 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span x-text="showPreview ? 'Hide Preview' : 'Preview Invoice'"></span>
                </button>
                <button 
                    @click="saveDraft()" 
                    :disabled="processing"
                    class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 font-medium disabled:opacity-50"
                >
                    Save as Draft
                </button>
                <div class="relative">
                    <button 
                        @click="sendAndMarkSent()" 
                        :disabled="processing || !canFinalize()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50"
                    >
                        Save and Send
                    </button>
                </div>
                <a 
                    href="{{ route('user.invoices.index') }}"
                    class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium"
                >
                    Cancel
                </a>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-sm">
                    <span class="text-gray-600">Total Quantity:</span>
                    <span class="font-semibold text-gray-900" x-text="formData.items.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0), 0)"></span>
                </div>
                <div class="text-lg">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-bold text-gray-900" x-text="formatCurrency(totals.grand_total)"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Preview Section -->
    <div class="mt-6" x-show="showPreview" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Invoice Preview</h2>
                <div class="flex items-center gap-2">
                    <span x-show="previewLoading" class="text-sm text-gray-500 flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Generating preview...
                    </span>
                </div>
            </div>
            <div class="p-4">
                <div class="border border-gray-200 rounded-lg bg-white overflow-hidden" style="max-height: 800px; overflow-y: auto;">
                    <!-- Use iframe for preview to ensure proper template rendering -->
                    <iframe 
                        x-show="showPreview && previewFrameUrl"
                        :src="previewFrameUrl"
                        class="w-full border-0"
                        style="min-height: 600px; width: 100%;"
                        x-cloak
                    ></iframe>
                    <div x-show="showPreview && !previewFrameUrl && !previewLoading" class="text-center py-8 text-gray-500" x-cloak>
                        <p>Add items to see preview</p>
                    </div>
                    <div x-show="previewLoading" class="text-center py-8" x-cloak>
                        <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Generating preview...</p>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 text-center">Preview uses your selected invoice template</p>
            </div>
        </div>
    </div>

    <!-- Create Client Modal -->
    <x-client-create-modal />
    
    <!-- Invoice Number Configuration Modal -->
    <x-invoice-number-config-modal :company="$company" />
    
    <!-- Listen for client created event -->
    <div @client-created.window="selectClient($event.detail)"></div>
    
    <!-- Listen for invoice config updates -->
    <div @invoice-config-updated.window="refreshInvoiceNumber()"></div>
    
    <!-- Template Library Modal -->
    <div 
        x-show="showTemplateLibrary" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.away="showTemplateLibrary = false"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showTemplateLibrary = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Invoice Templates</h3>
                        <button @click="showTemplateLibrary = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                        <template x-for="template in templates" :key="template.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-medium text-gray-900" x-text="template.name"></h4>
                                    <button 
                                        @click="toggleFavorite(template.id)"
                                        class="text-yellow-400 hover:text-yellow-500"
                                        :class="{ 'text-yellow-500': template.is_favorite }"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-sm text-gray-500 mb-3" x-text="template.description || 'No description'"></p>
                                <div class="flex items-center justify-between text-xs text-gray-400 mb-3">
                                    <span>Used <span x-text="template.usage_count"></span> times</span>
                                    <span x-text="template.last_used_at ? new Date(template.last_used_at).toLocaleDateString() : 'Never'"></span>
                                </div>
                                <div class="flex gap-2">
                                    <button 
                                        @click="loadTemplate(template.id)"
                                        class="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                                    >
                                        Load
                                    </button>
                                    <button 
                                        @click="deleteTemplate(template.id)"
                                        class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div x-show="templates.length === 0" class="col-span-full text-center py-8 text-gray-500">
                            No templates saved yet. Create one by clicking "Save Template" in the toolbar.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function onePageInvoiceBuilder(clients, services, company, nextInvoiceNumber) {
    return {
        clients: clients,
        services: services,
        company: company,
        formData: {
            client_id: null,
            client: null,
            issue_date: new Date().toISOString().split('T')[0],
            due_date: null,
            invoice_number: (nextInvoiceNumber && nextInvoiceNumber !== 'Select a client to see invoice number') ? nextInvoiceNumber : '',
            po_number: '',
            vat_registered: false,
            items: [{ description: '', quantity: 1, unit_price: 0, total: 0 }],
            notes: '',
            terms_and_conditions: '',
            discount: 0,
            discount_type: 'fixed',
        },
        clientSearch: '',
        clientSearchResults: [],
        showClientDropdown: false,
        showCreateClientModal: false,
        itemSearchResults: {}, // Object to store search results per item index
        itemSearchLoading: {}, // Object to store loading state per item index
        previewFrameUrl: null, // URL for iframe preview
        previewLoading: false,
        showPreview: false, // Controls preview visibility
        totals: {
            subtotal: 0,
            discount: 0,
            vat_amount: 0,
            platform_fee: 0,
            total: 0,
            grand_total: 0,
        },
        processing: false,
        autosaveStatus: 'idle', // 'idle', 'saving', 'saved'
        draftId: null,
        autosaveInterval: null,
        draggedItemIndex: null, // For drag and drop reordering
        showTemplateLibrary: false, // Template library modal visibility
        templates: [], // Array of saved templates
        showSaveTemplateModal: false, // Save template modal visibility
        
        getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : null;
        },

        // Helper to safely get search results for an index
        getItemSearchResults(index) {
            return this.itemSearchResults[index] || [];
        },

        // Helper to check if loading for an index
        isItemSearchLoading(index) {
            return this.itemSearchLoading[index] || false;
        },

        init() {
            // Don't auto-update preview on init - wait for user to click preview button
            // this.updatePreview();
            this.startAutosave();
            this.refreshInvoiceNumber();
            
            // Load all clients on init for dropdown
            this.searchClients();
            
            // Listen for invoice config updates
            window.addEventListener('invoice-config-updated', (e) => {
                if (e.detail) {
                    if (e.detail.next_invoice_number) {
                        // Use the new invoice number directly if provided
                        this.formData.invoice_number = e.detail.next_invoice_number;
                        if (this.showPreview) {
                            this.updatePreview();
                        }
                    } else {
                        // Otherwise refresh from server
                        this.refreshInvoiceNumber();
                    }
                }
            });
        },

        startAutosave() {
            // Periodic autosave (every 30 seconds as backup)
            this.autosaveInterval = setInterval(() => {
                if (this.formData.items.length > 0 && this.formData.items[0].description) {
                    this.autosave();
                }
            }, 30000); // Every 30 seconds
            
            // Enhanced: Autosave on field changes (debounced)
            // This is handled by @input.debounce on individual fields
        },
        
        // Debounced autosave trigger (called from field changes)
        triggerAutosave() {
            // Clear any existing timeout
            if (this.autosaveTimeout) {
                clearTimeout(this.autosaveTimeout);
            }
            
            // Set new timeout for debounced autosave (2 seconds after last change)
            this.autosaveTimeout = setTimeout(() => {
                if (this.formData.items.length > 0 && this.formData.items[0].description) {
                    this.autosave();
                }
            }, 2000);
        },

        async searchClients() {
            // Show all clients if search is empty, otherwise filter
            try {
                const csrfToken = this.getCsrfToken();
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }
                
                const searchQuery = this.clientSearch.length >= 2 ? this.clientSearch : '';
                const response = await fetch(`{{ route('user.clients.search') }}?q=${encodeURIComponent(searchQuery)}`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.clientSearchResults = data.clients;
                }
            } catch (error) {
                console.error('Error searching clients:', error);
            }
        },

        selectClient(client) {
            if (!client) return;
            this.formData.client = client;
            this.formData.client_id = client.id;
            this.clientSearch = client.name;
            this.showClientDropdown = false;
            
            // Auto-generate invoice number when client is selected
            this.refreshInvoiceNumber();
            
            if (this.showPreview) {
                this.updatePreview();
            }
        },

        async searchItems(index, query) {
            if (!query || query.length < 2) {
                // Use direct assignment for Alpine.js reactivity
                this.itemSearchResults = { ...this.itemSearchResults, [index]: [] };
                return;
            }

            try {
                // Use direct assignment for Alpine.js reactivity
                this.itemSearchLoading = { ...this.itemSearchLoading, [index]: true };
                const csrfToken = this.getCsrfToken();
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }

                const response = await fetch(`{{ route('user.items.search') }}?query=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Search failed');
                }

                const data = await response.json();
                // Use direct assignment for Alpine.js reactivity
                this.itemSearchResults = { ...this.itemSearchResults, [index]: data.items || [] };
            } catch (error) {
                console.error('Error searching items:', error);
                this.itemSearchResults = { ...this.itemSearchResults, [index]: [] };
            } finally {
                this.itemSearchLoading = { ...this.itemSearchLoading, [index]: false };
            }
        },

        selectItem(index, item) {
            if (!item) return;
            this.formData.items[index].description = item.name;
            this.formData.items[index].unit_price = item.unit_price;
            // Clear search results for this index
            this.itemSearchResults = { ...this.itemSearchResults, [index]: [] };
            this.calculateItemTotal(index);
            // Only update preview if it's currently visible
            if (this.showPreview) {
                this.updatePreview();
            }
        },

        togglePreview() {
            this.showPreview = !this.showPreview;
            // If showing preview and we don't have preview URL yet, generate it
            if (this.showPreview && !this.previewFrameUrl) {
                this.updatePreview();
            }
        },

        canShowPreview() {
            // Can show preview if there's at least one item with description
            return this.formData.items.length > 0 && 
                   this.formData.items.some(item => item.description && item.description.trim() !== '');
        },

        addLineItem() {
            this.formData.items.push({ description: '', quantity: 1, unit_price: 0, total: 0 });
        },

        removeItem(index) {
            this.formData.items.splice(index, 1);
            if (this.formData.items.length === 0) {
                this.addLineItem();
            }
            this.calculateTotals();
            if (this.showPreview) {
                this.updatePreview();
            }
        },

        duplicateItem(index) {
            const item = { ...this.formData.items[index] };
            this.formData.items.splice(index + 1, 0, item);
            this.calculateTotals();
            if (this.showPreview) {
                this.updatePreview();
            }
        },

        calculateItemTotal(index) {
            const item = this.formData.items[index];
            item.total = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            this.calculateTotals();
        },

        calculateTotals() {
            let subtotal = 0;
            this.formData.items.forEach(item => {
                subtotal += item.total || 0;
            });

            // Apply discount
            let discount = 0;
            if (this.formData.discount > 0) {
                if (this.formData.discount_type === 'percentage') {
                    discount = subtotal * (this.formData.discount / 100);
                } else {
                    discount = this.formData.discount;
                }
            }
            const subtotalAfterDiscount = Math.max(0, subtotal - discount);

            // Calculate VAT
            let vatAmount = 0;
            if (this.formData.vat_registered) {
                vatAmount = subtotalAfterDiscount * 0.16;
            }

            const totalBeforeFee = subtotalAfterDiscount + vatAmount;
            const platformFee = totalBeforeFee * 0.03;
            const grandTotal = totalBeforeFee + platformFee;

            this.totals = {
                subtotal,
                discount,
                vat_amount: vatAmount,
                platform_fee: platformFee,
                total: totalBeforeFee,
                grand_total: grandTotal,
            };
        },

        async updatePreview() {
            this.calculateTotals();

            if (this.formData.items.length === 0 || !this.formData.items[0].description) {
                this.previewFrameUrl = null;
                return;
            }

            this.previewLoading = true;
            try {
                // Build URL with query parameters for iframe preview
                const params = new URLSearchParams();
                
                // Add client data
                if (this.formData.client_id) {
                    params.append('client_id', this.formData.client_id);
                }
                if (this.formData.client && typeof this.formData.client === 'object') {
                    params.append('client[name]', this.formData.client.name || '');
                    params.append('client[email]', this.formData.client.email || '');
                    params.append('client[phone]', this.formData.client.phone || '');
                    params.append('client[address]', this.formData.client.address || '');
                    params.append('client[kra_pin]', this.formData.client.kra_pin || '');
                }
                
                // Add invoice details
                if (this.formData.issue_date) {
                    params.append('issue_date', this.formData.issue_date);
                }
                if (this.formData.due_date) {
                    params.append('due_date', this.formData.due_date);
                }
                if (this.formData.invoice_number) {
                    params.append('invoice_number', this.formData.invoice_number);
                }
                if (this.formData.po_number) {
                    params.append('po_number', this.formData.po_number);
                }
                
                // Add items
                const validItems = this.formData.items.filter(item => item.description && item.description.trim() !== '');
                validItems.forEach((item, index) => {
                    params.append(`items[${index}][description]`, item.description || '');
                    params.append(`items[${index}][quantity]`, (item.quantity || 1).toString());
                    params.append(`items[${index}][unit_price]`, (item.unit_price || 0).toString());
                });
                
                // Add other fields
                if (this.formData.vat_registered) {
                    params.append('vat_registered', '1');
                }
                if (this.formData.discount) {
                    params.append('discount', this.formData.discount.toString());
                }
                if (this.formData.discount_type) {
                    params.append('discount_type', this.formData.discount_type);
                }
                if (this.formData.notes) {
                    params.append('notes', this.formData.notes);
                }
                if (this.formData.terms_and_conditions) {
                    params.append('terms_and_conditions', this.formData.terms_and_conditions);
                }
                if (this.formData.payment_method) {
                    params.append('payment_method', this.formData.payment_method);
                }
                if (this.formData.payment_details) {
                    params.append('payment_details', this.formData.payment_details);
                }
                
                // Build iframe URL
                const baseUrl = '{{ route("user.invoices.preview-frame") }}';
                this.previewFrameUrl = `${baseUrl}?${params.toString()}`;
                
            } catch (error) {
                console.error('Error building preview URL:', error);
                this.previewFrameUrl = null;
            } finally {
                this.previewLoading = false;
            }
        },

        async autosave() {
            if (this.autosaveStatus === 'saving') return;
            
            // Don't autosave if there's no meaningful data (items are required, client is optional for drafts)
            if (!this.formData.items || this.formData.items.length === 0 || !this.formData.items[0].description) {
                return;
            }

            // Get CSRF token with null check
            const csrfToken = this.getCsrfToken();
            if (!csrfToken) {
                console.error('CSRF token not found. Cannot autosave.');
                return;
            }

            this.autosaveStatus = 'saving';
            try {
                const response = await fetch('{{ route("user.invoices.autosave") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        draft_id: this.draftId,
                        client_id: this.formData.client_id,
                        issue_date: this.formData.issue_date,
                        due_date: this.formData.due_date,
                        po_number: this.formData.po_number,
                        vat_registered: this.formData.vat_registered,
                        discount: this.formData.discount,
                        discount_type: this.formData.discount_type,
                        notes: this.formData.notes,
                        terms_and_conditions: this.formData.terms_and_conditions,
                        items: this.formData.items.filter(item => item.description && item.description.trim() !== '')
                    })
                });

                if (!response.ok) {
                    // Handle CSRF token mismatch specifically
                    if (response.status === 419) {
                        console.error('CSRF token mismatch. Page may need to be refreshed.');
                        // Optionally refresh the page or show a message
                        // window.location.reload();
                        this.autosaveStatus = 'idle';
                        return;
                    }
                    
                    const errorData = await response.json().catch(() => ({ error: 'Unknown error' }));
                    console.error('Autosave error:', errorData);
                    this.autosaveStatus = 'idle';
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    this.draftId = data.draft_id;
                    this.autosaveStatus = 'saved';
                    setTimeout(() => { this.autosaveStatus = 'idle'; }, 2000);
                } else {
                    console.error('Autosave failed:', data.error || 'Unknown error');
                    this.autosaveStatus = 'idle';
                }
            } catch (error) {
                console.error('Error autosaving:', error);
                this.autosaveStatus = 'idle';
            }
        },

        async saveDraft() {
            // Client is optional for drafts, but items are required
            if (!this.formData.items || this.formData.items.length === 0 || !this.formData.items[0].description) {
                alert('Please add at least one line item to save as draft');
                return;
            }
            
            // Warn if no client selected (but allow saving)
            if (!this.formData.client_id) {
                if (!confirm('No client selected. You can save as draft, but a client will be required before sending the invoice. Continue?')) {
                    return;
                }
            }

            this.processing = true;
            try {
                await this.autosave();
                if (this.autosaveStatus === 'saved') {
                    alert('Draft saved successfully!');
                }
            } catch (error) {
                console.error('Error saving draft:', error);
                alert('Failed to save draft. Please try again.');
            } finally {
                this.processing = false;
            }
        },

        async sendAndMarkSent() {
            if (!this.canFinalize()) return;

            this.processing = true;
            try {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("user.invoices.store") }}';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                const csrfToken = this.getCsrfToken();
                if (csrfToken) {
                    csrf.value = csrfToken;
                }
                form.appendChild(csrf);

                // Add all form data
                Object.keys(this.formData).forEach(key => {
                    if (key === 'items') {
                        this.formData.items.forEach((item, index) => {
                            Object.keys(item).forEach(itemKey => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = `items[${index}][${itemKey}]`;
                                input.value = item[itemKey];
                                form.appendChild(input);
                            });
                        });
                    } else if (key !== 'client') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = this.formData[key];
                        form.appendChild(input);
                    }
                });

                // Set status to sent
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = 'sent';
                form.appendChild(statusInput);

                document.body.appendChild(form);
                form.submit();
            } catch (error) {
                console.error('Error sending invoice:', error);
                this.processing = false;
            }
        },

        canFinalize() {
            // Client is required for finalizing (sending invoice)
            return this.formData.client_id && 
                   this.formData.issue_date && 
                   this.formData.due_date && 
                   this.formData.items.length > 0 &&
                   this.formData.items.every(item => item.description && item.quantity > 0 && item.unit_price > 0);
        },

        downloadPdf() {
            // This will be handled after invoice is created
            alert('PDF download will be available after saving the invoice');
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: 'KES',
                minimumFractionDigits: 2
            }).format(amount || 0);
        },

        async refreshInvoiceNumber() {
            try {
                // Build URL with client_id if available
                let url = '{{ route("user.invoices.create") }}';
                if (this.formData.client_id) {
                    url += '?client_id=' + this.formData.client_id;
                }
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.next_invoice_number) {
                        this.formData.invoice_number = data.next_invoice_number;
                        if (this.showPreview) {
                            this.updatePreview();
                        }
                    }
                }
            } catch (error) {
                console.error('Error refreshing invoice number:', error);
            }
        },
        
        // Drag and Drop handlers
        handleDragOver(event, index) {
            if (this.draggedItemIndex === null || this.draggedItemIndex === index) return;
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        },
        
        handleDrop(event, targetIndex) {
            event.preventDefault();
            if (this.draggedItemIndex === null || this.draggedItemIndex === targetIndex) return;
            
            // Reorder items
            const items = [...this.formData.items];
            const draggedItem = items[this.draggedItemIndex];
            items.splice(this.draggedItemIndex, 1);
            items.splice(targetIndex, 0, draggedItem);
            this.formData.items = items;
            
            // Trigger autosave and recalculate
            this.calculateTotals();
            this.triggerAutosave();
            if (this.showPreview) this.updatePreview();
            
            this.draggedItemIndex = null;
        },
        
        // Template functions
        async openTemplateLibrary() {
            this.showTemplateLibrary = true;
            await this.loadTemplates();
        },
        
        async loadTemplates() {
            try {
                const csrfToken = this.getCsrfToken();
                const response = await fetch('{{ route("user.invoices.templates") }}', {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.templates = data.templates || [];
                }
            } catch (error) {
                console.error('Error loading templates:', error);
            }
        },
        
        async loadTemplate(templateId) {
            try {
                const csrfToken = this.getCsrfToken();
                const response = await fetch(`{{ route("user.invoices.templates.load", ":id") }}`.replace(':id', templateId), {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.template_data) {
                        // Load template data into form
                        if (data.template_data.items) {
                            this.formData.items = data.template_data.items;
                        }
                        if (data.template_data.client_id) {
                            this.formData.client_id = data.template_data.client_id;
                            // Find and select client
                            const client = this.clients.find(c => c.id === data.template_data.client_id);
                            if (client) this.selectClient(client);
                        }
                        if (data.template_data.notes) {
                            this.formData.notes = data.template_data.notes;
                        }
                        if (data.template_data.terms_and_conditions) {
                            this.formData.terms_and_conditions = data.template_data.terms_and_conditions;
                        }
                        if (data.template_data.po_number) {
                            this.formData.po_number = data.template_data.po_number;
                        }
                        if (data.template_data.vat_registered !== undefined) {
                            this.formData.vat_registered = data.template_data.vat_registered;
                        }
                        
                        // Recalculate totals
                        this.calculateTotals();
                        this.showTemplateLibrary = false;
                    }
                }
            } catch (error) {
                console.error('Error loading template:', error);
                alert('Failed to load template. Please try again.');
            }
        },
        
        openSaveTemplateModal() {
            this.showSaveTemplateModal = true;
        },
        
        async saveAsTemplate() {
            const name = prompt('Enter template name:');
            if (!name || !name.trim()) return;
            
            const description = prompt('Enter template description (optional):') || '';
            
            try {
                const csrfToken = this.getCsrfToken();
                const response = await fetch('{{ route("user.invoices.save-as-template") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: name.trim(),
                        description: description.trim(),
                        template_data: {
                            items: this.formData.items.filter(item => item.description && item.description.trim() !== ''),
                            client_id: this.formData.client_id,
                            notes: this.formData.notes,
                            terms_and_conditions: this.formData.terms_and_conditions,
                            po_number: this.formData.po_number,
                            vat_registered: this.formData.vat_registered,
                        },
                    }),
                });
                
                if (response.ok) {
                    const data = await response.json();
                    alert('Template saved successfully!');
                    this.showSaveTemplateModal = false;
                    await this.loadTemplates();
                } else {
                    const error = await response.json();
                    alert('Failed to save template: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving template:', error);
                alert('Failed to save template. Please try again.');
            }
        },
        
        async deleteTemplate(templateId) {
            if (!confirm('Are you sure you want to delete this template?')) return;
            
            try {
                const csrfToken = this.getCsrfToken();
                const response = await fetch(`{{ route("user.invoices.templates.delete", ":id") }}`.replace(':id', templateId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                
                if (response.ok) {
                    await this.loadTemplates();
                }
            } catch (error) {
                console.error('Error deleting template:', error);
                alert('Failed to delete template. Please try again.');
            }
        },
        
        async toggleFavorite(templateId) {
            try {
                const csrfToken = this.getCsrfToken();
                const response = await fetch(`{{ route("user.invoices.templates.toggle-favorite", ":id") }}`.replace(':id', templateId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                
                if (response.ok) {
                    await this.loadTemplates();
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
        }
    }
}
</script>

