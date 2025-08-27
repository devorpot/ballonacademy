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

      <!-- Acciones rápidas -->
      <div class="d-flex flex-wrap gap-2 mt-3">
        <button
          v-if="hasEvaluations"
          type="button"
          class="btn btn-primary btn-sm"
          @click="onViewEvaluations"
        >
          <i class="bi bi-journal-check me-1"></i> Ver evaluaciones
        </button>

        <button
          v-if="!hasEvaluations && !isLastVideo"
          type="button"
          class="btn btn-outline-success btn-sm"
          @click="onNextFromModal"
        >
          <i class="bi bi-chevron-right me-1"></i> Siguiente video
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
                :class="isLastVideo ? 'bi-check-circle-fill text-success' : 'bi-play-circle-fill text-primary'"
              ></i>
              <span>{{ isLastVideo ? '¡Felicidades!' : 'Video completado' }}</span>
            </h5>
            <button type="button" class="btn-close" aria-label="Cerrar" @click="closeEvalModal"></button>
          </div>

          <div class="modal-body">
            <template v-if="hasEvaluations">
              <p class="mb-2" v-if="isLastVideo">Has completado el curso.</p>
              <p class="mb-0 text-muted">Puedes realizar las evaluaciones para validar tu aprendizaje.</p>
            </template>

            <template v-else>
              <p class="mb-2" v-if="isLastVideo">Has completado el curso.</p>
              <p class="mb-0 text-muted">
                Este video no tiene evaluaciones asignadas.
                <span v-if="!isLastVideo">Puedes continuar con el siguiente video.</span>
              </p>
            </template>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeEvalModal">Cerrar</button>

            <button
              v-if="isLastVideo"
              class="btn btn-primary mx-2"
              @click="onViewEvaluations"
            >
              Ver evaluaciones
            </button>

            <button
              v-if="!isLastVideo"
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
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'   
import { route } from 'ziggy-js'
import { Link, usePage } from '@inertiajs/vue3'
import videojs from 'video.js'
import 'video.js/dist/video-js.css'
import axios from 'axios'

const emit = defineEmits(['next', 'show-evaluations'])

const props = defineProps({
  video: { type: Object, required: true },
  sourceUrl: { type: String, required: true },
  courseId: { type: [String, Number], required: true },
  lastVideoId: { type: [String, Number, null], default: null },
  videoEvaluations: { type: Array, default: () => [] }
})

const page = usePage()

const videoElement = ref(null)
const showFinishedMessage = ref(false)
const showEvalModal = ref(false)
const countdown = ref(0)
const secondsToNext = 5 

let countdownInterval = null
let player = null

const autoAdvanceCancelled = ref(false)

const isLastVideo = computed(() => String(props.video?.id ?? '') === String(props.lastVideoId ?? ''))
const evalCount = computed(() => props.videoEvaluations?.length || 0)
const hasEvaluations = computed(() => evalCount.value > 0)

const L = computed(() => page.props.L ?? {})
const User = computed(() => page.props.auth.user ?? {})
const userLang = computed(() => User.value.locale || 'es')

const subtitles = computed(() => {
  if (!props.video?.captions || !Array.isArray(props.video.captions)) return []
  return props.video.captions.map(c => ({
    kind: 'subtitles',
    src: `/storage/${c.file}`,
    srclang: c.lang || 'es',
    label: c.label || c.lang.toUpperCase(),
    default: (c.lang === userLang.value) || (userLang.value === 'es' && c.lang === 'es')
  }))
})

/* Descripción con collapse */
const showFullDesc = ref(false)
const isLongDescription = computed(() => (props.video.description?.length || 0) > 200)
const shortDescription = computed(() => props.video.description ? props.video.description.substring(0, 200) : '')
const descId = computed(() => `desc-${props.video?.id ?? 'x'}`)
function toggleDesc() { showFullDesc.value = !showFullDesc.value }

