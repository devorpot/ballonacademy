<!-- resources/js/Components/Dashboard/Videos/VideoList.vue -->
<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  course: { type: Object, required: true },
  lesson: { type: Object, required: true }
})

// Tooltip dinámico para el icono de estado
function videoTooltip(video) {
  if (video.is_ended) return 'Completado'
  if (video.is_accessible) return 'Disponible'
  return 'Bloqueado'
}
</script>

<template>
  <div v-if="(lesson.videos && lesson.videos.length) || (lesson.videos_count > 0)" class="mt-1">
    <div class="d-flex align-items-center mb-2">
      <i class="bi bi-play-circle me-2"></i>
      <h6 class="mb-0">Clases de la lección</h6>
    </div>

    <ul class="list-group list-group-flush">
      <li
        v-for="(video, idx) in (lesson.videos || [])"
        :key="video.id ?? idx"
        class="list-group-item px-0 py-2"
      >
        <div class="d-flex align-items-center gap-3">
          <!-- Thumbnail -->
          <img
            v-if="video.thumbnail"
            :src="`/storage/${video.thumbnail}`"
            alt="Miniatura del video"
            class="rounded border"
            style="width: 100px; height: 100px; object-fit: cover;"
          />

          <!-- Número -->
          <span class="text-muted small" style="width: 2rem;">
            {{ (idx + 1).toString().padStart(2, '0') }}
          </span>

          <!-- Estado -->
          <i
            class="bi"
            :class="video.is_ended ? 'bi-check2-circle text-success' : (video.is_accessible ? 'bi-play-btn text-primary' : 'bi-lock-fill text-warning')"
            :title="videoTooltip(video)"
          />

          <!-- Info -->
          <div class="flex-grow-1">
            <div class="fw-semibold" :class="{ 'text-muted': !video.is_accessible }">
              {{ video.title }}
            </div>
            <small class="text-muted d-block">{{ video.description }}</small>
          </div>

          <!-- Duración -->
          <small class="text-muted text-nowrap" v-if="video.duration">{{ video.duration }}</small>

          <!-- Acción -->
          <div class="ms-2 d-flex gap-2">
            <Link
              v-if="video.is_accessible"
              :href="route('dashboard.courses.lessons.videos.show', {
                course: course.id,
                lesson: lesson.id,
                video: video.id
              })"
              class="btn btn-primary align-items-center px-3 py-2"
            >
              Ver video
            </Link>
          </div>
        </div>
      </li>

      <!-- Solo conteo si no vino el arreglo -->
      <li
        v-if="(!lesson.videos || lesson.videos.length === 0) && (lesson.videos_count > 0)"
        class="list-group-item px-0 text-muted"
      >
        Hay {{ lesson.videos_count }} videos asignados a esta lección.
      </li>
    </ul>
  </div>
</template>
