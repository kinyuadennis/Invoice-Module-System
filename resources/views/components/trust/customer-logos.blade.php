@props([
    'title' => 'Trusted by Kenyan Businesses',
    'logos' => [],
])

@php
    // Default logos if none provided (placeholder structure)
    $defaultLogos = [
        ['name' => 'Company 1', 'logo' => asset('images/logos/company-1.png'), 'alt' => 'Company 1 Logo'],
        ['name' => 'Company 2', 'logo' => asset('images/logos/company-2.png'), 'alt' => 'Company 2 Logo'],
        ['name' => 'Company 3', 'logo' => asset('images/logos/company-3.png'), 'alt' => 'Company 3 Logo'],
        ['name' => 'Company 4', 'logo' => asset('images/logos/company-4.png'), 'alt' => 'Company 4 Logo'],
        ['name' => 'Company 5', 'logo' => asset('images/logos/company-5.png'), 'alt' => 'Company 5 Logo'],
        ['name' => 'Company 6', 'logo' => asset('images/logos/company-6.png'), 'alt' => 'Company 6 Logo'],
    ];
    
    $displayLogos = !empty($logos) ? $logos : $defaultLogos;
@endphp

<section class="py-12 lg:py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">{{ $title }}</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 items-center">
            @foreach($displayLogos as $logo)
                <div class="flex items-center justify-center opacity-60 hover:opacity-100 transition-opacity duration-300">
                    @if(file_exists(public_path(str_replace(asset(''), '', $logo['logo']))) || str_starts_with($logo['logo'], 'http'))
                        <img 
                            src="{{ $logo['logo'] }}" 
                            alt="{{ $logo['alt'] ?? $logo['name'] }}"
                            class="h-12 w-auto object-contain grayscale hover:grayscale-0 transition-all duration-300"
                            loading="lazy"
                        />
                    @else
                        <!-- Placeholder for missing logos -->
                        <div class="h-12 w-24 bg-slate-200 rounded flex items-center justify-center">
                            <span class="text-xs text-slate-400 font-medium">{{ $logo['name'] }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

