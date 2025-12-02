@props([
    'title',
    'subtitle',
    'ctaPrimary' => 'Start Free',
    'ctaSecondary' => null,
    'ctaPrimaryHref' => null,
    'ctaSecondaryHref' => null,
    'stats' => [],
    'trustBadges' => [],
    'background' => 'gradient', // gradient, solid, pattern
])

@php
    $ctaPrimaryHref = $ctaPrimaryHref ?? route('register');
    $ctaSecondaryHref = $ctaSecondaryHref ?? '#features';
    
    $backgroundClasses = [
        'gradient' => 'bg-gradient-to-br from-slate-900 to-slate-800',
        'solid' => 'bg-slate-900',
        'pattern' => 'bg-slate-900 bg-[url("data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")]',
        'slate' => 'bg-gradient-to-br from-slate-900 to-slate-800',
    ];
@endphp

<section class="{{ $backgroundClasses[$background] }} text-white py-16 lg:py-24 relative overflow-hidden">
    <!-- Background decoration -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -mr-48 -mt-48"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full -ml-48 -mb-48"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <!-- Left Column: Text Content -->
            <div class="text-center lg:text-left">
                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-6">
                    {{ $title }}
                </h1>
                
                <!-- Subheadline -->
                <p class="text-lg sm:text-xl text-slate-300 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    {{ $subtitle }}
                </p>
                
                <!-- CTAs -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-8">
                    <a 
                        href="{{ $ctaPrimaryHref }}"
                        class="inline-flex items-center justify-center px-8 py-4 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105 text-lg"
                    >
                        {{ $ctaPrimary }}
                    </a>
                    
                    @if($ctaSecondary)
                        <a 
                            href="{{ $ctaSecondaryHref }}"
                            class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-all duration-200 text-lg"
                        >
                            {{ $ctaSecondary }}
                        </a>
                    @endif
                </div>
                
                <!-- Trust Badges -->
                @if(count($trustBadges) > 0)
                    <div class="flex flex-wrap items-center gap-4 justify-center lg:justify-start mb-6">
                        @foreach($trustBadges as $badge)
                            <div class="flex items-center gap-2 text-sm text-slate-300">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $badge }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <!-- Stats -->
                @if(count($stats) > 0)
                    <div class="grid grid-cols-3 gap-4 max-w-md mx-auto lg:mx-0">
                        @foreach($stats as $key => $value)
                            <div class="text-center lg:text-left">
                                <div class="text-2xl lg:text-3xl font-black">{{ $value }}</div>
                                <div class="text-xs sm:text-sm text-slate-400 uppercase tracking-wider mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Right Column: Invoice Preview -->
            <div class="flex justify-center lg:justify-end">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</section>

