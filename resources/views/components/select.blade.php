@props(['name', 'label' => null, 'required' => false, 'options' => []])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 dark:text-gray-200 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select 
        name="{{ $name }}" 
        id="{{ $name }}"
        {{ $attributes->merge(['class' => 'block w-full min-h-[44px] py-3 px-4 rounded-lg border border-gray-200 dark:border-[#333333] bg-white dark:bg-[#1A1A1A] text-base text-gray-900 dark:text-gray-100 shadow-sm dark:shadow-[0_4px_12px_rgba(0,0,0,0.3)] transition-all duration-150 focus:border-[#2B6EF6] focus:ring-4 focus:ring-[#2B6EF6]/8 dark:focus:ring-[#2B6EF6]/20 focus:outline-none disabled:bg-gray-50 dark:disabled:bg-[#0D0D0D] disabled:text-gray-500 disabled:cursor-not-allowed appearance-none bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")] bg-[length:1.5em_1.5em] bg-[right_0.5rem_center] bg-no-repeat pr-10' . ($errors->has($name) ? ' border-red-300 focus:border-red-500 focus:ring-red-500/8' : '')]) }}
        @if($required) required @endif
    >
        @foreach($options as $option)
            <option value="{{ $option['value'] }}" {{ old($name, $attributes->get('value')) == $option['value'] ? 'selected' : '' }}>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
    
    @error($name)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

