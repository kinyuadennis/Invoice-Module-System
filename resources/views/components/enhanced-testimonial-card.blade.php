@props(['testimonial', 'showVideo' => false])

<div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300">
    <!-- Quote Icon -->
    <div class="mb-4">
        <svg class="w-8 h-8 text-emerald-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
    </div>

    <!-- Quote -->
    <p class="text-gray-700 dark:text-gray-200 mb-6 italic">"{{ $testimonial['quote'] }}"</p>

    <!-- Metric -->
    @if(isset($testimonial['metric']))
        <div class="mb-6 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
            <p class="text-sm font-bold text-emerald-700">{{ $testimonial['metric'] }}</p>
        </div>
    @endif

    <!-- Author Info -->
    <div class="flex items-center">
        <!-- Avatar -->
        <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center text-white font-bold mr-3 flex-shrink-0">
            @if(isset($testimonial['photo']))
                <img src="{{ $testimonial['photo'] }}" alt="{{ $testimonial['name'] }}" class="w-full h-full rounded-full object-cover">
            @else
                {{ $testimonial['avatar'] ?? substr($testimonial['name'], 0, 2) }}
            @endif
        </div>

        <!-- Details -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <p class="font-semibold text-gray-900">{{ $testimonial['name'] }}</p>
                @if($testimonial['verified'] ?? false)
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ $testimonial['business'] }}</p>
            @if(isset($testimonial['location']))
                <p class="text-xs text-gray-500">{{ $testimonial['location'] }}</p>
            @endif
            @if(isset($testimonial['role']))
                <p class="text-xs text-gray-500">{{ $testimonial['role'] }}</p>
            @endif
        </div>
    </div>

    <!-- Video Review (optional) -->
    @if($showVideo && isset($testimonial['video']))
        <div class="mt-4 pt-4 border-t border-gray-200">
            <button class="flex items-center text-sm text-emerald-600 font-semibold hover:text-emerald-700">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                </svg>
                Watch Video Review
            </button>
        </div>
    @endif
</div>

