<template>

 
  <article
    class="card card-mini shadow-sm d-flex flex-row align-items-stretch overflow-hidden position-relative"
    :class="{ 'locked': !isAccessible, 'playing': isPlaying }"
    role="group"
    :aria-disabled="!isAccessible"
  >
    <!-- Miniatura (altura fija) -->
    <div class="thumb-wrapper position-relative">
      <img
        :src="video.image_cover ? `/storage/${video.image_cover}` : 'https://placehold.co/120x120?text=Sin+imagen'"
        :alt="video.title || 'Sin título'"
        class="thumb-image"
        loading="lazy"
      />

      <!-- Overlay bloqueado -->
      <div v-if="!isAccessible" class="locked-overlay">
        <span class="locked-text">
          <i class="bi bi-lock-fill me-1"></i> Bloqueado
        </span>
      </div>

      <!-- Overlay Play (sólo en hover si accesible) -->
      <div v-if="isAccessible" class="play-overlay">
        <i class="bi bi-play-circle-fill play-icon"></i>
      </div>
    </div>

    <!-- Cuerpo (altura fija con grid) -->
    <div class="card-body p-2 d-grid w-100">
      <!-- Fila 1: Título y metadatos compactos -->
      <div class="d-flex align-items-center justify-content-between gap-2">
        <h6 class="mb-0 text-primary text-uppercase fw-bold small line-clamp-2" :title="video.title">
  {{ video.title ?? '—' }}
</h6>



        <!-- Badge Capítulo / Orden (opcional) -->
        <span v-if="video.order !== undefined && video.order !== null" class="badge text-bg-light">
          <i class="bi bi-list-ol me-1"></i> {{ video.order }}
        </span>
      </div>

      <!-- Fila 2: Descripción (clamp 2 líneas) -->
      <p class="text-muted small mb-0 line-clamp-2">
        {{ video.description_short || 'Sin descripción disponible.' }}
      </p>

      <!-- Fila 3: Acciones / Estado -->
      <div class="d-flex align-items-center justify-content-between gap-2">
        <!-- Metadatos secundarios -->
        <div class="text-muted small d-flex align-items-center gap-3">
          <span v-if="video.duration">
            <i class="bi bi-clock me-1"></i>{{ video.duration }}
          </span>
          
        </div>

        <!-- CTA / Estado -->
        <div class="text-end">
          <template v-if="!isPlaying">
            <Link
              v-if="isAccessible && url"
              :href="url"
              class="btn btn-sm btn-outline-dark d-inline-flex align-items-center"
            >
              <i class="bi bi-play-fill me-1"></i> Ver video
            </Link>
            <span v-else class="text-muted small d-inline-flex align-items-center">
              <i class="bi bi-lock-fill me-1"></i> Bloqueado
            </span>
          </template>
          <template v-else>
            <span class="text-primary small d-inline-flex align-items-center gap-2">
              <span class="eq">
                <span></span><span></span><span></span>
              </span>
              Reproduciendo
            </span>
          </template>
        </div>
      </div>

      <!-- Link global (tarjeta clickeable completa) -->
      <Link
        v-if="isAccessible && url"
        :href="url"
        class="stretched-link"
        aria-label="Abrir video"
      />
    </div>

    <!-- Barra de progreso inferior (opcional) -->
    <div
      v-if="isAccessible && video.progress !== undefined && video.progress !== null"
      class="mini-progress"
      :style="{ '--progress': (Number(video.progress) || 0) + '%' }"
      role="progressbar"
      :aria-valuenow="Number(video.progress) || 0"
      aria-valuemin="0"
      aria-valuemax="100"
      :title="`Progreso: ${Number(video.progress) || 0}%`"
    ></div>
  </article>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  video: { type: Object, required: true },
  url: { type: [String, null], default: null },
  isAccessible: { type: Boolean, default: false },
  isPlaying: { type: Boolean, default: false }
})

// Formateadores
function formatDuration(seconds) {
  if (!seconds && seconds !== 0) return ''
  const total = Math.max(0, Number(seconds) || 0)
  const h = Math.floor(total / 3600)
  const m = Math.floor((total % 3600) / 60)
  const s = Math.floor(total % 60)
  return h > 0
    ? `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
    : `${m}:${s.toString().padStart(2, '0')}`
}

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
/* Altura fija global */
.card-mini {
  height: 120px;
  min-height: 120px;
  border: 0;
  transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
  border-radius: .5rem;
}
.card-mini:hover {
  transform: translateY(-1px);
  box-shadow: 0 .35rem .85rem rgba(0,0,0,.06);
}

/* Estado tocando / playing */
.card-mini.playing {
  border: 2px solid #0d6efd;
  background-color: #eef4ff;
}

/* Miniatura (altura fija) */
.thumb-wrapper {
  width: 120px;
  height: 120px;
  background-color: #f8f9fa;
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
}
.thumb-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform .35s ease, filter .25s ease;
}
.card-mini:hover .thumb-image { transform: scale(1.03); }

/* Cuerpo con grid de 3 filas: título / descripción / acciones */
.card-body {
  height: 120px;
  grid-template-rows: auto 1fr auto;
  gap: .35rem;
  position: relative; /* para stretched-link */
  overflow: hidden;
  padding: .5rem .75rem !important;
}

/* Truncado elegante a 2 líneas */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Bloqueo */
.card-mini.locked .thumb-image { filter: blur(3px) brightness(.7); }
.locked-overlay {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  z-index: 2;
}
.locked-text {
  background: rgba(0,0,0,.65);
  color: #fff;
  padding: .25rem .5rem;
  font-size: .8rem;
  border-radius: .25rem;
}

/* Play overlay */
.play-overlay {
  position: absolute;
  inset: 0;
  display: none;
  place-items: center;
  z-index: 1;
  background: linear-gradient(to bottom, rgba(0,0,0,0) 55%, rgba(0,0,0,.25) 100%);
}
.play-icon {
  font-size: 2rem;
  color: rgba(255,255,255,.95);
  filter: drop-shadow(0 2px 6px rgba(0,0,0,.35));
}
.card-mini:hover .play-overlay { display: grid; }

/* Barra de progreso inferior (sutil, fuera del flujo visual) */
.mini-progress {
  position: absolute;
  left: 0; right: 0; bottom: 0;
  height: .3rem;
  background: rgba(0,0,0,.05);
}
.mini-progress::after {
  content: '';
  display: block;
  height: 100%;
  width: var(--progress, 0%);
  background: #0d6efd;
  transition: width .3s ease;
}

/* Ecualizador animado para estado Reproduciendo */
.eq {
  display: inline-flex;
  align-items: flex-end;
  gap: 2px;
  height: 12px;
}
.eq span {
  display: inline-block;
  width: 3px;
  background: #0d6efd;
  border-radius: 1px;
  animation: bounce 1s infinite ease-in-out;
}
.eq span:nth-child(2) { animation-delay: .15s; }
.eq span:nth-child(3) { animation-delay: .3s; }
@keyframes bounce {
  0%, 100% { height: 3px; opacity: .8; }
  50% { height: 12px; opacity: 1; }
}

/* Accesibilidad focus visible al ser clickeable */
.card-mini:not(.locked):focus-within {
  outline: 2px solid #0d6efd;
  outline-offset: 2px;
}

.min-w-0 { min-width: 0 !important; }
</style>
