@props(['client'])

@php
    // Generate a color based on client name for consistent avatar colors
    $colors = ['bg-indigo-500', 'bg-purple-500', 'bg-pink-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-teal-500'];
    $colorIndex = crc32($client['name']) % count($colors);
    $bgColor = $colors[$colorIndex];
@endphp

<div class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md hover:scale-105 transition-all duration-200">
    <div class="flex items-center space-x-4">
        <div class="flex-shrink-0">
            <div class="h-12 w-12 {{ $bgColor }} rounded-full flex items-center justify-center text-white font-semibold text-lg">
                {{ $client['initials'] }}
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-base font-semibold text-gray-900 truncate">{{ $client['name'] }}</h3>
            <p class="text-sm text-gray-600 truncate">{{ $client['email'] }}</p>
        </div>
    </div>
</div>

