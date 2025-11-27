@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
$variantClasses = [
    'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
    'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500',
    'outline' => 'border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-indigo-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'ghost' => 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
][$variant] ?? 'bg-indigo-600 text-white hover:bg-indigo-700';

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-base',
    'lg' => 'px-6 py-3 text-lg',
][$size] ?? 'px-4 py-2 text-base';
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors ' . $variantClasses . ' ' . $sizeClasses]) }}>
    {{ $slot }}
</button>

