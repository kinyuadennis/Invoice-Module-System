
<script setup>
  import { computed } from 'vue'
  
  const props = defineProps({
    variant: {
      type: String,
      default: 'info',
      validator: (value) => ['success', 'warning', 'danger', 'info'].includes(value)
    },
    title: String,
    dismissible: Boolean
  })
  
  defineEmits(['close'])
  
  const variants = {
    success: {
      container: 'bg-green-50 border-green-200 text-green-800',
      icon: 'text-green-600',
      close: 'text-green-500 hover:text-green-700'
    },
    warning: {
      container: 'bg-amber-50 border-amber-200 text-amber-800',
      icon: 'text-amber-600',
      close: 'text-amber-500 hover:text-amber-700'
    },
    danger: {
      container: 'bg-red-50 border-red-200 text-red-800',
      icon: 'text-red-600',
      close: 'text-red-500 hover:text-red-700'
    },
    info: {
      container: 'bg-blue-50 border-blue-200 text-blue-800',
      icon: 'text-blue-600',
      close: 'text-blue-500 hover:text-blue-700'
    }
  }
  
  const alertClasses = computed(() => {
    return `p-4 border rounded-lg ${variants[props.variant].container}`
  })
  
  const titleClasses = computed(() => {
    return 'text-sm font-medium mb-1'
  })
  
  const messageClasses = computed(() => {
    return 'text-sm'
  })
  
  const closeClasses = computed(() => {
    return `${variants[props.variant].close} focus:outline-none`
  })
  
  const iconComponent = computed(() => {
    const icons = {
      success: 'svg',
      warning: 'svg',
      danger: 'svg',
      info: 'svg'
    }
    return icons[props.variant]
  })
  </script>
  
  <template>
    <div :class="alertClasses" role="alert">
      <div class="flex">
        <div class="flex-shrink-0">
          <component :is="iconComponent" class="w-5 h-5" />
        </div>
        <div class="ml-3 flex-1">
          <h3 v-if="title" :class="titleClasses">{{ title }}</h3>
          <div :class="messageClasses">
            <slot />
          </div>
        </div>
        <div v-if="dismissible" class="ml-auto pl-3">
          <button @click="$emit('close')" :class="closeClasses">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </template>