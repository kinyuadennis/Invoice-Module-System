@props(['plan', 'billing' => 'monthly'])

@php
    $price = $billing === 'yearly' ? ($plan['price_yearly'] ?? $plan['price_monthly'] * 12 * 0.8) : $plan['price_monthly'];
    $monthlyEquivalent = $billing === 'yearly' ? round($price / 12) : $price;
@endphp

<div class="relative bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 {{ ($plan['popular'] ?? false) ? 'border-emerald-500 scale-105' : 'border-gray-200' }}">
    @if($plan['popular'] ?? false)
        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
            <span class="bg-emerald-600 text-white px-4 py-1 rounded-full text-xs font-bold">MOST POPULAR</span>
        </div>
    @endif

    <h3 class="text-2xl font-black text-gray-900 mb-2">{{ $plan['name'] }}</h3>
    
    <!-- Price -->
    <div class="mb-6">
        @if($price == 0)
            <span class="text-4xl font-black text-gray-900">Free</span>
        @else
            <div class="flex items-baseline">
                <span class="text-4xl font-black text-gray-900">KSh {{ number_format($monthlyEquivalent, 0) }}</span>
                <span class="text-gray-600 ml-2">/month</span>
            </div>
            @if($billing === 'yearly')
                <p class="text-sm text-gray-500 mt-1">
                    KSh {{ number_format($price, 0) }}/year
                    <span class="text-emerald-600 font-semibold">(Save {{ number_format(($plan['price_monthly'] * 12) - $price, 0) }})</span>
                </p>
            @endif
        @endif
    </div>

    <!-- Social Proof -->
    @if(isset($plan['social_proof']))
        <div class="mb-6 text-sm text-gray-600">
            <span class="font-semibold text-emerald-600">{{ $plan['social_proof'] }}</span> use this plan
        </div>
    @endif

    <!-- Features -->
    <ul class="space-y-3 mb-8">
        @foreach($plan['features'] as $feature)
            <li class="flex items-start">
                <svg class="w-5 h-5 text-emerald-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-gray-700">{{ $feature }}</span>
            </li>
        @endforeach
    </ul>

    <!-- CTA -->
    <a 
        href="{{ route('register') }}" 
        class="block w-full text-center px-6 py-3 {{ ($plan['popular'] ?? false) ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-gray-100 hover:bg-gray-200' }} {{ ($plan['popular'] ?? false) ? 'text-white' : 'text-gray-900' }} font-bold rounded-lg transition-colors"
    >
        {{ $plan['cta'] ?? 'Get Started' }}
    </a>
</div>

