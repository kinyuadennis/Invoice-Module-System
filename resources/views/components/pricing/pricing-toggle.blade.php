@props([
    'showYearly' => true,
    'defaultPeriod' => 'monthly', // monthly, yearly
])

<div 
    x-data="{ period: '{{ $defaultPeriod }}' }"
    class="flex items-center justify-center gap-4 mb-8"
>
    <span 
        :class="period === 'monthly' ? 'text-slate-900 font-semibold' : 'text-slate-500'"
        class="transition-colors"
    >
        Monthly
    </span>
    
    <button
        @click="period = period === 'monthly' ? 'yearly' : 'monthly'"
        type="button"
        class="relative inline-flex h-6 w-11 items-center rounded-full bg-emerald-600 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
        role="switch"
        :aria-checked="period === 'yearly'"
    >
        <span
            :class="period === 'yearly' ? 'translate-x-6' : 'translate-x-1'"
            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
        ></span>
    </button>
    
    <span 
        :class="period === 'yearly' ? 'text-slate-900 font-semibold' : 'text-slate-500'"
        class="transition-colors"
    >
        Yearly
    </span>
    
    @if($showYearly)
        <span 
            x-show="period === 'yearly'"
            class="ml-2 px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full"
            x-cloak
        >
            Save 20%
        </span>
    @endif
</div>

