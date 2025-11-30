@props(['services'])

<div x-data="lineItemsEditor({{ json_encode($services) }})" class="space-y-4">
    <!-- Service Library Button -->
    <div class="flex justify-end">
        <x-service-library-dropdown :services="$services" />
    </div>

    <!-- Line Items List -->
    <div class="space-y-4">
        <template x-for="(item, index) in items" :key="index">
            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12 items-end">
                    <!-- Description -->
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-show="index === 0">Description *</label>
                            <input 
                                type="text" 
                                x-model="item.description"
                                :name="currentStep === 3 ? `items[${index}][description]` : ''"
                                placeholder="Item description"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                                :required="currentStep === 3"
                            >
                    </div>

                    <!-- Quantity -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-show="index === 0">Quantity *</label>
                            <input 
                                type="number" 
                                x-model.number="item.quantity"
                                :name="currentStep === 3 ? `items[${index}][quantity]` : ''"
                                @input="updateItemTotal(index)"
                                min="1"
                                step="1"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                                :required="currentStep === 3"
                            >
                    </div>

                    <!-- Unit Price -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-show="index === 0">Unit Price (KES) *</label>
                            <input 
                                type="number" 
                                x-model.number="item.unit_price"
                                :name="currentStep === 3 ? `items[${index}][unit_price]` : ''"
                                @input="updateItemTotal(index)"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                                :required="currentStep === 3"
                            >
                    </div>

                    <!-- VAT Toggle -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-show="index === 0">VAT (16%)</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                x-model="item.vat_included"
                                @change="updateItemTotal(index)"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="ml-3 text-sm text-gray-700" x-text="item.vat_included ? 'Included' : 'Excluded'"></span>
                        </label>
                    </div>

                    <!-- Total -->
                    <div class="md:col-span-1 text-right">
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-show="index === 0">Total</label>
                        <p class="text-base font-semibold text-gray-900 mt-2 md:mt-0" x-text="formatCurrency(item.total_price)"></p>
                    </div>
                </div>

                <!-- Remove Button -->
                <div class="mt-3 flex justify-end">
                    <button 
                        type="button" 
                        @click="removeItem(index)"
                        :disabled="items.length === 1"
                        class="text-red-600 hover:text-red-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Remove
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Add Custom Item Button -->
    <button
        type="button"
        @click="addCustomItem()"
        class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-700 hover:border-emerald-500 hover:text-emerald-600 transition-colors"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Custom Item
    </button>

    <!-- Error Display -->
    <div x-show="errors.items" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600" x-text="errors.items"></p>
    </div>
</div>

<script>
function lineItemsEditor(services) {
    return {
        items: [{
            description: '',
            quantity: 1,
            unit_price: 0,
            vat_included: true,
            total_price: 0
        }],
        errors: {},
        vatRate: 16,
        currentStep: 3,
        
        init() {
            // Get current step from wrapper div (provided by wizard)
            const wrapper = this.$el.closest('[x-data*="currentStep"]');
            if (wrapper && wrapper._x_dataStack && wrapper._x_dataStack[0]) {
                this.currentStep = wrapper._x_dataStack[0].currentStep || 3;
                // Watch for step changes by polling the wrapper
                const checkStep = () => {
                    if (wrapper._x_dataStack && wrapper._x_dataStack[0]) {
                        const newStep = wrapper._x_dataStack[0].currentStep;
                        if (newStep !== this.currentStep) {
                            this.currentStep = newStep;
                        }
                    }
                };
                // Check every 100ms for step changes
                setInterval(checkStep, 100);
            }
            // Listen for service selection
            this.$watch('$store.wizard.selectedService', (service) => {
                if (service) {
                    this.addService(service);
                }
            });
            
            // Listen for custom item addition
            window.addEventListener('service-selected', (e) => {
                this.addService(e.detail);
            });
            
            window.addEventListener('add-custom-item', () => {
                this.addCustomItem();
            });
            
            // Calculate initial totals
            this.items.forEach((item, index) => {
                this.updateItemTotal(index);
            });
        },
        
        addService(service) {
            this.items.push({
                description: service.description || '',
                quantity: service.quantity || 1,
                unit_price: service.unit_price || 0,
                vat_included: service.vat_included !== undefined ? service.vat_included : true,
                total_price: 0
            });
            const index = this.items.length - 1;
            this.updateItemTotal(index);
            this.$dispatch('items-updated', this.items);
        },
        
        addCustomItem() {
            this.items.push({
                description: '',
                quantity: 1,
                unit_price: 0,
                vat_included: true,
                total_price: 0
            });
            this.$dispatch('items-updated', this.items);
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.$dispatch('items-updated', this.items);
            }
        },
        
        updateItemTotal(index) {
            const item = this.items[index];
            const subtotal = (item.quantity || 0) * (item.unit_price || 0);
            
            if (item.vat_included) {
                // VAT is included in the price
                item.total_price = subtotal;
            } else {
                // VAT needs to be added
                const vat = subtotal * (this.vatRate / 100);
                item.total_price = subtotal + vat;
            }
            
            this.$dispatch('items-updated', this.items);
        },
        
        formatCurrency(amount) {
            return 'KES ' + new Intl.NumberFormat('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount || 0);
        }
    }
}
</script>

