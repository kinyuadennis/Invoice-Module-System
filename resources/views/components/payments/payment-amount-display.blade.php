@props([
    'amount',
    'currency' => 'KES',
    'locale' => 'en_US',
    'size' => 'md', // 'sm' | 'md' | 'lg' | 'xl'
    'showCurrency' => true,
])

@php
// Format currency using PHP's NumberFormatter
$formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
$formatted = $formatter->formatCurrency((float) $amount, $currency);

// Size classes
$sizeClasses = [
    'sm' => 'text-lg',
    'md' => 'text-xl',
    'lg' => 'text-2xl',
    'xl' => 'text-3xl',
][$size] ?? 'text-xl';

// Extract just the number if currency symbol should be separate
if (!$showCurrency) {
    $formatted = $formatter->format((float) $amount);
}
@endphp

<span {{ $attributes->merge(['class' => 'font-bold ' . $sizeClasses]) }}>
    {{ $formatted }}
</span>

