<template>
  <div class="card shadow-sm position-relative border-0">
    <!-- Video con ratio -->


 
    <div   @contextmenu.prevent>
      <video
        ref="videoElement"
        class="video-js vjs-big-play-centered video_player"
        controls
        playsinline
        controlsList="nodownload"
        disablePictureInPicture
        :poster="video.image_cover ? `/storage/${video.image_cover}` : ''"
      ></video>
    </div>




 
    <!-- Encabezado: título + chips -->
    <div class="px-3 pt-3">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <h2 class="h5 fw-bold mb-0 me-2">{{ video.title }}</h2>

        <!-- Chips informativos (opcionales) -->
        <span v-if="hasEvaluations" class="badge bg-primary">
          <i class="bi bi-ui-checks-grid me-1"></i>{{ evalCount }} {{ evalCount === 1 ? 'Evaluación' : 'Evaluaciones' }}
        </span>
        <span v-if="video.order" class="badge bg-secondary">
          <i class="bi bi-list-ol me-1"></i>Orden {{ video.order }}
        </span>
        
        <span v-if="video.duration" class="badge bg-dark">
          <i class="bi bi-clock me-1"></i>{{ video.duration }}
        </span>
      </div>
    </div>

    <!-- Descripción con collapse -->
    <div class="card-body pt-2">
      <!-- corta -->
      <p v-if="!isLongDescription" class="text-muted mb-0">
        {{ video.description || 'Sin descripción.' }}
      </p>

      <!-- larga -->
      <div v-else>
        <p class="text-muted mb-0">
          {{ shortDescription }}<span v-if="!showFullDesc">…</span>
        </p>
        <div class="collapse mt-2" :id="descId">
          <p class="text-muted mb-0">{{ video.description }}</p>
        </div>
        <button
          class="btn btn-link btn-sm px-0 mt-1"
          type="button"
          data-bs-toggle="collapse"
          :data-bs-target="'#' + descId"
          @click="toggleDesc"
        >
          {{ showFullDesc ? 'Ver menos' : 'Ver más' }}
        </button>
      </div>
 
    </div>

    <!-- Modal final usando Teleport al body -->
    <teleport to="body">
      <div v-if="showEvalModal" class="modal-backdrop-custom">
        <div class="modal-card" role="dialog" aria-modal="true">
          <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center gap-2">
              <i
                class="bi"
                :class="(isLastOfCourse || isLastOfLesson) ? 'bi-check-circle-fill text-success' : 'bi-play-circle-fill text-primary'"
              ></i>
              <span>
                {{ isLastOfCourse ? 'Curso completado' : (isLastOfLesson ? 'Lección completada' : 'Video completado') }}
              </span>
            </h5>
            <button type="button" class="btn-close" aria-label="Cerrar" @click="closeEvalModal"></button>
          </div>


          <div class="modal-body">
  <!-- Caso: último video del curso -->
    <template v-if="isLastOfCourse">
      <p class="mb-2">Has completado el curso.</p>
      <p class="mb-0 text-muted">
        <span v-if="hasEvaluations">Puedes realizar las evaluaciones del curso si están disponibles.</span>
        <span v-else>No hay evaluaciones asignadas.</span>
      </p>
    </template>

  <!-- Caso: último video de la lección -->
  <template v-else-if="isLastOfLesson">
    <p class="mb-2">Has completado la lección.</p>
    <p class="mb-0 text-muted">
      <span v-if="hasEvaluations">Puedes realizar las evaluaciones de esta lección.</span>
      <span v-else>Esta lección no tiene evaluaciones asignadas.</span>
    </p>

  </template>

  <!-- Caso: solo terminó el video -->
  <template v-else>
    <p class="mb-2">Has completado el video.</p>
    <p class="mb-0 text-muted">
      <span v-if="hasEvaluations">Puedes realizar las evaluaciones asignadas.</span>
      <span v-else>Puedes continuar con el siguiente video.</span>
    </p>
  </template>
</div>


  <div class="modal-footer">
      <button class="btn btn-outline-secondary" @click="closeEvalModal">Cerrar</button>
     


  <Link  
    v-if="isLastOfCourse || isLastOfLesson"
    :href="route('dashboard.courses.evaluations.index', { course: courseId })" class="btn btn-primary mx-2">Ver Evaluaciones</Link>

  <!-- Si no es último de lección/curso, ofrece ir al siguiente video -->
  <button
    v-if="!isLastOfCourse && !isLastOfLesson"
    class="btn btn-success"
    @click="onNextFromModal"
  >
    Siguiente video
  </button>
   
</div>

        </div>
      </div>
    </teleport>
 
  </div>
</template>

<script setup>
/**
 * Subtítulos al estilo “de ayer”:
 * - props.video.captions => [{file, lang, label}]
 * - addRemoteTextTrack con {kind, src, srclang, label, default}
 * - Forzado del textTrack según User.locale (fallback 'es', luego el primero)
 * - Sin helpers ni botones extra de captions
 */

import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import videojs from 'video.js'
import 'video.js/dist/video-js.css'

