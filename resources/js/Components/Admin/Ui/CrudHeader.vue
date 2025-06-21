<template>
  <section class="section-heading">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h1 class="h3 mb-0">
            <i v-if="icon" :class="`${icon} me-2`"></i>{{ title }}
          </h1>
          <Link :href="createRoute" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> {{ createLabel }}
          </Link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
const props = defineProps({
  title: String,
  createRoute: String,
  createLabel: { type: String, default: 'Nuevo' },
  icon: { type: String, default: '' }
});
</script>
