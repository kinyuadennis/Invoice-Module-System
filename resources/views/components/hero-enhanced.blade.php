@props([
    'title' => 'Stop Chasing Payments. Get Paid in 7 Days, Not 30.',
    'subtitle' => 'Send invoices, accept M-Pesa payments, track overdue accounts. KRA eTIMS ready. Trusted by 500+ Kenyan SMEs, freelancers, and agencies.',
    'ctaPrimary' => 'Start Free',
    'ctaSecondary' => 'Explore Features',
    'stats' => ['businesses' => 500, 'invoicesToday' => 12, 'avgPaymentDays' => 7]
])

<section class="bg-gradient-to-b from-gray-50 via-white to-gray-50 py-12 sm:py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
            <!-- LEFT COLUMN: Text + CTAs -->
            <div class="text-center lg:text-left" x-data="heroCounters({{ json_encode($stats) }})">
                <!-- Trust Badges Row -->
                <div class="flex flex-wrap items-center gap-3 mb-6 justify-center lg:justify-start">
                    <div class="inline-flex items-center px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-full">
                        <svg class="w-4 h-4 text-emerald-600 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs font-semibold text-emerald-700">KRA eTIMS Export Ready</span>
                    </div>
                    <div class="inline-flex items-center px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-full">
                        <svg class="w-4 h-4 text-blue-600 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs font-semibold text-blue-700">Bank-Level Security</span>
                    </div>
                    <div class="inline-flex items-center px-3 py-1.5 bg-green-50 border border-green-200 rounded-full">
                        <svg class="w-4 h-4 text-green-600 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                        </svg>
                        <span class="text-xs font-semibold text-green-700">M-Pesa Verified</span>
                    </div>
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
                        class="inline-flex items-center justify-center px-8 py-4 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    >
                        {{ $ctaPrimary }}
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a 
                        href="#features" 
                        class="inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        {{ $ctaSecondary }}
                    </a>
                </div>

                <!-- Trust Signals -->
                <div class="flex flex-wrap items-center gap-6 justify-center lg:justify-start text-sm text-gray-600 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Free to create</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>3% platform fee</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>M-Pesa integration</span>
                    </div>
                </div>

                <!-- Live Stats Counter -->
                <div class="flex flex-wrap items-center gap-6 justify-center lg:justify-start text-sm pt-4 border-t border-gray-200">
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-emerald-600 mr-2" x-text="formatNumber(businesses)"></span>
                        <span class="text-gray-600">Kenyan businesses</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-emerald-600 mr-2" x-text="invoicesToday"></span>
                        <span class="text-gray-600">invoices created today</span>
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

<script>
function heroCounters(stats) {
    return {
        businesses: stats.businesses || 500,
        invoicesToday: stats.invoicesToday || 12,
        avgPaymentDays: stats.avgPaymentDays || 7,
        
        init() {
            // Animate counter on load
            this.animateCounter();
        },
        
        animateCounter() {
            const target = this.businesses;
            const duration = 2000;
            const steps = 60;
            const increment = target / steps;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    this.businesses = target;
                    clearInterval(timer);
                } else {
                    this.businesses = Math.floor(current);
                }
            }, duration / steps);
        },
        
        formatNumber(num) {
            return num.toLocaleString('en-KE') + '+';
        }
    }
}
</script>

