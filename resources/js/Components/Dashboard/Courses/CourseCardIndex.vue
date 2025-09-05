<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  course: { type: Object, required: true },
  routeName: { type: String, default: 'dashboard.courses.show' },
  href: { type: String, default: '' },
  buttonText: { type: String, default: 'Ir al curso' },
  inactiveText: { type: String, default: 'Próximamente' },
})

const computedHref = computed(() => {
  if (props.href) return props.href
  try {
    return route(props.routeName, props.course.id)
  } catch {
    return `/courses/${props.course.id}`
  }
})
</script>

<template>

  <div class="card h-100 shadow-sm border-0">
    <!-- Portada + logo flotante -->
    <div class="position-relative">
      <img
        class="card-img-top course-cover"
        :src="course.image_cover ? `/storage/${course.image_cover}` : '/images/default-cover.jpg'"
        :alt="course.title || 'Portada del curso'"
        style="min-height: 250px; object-fit: cover; border-top-left-radius: .75rem; border-top-right-radius: .75rem; max-height: 300px;"
      />

      <img
        v-if="course.logo"
        :src="`/storage/${course.logo}`"
        alt="Logo del curso"
        class="course-logo shadow bg-white rounded p-2"
      />
    </div>

    <!-- Cuerpo -->
    <div class="card-body d-flex flex-column">
      <div class="flex-grow-1 mb-3">
        <h5 class="card-title fw-semibold text-truncate mb-1"><i class="bi bi-balloon"></i> {{ course.title }}</h5>
        <p class="card-subtitle text-muted small mb-2">
          {{ course.videos_count ?? 0 }} video(s)
        </p>
        <p class="text-muted small mb-0" v-if="course.description_short">{{ course.description_short }}</p>
      </div>

      <!-- Botón al fondo -->
      <div class="mt-auto">
        <template v-if="course.active">
          <Link :href="computedHref" class="btn btn-primary w-100 d-flex align-items-center justify-content-center py-2">
            <i class="bi bi-play-circle me-2"></i>{{ buttonText }}
          </Link>
        </template>
        <template v-else>
          <button class="btn btn-secondary w-100 d-flex align-items-center justify-content-center py-2" disabled>
            <i class="bi bi-lock me-2"></i>{{ inactiveText }}
          </button>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.course-cover {
  display: block;
  width: 100%;
}

.course-logo {
  position: absolute;
  left: 15px;
  top: 20px;            /* sobresale ligeramente hacia el cuerpo */
  max-width: 140px;         /* no supera 100px de ancho */
  height: auto;
  object-fit: contain;
  /* Sombra y borde ya vienen por clases, opcional reforzar: */
  /* box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); */
}
</style>
