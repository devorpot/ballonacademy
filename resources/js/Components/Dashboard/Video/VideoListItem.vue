<template>
  <div
    class="card video-card mb-4 shadow-sm d-flex flex-column flex-md-row overflow-hidden position-relative"
    :class="{ 'locked': !isAccessible, 'completed': video.is_ended }"
  >
    <!-- Imagen con overlays -->
    <div class="thumbnail-wrapper position-relative">
      <img
        :src="video.image_cover ? `/storage/${video.image_cover}` : 'https://placehold.co/320x180?text=Sin+imagen'"
        class="thumbnail-image"
        :alt="video.title || 'Video sin título'"
      />
      
      <!-- Indicador de duración -->
      <div v-if="video.duration && isAccessible" class="duration-indicator">
        {{ formatDuration(video.duration) }}
      </div>
      
      <!-- Indicador de progreso -->
      <div v-if="video.progress > 0 && video.progress < 100 && isAccessible" class="progress-indicator">
        <div class="progress-bar" :style="{ width: `${video.progress}%` }"></div>
      </div>
      
      <!-- Overlay bloqueado -->
      <div v-if="!isAccessible" class="locked-overlay">
        <span class="locked-text">
          <i class="bi bi-lock-fill me-1"></i> Bloqueado
        </span>
      </div>
      
      <!-- Overlay ícono de play -->
      <div v-if="isAccessible" class="play-overlay">
        <Link
          :href="route('dashboard.videos.video.show', { course: courseId, video: video.id })"
          class="stretched-link"
          aria-label="Reproducir video"
        >
          <i class="bi bi-play-circle-fill play-icon"></i>
        </Link>
      </div>
    </div>

    <!-- Contenido -->
    <div class="card-body d-flex flex-column justify-content-between">
      <div>
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h6 class="text-primary text-uppercase fw-bold mb-0">
            Capítulo {{ video.order ?? '—' }}
          </h6>
          <span v-if="video.is_ended" class="badge bg-success">
            <i class="bi bi-check-circle-fill me-1"></i> Completado
          </span>
        </div>
        <h5 class="card-title h5 fw-semibold mb-2">
          {{ video.title }}
        </h5>
        <p class="card-text text-muted small mb-3">
          {{ video.description_short || 'Sin descripción disponible.' }}
        </p>
        
        <!-- Metadatos adicionales -->
        <div v-if="video.duration || video.updated_at" class="video-metadata">
          <span v-if="video.duration" class="me-3">
            <i class="bi bi-clock me-1"></i> {{ formatDuration(video.duration) }}
          </span>
          <span v-if="video.updated_at" class="text-muted">
            <i class="bi bi-calendar3 me-1"></i> {{ formatDate(video.updated_at) }}
          </span>
        </div>
      </div>
      
      <div class="d-flex justify-content-between align-items-center mt-3 mt-md-auto">
        <div v-if="video.progress > 0 && video.progress < 100 && isAccessible" class="progress-text small text-muted">
          Progreso: {{ video.progress }}%
        </div>
        <div class="ms-auto">
          <Link
            v-if="isAccessible"
            :href="route('dashboard.videos.video.show', { course: courseId, video: video.id })"
            class="btn btn-primary btn-sm"
          >
            <i class="bi bi-play-fill me-1"></i> 
            {{ video.is_ended ? 'Repetir' : 'Ver' }}
          </Link>
          <span v-else class="text-muted small">
            <i class="bi bi-lock-fill me-1"></i> Bloqueado
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  video: Object,
  courseId: [String, Number],
  isAccessible: Boolean
})

// Función para formatear la duración (asumiendo que viene en segundos)
function formatDuration(seconds) {
  if (!seconds) return ''
  
  const mins = Math.floor(seconds / 60)
  const secs = Math.floor(seconds % 60)
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

// Función para formatear la fecha
function formatDate(dateString) {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}
</script>

<style scoped>
.video-card {
  transition: all 0.3s ease;
  border-radius: 0.75rem;
  border: none;
  overflow: hidden;
}

.video-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

/* Responsive thumbnail */
.thumbnail-wrapper {
  width: 100%;
  aspect-ratio: 16 / 9;
  background-color: #f8f9fa;
  position: relative;
  flex-shrink: 0;
  overflow: hidden;
}

@media (min-width: 768px) {
  .thumbnail-wrapper {
    width: 280px;
  }
}

.thumbnail-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease, filter 0.3s ease;
  display: block;
}

.video-card:hover .thumbnail-image {
  transform: scale(1.05);
}

/* Blur si bloqueado */
.video-card.locked .thumbnail-image {
  filter: blur(4px) brightness(0.6);
}

/* Indicador de duración */
.duration-indicator {
  position: absolute;
  bottom: 8px;
  right: 8px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  z-index: 2;
}

/* Indicador de progreso */
.progress-indicator {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.3);
  z-index: 2;
}

.progress-bar {
  height: 100%;
  background: #0d6efd;
  transition: width 0.3s ease;
}

/* Overlay bloqueado */
.locked-overlay {
  position: absolute;
  inset: 0;
  z-index: 3;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.2);
}

.locked-text {
  background: rgba(0, 0, 0, 0.75);
  color: white;
  padding: 0.5rem 1rem;
  font-size: 0.95rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
}

/* Overlay de ícono de reproducción */
.play-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  pointer-events: none;
  background: linear-gradient(to bottom, rgba(0,0,0,0) 60%, rgba(0,0,0,0.4) 100%);
}

.play-icon {
  font-size: 3rem;
  color: rgba(255, 255, 255, 0.9);
  pointer-events: auto;
  transition: all 0.3s ease;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.play-icon:hover {
  transform: scale(1.1);
  color: white;
}

/* Metadatos del video */
.video-metadata {
  font-size: 0.8rem;
  color: #6c757d;
}

/* Estilos responsive */
@media (min-width: 1024px) {
  .video-card {
    height: 200px;
  }
  
  .thumbnail-wrapper {
    height: 100%;
    width: 280px;
    aspect-ratio: unset;
  }
  
  .thumbnail-image {
    height: 100%;
    object-fit: cover;
  }
}

/* Animación para tarjetas completadas */
.video-card.completed {
  border-left: 4px solid #198754;
}

/* Estilos para el texto de progreso */
.progress-text {
  white-space: nowrap;
}
</style>