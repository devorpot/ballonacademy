<!-- VideoPlayerCard.vue -->
<template>
  <div class="card shadow-sm position-relative border-0 video-player-card">
    <div @contextmenu.prevent>
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

    <!-- Encabezado -->
    <div class="px-3 pt-3">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <h2 class="h5 fw-bold mb-0 me-2 text-truncate title-truncate" :title="video.title">
          {{ video.title }}
        </h2>

        <span v-if="hasEvaluations" class="badge bg-primary d-inline-flex align-items-center gap-1">
          <i class="bi bi-ui-checks-grid"></i>
          {{ evalCount }} {{ evalCount === 1 ? 'Evaluación' : 'Evaluaciones' }}
        </span>

        <span v-if="video.order" class="badge bg-secondary d-inline-flex align-items-center gap-1">
          <i class="bi bi-list-ol"></i> Orden {{ video.order }}
        </span>

        <span v-if="video.duration" class="badge bg-dark d-inline-flex align-items-center gap-1">
          <i class="bi bi-clock"></i> {{ video.duration }}
        </span>
      </div>
    </div>

    <!-- Descripción -->
    <div class="card-body pt-2">
      <p v-if="!isLongDescription" class="text-muted mb-0">
        {{ video.description || 'Sin descripción.' }}
      </p>

      <div v-else>
        <p class="text-muted mb-0">
          {{ shortDescription }}<span v-if="!showFullDesc">…</span>
        </p>

        <div class="collapse mt-2" :id="descId" :aria-hidden="!showFullDesc">
          <p class="text-muted mb-0">{{ video.description }}</p>
        </div>

        <button
          class="btn btn-link btn-sm px-0 mt-1"
          type="button"
          data-bs-toggle="collapse"
          :data-bs-target="'#' + descId"
          :aria-controls="descId"
          :aria-expanded="showFullDesc ? 'true' : 'false'"
          @click="toggleDesc"
        >
          {{ showFullDesc ? 'Ver menos' : 'Ver más' }}
        </button>
      </div>
    </div>
 
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import videojs from 'video.js'
import 'video.js/dist/video-js.css'
import axios from 'axios'

const emit = defineEmits(['next', 'completed'])

const props = defineProps({
  video: { type: Object, required: true },
  sourceUrl: { type: String, required: true },
  courseId: { type: [String, Number], required: true },
  lesson: { type: Object, required: true },
  lastVideoId: { type: [String, Number, null], default: null },
  lastVideoIdLesson: { type: [Number, null], default: null },
  firstVideoIdLesson: { type: [Number, null], default: null },
  videoEvaluations: { type: Array, default: () => [] }
})

const page = usePage()
const User = computed(() => page.props.auth?.user ?? {})
const userLang = computed(() => (User.value?.locale || 'es'))

const videoElement = ref(null)
let player = null
const showEvalModal = ref(false)

/* Descripción */
const showFullDesc = ref(false)
const isLongDescription = computed(() => (props.video.description?.length || 0) > 200)
const shortDescription = computed(() => props.video.description ? props.video.description.substring(0, 200) : '')
const descId = computed(() => `desc-${props.video?.id ?? 'x'}`)
function toggleDesc() { showFullDesc.value = !showFullDesc.value }

/* Evaluaciones (solo lo usado) */
const evalCount = computed(() => props.videoEvaluations?.length || 0)
const hasEvaluations = computed(() => evalCount.value > 0)
function pickEvalStatus(ev) {
  return ev?.user_status ?? ev?.pivot?.status ?? ev?.evaluation_user?.status ?? ev?.status ?? null
}
function isSentStatus(s) {
  if (s == null) return false
  const v = String(s).trim().toUpperCase()
  return v === 'SEND' || v === 'SENT' || v === 'ENVIADO' || s === 111 || v === 'SUBMITTED'
}
const allEvalsSent = computed(() => {
  if (!hasEvaluations.value) return false
  return props.videoEvaluations.every(ev =>
    isSentStatus(pickEvalStatus(ev)) ||
    Boolean(ev?.submitted_by_me) ||
    (ev?.submitted_by_me_count ?? 0) > 0 ||
    Boolean(ev?.my_last_submission?.id)
  )
})
const isBlockedByEvals = computed(() => hasEvaluations.value && !allEvalsSent.value)

/* Fin de lección/curso */
const isLastOfCourse = computed(() => String(props.video?.id ?? '') === String(props.lastVideoId ?? ''))
const isLastOfLesson = computed(() => String(props.video?.id ?? '') === String(props.lastVideoIdLesson ?? ''))

/* Poster */
const posterUrl = computed(() => props.video?.image_cover ? `/storage/${props.video.image_cover}` : '')

