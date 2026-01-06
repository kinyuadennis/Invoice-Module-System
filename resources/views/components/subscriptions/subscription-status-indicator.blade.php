@props([
    'status', // 'active' | 'pending' | 'grace' | 'expired' | 'cancelled'
    'size' => 'md',
    'showIcon' => true,
])

@php
$statusConfig = [
    'active' => [
        'bg' => 'bg-green-100 dark:bg-green-900/20',
        'text' => 'text-green-800 dark:text-green-200',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
        'label' => 'Active',
    ],
    'pending' => [
        'bg' => 'bg-yellow-100 dark:bg-yellow-900/20',
        'text' => 'text-yellow-800 dark:text-yellow-200',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z',
        'label' => 'Pending',
    ],
    'grace' => [
        'bg' => 'bg-orange-100 dark:bg-orange-900/20',
        'text' => 'text-orange-800 dark:text-orange-200',
        'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'label' => 'Grace Period',
    ],
    'expired' => [
        'bg' => 'bg-red-100 dark:bg-red-900/20',
        'text' => 'text-red-800 dark:text-red-200',
        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
        'label' => 'Expired',
    ],
    'cancelled' => [
        'bg' => 'bg-gray-100 dark:bg-gray-800',
        'text' => 'text-gray-800 dark:text-gray-200',
        'icon' => 'M5 8a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V8z',
        'label' => 'Cancelled',
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

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full font-semibold ' . $config['bg'] . ' ' . $config['text'] . ' ' . $sizeClasses]) }}>
    @if($showIcon)
        <svg class="{{ $iconSize }} flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="{{ $config['icon'] }}" clip-rule="evenodd"/>
        </svg>
    @endif
    <span>{{ $slot ?? $config['label'] }}</span>
</span>

