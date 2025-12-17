@extends('layouts.public')

@section('title', 'Testimonials')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl lg:text-5xl font-black text-slate-900 mb-4">
            What Our Customers Say
        </h1>
        <p class="text-lg text-slate-600 max-w-3xl mx-auto">
            Real stories from Kenyan businesses using InvoiceHub to streamline their invoicing and get paid faster
        </p>
    </div>

    <!-- Filter by Rating -->
    <div class="mb-8 flex flex-wrap justify-center gap-2">
        <a 
            href="{{ route('testimonials') }}"
            class="px-4 py-2 rounded-lg {{ !request('rating') ? 'bg-blue-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }} border border-slate-200 transition-colors"
        >
            All Reviews
        </a>
        @for($i = 5; $i >= 1; $i--)
            <a 
                href="{{ route('testimonials', ['rating' => $i]) }}"
                class="px-4 py-2 rounded-lg {{ request('rating') == $i ? 'bg-blue-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }} border border-slate-200 transition-colors flex items-center gap-2"
            >
                <div class="flex items-center">
                    @for($j = 1; $j <= 5; $j++)
                        <svg class="w-4 h-4 {{ $j <= $i ? 'text-yellow-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <span>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</span>
            </a>
        @endfor
    </div>

    <!-- Testimonials Grid -->
    @if($reviews->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mb-12">
            @foreach($reviews as $review)
                <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 border border-slate-200 transform hover:-translate-y-1">
                    <!-- Rating -->
                    <div class="mb-4 flex items-center gap-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                    
                    <!-- Quote -->
                    <div class="mb-6">
                        <svg class="w-8 h-8 text-blue-400 mb-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                        <p class="text-slate-700 italic text-base leading-relaxed">{{ $review->content }}</p>
                    </div>
                    
                    <!-- Author -->
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                            {{ strtoupper(substr($review->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900">{{ $review->name }}</p>
                            @if($review->title)
                                <p class="text-sm text-slate-600">{{ $review->title }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $reviews->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <svg class="w-24 h-24 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No testimonials yet</h3>
            <p class="text-slate-600 mb-6">Be the first to share your experience with InvoiceHub!</p>
            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                Get Started
            </a>
        </div>
    @endif

    <!-- CTA Section -->
    <div class="mt-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl p-8 lg:p-12 text-center text-white">
        <h2 class="text-3xl lg:text-4xl font-bold mb-4">
            Ready to Get Started?
        </h2>
        <p class="text-lg text-blue-100 mb-6 max-w-2xl mx-auto">
            Join 500+ Kenyan businesses already using InvoiceHub to streamline their invoicing
        </p>
        <a 
            href="{{ route('register') }}" 
            class="inline-flex items-center gap-2 px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-50 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105"
        >
            Start Free Today
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>
</div>
@endsection

