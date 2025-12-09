@props(['type' => 'text', 'name', 'label' => null, 'required' => false, 'value' => null])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'block w-full min-h-[44px] py-3 px-4 rounded-lg border border-gray-200 bg-white text-base text-gray-900 placeholder-gray-400 shadow-sm transition-all duration-150 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/8 focus:outline-none disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed' . ($errors->has($name) ? ' border-red-300 focus:border-red-500 focus:ring-red-500/8' : '')]) }}
        @if($required) required @endif
    >
    
    @error($name)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

