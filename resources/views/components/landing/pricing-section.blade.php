@props([
    'plans' => [],
    'userCountry' => null,
])

@php
// Get user country for gateway suggestion
$country = $userCountry ?? (auth()->user()->country ?? null);
$suggestedGateway = $country === 'KE' ? 'mpesa' : 'stripe';
@endphp

<section id="pricing" class="bg-gray-50 dark:bg-[#1A1A1A] dark:bg-gray-900 dark:bg-[#0D0D0D] py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                Choose Your Plan
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 dark:text-gray-400">
                Start at KES 500/month • Global plans with local payments
            </p>
            <p class="mt-2 text-base text-gray-500 dark:text-gray-500">
                M-Pesa in Kenya, cards/banks worldwide
            </p>
        </div>

        <!-- Gateway Suggestion Banner -->
        <div class="max-w-3xl mx-auto mb-8">
            <x-shared.country-gateway-banner :country="$country" :suggested-gateway="$suggestedGateway" />
        </div>

        <!-- Billing Cycle Toggle & Pricing Cards Grid -->
        <div x-data="{ cycle: 'monthly' }" class="max-w-7xl mx-auto">
            <!-- Billing Cycle Toggle -->
            <div class="flex justify-center mb-12">
                <div class="inline-flex rounded-lg bg-white dark:bg-[#242424] dark:bg-gray-800 p-1 shadow-sm border border-gray-200 dark:border-[#333333] dark:border-gray-700">
                    <button
                        @click="cycle = 'monthly'"
                        :class="cycle === 'monthly' ? 'bg-[#2B6EF6] text-white' : 'text-gray-700 dark:text-gray-200 dark:text-gray-300'"
                        class="px-6 py-2 rounded-md text-sm font-medium transition-colors duration-150"
                    >
                        Monthly
                    </button>
                    <button
                        @click="cycle = 'yearly'"
                        :class="cycle === 'yearly' ? 'bg-[#2B6EF6] text-white' : 'text-gray-700 dark:text-gray-200 dark:text-gray-300'"
                        class="px-6 py-2 rounded-md text-sm font-medium transition-colors duration-150"
                    >
                        Yearly
                        <span class="ml-1 text-xs opacity-90">Save 20%</span>
                    </button>
                </div>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($plans as $plan)
                @php
                    $priceMonthly = $plan['price_monthly'] ?? 0;
                    $priceYearly = $plan['price_yearly'] ?? ($priceMonthly * 12 * 0.8); // 20% discount
                    $isPopular = $plan['popular'] ?? false;
                @endphp

                <div 
                    class="relative bg-white dark:bg-[#242424] dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 {{ $isPopular ? 'border-[#2B6EF6] scale-105 ring-4 ring-blue-100 dark:ring-blue-900/20' : 'border-gray-200 dark:border-[#333333] dark:border-gray-700' }}"
                    x-show="true"
                >
                    @if($isPopular)
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="bg-[#2B6EF6] text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg">
                                MOST POPULAR
                            </span>
                        </div>
                    @endif

                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $plan['name'] }}
                    </h3>

                    <!-- Price Display -->
                    <div class="mb-6">
                        <div class="flex items-baseline" x-show="cycle === 'monthly'">
                            <span class="text-4xl font-bold text-gray-900 dark:text-white">
                                KES {{ number_format($priceMonthly, 0) }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-300 dark:text-gray-400 ml-2">/month</span>
                        </div>
                        <div class="flex items-baseline" x-show="cycle === 'yearly'" x-cloak>
                            <span class="text-4xl font-bold text-gray-900 dark:text-white">
                                KES {{ number_format($priceYearly / 12, 0) }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-300 dark:text-gray-400 ml-2">/month</span>
                        </div>
                        <div x-show="cycle === 'yearly'" x-cloak class="mt-1">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="line-through">KES {{ number_format($priceMonthly * 12, 0) }}/year</span>
                                <span class="text-[#2B6EF6] font-semibold ml-2">
                                    Save {{ number_format(($priceMonthly * 12) - $priceYearly, 0) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Social Proof -->
                    @if(isset($plan['social_proof']) && $plan['social_proof'])
                        <div class="mb-6 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400">
                            <span class="font-semibold text-[#2B6EF6]">{{ $plan['social_proof'] }}</span> use this plan
                        </div>
                    @endif

                    <!-- Features List -->
                    <ul class="space-y-3 mb-8">
                        @foreach($plan['features'] ?? [] as $feature)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-[#2B6EF6] mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-200 dark:text-gray-300">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <!-- CTA Button -->
                    @auth
                        @php
                            $planId = $plan['id'] ?? null;
                            $checkoutUrl = $planId 
                                ? route('user.subscriptions.checkout', ['plan' => $planId])
                                : route('user.subscriptions.index');
                        @endphp
                        <a 
                            href="{{ $checkoutUrl }}"
                            class="block w-full text-center px-6 py-4 {{ $isPopular ? 'bg-[#2B6EF6] hover:bg-[#2563EB] text-white' : 'bg-gray-100 dark:bg-[#2A2A2A] dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white' }} font-bold rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
                        >
                            {{ $plan['cta'] ?? 'Subscribe Now' }}
                        </a>
                    @else
                        @php
                            $planSlug = $plan['slug'] ?? strtolower($plan['name'] ?? '');
                            $registerUrl = $planSlug 
                                ? route('register', ['plan' => $planSlug])
                                : route('register');
                        @endphp
                        <a 
                            href="{{ $registerUrl }}"
                            class="block w-full text-center px-6 py-4 {{ $isPopular ? 'bg-[#2B6EF6] hover:bg-[#2563EB] text-white' : 'bg-gray-100 dark:bg-[#2A2A2A] dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white' }} font-bold rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
                        >
                            {{ $plan['cta'] ?? 'Get Started' }}
                        </a>
                    @endauth
                </div>
            @endforeach
            </div>
        </div>

        <!-- Trust Badges -->
        <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                <span>SSL Secured</span>
            </div>
            <div class="flex items-center gap-2">
                <x-payments.payment-method-icon gateway="mpesa" size="sm" />
                <span>M-Pesa Secure</span>
            </div>
            <div class="flex items-center gap-2">
                <x-payments.payment-method-icon gateway="stripe" size="sm" />
                <span>Stripe Secure</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>PCI Compliant</span>
            </div>
        </div>
    </div>
</section>

