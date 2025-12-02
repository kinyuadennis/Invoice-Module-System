<div x-data="paymentMethodSelector()" class="space-y-4">
    <label class="block text-sm font-semibold text-slate-900 mb-3">Preferred Payment Method</label>
    
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- M-Pesa -->
        <button
            type="button"
            @click="selectMethod('mpesa')"
            :class="selectedMethod === 'mpesa' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-slate-200 hover:border-slate-300'"
            class="p-4 border-2 rounded-lg transition-all text-left"
        >
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">📱</span>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">M-Pesa</p>
                    <p class="text-xs text-slate-600">Mobile Money</p>
                </div>
                <svg 
                    x-show="selectedMethod === 'mpesa'"
                    class="w-5 h-5 text-blue-600 ml-auto" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
        </button>

        <!-- Bank Transfer -->
        <button
            type="button"
            @click="selectMethod('bank_transfer')"
            :class="selectedMethod === 'bank_transfer' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-slate-200 hover:border-slate-300'"
            class="p-4 border-2 rounded-lg transition-all text-left"
        >
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">🏦</span>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Bank Transfer</p>
                    <p class="text-xs text-slate-600">Direct Deposit</p>
                </div>
                <svg 
                    x-show="selectedMethod === 'bank_transfer'"
                    class="w-5 h-5 text-blue-600 ml-auto" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
        </button>

        <!-- Cash -->
        <button
            type="button"
            @click="selectMethod('cash')"
            :class="selectedMethod === 'cash' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-slate-200 hover:border-slate-300'"
            class="p-4 border-2 rounded-lg transition-all text-left"
        >
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">💵</span>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Cash</p>
                    <p class="text-xs text-slate-600">Physical Payment</p>
                </div>
                <svg 
                    x-show="selectedMethod === 'cash'"
                    class="w-5 h-5 text-blue-600 ml-auto" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
        </button>
    </div>

    <!-- Additional Fields Based on Selection -->
    <div x-show="selectedMethod === 'mpesa'" x-transition class="mt-4">
        <label class="block text-sm font-semibold text-slate-900 mb-2">M-Pesa Number (Optional)</label>
        <input 
            type="text" 
            x-model="paymentDetails"
            placeholder="+254 700 000 000"
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        >
    </div>

    <div x-show="selectedMethod === 'bank_transfer'" x-transition class="mt-4">
        <label class="block text-sm font-semibold text-slate-900 mb-2">Bank Account Details (Optional)</label>
        <textarea 
            x-model="paymentDetails"
            rows="2"
            placeholder="Bank name, account number, etc."
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        ></textarea>
    </div>
</div>

<script>
function paymentMethodSelector() {
    return {
        selectedMethod: null,
        paymentDetails: '',
        
        selectMethod(method) {
            this.selectedMethod = method;
            this.$dispatch('payment-method-selected', {
                method: method,
                details: this.paymentDetails
            });
        }
    }
}
</script>

