
  <script setup>
  import { computed } from 'vue'
  
  const props = defineProps({
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'success', 'warning', 'danger', 'info'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
  })
  
  const badgeClasses = computed(() => {
    const base = 'inline-flex items-center font-semibold rounded-full'
    
    const variants = {
      default: 'bg-gray-100 text-gray-800',
      success: 'bg-green-100 text-green-800',
      warning: 'bg-amber-100 text-amber-800',
      danger: 'bg-red-100 text-red-800',
      info: 'bg-blue-100 text-blue-800'
    }
    
    const sizes = {
      sm: 'px-2 py-0.5 text-xs',
      md: 'px-3 py-1 text-sm',
      lg: 'px-4 py-1.5 text-base'
    }
    
    return `${base} ${variants[props.variant]} ${sizes[props.size]}`
  })
  </script>

  <template>
    <span :class="badgeClasses">
      <slot />
    </span>
  </template>