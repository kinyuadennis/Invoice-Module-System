@props(['clients'])

<div 
    x-data="invoicePreview()" 
    class="w-full max-w-4xl mx-auto"
>
    <!-- Step Indicator -->
    <div class="flex items-center justify-center mb-8">
        <div class="flex items-center space-x-4">
            <!-- Step 1 -->
            <div class="flex items-center">
                <div 
                    :class="currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'"
                    class="flex items-center justify-center w-10 h-10 rounded-full font-semibold transition-all duration-300"
                >
                    <span x-show="currentStep > 1">✓</span>
                    <span x-show="currentStep <= 1">1</span>
                </div>
                <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:block">Client</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200">
                <div 
                    :class="currentStep >= 2 ? 'bg-indigo-600' : 'bg-gray-200'"
                    class="h-full transition-all duration-300"
                    :style="'width: ' + (currentStep >= 2 ? '100' : '0') + '%'"
                ></div>
            </div>
            
            <!-- Step 2 -->
            <div class="flex items-center">
                <div 
                    :class="currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'"
                    class="flex items-center justify-center w-10 h-10 rounded-full font-semibold transition-all duration-300"
                >
                    <span x-show="currentStep > 2">✓</span>
                    <span x-show="currentStep <= 2">2</span>
                </div>
                <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:block">Items</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200">
                <div 
                    :class="currentStep >= 3 ? 'bg-indigo-600' : 'bg-gray-200'"
                    class="h-full transition-all duration-300"
                    :style="'width: ' + (currentStep >= 3 ? '100' : '0') + '%'"
                ></div>
            </div>
            
            <!-- Step 3 -->
            <div class="flex items-center">
                <div 
                    :class="currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'"
                    class="flex items-center justify-center w-10 h-10 rounded-full font-semibold transition-all duration-300"
                >
                    3
                </div>
                <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:block">Preview</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left: Form Steps -->
        <div class="space-y-6">
            <!-- Step 1: Client Selection -->
            <div 
                x-show="currentStep === 1"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                class="backdrop-blur-md bg-white/80 rounded-3xl p-6 shadow-2xl border border-white/20"
            >
                <h3 class="text-xl font-bold text-gray-900 mb-4">Select Client</h3>
                <select 
                    x-model="selectedClient"
                    @change="currentStep = 2"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 bg-white"
                >
                    <option value="">Choose a client...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                    @endforeach
                    <option value="new">+ Add New Client</option>
                </select>
                <p class="mt-3 text-sm text-gray-600">
                    <span x-show="selectedClient">Selected: <strong x-text="getClientName(selectedClient)"></strong></span>
                    <span x-show="!selectedClient">Select a client to continue</span>
                </p>
            </div>

            <!-- Step 2: Line Items -->
            <div 
                x-show="currentStep === 2"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                class="backdrop-blur-md bg-white/80 rounded-3xl p-6 shadow-2xl border border-white/20"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Add Line Items</h3>
                    <button 
                        @click="addItem()"
                        type="button"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
                    >
                        + Add Item
                    </button>
                </div>
                
                <div class="space-y-4" x-ref="itemsContainer">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex gap-3">
                            <input 
                                type="text"
                                x-model="item.description"
                                placeholder="Description"
                                @input="calculateTotals()"
                                class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 bg-white"
                            />
                            <input 
                                type="number"
                                x-model.number="item.amount"
                                placeholder="Amount"
                                step="0.01"
                                min="0"
                                @input="calculateTotals()"
                                class="w-32 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 bg-white"
                            />
                            <button 
                                @click="removeItem(index)"
                                type="button"
                                class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            >
                                ✕
                            </button>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex gap-3">
                    <button 
                        @click="currentStep = 1"
                        type="button"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Back
                    </button>
                    <button 
                        @click="currentStep = 3"
                        :disabled="items.length === 0 || items.every(i => !i.description || !i.amount)"
                        type="button"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Continue to Preview
                    </button>
                </div>
            </div>

            <!-- Step 3: Preview Actions -->
            <div 
                x-show="currentStep === 3"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                class="backdrop-blur-md bg-white/80 rounded-3xl p-6 shadow-2xl border border-white/20"
            >
                <h3 class="text-xl font-bold text-gray-900 mb-4">Ready to Create?</h3>
                <p class="text-gray-600 mb-6">
                    Review your invoice preview on the right. Click below to create your free account and generate this invoice.
                </p>
                <div class="flex gap-3">
                    <button 
                        @click="currentStep = 2"
                        type="button"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Edit Items
                    </button>
                    <a 
                        href="{{ route('register') }}"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-center font-medium"
                    >
                        Create Free Account →
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Live Preview Card -->
        <div class="lg:sticky lg:top-24 h-fit">
            <div class="backdrop-blur-md bg-white/90 rounded-3xl p-8 shadow-2xl border border-white/20">
                <div class="text-center mb-6">
                    <h4 class="text-2xl font-bold text-gray-900">Invoice Preview</h4>
                    <p class="text-sm text-gray-500 mt-1">#INV-<span x-text="String(Math.floor(Math.random() * 99999) + 10000).padStart(5, '0')"></span></p>
                </div>

                <!-- Client Info -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <p class="text-sm text-gray-500 mb-1">Client</p>
                    <p class="text-lg font-semibold text-gray-900" x-text="getClientName(selectedClient) || 'Select a client'"></p>
                </div>

                <!-- Items List -->
                <div class="mb-6 space-y-3" x-show="items.length > 0">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex justify-between items-start" x-show="item.description && item.amount">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900" x-text="item.description"></p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 ml-4" x-text="formatCurrency(item.amount)"></p>
                        </div>
                    </template>
                </div>
                <div class="mb-6 text-center text-gray-400 text-sm" x-show="items.length === 0 || !items.some(i => i.description && i.amount)">
                    No items added yet
                </div>

                <!-- Totals -->
                <div class="space-y-2 pt-6 border-t border-gray-200" x-show="totals.subtotal > 0">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-900 font-medium" x-text="formatCurrency(totals.subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">VAT (16%)</span>
                        <span class="text-gray-900 font-medium" x-text="formatCurrency(totals.tax)"></span>
                    </div>
                    <div class="flex justify-between text-sm items-center">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-600">Platform Fee (0.8%)</span>
                            <span 
                                class="text-xs text-indigo-600 cursor-help"
                                title="Platform fee supports service maintenance"
                            >
                                ℹ️
                            </span>
                        </div>
                        <span class="text-gray-900 font-medium" x-text="formatCurrency(totals.platform_fee)"></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 mt-2">
                        <span class="text-gray-900">Total</span>
                        <span class="text-indigo-600" x-text="formatCurrency(totals.total)"></span>
                    </div>
                </div>

                <!-- Platform Fee Badge -->
                <div 
                    x-show="totals.platform_fee > 0"
                    class="mt-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200"
                >
                    <p class="text-sm text-indigo-900 text-center">
                        <span class="font-semibold">Only <span x-text="formatCurrency(totals.platform_fee)"></span> platform fee</span>
                        <span class="block text-xs text-indigo-700 mt-1">Charged only when payment is received</span>
                    </p>
                </div>

                <!-- CTA Button -->
                <a 
                    href="{{ route('register') }}"
                    class="mt-6 block w-full px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 text-center font-semibold shadow-lg hover:shadow-xl"
                    x-show="totals.total > 0"
                >
                    Send & Get Paid →
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function invoicePreview() {
    return {
        currentStep: 1,
        selectedClient: '',
        items: [
            { description: '', amount: 0 },
            { description: '', amount: 0 }
        ],
        totals: {
            subtotal: 0,
            tax: 0,
            platform_fee: 0,
            total: 0
        },
        clients: @json($clients),
        
        init() {
            this.calculateTotals();
        },
        
        getClientName(clientId) {
            if (!clientId || clientId === 'new') return 'New Client';
            const client = this.clients.find(c => c.id == clientId);
            return client ? client.name : 'Unknown Client';
        },
        
        addItem() {
            this.items.push({ description: '', amount: 0 });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },
        
        calculateTotals() {
            // Calculate subtotal
            this.totals.subtotal = this.items
                .filter(item => item.description && item.amount)
                .reduce((sum, item) => sum + parseFloat(item.amount || 0), 0);
            
            // Calculate tax (16% VAT)
            this.totals.tax = this.totals.subtotal * 0.16;
            
            // Calculate total before platform fee
            const totalBeforeFee = this.totals.subtotal + this.totals.tax;
            
            // Calculate platform fee (0.8%)
            this.totals.platform_fee = totalBeforeFee * 0.008;
            
            // Calculate final total
            this.totals.total = totalBeforeFee + this.totals.platform_fee;
            
            // Round all values
            this.totals.subtotal = Math.round(this.totals.subtotal * 100) / 100;
            this.totals.tax = Math.round(this.totals.tax * 100) / 100;
            this.totals.platform_fee = Math.round(this.totals.platform_fee * 100) / 100;
            this.totals.total = Math.round(this.totals.total * 100) / 100;
        },
        
        formatCurrency(amount) {
            return 'KSh ' + parseFloat(amount || 0).toLocaleString('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }
}
</script>