/* Reproductor */
onMounted(() => {
  if (!videoElement.value) return

  const detectType = (url) => {
    if (!url) return 'video/mp4'
    const u = url.split('?')[0].toLowerCase()
    if (u.endsWith('.m3u8')) return 'application/x-mpegURL'
    if (u.endsWith('.mpd')) return 'application/dash+xml'
    if (u.endsWith('.webm')) return 'video/webm'
    if (u.endsWith('.ogg') || u.endsWith('.ogv')) return 'video/ogg'
    return 'video/mp4'
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
    sources: [{ src: props.sourceUrl, type: detectType(props.sourceUrl) }]
  })

  // Botón de descarga personalizado
  player.ready(() => {
    const controlBar = player.getChild('controlBar')
    if (controlBar) {
      const DownloadButton = videojs.getComponent('Button')
      class CustomDownloadButton extends DownloadButton {
        createEl() {
          const el = super.createEl('button', { className: 'vjs-control vjs-button' })
          el.setAttribute('aria-label', 'Descargar video')
          el.innerHTML = '<i class="bi bi-download"></i>'
          return el
        }
        handleClick() {
          const src = props.sourceUrl
          const a = document.createElement('a')
          a.href = src
          a.download = (props.video?.title || 'video') + '.mp4'
          a.target = '_blank'
          a.rel = 'noopener'
          document.body.appendChild(a)
          a.click()
          a.remove()
        }
      }
      videojs.registerComponent('DownloadButton', CustomDownloadButton)
      controlBar.addChild('DownloadButton', {}, controlBar.children_.length)
    }

    // Agrega subtítulos
    subtitles.value.forEach(track => {
      player.addRemoteTextTrack(track, false)
    })

    // Fuerza la activación del subtítulo del usuario
    const textTracks = player.textTracks()
    if (textTracks?.length) {
      for (let i = 0; i < textTracks.length; i++) {
        const t = textTracks[i]
        if (t.language === userLang.value) {
          t.mode = 'showing'
        } else {
          t.mode = 'disabled'
        }
      }
    }
  })

  // Actividades
  player.on('play', () => registerActivity('play', player.currentTime()))
  player.on('pause', () => registerActivity('pause', player.currentTime()))
  player.on('ended', () => {
    registerActivity('ended', player.currentTime())
    axios.post('/frontend/courses/video-activity', {
      course_id: props.courseId,
      video_id: props.video.id
    }).catch(() => {})

    clearCountdown()
    autoAdvanceCancelled.value = true

    if (isLastVideo.value) {
      axios.post('/frontend/courses/course-activity', {
        course_id: props.courseId,
        activity: 'Curso finalizado por el usuario'
      }).catch(() => {})
    }

    showFinishedMessage.value = false
    showEvalModal.value = true
  })
})

onBeforeUnmount(() => {
  clearCountdown()
  if (player) {
    player.dispose()
    player = null
  }
})

watch(() => [props.video?.id, props.sourceUrl], () => {
  showFinishedMessage.value = false
  showEvalModal.value = false
  autoAdvanceCancelled.value = false
  clearCountdown()
  showFullDesc.value = false
})

/* Otros métodos */
function clearCountdown() {
  if (countdownInterval) {
    clearInterval(countdownInterval)
    countdownInterval = null
  }
}

function registerActivity(event, second) {
  axios.post('/frontend/video-activity', {
    video_id: props.video.id,
    course_id: props.courseId,
    event,
    second
  }).catch(() => {})
}

function startCountdown() {
  if (isLastVideo.value) return
  autoAdvanceCancelled.value = false
  clearCountdown()
  countdown.value = secondsToNext
  countdownInterval = setInterval(() => {
    if (autoAdvanceCancelled.value) {
      clearCountdown()
      return
    }
    countdown.value--
    if (countdown.value <= 0) {
      clearCountdown()
      if (!autoAdvanceCancelled.value) emit('next')
    }
  }, 1000)
}

function onViewEvaluations() {
  autoAdvanceCancelled.value = true
  clearCountdown()
  showEvalModal.value = false

  const courseId = props.course?.id ?? props.video?.course_id
  if (!courseId) {
    console.warn('No se encontró courseId para navegar a evaluaciones')
    return
  }

  // Preferido: usando Ziggy si tienes una ruta nombrada
  // Ajusta el nombre si el tuyo es distinto (ej. 'frontend.courses.evaluations.index')
  let url
  try {
    url = route('frontend.courses.evaluations.index', { course: courseId })
  } catch {
    // Fallback: URL directa
    url = `/frontend/courses/${courseId}/evaluations`
  }

  Inertia.visit(url) // o: router.visit(url)
}

function onNextFromModal() {
  autoAdvanceCancelled.value = true
  clearCountdown()
  showEvalModal.value = false
  emit('next')
}

function closeEvalModal() {
  showEvalModal.value = false
}
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
