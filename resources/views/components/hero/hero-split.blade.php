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
    'featuredTestimonial' => null,
])

@php
    $ctaPrimaryHref = $ctaPrimaryHref ?? route('register');
    $ctaSecondaryHref = $ctaSecondaryHref ?? '#invoicing-workflow';
    
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
                
                <!-- Featured Testimonial Snippet -->
                @if($featuredTestimonial)
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-6 border border-white/20">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($featuredTestimonial->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-white/90 italic text-sm mb-2 line-clamp-2">
                                    "{{ Str::limit($featuredTestimonial->content, 120) }}"
                                </p>
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-semibold text-sm">{{ $featuredTestimonial->name }}</span>
                                    @if($featuredTestimonial->title)
                                        <span class="text-white/70 text-xs">— {{ $featuredTestimonial->title }}</span>
                                    @endif
                                    <div class="flex items-center ml-auto">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3 h-3 {{ $i <= $featuredTestimonial->rating ? 'text-yellow-400' : 'text-white/30' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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

