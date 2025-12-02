@props([
    'text' => 'Trusted by 500+ Kenyan Businesses',
    'logos' => [], // Array of logo URLs or placeholder data
])

<section class="bg-white py-8 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center gap-6">
            <p class="text-sm font-medium text-slate-600 uppercase tracking-wider">
                {{ $text }}
            </p>
            
            <!-- Stats Row -->
            <div class="flex flex-wrap items-center justify-center gap-8 text-center">
                <div>
                    <div class="text-2xl font-bold text-slate-900">500+</div>
                    <div class="text-xs text-slate-500 uppercase tracking-wider mt-1">Businesses</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">12,000+</div>
                    <div class="text-xs text-slate-500 uppercase tracking-wider mt-1">Invoices</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">KES 2.5M+</div>
                    <div class="text-xs text-slate-500 uppercase tracking-wider mt-1">Processed</div>
                </div>
            </div>
            
            @if(count($logos) > 0)
                <div class="flex flex-wrap items-center justify-center gap-8 opacity-60 grayscale hover:grayscale-0 transition-all mt-4">
                    @foreach($logos as $logo)
                        @if(isset($logo['url']))
                            <img 
                                src="{{ $logo['url'] }}" 
                                alt="{{ $logo['alt'] ?? 'Client logo' }}"
                                class="h-8 w-auto object-contain"
                            />
                        @else
                            <div class="h-8 w-24 bg-slate-300 rounded flex items-center justify-center">
                                <span class="text-xs text-slate-500 font-medium">{{ $logo['name'] ?? 'Logo' }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

