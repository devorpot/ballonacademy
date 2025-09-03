<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  username: {
    type: String,
    default: 'Usuario'
  },
  breadcrumbs: {
    type: Array,
    default: () => [
      { label: 'Dashboard', route: 'dashboard.index' }
    ]
  }
})

// Detecta si el valor es un nombre de ruta Ziggy o ya es una URL completa
function isRouteName(value) {
  return typeof value === 'string' && !value.startsWith('http') && !value.startsWith('/')
}
</script>

<template>
  <section class="section-breadcrumbs w-100 d-block my-1">
    <div class="container-fluid">
      <div class="card border-0 border-bottom   rounded">
        
      <div class="card-body   p-2">
      <div class="row align-items-center">
        <div class="col-12 col-md-6 my-0">
          
              <h2 class="fw-bold h6 text-uppercase">
              {{ breadcrumbs.at(-1)?.label || 'Dashboard' }}
            </h2>

            <!-- Breadcrumb navegable -->
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li
                  v-for="(item, index) in breadcrumbs"
                  :key="index"
                  class="breadcrumb-item"
                  :class="{ active: index === breadcrumbs.length - 1 }"
                  aria-current="page"
                >
                  <!-- Último breadcrumb (activo) -->
                  <span v-if="index === breadcrumbs.length - 1 || !item.route">
                    {{ item.label }}
                  </span>

                  <!-- Link navegable -->
                  <Link
                    v-else
                    :href="isRouteName(item.route)
                      ? (item.params ? route(item.route, item.params) : route(item.route))
                      : item.route"
                  >
                    {{ item.label }}
                  </Link>
                </li>
              </ol>
            </nav>
           </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>



<style lang="less">
  .section-breadcrumbs{
    .breadcrumb-item{
      font-size: 0.9rem;
    }
  }
</style>