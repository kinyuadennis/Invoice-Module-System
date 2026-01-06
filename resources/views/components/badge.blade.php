@props(['variant' => 'default'])

@php
$classes = [
    'default' => 'bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200',
    'primary' => 'bg-[#EFF6FF] dark:bg-blue-900/20 text-[#1D4ED8] dark:text-blue-300',
    'success' => 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200',
    'warning' => 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200',
    'danger' => 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200',
    'info' => 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200',
    'accent' => 'bg-purple-100 dark:bg-purple-900/20 text-purple-800 dark:text-purple-200',
][$variant] ?? 'bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ' . $classes]) }}>
    {{ $slot }}
</span>

