<template>
  <article
    class="card video-card border-0 shadow-sm overflow-hidden position-relative d-flex flex-column flex-md-row mb-2"
    :class="{ 'locked': !isAccessible, 'completed': video.is_ended }"
    :aria-disabled="!isAccessible"
    :tabindex="isAccessible ? 0 : -1"
    role="group"
    @keydown="handleKeydown"
  >
    <!-- Miniatura -->
    <div class="thumbnail-wrapper position-relative bg-light">
      <img
        :src="video.image_cover ? `/storage/${video.image_cover}` : 'https://placehold.co/320x180?text=Sin+imagen'"
        class="thumbnail-image"
        :alt="video.title || 'Video sin título'"
        loading="lazy"
      />

      <!-- Duración -->
      <span v-if="video.duration && isAccessible" class="badge bg-dark bg-opacity-75 duration-indicator">
        <i class="bi bi-clock me-1"></i>{{  video.duration }}
      </span>

      <!-- Barra de progreso en la miniatura -->
      <div v-if="video.progress > 0 && video.progress < 100 && isAccessible" class="progress-indicator">
        <div class="progress">
          <div
            class="progress-bar"
            role="progressbar"
            :style="{ width: `${video.progress}%` }"
            :aria-valuenow="video.progress"
            aria-valuemin="0"
            aria-valuemax="100"
          ></div>
        </div>
      </div>

      <!-- Overlay bloqueado -->
      <div v-if="!isAccessible" class="locked-overlay">
        <span class="locked-text" data-bs-toggle="tooltip" data-bs-placement="top" :title="lockTooltip">
          <i class="bi bi-lock-fill me-1"></i> Bloqueado
        </span>
      </div>

      <!-- Overlay de play -->
      <div v-if="isAccessible" class="play-overlay">
        <Link
          :href="videoHref"
          class="stretched-link"
          aria-label="Reproducir video"
        >
          <i class="bi bi-play-circle-fill play-icon"></i>
        </Link>
      </div>
    </div>

    <!-- Cuerpo -->
    <div class="card-body d-flex flex-column justify-content-between">
      <div>
        <!-- Encabezado y estado -->
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="d-flex align-items-center gap-2">
            <span class="text-primary text-uppercase fw-bold small">
              Capítulo {{ video.order ?? '—' }}
            </span>
            <span
              v-if="video.is_ended"
              class="badge bg-success d-inline-flex align-items-center"
              title="Completado"
            >
              <i class="bi bi-check-circle-fill me-1"></i> Completado
            </span>
          </div>

          <!-- Menú de acciones 
          <div class="dropdown">
            <button
              class="btn btn-outline-secondary btn-sm"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-three-dots"></i>
              <span class="visually-hidden">Acciones</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item d-flex align-items-center gap-2" @click="$emit('open-resources', video)">
                  <i class="bi bi-folder2-open"></i> Recursos
                </button>
              </li>
              <li>
                <button class="dropdown-item d-flex align-items-center gap-2" @click="$emit('mark-complete', video)">
                  <i class="bi bi-check2-square"></i> Marcar como completado
                </button>
              </li>
              <li><hr class="dropdown-divider" /></li>
              <li>
                <Link
                  class="dropdown-item d-flex align-items-center gap-2"
                  :href="videoHref"
                  :aria-disabled="!isAccessible"
                >
                  <i class="bi bi-play-fill"></i> Abrir
                </Link>
              </li>
            </ul>
          </div>
          -->
        </div>

        <!-- Título -->
        <h5 class="card-title h5 fw-semibold mb-2 text-truncate" :title="video.title">
          {{ video.title }}
        </h5>

        <!-- Descripción -->
        <p class="card-text text-muted small mb-3 line-clamp-2">
          {{ video.description_short || 'Sin descripción disponible.' }}
        </p>

        <!-- Metadatos -->
        <ul class="list-inline text-muted small mb-0 video-meta">
          <li v-if="video.duration" class="list-inline-item me-3">
            <i class="bi bi-hourglass-split me-1"></i>{{  video.duration  }}
          </li>
          <li v-if="video.updated_at" class="list-inline-item">
            <i class="bi bi-calendar3 me-1"></i>{{ formatDate(video.updated_at) }}
          </li>
        </ul>
      </div>

      <!-- Pie: progreso y CTA -->
      <div class="d-flex align-items-center gap-3 mt-3 mt-md-auto">
        <div v-if="video.progress > 0 && isAccessible" class="flex-grow-1">
          <div class="progress" style="height:.45rem">
            <div
              class="progress-bar"
              :class="{ 'bg-success': video.is_ended }"
              :style="{ width: `${video.progress}%` }"
              role="progressbar"
              :aria-valuenow="video.progress"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
          <div class="small text-muted mt-1">
            Progreso: {{ video.progress }}%
          </div>
        </div>

        <div class="ms-auto">
          <Link
            v-if="isAccessible"
            :href="videoHref"
            class="btn btn-primary btn-sm d-inline-flex align-items-center"
          >
            <i class="bi bi-play-fill me-1"></i>
            <span>
              {{ video.is_ended ? 'Repetir' : (video.progress > 0 ? 'Continuar' : 'Ver') }}
            </span>
          </Link>
          <button
            v-else
            type="button"
            class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            :title="lockTooltip"
            disabled
          >
            <i class="bi bi-lock-fill me-1"></i> Bloqueado
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { onMounted, onBeforeUnmount, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Tooltip from 'bootstrap/js/dist/tooltip'

const props = defineProps({
  video: { type: Object, required: true },
  courseId: { type: [String, Number], required: true },
  isAccessible: { type: Boolean, default: true },
})
defineEmits(['open-resources', 'mark-complete'])

const videoHref = computed(() =>
  route('dashboard.videos.video.show', { course: props.courseId, video: props.video.id })
)

const lockTooltip = computed(() =>
  props.video?.lock_reason || 'Este contenido no está disponible por el momento.'
)

/* Tooltips */
let tooltipList = []
onMounted(() => {
  tooltipList = [...document.querySelectorAll('[data-bs-toggle="tooltip"]')]
    .map(el => new Tooltip(el))
})
onBeforeUnmount(() => {
  tooltipList.forEach(t => t.dispose())
  tooltipList = []
})

/* Accesibilidad teclado */
function handleKeydown(e) {
  if (!props.isAccessible) return
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault()
    window.location.href = videoHref.value
  }
}

