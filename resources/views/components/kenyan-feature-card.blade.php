@props(['feature'])

@php
    $badgeColor = match($feature['badge'] ?? null) {
        'M-Pesa Verified' => 'bg-green-100 text-green-700 border-green-200',
        'KRA Ready' => 'bg-blue-100 text-blue-700 border-blue-200',
        'New' => 'bg-purple-100 text-purple-700 border-purple-200',
        default => 'bg-gray-100 text-gray-700 dark:text-gray-200 border-gray-200',
    };
@endphp

<div 
    class="bg-white rounded-xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 group"
    x-data="{ showDemo: false }"
    @mouseenter="showDemo = true"
    @mouseleave="showDemo = false"
>
    <!-- Icon -->
    <div class="flex items-center justify-center w-12 h-12 bg-emerald-100 rounded-lg mb-4">
        {!! $feature['icon'] ?? '' !!}
    </div>

    <!-- Badge -->
    @if(isset($feature['badge']))
        <div class="inline-flex items-center px-2 py-1 mb-3 border rounded-full {{ $badgeColor }}">
            <span class="text-xs font-semibold">{{ $feature['badge'] }}</span>
        </div>
    @endif

    <!-- Title -->
    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $feature['name'] }}</h3>
    
    <!-- Description -->
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ $feature['description'] }}</p>

    <!-- Benefit/Metric -->
    @if(isset($feature['benefit']))
        <div class="flex items-center text-sm font-semibold text-emerald-600 mb-4">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ $feature['benefit'] }}
        </div>
    @endif

    @if(isset($feature['metric']))
        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">
            {{ $feature['metric'] }}
        </div>
    @endif

    <!-- Demo Button (on hover) -->
    @if(isset($feature['demo']))
        <div 
            x-show="showDemo"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="mt-4"
        >
            <button class="w-full px-4 py-2 bg-gray-100 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 text-sm font-semibold transition-colors">
                View Demo
            </button>
        </div>
    @endif
</div>

