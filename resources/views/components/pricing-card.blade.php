@props([
    'name',
    'priceMonthly',
    'priceYearly' => null,
    'features',
    'popular' => false,
    'cta' => 'Get Started',
    'note' => null,
    'showYearly' => false,
])

<div class="relative bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 {{ $popular ? 'border-blue-500 scale-105 ring-4 ring-blue-100' : 'border-slate-200' }}">
    @if($popular)
        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
            <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg">MOST POPULAR</span>
        </div>
    @endif

    <h3 class="text-2xl font-black text-slate-900 mb-2">{{ $name }}</h3>
    
    <div class="mb-6">
        <div class="flex items-baseline">
            <span class="text-4xl font-black text-slate-900">KES {{ number_format($showYearly && $priceYearly ? $priceYearly : $priceMonthly, 0) }}</span>
            <span class="text-slate-600 ml-2">/{{ $showYearly ? 'year' : 'month' }}</span>
        </div>
        @if($showYearly && $priceYearly)
            <p class="text-sm text-slate-500 mt-1">
                <span class="line-through">KES {{ number_format($priceMonthly * 12, 0) }}/year</span>
                <span class="text-blue-600 font-semibold ml-2">Save 20%</span>
            </p>
        @endif
        @if($note)
            <p class="text-xs text-slate-500 mt-2">{{ $note }}</p>
        @endif
    </div>

    <ul class="space-y-3 mb-8">
        @foreach($features as $feature)
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-slate-700">{{ $feature }}</span>
            </li>
        @endforeach
    </ul>

    <a 
        href="{{ route('register') }}" 
        class="block w-full text-center px-6 py-4 {{ $popular ? 'bg-blue-500 hover:bg-blue-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-900' }} font-bold rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
    >
        {{ $cta }}
    </a>
</div>

