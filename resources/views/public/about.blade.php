@extends('layouts.public')

@section('title', 'About')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
    <!-- About InvoiceHub Section -->
    <div class="max-w-4xl mx-auto mb-12">
        <div class="text-center mb-8">
            <h1 class="text-3xl lg:text-4xl font-black text-slate-900 mb-4">
                About InvoiceHub
            </h1>
            <p class="text-lg lg:text-xl text-slate-600 max-w-3xl mx-auto leading-relaxed">
                Smart, secure, and compliant invoicing designed for Kenyan businesses.
            </p>
        </div>

        <div class="prose prose-slate max-w-none text-center">
            <p class="text-base text-slate-700 leading-relaxed">
                InvoiceHub is a modern invoicing platform that helps businesses create professional invoices, track payments, and maintain accurate financial records with ease. Built with Kenya's regulatory landscape in mind, it ensures speed, compliance, and simplicity for SMEs, freelancers, and enterprises.
            </p>
        </div>
    </div>

    <!-- Team Section -->
    @if(isset($teamMembers) && count($teamMembers) > 0)
        <div class="max-w-5xl mx-auto mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Our Team</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Meet the people behind InvoiceHub
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($teamMembers as $member)
                    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow text-center">
                        <div class="mb-4">
                            @if(isset($member['photo']) && file_exists(public_path(str_replace(asset(''), '', $member['photo']))))
                                <img 
                                    src="{{ $member['photo'] }}" 
                                    alt="{{ $member['name'] }}"
                                    class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-slate-200"
                                >
                            @else
                                <div class="w-32 h-32 rounded-full mx-auto bg-blue-500 flex items-center justify-center text-white text-4xl font-bold border-4 border-slate-200">
                                    {{ strtoupper(substr($member['name'], 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-1">{{ $member['name'] }}</h3>
                        <p class="text-blue-600 font-semibold mb-3">{{ $member['role'] }}</p>
                        @if(isset($member['bio']))
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $member['bio'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Mission & Vision Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 mb-12 max-w-5xl mx-auto">
        <!-- Our Mission -->
        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900">Our Mission</h2>
            </div>
            <p class="text-sm text-slate-700 leading-relaxed">
                To simplify how Kenyan businesses handle invoicing and payments by providing an intuitive, reliable, and compliant digital invoicing solution.
            </p>
        </div>

        <!-- Our Vision -->
        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900">Our Vision</h2>
            </div>
            <p class="text-sm text-slate-700 leading-relaxed">
                To become the most trusted and widely adopted invoicing and business automation platform in East Africa.
            </p>
        </div>
    </div>

    <!-- Company Story/Timeline -->
    @if(isset($companyStory) && count($companyStory) > 0)
        <div class="max-w-4xl mx-auto mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Our Story</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    From launch to today — our journey building InvoiceHub
                </p>
            </div>
            
            <x-company-timeline :timeline="$companyStory" />
        </div>
    @endif

    <!-- Values/Personality Section -->
    @if(isset($values) && count($values) > 0)
        <div class="max-w-5xl mx-auto mb-12">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Our Values</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    What drives us every day
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($values as $value)
                    <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="text-4xl flex-shrink-0">{{ $value['icon'] ?? '💡' }}</div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $value['title'] ?? '' }}</h3>
                                <p class="text-slate-600 leading-relaxed">{{ $value['description'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Contact Us Section -->
    <div class="max-w-4xl mx-auto mb-12">
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-6 lg:p-8 border border-slate-200">
            <div class="text-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-2">Contact Us</h2>
                <p class="text-base text-slate-600">
                    We're here to help you get the most out of your invoicing experience.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left">
                <!-- Company Details -->
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-2">Company</h3>
                    <p class="text-base font-medium text-slate-900 mb-1">Nuvemite Technologies</p>
                    <p class="text-sm text-slate-600">Nairobi, Kenya</p>
                </div>

                <!-- Email -->
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-2">Email</h3>
                    <a href="mailto:denis@nuvemite.co.ke" class="text-blue-600 hover:text-blue-700 font-medium text-base">
                        denis@nuvemite.co.ke
                    </a>
                </div>

                <!-- Business Hours -->
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-2">Business Hours</h3>
                    <p class="text-sm text-slate-700">Mon–Fri, 8:00 AM – 5:00 PM</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Today CTA Section -->
    <div class="max-w-4xl mx-auto">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl p-8 lg:p-10 text-center text-white shadow-xl">
            <h2 class="text-2xl lg:text-3xl font-bold mb-3">
                Start Today
            </h2>
            <p class="text-lg text-blue-100 mb-6 max-w-2xl mx-auto">
                Experience fast, reliable, and compliant invoicing built for modern Kenyan businesses.
            </p>
            <a 
                href="{{ route('register') }}" 
                class="inline-flex items-center gap-2 px-8 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-50 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105"
            >
                Get Started with InvoiceHub
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection
