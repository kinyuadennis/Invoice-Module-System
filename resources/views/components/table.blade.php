@props(['striped' => false])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200' . ($striped ? ' bg-white' : '')]) }}>
        <thead class="bg-gray-50">
            {{ $header }}
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
</div>

