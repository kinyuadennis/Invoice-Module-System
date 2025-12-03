@props(['company'])

<div 
    x-data="paymentMethodModal({{ $company->id }})"
    x-show="open"
    @open-payment-method-modal.window="open = true; editingMethod = $event.detail || null; initForm()"
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    x-cloak
    style="display: none;"
>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="open = false"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        ></div>

        <!-- Modal panel -->
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
            @click.stop
        >
            <form @submit.prevent="savePaymentMethod()">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900" x-text="editingMethod ? 'Edit Payment Method' : 'Add Payment Method'"></h3>
                        <button 
                            type="button"
                            @click="open = false"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Payment Method Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method Type *</label>
                            <select 
                                x-model="form.type"
                                @change="updateFormFields()"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="">Select a payment method</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mpesa">MPesa</option>
                                <option value="paypal">PayPal</option>
                                <option value="stripe">Stripe</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        <!-- Custom Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Custom Name (Optional)</label>
                            <input 
                                type="text"
                                x-model="form.name"
                                placeholder="e.g., Main Bank Account"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div x-show="form.type === 'bank_transfer'" class="space-y-3 border-t pt-4">
                            <h4 class="font-medium text-gray-900">Bank Transfer Details</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                                    <input 
                                        type="text"
                                        x-model="form.bank_name"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                    <input 
                                        type="text"
                                        x-model="form.account_name"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number *</label>
                                    <input 
                                        type="text"
                                        x-model="form.account_number"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SWIFT Code</label>
                                    <input 
                                        type="text"
                                        x-model="form.swift_code"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch Code</label>
                                    <input 
                                        type="text"
                                        x-model="form.branch_code"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Clearing Days</label>
                                    <input 
                                        type="number"
                                        x-model="form.clearing_days"
                                        min="0"
                                        max="30"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">0 = Instant, 1-3 = Business days</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                                <textarea 
                                    x-model="form.bank_instructions"
                                    rows="3"
                                    placeholder="Additional instructions for bank transfer..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                ></textarea>
                            </div>
                        </div>

                        <!-- MPesa Fields -->
                        <div x-show="form.type === 'mpesa'" class="space-y-3 border-t pt-4">
                            <h4 class="font-medium text-gray-900">MPesa Details</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Paybill Number *</label>
                                    <input 
                                        type="text"
                                        x-model="form.mpesa_paybill"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                    <input 
                                        type="text"
                                        x-model="form.mpesa_account_number"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Clearing Days</label>
                                    <input 
                                        type="number"
                                        x-model="form.clearing_days"
                                        min="0"
                                        max="30"
                                        value="0"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">MPesa is usually instant (0 days)</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                                <textarea 
                                    x-model="form.mpesa_instructions"
                                    rows="3"
                                    placeholder="Instructions for MPesa payment..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                ></textarea>
                            </div>
                        </div>

                        <!-- PayPal/Stripe Fields -->
                        <div x-show="form.type === 'paypal' || form.type === 'stripe'" class="space-y-3 border-t pt-4">
                            <h4 class="font-medium text-gray-900" x-text="form.type === 'paypal' ? 'PayPal Details' : 'Stripe Details'"></h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Link *</label>
                                <input 
                                    type="url"
                                    x-model="form.payment_link"
                                    placeholder="https://..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merchant ID</label>
                                <input 
                                    type="text"
                                    x-model="form.merchant_id"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Clearing Days</label>
                                <input 
                                    type="number"
                                    x-model="form.clearing_days"
                                    min="0"
                                    max="30"
                                    value="1"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">Usually 1-3 business days</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                                <textarea 
                                    x-model="form.online_instructions"
                                    rows="3"
                                    placeholder="Instructions for online payment..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Mobile Money Fields -->
                        <div x-show="form.type === 'mobile_money'" class="space-y-3 border-t pt-4">
                            <h4 class="font-medium text-gray-900">Mobile Money Details</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                    <input 
                                        type="text"
                                        x-model="form.mobile_money_provider"
                                        placeholder="e.g., Airtel Money, Tigo Pesa"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number *</label>
                                    <input 
                                        type="text"
                                        x-model="form.mobile_money_number"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Clearing Days</label>
                                    <input 
                                        type="number"
                                        x-model="form.clearing_days"
                                        min="0"
                                        max="30"
                                        value="0"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Usually instant (0 days)</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                                <textarea 
                                    x-model="form.mobile_money_instructions"
                                    rows="3"
                                    placeholder="Instructions for mobile money payment..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Cash Fields -->
                        <div x-show="form.type === 'cash'" class="space-y-3 border-t pt-4">
                            <h4 class="font-medium text-gray-900">Cash Payment Details</h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Clearing Days</label>
                                <input 
                                    type="number"
                                    x-model="form.clearing_days"
                                    min="0"
                                    max="30"
                                    value="0"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <p class="mt-1 text-xs text-gray-500">Cash is usually instant (0 days)</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                                <textarea 
                                    x-model="form.cash_instructions"
                                    rows="3"
                                    placeholder="Instructions for cash payment (e.g., location, hours)..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Enabled Toggle -->
                        <div class="flex items-center gap-2 border-t pt-4">
                            <input 
                                type="checkbox"
                                x-model="form.is_enabled"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <label class="text-sm font-medium text-gray-700">Enable this payment method</label>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="submit"
                        :disabled="saving"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                    >
                        <span x-show="!saving" x-text="editingMethod ? 'Update' : 'Create'"></span>
                        <span x-show="saving">Saving...</span>
                    </button>
                    <button 
                        type="button"
                        @click="open = false"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function paymentMethodModal(companyId) {
    return {
        open: false,
        saving: false,
        editingMethod: null,
        companyId: companyId,
        form: {
            type: '',
            name: '',
            is_enabled: true,
            sort_order: 0,
            clearing_days: 0,
            // Bank Transfer
            bank_name: '',
            account_name: '',
            account_number: '',
            swift_code: '',
            branch_code: '',
            bank_instructions: '',
            // MPesa
            mpesa_paybill: '',
            mpesa_account_number: '',
            mpesa_instructions: '',
            // PayPal/Stripe
            payment_link: '',
            merchant_id: '',
            online_instructions: '',
            // Mobile Money
            mobile_money_provider: '',
            mobile_money_number: '',
            mobile_money_instructions: '',
            // Cash
            cash_instructions: '',
        },

        initForm() {
            if (this.editingMethod) {
                // Populate form with existing data
                Object.keys(this.form).forEach(key => {
                    if (this.editingMethod[key] !== undefined) {
                        this.form[key] = this.editingMethod[key];
                    }
                });
            } else {
                // Reset form for new payment method
                this.form = {
                    type: '',
                    name: '',
                    is_enabled: true,
                    sort_order: 0,
                    clearing_days: 0,
                    bank_name: '',
                    account_name: '',
                    account_number: '',
                    swift_code: '',
                    branch_code: '',
                    bank_instructions: '',
                    mpesa_paybill: '',
                    mpesa_account_number: '',
                    mpesa_instructions: '',
                    payment_link: '',
                    merchant_id: '',
                    online_instructions: '',
                    mobile_money_provider: '',
                    mobile_money_number: '',
                    mobile_money_instructions: '',
                    cash_instructions: '',
                };
            }
        },

        updateFormFields() {
            // Set default clearing days based on type
            if (this.form.type === 'mpesa' || this.form.type === 'mobile_money' || this.form.type === 'cash') {
                this.form.clearing_days = 0;
            } else if (this.form.type === 'paypal' || this.form.type === 'stripe') {
                this.form.clearing_days = 1;
            } else if (this.form.type === 'bank_transfer') {
                this.form.clearing_days = 1;
            }
        },

        async savePaymentMethod() {
            this.saving = true;
            try {
                const url = this.editingMethod 
                    ? `/app/company/payment-methods/${this.editingMethod.id}`
                    : '/app/company/payment-methods';
                const method = this.editingMethod ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                if (data.success) {
                    this.open = false;
                    // Reload page to refresh payment methods list
                    window.location.reload();
                } else {
                    alert('Failed to save payment method: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving payment method:', error);
                alert('Failed to save payment method. Please try again.');
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>

