import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'

// Bootstrap y estilos
import 'bootstrap/dist/css/bootstrap.min.css';
import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';
window.bootstrap = bootstrap;

import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/app.less'

// Bootstrap JS (incluye Popper)
 

const pages = import.meta.glob('./Pages/**/*.vue')

createInertiaApp({
  resolve: name => {
    const page = pages[`./Pages/${name}.vue`]
    if (!page) {
      throw new Error(`Unknown page: ${name}. Available: ${Object.keys(pages).join(', ')}`)
    }
    return page()
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
