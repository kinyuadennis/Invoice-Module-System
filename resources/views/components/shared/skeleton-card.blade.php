@props([
    'lines' => 3, // Number of skeleton lines
])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
    @for($i = 0; $i < $lines; $i++)
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded {{ $i < $lines - 1 ? 'mb-2' : '' }}" style="width: {{ rand(60, 100) }}%"></div>
    @endfor
</div>

