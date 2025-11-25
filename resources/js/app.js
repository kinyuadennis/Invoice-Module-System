import './bootstrap';
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'


createInertiaApp({
  resolve: name => {
    const page = require(`./Pages/${name}.vue`).default
    // Use AppLayout by default unless page sets its own
    page.layout ??= AppLayout
    return page
  },

  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
