@extends('layouts.public')

@section('title', 'Home')

@section('content')
    <!-- HERO SECTION -->
    <x-hero-enhanced
        title="Stop Chasing Payments. Get Paid in 7 Days, Not 30."
        subtitle="Send invoices, accept M-Pesa payments, track overdue accounts. KRA eTIMS compliant. Trusted by 500+ Kenyan SMEs, freelancers, and agencies."
        ctaPrimary="Start Free"
        ctaSecondary="Explore Features"
        :stats="$stats"
    >
        <x-invoice-preview />
    </x-hero-enhanced>

    <!-- RECENT INVOICES -->
    <x-invoice-showcase
        :invoices="$recentInvoices"
        :showFilters="true"
        :showActions="false"
    />

    <!-- HOW IT WORKS -->
    <x-how-it-works-persona
        :persona="['name' => 'Sarah', 'role' => 'Design Agency Owner', 'location' => 'Nairobi', 'business' => 'Creative Studio']"
        :steps="$steps"
    />

    <!-- FEATURES -->
    <x-features-showcase
        :categories="['payment', 'compliance', 'analytics', 'automation']"
        :features="$features"
        :showComparison="true"
    />

    <!-- PRICING -->
    <x-pricing-showcase
        :plans="$plans"
        :showYearly="true"
        :showComparison="true"
        :showROI="true"
    />

    <!-- TESTIMONIALS -->
    <x-testimonials-showcase
        :testimonials="$testimonials"
        :showFilter="true"
        :showVideo="false"
    />

    <!-- ROI CALCULATOR -->
    <x-roi-calculator
        :defaultValues="['invoicesPerMonth' => 20, 'avgInvoiceValue' => 50000, 'currentDelay' => 30]"
        :showChart="true"
        :showCTA="true"
    />

    <!-- FINAL CTA -->
    <section class="bg-gradient-to-r from-emerald-600 to-teal-600 py-16 lg:py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Ready to Get Paid Faster?</h2>
            <p class="text-xl text-emerald-100 mb-8">Join 500+ Kenyan businesses using InvoiceHub</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-emerald-600 font-bold rounded-lg hover:bg-gray-50 shadow-xl transition-all duration-200 transform hover:scale-105">
                    Start Free Today
                </a>
                <a href="#features" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-all duration-200">
                    Learn More
                </a>
            </div>
            <p class="mt-6 text-sm text-emerald-200">No credit card required • Free forever • Pay only when you get paid</p>
        </div>
    </section>
@endsection

