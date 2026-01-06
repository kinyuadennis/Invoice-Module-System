@props([
    'persona' => [
        'name' => 'Sarah',
        'role' => 'Design Agency Owner',
        'location' => 'Nairobi',
        'business' => 'Creative Studio'
    ],
    'steps' => []
])

<section id="how-it-works" class="bg-gray-50 py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">How It Works</h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">See how {{ $persona['name'] }} from {{ $persona['location'] }} gets paid faster</p>
        </div>

        <!-- Persona Introduction -->
        <div class="max-w-2xl mx-auto mb-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
                <span class="text-2xl font-bold text-emerald-600">{{ substr($persona['name'], 0, 1) }}</span>
            </div>
            <p class="text-lg text-gray-700 dark:text-gray-200">
                <span class="font-semibold">{{ $persona['name'] }}</span> runs a {{ strtolower($persona['business']) }} in {{ $persona['location'] }}. 
                She used to wait 30+ days for payments. Now she gets paid in 7 days average.
            </p>
        </div>

        <!-- Steps with Connector -->
        <div class="relative">
            <!-- Connector Line (hidden on mobile) -->
            <div class="hidden lg:block absolute top-16 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-200 via-emerald-400 to-emerald-200" style="top: 4rem;"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 relative">
                @foreach($steps as $index => $step)
                    <div class="text-center relative" x-data="{ inView: false }" x-intersect="inView = true">
                        <!-- Step Number -->
                        <div 
                            class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 text-white rounded-full text-2xl font-bold mb-6 relative z-10 mx-auto"
                            x-bind:class="{ 'scale-110': inView }"
                            x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="scale-0"
                            x-transition:enter-end="scale-100"
                        >
                            {{ $step['number'] ?? $index + 1 }}
                        </div>

                        <!-- Icon/Mockup -->
                        <div 
                            class="w-32 h-32 bg-white rounded-xl mx-auto mb-4 flex items-center justify-center shadow-lg border border-gray-200"
                            x-bind:class="{ 'shadow-2xl scale-105': inView }"
                            x-transition:enter="transition ease-out duration-500 delay-100"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                        >
                            {!! $step['icon'] ?? '<svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' !!}
                        </div>

                        <!-- Content -->
                        <div
                            x-transition:enter="transition ease-out duration-500 delay-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                        >
                            <!-- Time Badge -->
                            @if(isset($step['time']))
                                <div class="inline-flex items-center px-3 py-1 bg-emerald-50 border border-emerald-200 rounded-full mb-3">
                                    <svg class="w-4 h-4 text-emerald-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-xs font-semibold text-emerald-700">{{ $step['time'] }}</span>
                                </div>
                            @endif

                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $step['title'] }}</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-3">{{ $step['description'] }}</p>
                            
                            @if(isset($step['outcome']))
                                <p class="text-sm font-semibold text-emerald-600">{{ $step['outcome'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

