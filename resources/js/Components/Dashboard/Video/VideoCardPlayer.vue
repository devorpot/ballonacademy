<template>
  <div class="card shadow-sm position-relative">
    <!-- Video -->


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





    <!-- Overlay mensaje final (video intermedio) -->
    <div v-if="showFinishedMessage && !isLastVideo" class="finished-overlay">
      <div class="text-center">
        <i class="bi bi-check-circle-fill display-1 text-success mb-3"></i>
        <h5 class="fw-bold">Has finalizado el video</h5>
        <p class="text-muted mb-4">Continúa con el siguiente</p>
        <div>
          <div class="spinner-border text-success mb-3" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <div class="text-muted small">
            Avanzando en {{ countdown }} segundos...
          </div>
        </div>
      </div>
    </div>


 
    <!-- Overlay mensaje final del curso -->
    <div v-if="showFinishedMessage && isLastVideo" class="finished-overlay">
      <div class="text-center">
        <i class="bi bi-check-circle-fill display-1 text-success mb-3"></i>
        <h5 class="fw-bold">¡Felicidades!</h5>
        <p class="text-muted">Has completado todo el curso</p>
      </div>
    </div>

    <!-- Título y descripción -->
    <div class="card-body">
      <h2 class="h5 fw-bold">{{ video.title }}</h2>
      <p class="text-muted">{{ video.description }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import videojs from 'video.js'
import 'video.js/dist/video-js.css'
import axios from 'axios'

const emit = defineEmits(['next'])

const props = defineProps({
  video: Object,
  sourceUrl: String,
  courseId: [String, Number],
  lastVideoId: Number // ✅ nuevo prop
})

const videoElement = ref(null)
const showFinishedMessage = ref(false)
const countdown = ref(0)
const secondsToNext = 5
let countdownInterval = null
let player = null

const isLastVideo = computed(() => props.video.id === props.lastVideoId)

function registerActivity(event, second) {
  axios.post('/frontend/video-activity', {
    video_id: props.video.id,
    course_id: props.courseId,
    event,
    second
  }).then(() => {
    console.log(`📡 ${event} registrado en ${second}s`)
  }).catch(error => {
    console.error('❌ Error al registrar actividad:', error)
  })
}

function startCountdown() {
  countdown.value = secondsToNext
  countdownInterval = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) {
      clearInterval(countdownInterval)
      emit('next')
    }
  }, 1000)
}

onMounted(() => {
  if (!videoElement.value) return

  player = videojs(videoElement.value, {
    sources: [{ src: props.sourceUrl, type: 'video/mp4' }],
    preload: 'auto'
  })

  player.on('play', () => {
    registerActivity('play', player.currentTime())
  })

  player.on('pause', () => {
    registerActivity('pause', player.currentTime())
  })

  player.on('ended', () => {
    registerActivity('ended', player.currentTime())
    showFinishedMessage.value = true;
      if (isLastVideo.value) {
          axios.post('/frontend/courses/course-activity', {

              course_id: props.courseId,
              activity: 'Curso finalizado por el usuario'
            }).then(() => {
               console.log(' Curso finalizado registrado')
            }).catch(error => {
              console.error('Error al registrar curso finalizado:', error)
            })
      }

       axios.post('/frontend/courses/video-activity', {

              course_id: props.courseId,
              video_id: props.video.id
            }).then(() => {
               console.log(' Video finalizado registrado')
            }).catch(error => {
              console.error('Error al registrar video finalizado:', error)
            })

      if (!isLastVideo.value) {
        startCountdown()
      }
  })
})

onBeforeUnmount(() => {
  if (player) player.dispose()
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>

<style scoped>
.video_player {
  width: 100%;
  height: 50vh;
  background: #000;
}

.finished-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 50vh;
  background-color: rgba(255, 255, 255, 0.95);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10;
  padding: 2rem;
  text-align: center;
}
</style>
