@props([
    'plans' => [],
    'showYearly' => true,
    'showComparison' => true,
    'showROI' => false
])

<section id="pricing" class="bg-gray-50 py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Simple, Transparent Pricing</h2>
            <p class="mt-4 text-lg text-gray-600">Free to start. Pay only when you get paid.</p>
        </div>

        <!-- Yearly/Monthly Toggle -->
        @if($showYearly)
        <div class="flex justify-center mb-12" x-data="{ billing: 'monthly' }">
            <div class="inline-flex items-center bg-white rounded-lg p-1 border border-gray-200 shadow-sm">
                <button 
                    @click="billing = 'monthly'"
                    :class="billing === 'monthly' ? 'bg-emerald-600 text-white' : 'text-gray-700'"
                    class="px-6 py-2 rounded-md font-semibold transition-colors"
                >
                    Monthly
                </button>
                <button 
                    @click="billing = 'yearly'"
                    :class="billing === 'yearly' ? 'bg-emerald-600 text-white' : 'text-gray-700'"
                    class="px-6 py-2 rounded-md font-semibold transition-colors relative"
                >
                    Yearly
                    <span class="absolute -top-2 -right-2 bg-emerald-500 text-white text-xs px-2 py-0.5 rounded-full">-20%</span>
                </button>
            </div>
        </div>
        @endif

        <!-- Pricing Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto" x-data="{ billing: 'monthly' }">
            @foreach($plans as $plan)
                <x-enhanced-pricing-card :plan="$plan" :billing="'monthly'" />
            @endforeach
        </div>

        <!-- ROI Link -->
        @if($showROI)
        <div class="text-center mt-8">
            <a href="#roi-calculator" class="text-emerald-600 font-semibold hover:text-emerald-700 inline-flex items-center">
                Calculate your ROI
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
        @endif

        <p class="text-center text-sm text-gray-500 mt-8">
            All plans include 0.8% platform fee on successful payments. No hidden fees.
        </p>
    </div>
</section>

