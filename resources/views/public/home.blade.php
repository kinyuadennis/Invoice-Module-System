@extends('layouts.public')

@section('title', 'Home')

@section('content')
    <!-- HERO SECTION -->
    <x-hero.hero-split
        title="Stop Chasing Payments. Get Paid in 7 Days, Not 30."
        subtitle="Send professional invoices, accept M-Pesa payments, track everything. KRA eTIMS compliant. Trusted by 500+ Kenyan businesses."
        ctaPrimary="Start Free"
        ctaSecondary="Explore Features"
        :stats="$stats ?? ['businesses' => 500, 'invoices' => '12,000+', 'days' => '7']"
        :trustBadges="['No credit card required', 'Free forever', 'KRA compliant']"
        background="slate"
    >
        <x-hero.invoice-preview-interactive />
    </x-hero.hero-split>
    
    <!-- Social Proof Bar -->
    <x-trust.social-proof-bar />

    <!-- FEATURE HIGHLIGHTS (4× Grid) -->
    <section id="features" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    Everything You Need to Get Paid Faster
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Built for Kenyan businesses with M-Pesa integration, KRA compliance, and everything you need to manage invoices professionally.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <x-feature-card
                    icon="<svg class='h-8 w-8 text-blue-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' /></svg>"
                    title="Instant Invoice Creation"
                    description="Create professional invoices in under 60 seconds"
                />
                <x-feature-card
                    icon="<svg class='h-8 w-8 text-blue-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' /></svg>"
                    title="Client & Payment Tracking"
                    description="Manage clients and track payments automatically"
                />
                <x-feature-card
                    icon="<svg class='h-8 w-8 text-blue-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' /></svg>"
                    title="Multi-Channel Delivery"
                    description="Send via email, WhatsApp, or download PDF"
                />
                <x-feature-card
                    icon="<svg class='h-8 w-8 text-blue-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' /></svg>"
                    title="VAT-Compliant"
                    description="Auto-calculates 16% VAT and platform fees"
                />
            </div>
            
            <!-- CTA after features -->
            <div class="text-center mt-12">
                <a 
                    href="{{ route('register') }}"
                    class="inline-flex items-center justify-center px-8 py-4 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105"
                >
                    Create Your First Invoice
                </a>
            </div>
        </div>
    </section>

    <!-- PRODUCT PREVIEW SECTION -->
    <section class="py-16 lg:py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    See It In Action
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Create professional invoices in seconds with our intuitive interface
                </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- Form Preview -->
                <div class="bg-white rounded-xl shadow-lg p-6 lg:p-8 border border-slate-200">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Client</label>
                            <select class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900">
                                <option>Select or add client...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Items</label>
                            <div class="border border-slate-200 rounded-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-slate-600 font-medium">Description</th>
                                            <th class="px-4 py-2 text-right text-slate-600 font-medium">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-t border-slate-200">
                                            <td class="px-4 py-2 text-slate-700">Consultation Services</td>
                                            <td class="px-4 py-2 text-right font-medium text-slate-900">KES 50,000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="vat" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="vat" class="text-sm text-slate-700">Include 16% VAT</label>
                        </div>
                    </div>
                </div>
                
                <!-- Live Preview Panel -->
                <div class="bg-white rounded-xl shadow-lg p-6 lg:p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Live Preview</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Subtotal</span>
                            <span class="font-medium text-slate-900">KES 50,000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">VAT (16%)</span>
                            <span class="font-medium text-slate-900">KES 8,000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Platform Fee (0.8%)</span>
                            <span class="font-medium text-slate-900">KES 464</span>
                        </div>
                        <div class="pt-3 border-t-2 border-slate-200 flex justify-between">
                            <span class="font-bold text-slate-900">Total</span>
                            <span class="font-black text-lg text-blue-600">KES 58,464</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS (3 Steps) -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    How It Works
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Get started in minutes. No credit card required.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full text-2xl font-bold mb-4">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Sign Up & Set Up</h3>
                    <p class="text-slate-600">Create your free account and add company details</p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full text-2xl font-bold mb-4">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Add Clients</h3>
                    <p class="text-slate-600">Import or add clients manually—one-time setup</p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full text-2xl font-bold mb-4">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Generate & Send</h3>
                    <p class="text-slate-600">Create invoices in seconds and send via preferred channel</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING -->
    <x-pricing-showcase
        :plans="$plans ?? []"
        :showYearly="true"
        :showComparison="true"
        :showROI="false"
    />

    <!-- TESTIMONIALS -->
    <x-testimonials.testimonials-grid
        :testimonials="$testimonials ?? []"
    />

    <!-- FAQ SECTION -->
    <section class="py-16 lg:py-24 bg-slate-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    Frequently Asked Questions
                </h2>
            </div>
            
            <div class="space-y-4" x-data="{ open: null }">
                @php
                    $faqs = [
                        [
                            'question' => 'Is VAT automatically calculated?',
                            'answer' => 'Yes! Our system automatically calculates 16% VAT (Kenyan standard) on all invoices. You can toggle VAT on or off for each line item.'
                        ],
                        [
                            'question' => 'Can I use my own logo?',
                            'answer' => 'Absolutely! You can upload your company logo during setup, and it will appear on all your invoices, making them look professional and branded.'
                        ],
                        [
                            'question' => 'Is there a mobile app?',
                            'answer' => 'Currently, InvoiceHub is optimized for mobile browsers. A native mobile app is in development and will be available soon.'
                        ],
                        [
                            'question' => 'How do you handle data security?',
                            'answer' => 'We use bank-level encryption, regular security audits, and comply with GDPR standards. Your data is stored securely and never shared with third parties.'
                        ],
                        [
                            'question' => 'What payment methods are supported?',
                            'answer' => 'We support M-Pesa payments with automatic reconciliation. You can also accept bank transfers and other payment methods, which you can track manually.'
                        ]
                    ];
                @endphp
                
                @foreach($faqs as $index => $faq)
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <button
                            @click="open = open === {{ $index }} ? null : {{ $index }}"
                            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-slate-50 transition-colors"
                        >
                            <span class="font-semibold text-slate-900">{{ $faq['question'] }}</span>
                            <svg 
                                class="h-5 w-5 text-slate-500 transform transition-transform flex-shrink-0"
                                :class="open === {{ $index }} ? 'rotate-180' : ''"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div 
                            x-show="open === {{ $index }}"
                            x-collapse
                            class="px-6 pb-4 text-slate-600"
                        >
                            {{ $faq['answer'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="bg-gradient-to-br from-slate-900 to-slate-800 py-16 lg:py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Ready to Get Paid Faster?</h2>
            <p class="text-xl text-slate-300 mb-8">Join 500+ Kenyan businesses using InvoiceHub</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105">
                    Start Free Today
                </a>
                <a href="#features" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-all duration-200">
                    Learn More
                </a>
            </div>
            <p class="mt-6 text-sm text-slate-400">No credit card required • Free forever • Pay only when you get paid</p>
        </div>
    </section>
@endsection
