@props(['padding' => 'default'])

@php
$paddingClasses = [
    'none' => '',
    'sm' => 'p-3',
    'default' => 'p-5',
    'lg' => 'p-6',
    'xl' => 'p-8',
][$padding] ?? 'p-5';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-shadow duration-150 hover:shadow-md ' . $paddingClasses]) }}>
    {{ $slot }}
</div>

