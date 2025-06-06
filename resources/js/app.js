import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'
import 'bootstrap-icons/font/bootstrap-icons.css'
import '../css/app.less';

const pages = import.meta.glob('./Pages/**/*.vue') // <- permite rutas anidadas

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
