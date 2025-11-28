@props(['icon', 'title', 'description'])

<div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100">
    <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg mb-4">
        {!! $icon !!}
    </div>
    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $title }}</h3>
    <p class="text-sm text-gray-600">{{ $description }}</p>
</div>

