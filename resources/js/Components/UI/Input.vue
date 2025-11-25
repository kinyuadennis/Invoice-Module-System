<script setup>
  import { computed } from 'vue'
  
  const props = defineProps({
    id: String,
    label: String,
    type: {
      type: String,
      default: 'text'
    },
    modelValue: [String, Number],
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    error: String,
    hint: String
  })
  
  defineEmits(['update:modelValue'])
  
  const inputClasses = computed(() => {
    const base = 'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors'
    const error = props.error ? 'border-red-300 focus:ring-red-500' : 'border-gray-300'
    const disabled = props.disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
    
    return `${base} ${error} ${disabled}`
  })
</script>

<template>
    <div>
      <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-2">
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
      </label>
      <div class="relative">
        <input
          :id="id"
          :type="type"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          :placeholder="placeholder"
          :required="required"
          :disabled="disabled"
          :class="inputClasses"
          v-bind="$attrs"
        />
        <div v-if="$slots.icon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <slot name="icon" />
        </div>
      </div>
      <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
      <p v-else-if="hint" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
    </div>
</template>