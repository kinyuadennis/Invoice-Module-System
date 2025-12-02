@props([
    'features' => [],
    'columns' => 3, // 2 or 3
])

@php
    $gridCols = [
        2 => 'md:grid-cols-2',
        3 => 'md:grid-cols-2 lg:grid-cols-3',
    ];
@endphp

<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                Everything You Need to Get Paid Faster
            </h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Built for Kenyan businesses with M-Pesa integration, KRA compliance, and everything you need to manage invoices professionally.
            </p>
        </div>
        
        <div class="grid grid-cols-1 {{ $gridCols[$columns] }} gap-6 lg:gap-8">
            @foreach($features as $feature)
                <x-feature-card
                    :icon="$feature['icon'] ?? ''"
                    :title="$feature['name'] ?? ''"
                    :description="$feature['description'] ?? ''"
                    :benefit="$feature['benefit'] ?? null"
                    :badge="$feature['badge'] ?? null"
                />
            @endforeach
        </div>
        
        <!-- CTA after features -->
        <div class="text-center mt-12">
            <a 
                href="{{ route('register') }}"
                class="inline-flex items-center justify-center px-8 py-4 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105"
            >
                Create Your First Invoice
            </a>
        </div>
    </div>
</section>

