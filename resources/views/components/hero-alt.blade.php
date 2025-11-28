@props([
    'title' => 'Stop chasing payments. Get paid on time, every time.',
    'subtitle' => 'Professional invoicing with M-Pesa integration. Automated reminders reduce payment delays from 30 days to 7 days. Built for Kenyan businesses.',
    'ctaPrimary' => 'Start Free',
    'ctaSecondary' => 'See How It Works',
    'trustBadge' => '500+ Kenyan businesses trust us'
])

<section class="bg-white py-12 sm:py-16 lg:py-24 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
            <!-- LEFT COLUMN: Text + CTAs -->
            <div class="text-center lg:text-left">
                <!-- Trust Badge -->
                <div class="inline-flex items-center px-4 py-2 mb-6 bg-gray-100 border border-gray-300 rounded-full">
                    <svg class="w-4 h-4 text-gray-700 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">{{ $trustBadge }}</span>
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                    {{ $title }}
                </h1>

                <!-- Subtitle -->
                <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed max-w-xl mx-auto lg:mx-0">
                    {{ $subtitle }}
                </p>

                <!-- CTAs -->
                <div class="flex flex-col sm:flex-row gap-4 mb-8 justify-center lg:justify-start">
                    <a 
                        href="{{ route('register') }}" 
                        class="inline-flex items-center justify-center px-8 py-4 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        {{ $ctaPrimary }}
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a 
                        href="#how-it-works" 
                        class="inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        {{ $ctaSecondary }}
                    </a>
                </div>

                <!-- Trust Signals -->
                <div class="flex flex-wrap items-center gap-6 justify-center lg:justify-start text-sm text-gray-600">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-700 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>No monthly fees</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-700 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>7-day payment average</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-700 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Bank-level security</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Invoice Preview (Slot) -->
            <div class="relative mt-12 lg:mt-0">
                {{ $slot }}
            </div>
        </div>
    </div>
</section>

