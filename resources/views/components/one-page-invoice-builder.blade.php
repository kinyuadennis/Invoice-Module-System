@props(['clients', 'services', 'company', 'nextInvoiceNumber'])

<div 
    x-data="onePageInvoiceBuilder({{ json_encode($clients) }}, {{ json_encode($services) }}, {{ json_encode($company) }}, {{ json_encode($nextInvoiceNumber) }})"
    class="space-y-6"
    @keydown.ctrl.s.prevent="saveDraft()"
    @keydown.meta.s.prevent="saveDraft()"
>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">New Invoice</h1>
        </div>
        <div class="flex items-center gap-3">
            <span x-show="autosaveStatus === 'saving'" class="text-sm text-gray-500 flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            </span>
            <span x-show="autosaveStatus === 'saved'" class="text-sm text-green-600">✓ Saved</span>
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
                        @click="formData.client = null; formData.client_id = null"
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
                            @change="updatePreview()"
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
                            @change="updatePreview()"
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
                            <input 
                                type="text"
                                x-model="formData.invoice_number"
                                value="{{ $nextInvoiceNumber }}"
                                readonly
                                class="flex-1 rounded-lg border-gray-300 bg-gray-50 shadow-sm"
                            >
                            <button 
                                type="button"
                                @click="$dispatch('open-invoice-config')"
                                class="px-3 py-2 text-gray-600 hover:text-gray-700 border border-gray-300 rounded-lg"
                                title="Configure Invoice Number"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Auto-generated</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                        <input 
                            type="text"
                            x-model="formData.po_number"
                            @change="updatePreview()"
                            placeholder="Optional"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div class="col-span-2">
                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox"
                                x-model="formData.vat_registered"
                                @change="updatePreview()"
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(item, index) in formData.items" :key="index">
                                <tr>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="text"
                                            x-model="item.description"
                                            @input="calculateItemTotal(index); updatePreview()"
                                            placeholder="Item description"
                                            class="w-full border-0 focus:ring-0 p-0 text-sm"
                                            required
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="number"
                                            x-model="item.quantity"
                                            @input="calculateItemTotal(index); updatePreview()"
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
                                            @input="calculateItemTotal(index); updatePreview()"
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
                            @change="updatePreview()"
                            rows="3"
                            placeholder="Any additional notes or terms..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                        <textarea 
                            x-model="formData.terms_and_conditions"
                            @change="updatePreview()"
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

    <!-- Right Column: Live Preview (Optional - can be hidden on smaller screens) -->
    <div class="mt-6 lg:hidden">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Preview</h2>
                <button 
                    @click="downloadPdf()"
                    :disabled="!previewHtml"
                    class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 disabled:opacity-50"
                >
                    Download PDF
                </button>
            </div>
            <div class="border border-gray-200 rounded-lg p-4 bg-white" style="max-height: 600px; overflow-y: auto;">
                <div x-html="previewHtml || '<p class=\"text-gray-500 text-center py-8\">Add items to see preview</p>'"></div>
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
            invoice_number: nextInvoiceNumber,
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
        previewHtml: '',
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
        
        getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : null;
        },

        init() {
            this.updatePreview();
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
                        this.updatePreview();
                    } else {
                        // Otherwise refresh from server
                        this.refreshInvoiceNumber();
                    }
                }
            });
        },

        startAutosave() {
            this.autosaveInterval = setInterval(() => {
                if (this.formData.items.length > 0 && this.formData.items[0].description) {
                    this.autosave();
                }
            }, 15000); // Every 15 seconds
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
            this.updatePreview();
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
            this.updatePreview();
        },

        duplicateItem(index) {
            const item = { ...this.formData.items[index] };
            this.formData.items.splice(index + 1, 0, item);
            this.calculateTotals();
            this.updatePreview();
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
                this.previewHtml = '<p class="text-gray-500 text-center py-8">Add items to see preview</p>';
                return;
            }

            try {
                const csrfToken = this.getCsrfToken();
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }
                
                const response = await fetch('{{ route("user.invoices.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        client_id: this.formData.client_id,
                        client: this.formData.client,
                        issue_date: this.formData.issue_date,
                        due_date: this.formData.due_date,
                        invoice_number: this.formData.invoice_number,
                        items: this.formData.items.filter(item => item.description && item.description.trim() !== ''),
                        vat_registered: this.formData.vat_registered,
                        discount: this.formData.discount,
                        discount_type: this.formData.discount_type,
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Preview error:', errorText);
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    this.previewHtml = data.html;
                    // Update totals from server calculation
                    if (data.totals) {
                        this.totals = data.totals;
                    }
                } else {
                    console.error('Preview failed:', data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('Error updating preview:', error);
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
                const response = await fetch('{{ route("user.invoices.create") }}', {
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
                        this.updatePreview();
                    }
                }
            } catch (error) {
                console.error('Error refreshing invoice number:', error);
            }
        }
    }
}
</script>

