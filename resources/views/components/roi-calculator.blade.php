@props([
    'defaultValues' => [
        'invoicesPerMonth' => 20,
        'avgInvoiceValue' => 50000,
        'currentDelay' => 30
    ],
    'showChart' => true,
    'showCTA' => true
])

<section id="roi-calculator" class="bg-gradient-to-br from-emerald-50 to-teal-50 py-16 lg:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Calculate Your ROI</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">See how much time and money you'll save with InvoiceHub</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 lg:p-12" x-data="roiCalculator({{ json_encode($defaultValues) }})" x-effect="calculate()">
            <!-- Inputs -->
            <div class="space-y-8 mb-8">
                <!-- Invoices per Month -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-3">
                        How many invoices do you send per month?
                    </label>
                    <div class="flex items-center gap-4">
                        <input 
                            type="range" 
                            x-model.number="invoicesPerMonth"
                            @input="calculate()"
                            min="1"
                            max="200"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-600"
                        >
                        <input 
                            type="number" 
                            x-model.number="invoicesPerMonth"
                            @input="calculate()"
                            min="1"
                            max="200"
                            class="w-24 px-4 py-2 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                    </div>
                </div>

                <!-- Average Invoice Value -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-3">
                        What's your average invoice value? (KES)
                    </label>
                    <div class="flex items-center gap-4">
                        <input 
                            type="range" 
                            x-model.number="avgInvoiceValue"
                            @input="calculate()"
                            min="1000"
                            max="1000000"
                            step="1000"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-600"
                        >
                        <input 
                            type="number" 
                            x-model.number="avgInvoiceValue"
                            @input="calculate()"
                            min="1000"
                            max="1000000"
                            step="1000"
                            class="w-32 px-4 py-2 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                    </div>
                </div>

                <!-- Current Payment Delay -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-3">
                        How many days do you wait for payment currently?
                    </label>
                    <div class="flex items-center gap-4">
                        <input 
                            type="range" 
                            x-model.number="currentDelay"
                            @input="calculate()"
                            min="1"
                            max="90"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-600"
                        >
                        <input 
                            type="number" 
                            x-model.number="currentDelay"
                            @input="calculate()"
                            min="1"
                            max="90"
                            class="w-24 px-4 py-2 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                        <span class="text-sm text-gray-600 dark:text-gray-300">days</span>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-6 mb-8" x-show="results">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Time Saved -->
                    <div class="text-center">
                        <div class="text-3xl font-black text-emerald-600 mb-2" x-text="formatNumber(results.timeSaved)"></div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">hours saved per month</div>
                    </div>

                    <!-- Money Saved -->
                    <div class="text-center">
                        <div class="text-3xl font-black text-emerald-600 mb-2" x-text="'KES ' + formatNumber(results.moneySaved)"></div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">faster payments per month</div>
                    </div>

                    <!-- Payback Period -->
                    <div class="text-center">
                        <div class="text-3xl font-black text-emerald-600 mb-2" x-text="results.paybackDays + ' days'"></div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">payback period</div>
                    </div>

                    <!-- Annual Savings -->
                    <div class="text-center">
                        <div class="text-3xl font-black text-emerald-600 mb-2" x-text="'KES ' + formatNumber(results.annualSavings)"></div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">annual savings</div>
                    </div>
                </div>
            </div>

            <!-- Chart (optional) -->
            @if($showChart)
            <div class="mb-8" x-show="results">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Before vs After</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600 dark:text-gray-300">Current Payment Delay</span>
                            <span class="font-semibold" x-text="currentDelay + ' days'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div 
                                class="bg-rose-500 h-4 rounded-full transition-all duration-500"
                                :style="'width: ' + (currentDelay / 90 * 100) + '%'"
                            ></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600 dark:text-gray-300">With InvoiceHub</span>
                            <span class="font-semibold text-emerald-600">7 days</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div 
                                class="bg-emerald-500 h-4 rounded-full transition-all duration-500"
                                style="width: 7.8%"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- CTA -->
            @if($showCTA)
            <div class="text-center">
                <a 
                    href="{{ route('register') }}" 
                    class="inline-flex items-center justify-center px-8 py-4 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                >
                    Start Your Free Trial →
                </a>
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">See these savings for yourself</p>
            </div>
            @endif
        </div>
    </div>
</section>

<script>
function roiCalculator(defaults) {
    return {
        invoicesPerMonth: defaults.invoicesPerMonth || 20,
        avgInvoiceValue: defaults.avgInvoiceValue || 50000,
        currentDelay: defaults.currentDelay || 30,
        results: null,
        
        init() {
            this.calculate();
        },
        
        calculate() {
            // Time saved: 30 minutes per invoice (manual work eliminated)
            const timeSaved = this.invoicesPerMonth * 0.5;
            
            // New delay with InvoiceHub (7 days average)
            const newDelay = 7;
            const daysSaved = this.currentDelay - newDelay;
            
            // Money saved: faster payment = better cash flow
            const moneySaved = (this.invoicesPerMonth * this.avgInvoiceValue * daysSaved) / 30;
            
            // Payback period: Monthly cost / (Money saved per day)
            const monthlyCost = 999; // Starter plan
            const paybackDays = moneySaved > 0 ? (monthlyCost / (moneySaved / 30)) : 0;
            
            // Annual savings
            const annualSavings = moneySaved * 12;
            
            this.results = {
                timeSaved: Math.round(timeSaved * 10) / 10,
                moneySaved: Math.round(moneySaved),
                paybackDays: Math.round(paybackDays),
                annualSavings: Math.round(annualSavings)
            };
        },
        
        formatNumber(num) {
            return num.toLocaleString('en-KE');
        }
    }
}

</script>

