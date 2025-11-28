@props(['quote', 'author', 'business', 'avatar'])

<div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300">
    <div class="mb-4">
        <svg class="w-8 h-8 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
    </div>
    <p class="text-gray-700 mb-6 italic">"{{ $quote }}"</p>
    <div class="flex items-center">
        <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
            {{ $avatar }}
        </div>
        <div>
            <p class="font-semibold text-gray-900">{{ $author }}</p>
            <p class="text-sm text-gray-600">{{ $business }}</p>
        </div>
    </div>
</div>

