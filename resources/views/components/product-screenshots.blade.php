@props([
    'screenshots' => [],
])

@php
    // Default screenshots if none provided (with fallbacks)
    $defaultScreenshots = [
        [
            'title' => 'Dashboard Overview',
            'image' => asset('images/screenshots/dashboard.png'),
            'fallback' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IiM2YjcyODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5EYXNoYm9hcmQgT3ZlcnZpZXc8L3RleHQ+PC9zdmc+',
        ],
        [
            'title' => 'Invoice Creation',
            'image' => asset('images/screenshots/invoice-creation.png'),
            'fallback' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IiM2YjcyODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbnZvaWNlIENyZWF0aW9uPC90ZXh0Pjwvc3ZnPg==',
        ],
        [
            'title' => 'Payment Tracking',
            'image' => asset('images/screenshots/payment-tracking.png'),
            'fallback' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IiM2YjcyODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5QYXltZW50IFRyYWNraW5nPC90ZXh0Pjwvc3ZnPg==',
        ],
    ];
    
    $screenshots = !empty($screenshots) ? $screenshots : $defaultScreenshots;
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
    @foreach($screenshots as $index => $screenshot)
        <div class="relative group">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="aspect-[4/3] bg-slate-100 relative">
                    <img 
                        src="{{ $screenshot['image'] ?? ($screenshot['fallback'] ?? '') }}"
                        alt="{{ $screenshot['title'] ?? 'Product Screenshot' }}"
                        class="w-full h-full object-cover"
                        onerror="this.src='{{ $screenshot['fallback'] ?? '' }}'"
                        loading="lazy"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-slate-900 text-sm">{{ $screenshot['title'] ?? 'Screenshot' }}</h3>
                </div>
            </div>
        </div>
    @endforeach
</div>

