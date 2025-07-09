<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  username: {
    type: String,
    default: 'Usuario'
  },
  breadcrumbs: {
    type: Array,
    default: () => [
      { label: 'Dashboard', route: 'admin.dashboard' }
    ]
  }
});
</script>

<template>
  <section class="section-breadcrumbs w-100 d-block my-3">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-12 col-md-6">
          <div class="d-block">
            <h2 class="fw-bold h3">{{ breadcrumbs.at(-1)?.label || 'Dashboard' }}</h2>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li
                  v-for="(item, index) in breadcrumbs"
                  :key="index"
                  class="breadcrumb-item"
                  :class="{ active: index === breadcrumbs.length - 1 }"
                  aria-current="page"
                >
                  <span v-if="index === breadcrumbs.length - 1">
                    {{ item.label }}
                  </span>
                  <Link
                    v-else
                    :href="item.params ? route(item.route, item.params) : route(item.route)"
                  >
                    {{ item.label }}
                  </Link>
                </li>
              </ol>
            </nav>
          </div>
        </div>

        <div class="col-12 col-md-6 text-end">
          <div class="d-block">
            <p>Bienvenido <strong>@{{ username }}</strong></p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
