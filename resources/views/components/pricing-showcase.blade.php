@props([
    'plans' => [],
    'showYearly' => true,
    'showComparison' => true,
    'showROI' => false
])

<section id="pricing" class="bg-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Simple, Transparent Pricing</h2>
            <p class="text-lg text-slate-600">Free to start. Pay only when you get paid.</p>
        </div>

        <!-- Yearly/Monthly Toggle -->
        @if($showYearly)
            <x-pricing.pricing-toggle :showYearly="true" />
        @endif

        <!-- Pricing Cards -->
        <div 
            x-data="{ period: 'monthly' }"
            class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 max-w-6xl mx-auto"
        >
            @foreach($plans as $index => $plan)
                <x-pricing-card
                    :name="$plan['name'] ?? ''"
                    :priceMonthly="$plan['price_monthly'] ?? 0"
                    :priceYearly="$plan['price_yearly'] ?? null"
                    :features="$plan['features'] ?? []"
                    :popular="$plan['popular'] ?? false"
                    :cta="$plan['cta'] ?? 'Get Started'"
                    :note="$plan['note'] ?? null"
                    :showYearly="false"
                />
            @endforeach
        </div>

        <!-- ROI Link -->
        @if($showROI)
            <div class="text-center mt-8">
                <a href="#roi-calculator" class="text-blue-600 font-semibold hover:text-blue-700 inline-flex items-center">
                    Calculate your ROI
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        @endif

        <p class="text-center text-sm text-slate-500 mt-8">
            All plans include 3% platform fee on successful payments. No hidden fees.
        </p>
    </div>
</section>

