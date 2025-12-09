@props(['name', 'label' => null, 'required' => false, 'options' => []])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select 
        name="{{ $name }}" 
        id="{{ $name }}"
        {{ $attributes->merge(['class' => 'block w-full min-h-[44px] py-3 px-4 rounded-lg border border-gray-200 bg-white text-base text-gray-900 shadow-sm transition-all duration-150 focus:border-[#2B6EF6] focus:ring-4 focus:ring-[#2B6EF6]/8 focus:outline-none disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed appearance-none bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e")] bg-[length:1.5em_1.5em] bg-[right_0.5rem_center] bg-no-repeat pr-10' . ($errors->has($name) ? ' border-red-300 focus:border-red-500 focus:ring-red-500/8' : '')]) }}
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

