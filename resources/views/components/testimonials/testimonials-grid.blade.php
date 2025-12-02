@props([
    'testimonials' => [],
    'title' => 'Trusted by Kenyan Businesses',
    'subtitle' => 'See how InvoiceHub helps businesses get paid faster',
])

<section class="py-16 lg:py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                {{ $title }}
            </h2>
            @if($subtitle)
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
            @foreach($testimonials as $testimonial)
                <x-testimonial-card
                    :quote="$testimonial['quote'] ?? ''"
                    :author="$testimonial['name'] ?? ''"
                    :business="$testimonial['business'] ?? ''"
                    :avatar="$testimonial['avatar'] ?? '??'"
                    :role="$testimonial['role'] ?? null"
                    :location="$testimonial['location'] ?? null"
                    :metric="$testimonial['metric'] ?? null"
                    :verified="$testimonial['verified'] ?? false"
                />
            @endforeach
        </div>
        
        <!-- CTA after testimonials -->
        <div class="text-center mt-12">
            <a 
                href="{{ route('register') }}"
                class="inline-flex items-center justify-center px-8 py-4 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105"
            >
                Join 500+ Businesses
            </a>
        </div>
    </div>
</section>

