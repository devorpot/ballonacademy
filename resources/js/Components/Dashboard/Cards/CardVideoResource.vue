<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  resources: { type: Array, default: () => [] },
  routeShow: { type: String, default: 'dashboard.videos.resources.show' },
  videoId: { type: [String, Number], required: true }
})

function iconClass(type) {
  switch (type) {
    case 1: return 'bi-file-earmark-text'
    case 2: return 'bi-camera-video'
    case 3: return 'bi-image'
    default: return 'bi-collection'
  }
}

function badgeClass(type) {
  switch (type) {
    case 1: return 'bg-secondary'
    case 2: return 'bg-primary'
    case 3: return 'bg-success'
    default: return 'bg-dark'
  }
}
</script>

<template>
  <div class="d-flex flex-column gap-3">
    <div
      v-for="res in resources"
      :key="res.id"
      class="card shadow-sm border-0"
    >
      <div class="row g-0 flex-md-row flex-column">
        <!-- Lateral -->
        <div class="col-auto d-flex align-items-center">
          <div class="h-100 d-none d-md-block bg-light"
               style="width:6px; border-top-left-radius:.5rem; border-bottom-left-radius:.5rem;"></div>
          <div class="p-3 d-md-none d-flex align-items-center">
            <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center"
                 style="width:48px; height:48px;">
              <i class="bi" :class="iconClass(res.type)"></i>
            </div>
          </div>
        </div>

        <!-- Contenido -->
        <div class="col">
          <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div class="d-flex flex-column">
                <h5 class="card-title mb-1">{{ res.title }}</h5>

                <div class="d-flex flex-wrap align-items-center gap-2">
                  <span class="badge" :class="badgeClass(res.type)">
                    <i class="bi me-1" :class="iconClass(res.type)"></i>
                    {{ res.type_label }}
                  </span>
                  <span v-if="res.uploaded" class="badge text-bg-light border">
                    {{ res.uploaded }}
                  </span>
                </div>
              </div>

              <!-- Acción: ver recurso -->
              <div>
                <Link
                  class="btn btn-outline-primary btn-sm fw-semibold d-inline-flex align-items-center gap-2"
                  :href="route(routeShow, { video: videoId, resource: res.id })"
                  title="Ver recurso"
                >
                  <i class="bi bi-eye"></i> Ver recurso
                </Link>
              </div>
            </div>

            <p v-if="res.description" class="mt-2 small text-muted mb-0">
              {{ res.description }}
            </p>
          </div>
        </div>
      </div>
    </div> <!-- /card -->
  </div>
</template>
