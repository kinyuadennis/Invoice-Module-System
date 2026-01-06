@props(['active' => false])

@php
$classes = $active
    ? 'bg-indigo-50 border-indigo-500 text-indigo-700 group flex items-center px-2 py-2 text-sm font-medium border-l-4'
    : 'border-transparent text-gray-600 dark:text-gray-300 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium border-l-4';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

