<template>
  <div class="video-card card mb-3 shadow-sm">
    <div class="row g-0">
      <div class="col-4">
        <img
          :src="video.image_cover ? `/storage/${video.image_cover}` : 'https://placehold.co/300x180?text=Sin+imagen'"
          class="img-fluid rounded-start"
          :alt="video.title || 'Video sin título'"
        />
      </div>
      <div class="col-8">
        <div class="card-body">
          <h6 class="card-title text-uppercase fw-bold text-primary mb-1">
            Capítulo {{ video.order ?? '—' }}
          </h6>
          <p class="card-text mb-1">
            {{ video.description_short || 'Sin descripción disponible.' }}
          </p>
          <p class="card-text small text-muted mb-0">
            Duración: {{ getDuration(video.comments) }}
          </p>

          <div class="text-end mt-2">
            <Link
              v-if="isAccessible && url"
              :href="url"
              class="btn btn-sm btn-dark"
            >
              <i class="bi bi-play-fill me-1"></i> Ver
            </Link>
            <span v-else class="text-muted">
              <i class="bi bi-lock-fill"></i> Bloqueado
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  video: {
    type: Object,
    required: true
  },
  url: {
    type: [String, null],
    default: null
  },
  isAccessible: {
    type: Boolean,
    default: false
  }
})

function getDuration(text) {
  if (typeof text !== 'string') return '---'
  const match = text.match(/Duración:\s*([0-9]+min)/i)
  return match ? match[1] : '---'
}
</script>

<style scoped>
.video-card img {
  height: 100%;
  object-fit: cover;
}
</style>
