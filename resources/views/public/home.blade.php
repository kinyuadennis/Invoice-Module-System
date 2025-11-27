@extends('layouts.public')

@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-b from-indigo-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24 lg:py-32">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl lg:text-7xl">
                Invoice Management
                <span class="text-indigo-600 block mt-2">Made Simple</span>
            </h1>
            <p class="mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600">
                Create, manage, and track invoices effortlessly. Professional invoicing software designed for Kenyan businesses. Get paid faster with automated reminders and payment tracking.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:shadow-xl transition-all duration-200">
                    Start Free Trial
                </a>
                <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 border border-gray-300 text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50 shadow-sm hover:shadow-md transition-all duration-200">
                    See Features
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Invoices Section -->
@if(isset($recentInvoices) && $recentInvoices->count() > 0)
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Recent Invoices</h2>
        <p class="mt-4 text-lg text-gray-600">See how easy it is to manage your invoices</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($recentInvoices as $invoice)
            <x-invoice-card :invoice="$invoice" />
        @endforeach
    </div>
</div>
@endif

<!-- Demo Clients Section -->
@if(isset($demoClients) && $demoClients->count() > 0)
<div id="features" class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Trusted by Kenyan Businesses</h2>
            <p class="mt-4 text-lg text-gray-600">Join leading companies managing their invoices with InvoiceHub</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($demoClients as $client)
                <x-client-card :client="$client" />
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Call to Action Section -->
<div class="bg-indigo-600 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                Start Creating Invoices Free Today
            </h2>
            <p class="mt-4 text-lg text-indigo-100 max-w-2xl mx-auto">
                Create unlimited invoices for free. Only pay a small platform fee (5%) when you receive payment. No monthly subscriptions, no hidden fees.
            </p>
            <div class="mt-10">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50 shadow-lg hover:shadow-xl transition-all duration-200">
                    Get Started Free
                </a>
            </div>
            <p class="mt-6 text-sm text-indigo-200">
                No credit card required • Free forever • Pay only when you get paid
            </p>
        </div>
    </div>
</div>
@endsection