/* ===== Props mínimas necesarias para captions ===== */
const props = defineProps({
  video: { type: Object, required: true },     // { image_cover, captions: [{file, lang, label}] }
  sourceUrl: { type: String, required: true }  // URL (mp4, m3u8, etc.)
})

/* ===== Usuario (locale) ===== */
const page = usePage()
const User = computed(() => page.props.auth?.user ?? {})
const userLang = computed(() => (User.value?.locale || 'es').toLowerCase()) // ej. es-MX

/* ===== Refs ===== */
const videoElement = ref(null)
let player = null

/* ===== Poster ===== */
const posterUrl = computed(() =>
  props.video?.image_cover ? `/storage/${props.video.image_cover}` : ''
)

/* ===== Tipo de fuente ===== */
function detectType(url) {
  if (!url) return 'video/mp4'
  const u = url.split('?')[0].toLowerCase()
  if (u.endsWith('.m3u8')) return 'application/x-mpegURL'
  if (u.endsWith('.mpd'))  return 'application/dash+xml'
  if (u.endsWith('.webm')) return 'video/webm'
  if (u.endsWith('.ogg') || u.endsWith('.ogv')) return 'video/ogg'
  return 'video/mp4'
}

/* ===== Captions como ayer ===== */
const subtitles = computed(() => {
  const list = Array.isArray(props.video?.captions) ? props.video.captions : []
  const u = userLang.value // ej. 'es-mx'
  return list
    .filter(c => c?.file)
    .map(c => {
      const lang = (c.lang || 'es').toLowerCase()
      return {
        kind: 'subtitles',
        src: `/storage/${c.file}`,
        srclang: lang,
        label: c.label || lang.toUpperCase(),
        default: (lang === u) || (u === 'es' && lang === 'es')
      }
    })
})

/* ===== Inicialización del player ===== */
function initPlayer() {
  if (!videoElement.value) return

  // Destruye para evitar duplicado de tracks
  if (player) {
    try { player.dispose() } catch {}
    player = null
  }

  player = videojs(videoElement.value, {
    controls: true,
    preload: 'auto',
    fluid: true,
    playbackRates: [0.75, 1, 1.25, 1.5, 2],
    controlBar: {
      remainingTimeDisplay: true,
      children: [
        'playToggle',
        'progressControl',
        'currentTimeDisplay',
        'timeDivider',
        'durationDisplay',
        'volumePanel',
        'playbackRateMenuButton',
        'subsCapsButton',
        'fullscreenToggle'
      ]
    },
    sources: [{ src: props.sourceUrl, type: detectType(props.sourceUrl) }],
    poster: posterUrl.value
  })

  player.ready(() => {
    // Agregar tracks remotos
    subtitles.value.forEach(track => {
      player.addRemoteTextTrack(track, false)
    })

    // Forzar activación: idioma del usuario -> 'es' -> primero
    const tracks = player.textTracks?.()
    if (tracks && tracks.length) {
      const want = userLang.value.split('-')[0] // 'es-mx' -> 'es'
      let chosen = -1

      // 1) Match con usuario
      for (let i = 0; i < tracks.length; i++) {
        const t = tracks[i]
        const lang = String(t.language || t.srclang || '').toLowerCase().split('-')[0]
        if (lang === want) { chosen = i; break }
      }
      // 2) fallback 'es'
      if (chosen === -1 && want === 'es') {
        for (let i = 0; i < tracks.length; i++) {
          const t = tracks[i]
          const lang = String(t.language || t.srclang || '').toLowerCase().split('-')[0]
          if (lang === 'es') { chosen = i; break }
        }
      }
      // 3) primero
      if (chosen === -1) chosen = 0

      for (let i = 0; i < tracks.length; i++) {
        tracks[i].mode = (i === chosen) ? 'showing' : 'disabled'
      }
    }
  })
}

/* ===== Ciclo de vida ===== */
onMounted(initPlayer)

onBeforeUnmount(() => {
  try { player?.dispose?.() } catch {}
  player = null
})

/* Reinicializa cuando cambie video, fuente, poster o locale */
watch(
  () => [props.video?.id, props.sourceUrl, posterUrl.value, userLang.value],
  () => { initPlayer() }
)
</script>


<style scoped>
  .video_player { width: 100%; height: 50vh; background: #000; }
/* El ratio controla la altura; ya no forzamos 50vh */
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(33, 37, 41, 0.6);
  z-index: 1055; /* sobre Bootstrap navbar/modals */
  display: grid;
  place-items: center;
  padding: 1rem;
}

.modal-card {
  width: 100%;
  max-width: 560px;
  background: #fff;
  border-radius: .75rem;
  box-shadow: 0 20px 50px rgba(0,0,0,.2);
  overflow: hidden;
}

.modal-header,
.modal-footer {
  padding: .75rem 1rem;
  background: #fff;
  border-bottom: 1px solid rgba(0,0,0,.05);
}

.modal-footer {
  border-top: 1px solid rgba(0,0,0,.05);
  border-bottom: 0;
}

.modal-title { margin: 0; font-weight: 700; }
.modal-body { padding: 1rem; }

.btn-close {
  border: 0;
  width: 2rem;
  height: 2rem;
  background: transparent;
}
.btn-close::before { content: "×"; font-size: 1.5rem; line-height: 1; }
</style>