/* Subtítulos (estilo “ayer”) */
const subtitles = computed(() => {
  const list = Array.isArray(props.video?.captions) ? props.video.captions : []
  const u = (userLang.value || 'es').toLowerCase()
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

function detectType(url) {
  if (!url) return 'video/mp4'
  const u = url.split('?')[0].toLowerCase()
  if (u.endsWith('.m3u8')) return 'application/x-mpegURL'
  if (u.endsWith('.mpd'))  return 'application/dash+xml'
  if (u.endsWith('.webm')) return 'video/webm'
  if (u.endsWith('.ogg') || u.endsWith('.ogv')) return 'video/ogg'
  return 'video/mp4'
}

function refreshSubtitles() {
  if (!player) return
  const prev = player.remoteTextTracks?.()
  if (prev && prev.length) {
    for (let i = prev.length - 1; i >= 0; i--) player.removeRemoteTextTrack(prev[i])
  }
  subtitles.value.forEach(track => player.addRemoteTextTrack(track, false))

  const tracks = player.textTracks?.()
  if (tracks && tracks.length) {
    const want = (userLang.value || 'es').toLowerCase().split('-')[0]
    let chosen = -1
    for (let i = 0; i < tracks.length; i++) {
      const t = tracks[i]
      const lang = String(t.language || t.srclang || '').toLowerCase().split('-')[0]
      if (lang === want) { chosen = i; break }
    }
    if (chosen === -1 && want === 'es') {
      for (let i = 0; i < tracks.length; i++) {
        const t = tracks[i]
        const lang = String(t.language || t.srclang || '').toLowerCase().split('-')[0]
        if (lang === 'es') { chosen = i; break }
      }
    }
    if (chosen === -1) chosen = 0
    for (let i = 0; i < tracks.length; i++) tracks[i].mode = (i === chosen) ? 'showing' : 'disabled'
  }
}

function initPlayer() {
  if (!videoElement.value) return

  if (player) {
    player.src({ src: props.sourceUrl, type: detectType(props.sourceUrl) })
    player.poster(posterUrl.value)
    refreshSubtitles()
    return
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

  player.ready(() => refreshSubtitles())

  player.on('play',  () => registerVideoActivity('play',  player.currentTime()))
  player.on('pause', () => registerVideoActivity('pause', player.currentTime()))
  player.on('ended', onEnded)

}

function onEnded() {
  registerVideoActivity('ended', player?.currentTime?.() ?? 0)

  axios.post('/frontend/courses/video-activity', {
    course_id: props.courseId,
    video_id: props.video.id,
    lesson_id: props.lesson.id
  }).catch(() => {})

  if (isLastOfLesson.value) {
    axios.post('/frontend/courses/lesson-activity', {
      course_id: props.courseId,
      lesson_id: props.lesson.id,
      activity: 'Leccion finalizada por el usuario'
    }).catch(() => {})
  }

  if (isLastOfCourse.value) {
    axios.post('/frontend/courses/course-activity', {
      course_id: props.courseId,
      activity: 'Curso finalizado por el usuario'
    }).catch(() => {})
  }

  // Notifica al padre; no abras modal aquí
  emit('completed')
  // elimina: showEvalModal.value = true
}


function registerVideoActivity(event, second) {
  axios.post('/frontend/video-activity', {
    video_id: props.video.id,
    course_id: props.courseId,
    lesson_id: props.lesson.id,
    event,
    second
  }).catch(() => {})
}

function onNextFromModal() {
  showEvalModal.value = false
  emit('next')
}
function closeEvalModal() {
  showEvalModal.value = false
}

onMounted(initPlayer)

onBeforeUnmount(() => {
  try { player?.dispose?.() } catch {}
  player = null
})

watch(
  () => [props.video?.id, props.sourceUrl, posterUrl.value, userLang.value],
  () => { initPlayer() }
)
</script>

<style scoped>
.video_player { width: 100%; height: 100%; background: #000; object-fit: cover; }

.title-truncate {
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.video-player-card { border-radius: 1rem; }
.video-player-card .card-body { padding-bottom: 1rem; }

.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(33, 37, 41, 0.6);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 1080;
}
.modal-card {
  width: 100%;
  max-width: 560px;
  background: var(--bs-body-bg);
  color: var(--bs-body-color);
  border-radius: .75rem;
  box-shadow: 0 20px 50px rgba(0,0,0,.2);
  overflow: hidden;
}
.modal-header,
.modal-footer { padding: .875rem 1rem; border: 0; }
.modal-body { padding: .5rem 1rem 1rem; }
.btn-close { outline: none; }

.badge { font-weight: 500; line-height: 1; padding: .5rem .6rem; border-radius: 999px; }
</style>