/* Formateadores */
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
/* ========= Altura fija y layout coherente ========= */
.video-card{
  /* Altura global de la tarjeta: ajusta estos valores si lo necesitas */
  --card-h: 200px;     /* alto default */
  --thumb-w: 120px;    /* ancho miniatura en móviles */
  height: var(--card-h);
  min-height: var(--card-h);
  border-radius: .75rem;
  border: 0;
  display: flex;
  transition: transform .2s ease, box-shadow .2s ease;
}
.video-card:hover { transform: translateY(-2px); box-shadow: 0 .35rem .85rem rgba(0,0,0,.06); }

/* En pantallas medianas+ aumentamos altura y el ancho de la miniatura */
@media (min-width: 768px){
  .video-card{ --card-h: 200px; --thumb-w: 292px; } /* 18rem aprox */
}
/* En pantallas grandes puedes dar un poco más de aire si quieres */
@media (min-width: 1200px){
  .video-card{ --card-h: 220px; }
}

/* ========= Miniatura con tamaño estable ========= */
.thumbnail-wrapper{
  width: var(--thumb-w);
  flex: 0 0 var(--thumb-w);        /* evita que se estire/encoja */
  height: var(--card-h);           /* siempre igual al alto de la tarjeta */
  position: relative;
  overflow: hidden;
  background-color: #f8f9fa;
}
.thumbnail-image{
  width: 100%;
  height: 100%;
  object-fit: cover;               /* recorte elegante */
  display: block;
  transition: transform .35s ease, filter .25s ease;
}
.video-card:hover .thumbnail-image{ transform: scale(1.03); }

/* ========= Cuerpo ocupando el 100% del alto ========= */
.video-card .card-body{
  height: var(--card-h);
  /* Tres filas: encabezado/meta, descripción (se expande), acciones */
  display: grid;
  grid-template-rows: auto 1fr auto;
  gap: .5rem;
  padding: .75rem .9rem !important;
  position: relative;              /* para stretched-link si lo usas */
  overflow: hidden;                /* evita desbordes verticales */
  min-width: 0;                    /* clave para truncados en flex/grid */
}
/* Asegura que hijos no fuercen el desbordamiento horizontal */
.video-card .card-body > *{ min-width: 0; }

/* ========= Estados ========= */
.video-card.locked .thumbnail-image{ filter: blur(3px) brightness(.7); }
.locked-overlay{
  position: absolute; inset: 0;
  display: grid; place-items: center;
  background: rgba(0,0,0,.25);
  z-index: 2;
}
.locked-text{
  background: rgba(0,0,0,.7);
  color: #fff;
  padding: .45rem .75rem;
  border-radius: .5rem;
  font-size: .9rem;
}
.video-card.completed{ border-left: 4px solid #198754; }

/* ========= Overlays y microinteracciones ========= */
.play-overlay{
  position: absolute; inset: 0;
  display: grid; place-items: center;
  pointer-events: none; z-index: 1;
  background: linear-gradient(to bottom, rgba(0,0,0,0) 55%, rgba(0,0,0,.25) 100%);
}
.play-icon{
  font-size: 3rem; color: rgba(255,255,255,.95);
  filter: drop-shadow(0 2px 6px rgba(0,0,0,.35));
  transition: transform .2s ease, opacity .2s ease;
  pointer-events: auto;            /* permite foco/click si lo necesitas */
}
.video-card:hover .play-icon{ transform: scale(1.06); }

/* ========= Indicadores ========= */
.duration-indicator{
  position: absolute; bottom: .5rem; right: .5rem;
  font-weight: 500;
}
.progress-indicator{
  position: absolute; left: 0; right: 0; bottom: 0;
  padding: 0 .5rem .5rem; z-index: 1;
}
.progress-indicator .progress{
  height: .35rem; background-color: rgba(255,255,255,.35);
}
.progress-indicator .progress-bar{ background-color: #0d6efd; }

/* ========= Tipografía y truncados ========= */
.video-meta .list-inline-item{ vertical-align: middle; }

/* Truncado a 2 líneas para descripciones largas */
.line-clamp-2{
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Títulos extremadamente largos: usa text-truncate y asegúrate de min-width:0 en el padre */
.text-truncate{ max-width: 100%; }

/* Si quieres limitar el ancho del título para evitar empujar acciones */
@media (min-width: 768px){
  .text-truncate{ max-width: 32ch; }
}

</style>
