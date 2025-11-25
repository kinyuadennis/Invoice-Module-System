
  
  <script setup>
  import { computed } from 'vue'
  
  const props = defineProps({
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    message: String,
    centered: {
      type: Boolean,
      default: true
    }
  })
  
  const containerClasses = computed(() => {
    return props.centered ? 'flex flex-col items-center justify-center' : ''
  })
  
  const spinnerClasses = computed(() => {
    const base = 'animate-spin rounded-full border-b-2 border-blue-600'
    const sizes = {
      sm: 'h-6 w-6',
      md: 'h-12 w-12',
      lg: 'h-16 w-16'
    }
    return `${base} ${sizes[props.size]}`
  })
  </script>
  
  <template>
    <div :class="containerClasses">
      <div :class="spinnerClasses"></div>
      <p v-if="message" class="mt-3 text-sm text-gray-600">{{ message }}</p>
    </div>
  </template>