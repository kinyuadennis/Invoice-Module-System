@props(['company', 'paymentMethods'])

<div class="mt-6">
    <x-card>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Payment Methods</h2>
                <p class="mt-1 text-sm text-gray-600">Configure how customers can pay your invoices</p>
            </div>
            <button 
                type="button"
                @click="window.dispatchEvent(new CustomEvent('open-payment-method-modal'))"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
            >
                + Add Payment Method
            </button>
        </div>

        <div 
            x-data="paymentMethodsManager({{ json_encode($paymentMethods) }}, {{ $company->id }})"
            class="space-y-4"
        >
            <!-- Payment Methods List -->
            <div class="space-y-3" x-show="paymentMethods.length > 0">
                <template x-for="(method, index) in paymentMethods" :key="method.id">
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="checkbox"
                                        x-model="method.is_enabled"
                                        @change="updatePaymentMethod(method)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="font-medium" x-text="method.display_name || method.name || getTypeName(method.type)"></span>
                                </div>
                                <div class="text-sm text-gray-500" x-text="getMethodDetails(method)"></div>
                                <div class="text-xs text-gray-400" x-text="'Clears in: ' + getClearingTime(method.clearing_days)"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="editPaymentMethod(method)"
                                    class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-50"
                                >
                                    Edit
                                </button>
                                <button 
                                    @click="deletePaymentMethod(method.id)"
                                    class="px-3 py-1.5 text-sm text-red-600 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="paymentMethods.length === 0" class="text-center py-8 text-gray-500">
                <p>No payment methods configured yet.</p>
                <p class="text-sm mt-2">Add a payment method to allow customers to pay your invoices.</p>
            </div>
        </div>
    </x-card>

    <!-- Add/Edit Payment Method Modal -->
    <x-payment-method-modal :company="$company" />
</div>

<script>
function paymentMethodsManager(initialMethods, companyId) {
    return {
        paymentMethods: initialMethods || [],
        companyId: companyId,
        editingMethod: null,

        getTypeName(type) {
            const names = {
                'bank_transfer': 'Bank Transfer',
                'mpesa': 'MPesa',
                'paypal': 'PayPal',
                'stripe': 'Stripe',
                'mobile_money': 'Mobile Money',
                'cash': 'Cash'
            };
            return names[type] || type;
        },

        getMethodDetails(method) {
            switch(method.type) {
                case 'bank_transfer':
                    return method.account_number ? `Account: ${method.account_number}` : 'Bank Transfer';
                case 'mpesa':
                    return method.mpesa_paybill ? `Paybill: ${method.mpesa_paybill}` : 'MPesa';
                case 'paypal':
                case 'stripe':
                    return method.payment_link ? 'Online Payment' : method.type;
                case 'mobile_money':
                    return method.mobile_money_number ? `${method.mobile_money_provider || 'Mobile Money'}: ${method.mobile_money_number}` : 'Mobile Money';
                case 'cash':
                    return 'Cash Payment';
                default:
                    return '';
            }
        },

        getClearingTime(days) {
            if (days === 0) return 'Instant';
            if (days === 1) return '1 business day';
            return `${days} business days`;
        },

        async updatePaymentMethod(method) {
            try {
                const response = await fetch(`/app/company/payment-methods/${method.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        is_enabled: method.is_enabled
                    })
                });

                const data = await response.json();
                if (data.success) {
                    // Update local state
                    const index = this.paymentMethods.findIndex(m => m.id === method.id);
                    if (index !== -1) {
                        this.paymentMethods[index] = data.payment_method;
                    }
                }
            } catch (error) {
                console.error('Error updating payment method:', error);
                alert('Failed to update payment method');
            }
        },

        editPaymentMethod(method) {
            this.editingMethod = method;
            window.dispatchEvent(new CustomEvent('open-payment-method-modal', { detail: method }));
        },

        async deletePaymentMethod(methodId) {
            if (!confirm('Are you sure you want to delete this payment method?')) {
                return;
            }

            try {
                const response = await fetch(`/app/company/payment-methods/${methodId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.paymentMethods = this.paymentMethods.filter(m => m.id !== methodId);
                } else {
                    alert('Failed to delete payment method: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error deleting payment method:', error);
                alert('Failed to delete payment method');
            }
        }
    }
}
</script>

