<template>
  <div
    class="card card-mini shadow-sm d-flex flex-row align-items-center overflow-hidden position-relative"
    :class="{ 'locked': !isAccessible, 'playing': isPlaying }"
  >
    <!-- Imagen con overlays -->
    <div class="thumb-wrapper position-relative">
      <img
        :src="video.image_cover ? `/storage/${video.image_cover}` : 'https://placehold.co/120x120?text=Sin+imagen'"
        :alt="video.title || 'Sin título'"
        class="thumb-image"
      />

      <div v-if="!isAccessible" class="locked-overlay">
        <span class="locked-text">
          <i class="bi bi-lock-fill me-1"></i> Bloqueado
        </span>
      </div>

      <div v-if="isAccessible" class="play-overlay">
        <i class="bi bi-play-circle-fill play-icon"></i>
      </div>
    </div>

    <!-- Contenido -->
    <div class="card-body p-2 d-flex flex-column justify-content-between w-100">
      <div>
        <h6 class="text-uppercase text-primary fw-bold small mb-1">
           {{ video.title ?? '—' }}
        </h6>
        <p class="text-muted small mb-0 text-truncate">
          {{ video.description_short || 'Sin descripción disponible.' }}
        </p>
      </div>

      <div class="text-end mt-2" >
        <div v-if="!isPlaying">
        <Link
          v-if="isAccessible && url" 
          :href="url"
          class="btn btn-sm btn-outline-dark"
        >

          <i class="bi bi-play-fill me-1" ></i> Ver Video
        </Link>
        <span v-else class="text-muted small d-inline-flex align-items-center">
          <i class="bi bi-lock-fill me-1"></i> Bloqueado
        </span>
        </div>
        <div v-else>
          <i class="bi bi-play-fill me-1" ></i> Reproduciendo
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
  },
  isPlaying: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
.card-mini {
  height: 120px;
  min-height: 120px;
  transition: box-shadow 0.2s ease;
  position: relative;
}
.card-mini.playing {
  border: 2px solid #0d6efd;
  background-color: #eaf1ff;
}
.card-mini:hover {
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.075);
}

.thumb-wrapper {
  width: 120px;
  height: 120px;
  background-color: #f8f9fa;
  flex-shrink: 0;
  overflow: hidden;
  position: relative;
}

.thumb-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: filter 0.3s ease;
}

/* Blur cuando está bloqueado */
.card-mini.locked .thumb-image {
  filter: blur(3px) brightness(0.7);
}

/* Overlay bloqueado */
.locked-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2;
}

.locked-text {
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  padding: 0.25rem 0.5rem;
  font-size: 0.85rem;
  border-radius: 0.25rem;
}

/* Icono de play (visible solo en hover si accesible) */
.play-overlay {
  position: absolute;
  inset: 0;
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 1;
}

.play-icon {
  font-size: 2rem;
  color: rgba(255, 255, 255, 0.9);
}

.card-mini:hover .play-overlay {
  display: flex;
}
</style>
