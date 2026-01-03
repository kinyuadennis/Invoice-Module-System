@props([
    'status', // 'success' | 'pending' | 'failed' | 'timeout' | 'initiated'
    'size' => 'md', // 'sm' | 'md' | 'lg'
    'showIcon' => true,
])

@php
$statusConfig = [
    'success' => [
        'bg' => 'bg-green-100 dark:bg-green-900/20',
        'text' => 'text-green-800 dark:text-green-200',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
        'label' => 'Success',
    ],
    'pending' => [
        'bg' => 'bg-yellow-100 dark:bg-yellow-900/20',
        'text' => 'text-yellow-800 dark:text-yellow-200',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z',
        'label' => 'Pending',
    ],
    'failed' => [
        'bg' => 'bg-red-100 dark:bg-red-900/20',
        'text' => 'text-red-800 dark:text-red-200',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
        'label' => 'Failed',
    ],
    'timeout' => [
        'bg' => 'bg-orange-100 dark:bg-orange-900/20',
        'text' => 'text-orange-800 dark:text-orange-200',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z',
        'label' => 'Timeout',
    ],
    'initiated' => [
        'bg' => 'bg-blue-100 dark:bg-blue-900/20',
        'text' => 'text-blue-800 dark:text-blue-200',
        'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z',
        'label' => 'Initiated',
    ],
];

$config = $statusConfig[strtolower($status)] ?? $statusConfig['pending'];
$sizeClasses = [
    'sm' => 'text-xs px-2 py-0.5',
    'md' => 'text-xs px-2.5 py-1',
    'lg' => 'text-sm px-3 py-1.5',
][$size] ?? 'text-xs px-2.5 py-1';

$iconSize = [
    'sm' => 'w-3 h-3',
    'md' => 'w-4 h-4',
    'lg' => 'w-5 h-5',
][$size] ?? 'w-4 h-4';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-md font-semibold ' . $config['bg'] . ' ' . $config['text'] . ' ' . $sizeClasses]) }}>
    @if($showIcon)
        <svg class="{{ $iconSize }} flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="{{ $config['icon'] }}" clip-rule="evenodd"/>
        </svg>
    @endif
    <span>{{ $slot ?? $config['label'] }}</span>
</span>

