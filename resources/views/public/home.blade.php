@extends('layouts.public')

@section('title', 'Home')

@section('content')
    <!-- HERO SECTION -->
    <x-hero.hero-split
        title="{{ $heroHeading ?? 'Professional invoicing for Kenyan businesses — compliant, simple, reliable.' }}"
        subtitle="Send professional invoices, accept M-Pesa payments, track everything. KRA eTIMS compliant. Trusted by 500+ Kenyan businesses."
        ctaPrimary="Start Free"
        ctaPrimaryHref="{{ route('register') }}"
        ctaSecondary="Explore Features"
        ctaSecondaryHref="#invoicing-workflow"
        :stats="$stats ?? ['businesses' => 500, 'invoices' => '12,000+', 'days' => '7']"
        :trustBadges="['No credit card required', 'Free forever', 'KRA compliant']"
        background="slate"
    >
        <x-hero.invoice-preview-interactive />
    </x-hero.hero-split>
    
    <!-- Social Proof Bar -->
    <x-trust.social-proof-bar />

    <!-- PRODUCT PREVIEW SECTION -->
    <section class="py-16 lg:py-24 bg-slate-50" id="see-it-in-action">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    See It In Action
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto mb-8">
                    Take an interactive tour to see how easy it is to create professional invoices, from signup to getting paid
                </p>
                
                <!-- Play Demo Button -->
                <button
                    onclick="window.dispatchEvent(new Event('start-demo'))"
                    class="inline-flex items-center gap-3 px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all transform hover:scale-105"
                    aria-label="Start interactive demo"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Play Interactive Demo</span>
                </button>
                
                <p class="mt-4 text-sm text-slate-500">
                    Or <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium underline">sign up now</a> to get started
                </p>
            </div>
            
            <!-- Static Preview (Fallback) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center mt-12">
                <!-- Form Preview -->
                <div class="bg-white rounded-xl shadow-lg p-6 lg:p-8 border border-slate-200">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Client</label>
                            <select class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900" disabled>
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
                            <input type="checkbox" id="vat" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" disabled>
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
                            <span class="text-slate-600">Platform Fee (3%)</span>
                            <span class="font-medium text-slate-900">KES 1,740</span>
                        </div>
                        <div class="pt-3 border-t-2 border-slate-200 flex justify-between">
                            <span class="font-bold text-slate-900">Total</span>
                            <span class="font-black text-lg text-blue-600">KES 59,740</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Demo Walkthrough Modal -->
    <x-demo-walkthrough />
    
    @push('scripts')
        @vite('resources/js/demo-landing.js')
    @endpush

    <!-- HOW IT WORKS (Enhanced Business-Focused) -->
    <section id="invoicing-workflow" class="py-16 lg:py-24 bg-white scroll-mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    How InvoiceHub Works for Your Business
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    A complete invoicing solution designed for Kenyan businesses. Get professional invoices, track payments, and stay compliant—all in one place.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-8">
                <!-- Step 1: Setup & Branding -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 hover:border-blue-300 hover:shadow-lg transition-all">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-500 text-white rounded-full text-xl font-bold mb-4">
                        1
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Setup & Branding</h3>
                    <ul class="text-sm text-slate-600 space-y-2 text-left">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Create your company profile</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Upload your company logo</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Add KRA PIN & business details</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Step 2: Customize Your Invoices -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 hover:border-blue-300 hover:shadow-lg transition-all">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-500 text-white rounded-full text-xl font-bold mb-4">
                        2
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Customize Your Invoices</h3>
                    <ul class="text-sm text-slate-600 space-y-2 text-left">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Choose from 4 professional templates</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Set custom invoice number format</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Add prefix, suffix & padding</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Step 3: Manage Clients -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 hover:border-blue-300 hover:shadow-lg transition-all">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-500 text-white rounded-full text-xl font-bold mb-4">
                        3
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Manage Clients</h3>
                    <ul class="text-sm text-slate-600 space-y-2 text-left">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Add clients with contact details</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Build your client database</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Quick-select for future invoices</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Step 4: Create & Send -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 hover:border-blue-300 hover:shadow-lg transition-all">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-500 text-white rounded-full text-xl font-bold mb-4">
                        4
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Create & Send</h3>
                    <ul class="text-sm text-slate-600 space-y-2 text-left">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>6-step guided invoice wizard</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Auto-calculate 16% VAT & fees</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Send via Email, WhatsApp & PDF</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Additional Business Benefits -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-6 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-500 text-white rounded-lg mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-900 mb-2">KRA eTIMS Compliant</h4>
                    <p class="text-sm text-slate-600">Fully compliant with Kenyan tax regulations. All invoices meet KRA requirements automatically.</p>
                </div>
                
                <div class="text-center p-6 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-500 text-white rounded-lg mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-900 mb-2">Payment Tracking</h4>
                    <p class="text-sm text-slate-600">Track all payments, see what's outstanding, and get alerts for overdue invoices.</p>
                </div>
                
                <div class="text-center p-6 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-500 text-white rounded-lg mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-slate-900 mb-2">Business Insights</h4>
                    <p class="text-sm text-slate-600">Dashboard shows revenue, outstanding amounts, and business performance at a glance.</p>
                </div>
            </div>
        </div>
    </section>

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

@endsection
