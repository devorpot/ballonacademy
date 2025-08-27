<!-- resources/js/Components/Dashboard/Cards/CardVideoEnded.vue -->
<template>
  <div class="card h-100 shadow-sm video-card">
    <!-- Imagen de portada -->
    <img
      class="card-img-top"
      :src="video.image_cover ? `/storage/${video.image_cover}` : '/images/default-cover.jpg'"
      :alt="video.title"
    />

    <div class="card-body d-flex flex-column">
      <!-- Título del video -->
      <h5 class="card-title text-truncate mb-2">{{ video.title }}</h5>

      <!-- Info del curso -->
      <p class="card-subtitle text-muted small mb-2">
        Curso: {{ video.course?.title }}
      </p>

      <!-- Duración y tamaño -->
      <div class="d-flex justify-content-between small text-muted mb-2">
        <span><i class="bi bi-clock me-1"></i>{{ video.duration }}</span>
        <span><i class="bi bi-hdd me-1"></i>{{ video.size }}</span>
      </div>

      <!-- Última vez visto -->
      <p class="text-muted small mb-3">
        Última vez visto: {{ formatDate(video.last_seen) }}
      </p>

      <!-- Botón reproducir -->
      <div class="mt-auto">
        <Link
          class="btn btn-primary w-100"
          :href="playUrl"
        >
          <i class="bi bi-play-circle me-1"></i> Ver de nuevo
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

// Prop que recibe el video
const props = defineProps({
  video: {
    type: Object,
    required: true,
  },
})

// Construcción de la URL de reproducción
const playUrl = route('dashboard.courses.videos.show', {
  course: props.video.course?.id,
  video: props.video.id,
})

// Función para formatear fecha
function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleString('es-MX', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.video-card img {
  height: 180px;
  object-fit: cover;
}
</style>
