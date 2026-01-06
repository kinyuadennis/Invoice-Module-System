@props([
    'testimonials' => [],
    'showFilter' => true,
    'showVideo' => false
])

<section class="bg-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Trusted by Kenyan Businesses</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">See how businesses across Kenya are getting paid faster</p>
        </div>

        <!-- Industry Filter -->
        @if($showFilter && count($testimonials) > 0)
        <div class="flex flex-wrap justify-center gap-3 mb-12" x-data="{ activeFilter: 'all' }">
            <button 
                @click="activeFilter = 'all'"
                :class="activeFilter === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 dark:text-gray-200 hover:bg-gray-200'"
                class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors"
            >
                All Industries
            </button>
            @php
                $industries = collect($testimonials)->pluck('industry')->unique()->filter();
            @endphp
            @foreach($industries as $industry)
                <button 
                    @click="activeFilter = '{{ $industry }}'"
                    :class="activeFilter === '{{ $industry }}' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 dark:text-gray-200 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors capitalize"
                >
                    {{ ucfirst($industry) }}
                </button>
            @endforeach
        </div>
        @endif

        <!-- Testimonials Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($testimonials as $testimonial)
                <x-enhanced-testimonial-card :testimonial="$testimonial" :showVideo="$showVideo" />
            @empty
                <div class="col-span-4 text-center py-12">
                    <p class="text-gray-500">No testimonials yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

