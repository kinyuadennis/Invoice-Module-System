import './bootstrap';
import { createApp, h } from 'vue'
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';
import { createInertiaApp } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

createInertiaApp({
  resolve: async (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    
    // Convert dot notation (auth.login) to path notation (Auth/Login)
    const path = name.replace(/\./g, '/')
    
    // Try exact match first
    let page = pages[`./Pages/${path}.vue`]
    
    if (!page) {
      // Try with capitalized first letter of each segment
      const capitalizedPath = path.split('/').map(segment => 
        segment.charAt(0).toUpperCase() + segment.slice(1)
      ).join('/')
      page = pages[`./Pages/${capitalizedPath}.vue`]
      
      if (!page) {
        throw new Error(`Page not found: ${name} (tried: ./Pages/${path}.vue and ./Pages/${capitalizedPath}.vue)`)
      }
    }
    
    // Use AppLayout by default unless page sets its own
    page.default.layout ??= AppLayout
    return page.default
  },

  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .mount(el)
  },
})
