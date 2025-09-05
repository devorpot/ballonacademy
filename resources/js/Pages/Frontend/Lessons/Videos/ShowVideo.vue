<template>
  <Head :title="video.title" />

  <StudentLayout>
    <!-- Breadcrumb -->
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Cursos', route: 'dashboard.courses.index' },
        { label: course.title, route: 'dashboard.courses.show', params: [course.id] },
        { label: video.title }
      ]"
    />

 

 <!-- Alerta -->

 
  <!--Video Player-->
  <section class="section-panel py-3">
     <div class="container-fluid">
      <div class="row">
        <div class="col-12">
            <!-- Alerta de éxito al entrar si ya cumple -->
<div
  v-if="showSuccessAlert"
  class="alert alert-success alert-dismissible fade show mb-3"
  role="alert"
  aria-live="polite"
>
  <i class="bi bi-check-circle-fill me-2"></i>
  Has completado este video y enviado tus evaluaciones. Ya puedes continuar al siguiente video.
  <button type="button" class="btn-close" aria-label="Cerrar" @click="showSuccessAlert = false"></button>
</div>

           <VideoCardPlayerLesson
              :key="video.id"
              :video="video"
              :lesson="lesson"
              :course-id="course.id"
              :source-url="`/storage/${video.video_path}`"
              :last-video-id="lastVideoId"
              :first-video-id-lesson="firstVideoIdLesson"
              :last-video-id-lesson="lastVideoIdLesson"
              :video-evaluations="videoEvaluations"
              @next="goToNextVideo"
              @show-evaluations="revealEvaluations"
              @completed="onVideoCompleted"    
            />
        </div>
      </div>
    </div>
</section>
  <!--/Video Player-->


 
  <!--/Comentarios-->
  <section class="section-panel py-3">
     <div class="container-fluid">
      <div class="row">
        <!--Contenido-->
        <div class="col-12 col-md-8">
          <div class="d-block my-3">
            <VideoCardEvaluations
            :video-evaluations="videoEvaluations"
            route-show-submission="dashboard.evaluation-users.show"
            route-start-evaluation="dashboard.courses.evaluations.evaluation.preview"
          />
          </div>
          <div class="d-block my-3">
               <VideoCardMaterials
                :video-id="video.id"
                :video-materials="videoMaterials"
                :materials-summary="materialsSummary"
                currency="MXN"
                locale="es-MX"
              />
        </div>
         <div class="d-block my-3">
              <VideoCardResources
                :video-resources="videoResources"
                :video-id="video.id"
                @show-resource="openResourceModal"
              />
        </div>
        <div class="d-block my-3">
          <VideoComments
                  :video-id="video.id"
                  :course-id="course.id"
                  :can-comment="user != null"
                />
        </div>


        </div>
         <!--/contenido-->
        <!--sidebar-->
         <div class="col-12 col-lg-4" id="sidebar" ref="sidebarCol" :style="stickyStyle" :class="{'lg-sticky': stickSidebar}">
            <div class="card border-0 shadow mb-3">
              <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0">Lista de Videos</h6>
              </div>
              <div class="card-body pt-3 pb-0 px-3" id="video-list">

                <VideoCardMini
                  v-for="(v, idx) in videos"
                  :key="v.id"
                  :video="v"
                  :url="(!isLockedByCurrentEvals(idx) && v.is_accessible)
                    ? route('dashboard.courses.lessons.videos.show', {
                        course: course.id,
                        lesson: lesson.id,
                        video: v.id
                      })
                    : null"
                  :is-accessible="!isLockedByCurrentEvals(idx) && v.is_accessible"
                  :is-playing="v.id === video.id"
                  class="mb-3"
                />

              </div>
            </div>
          </div>
        <!--/sidebar-->
      </div>
    </div>
</section>
  <!--/Video Player-->
<LessonEvalAdvanceModal
  v-model="showLessonAdvanceModal"
  :video-evaluations="videoEvaluations"
  :course-id="course.id"
  :is-last-of-course="Number(video.id) === Number(videos?.[videos.length-1]?.id)"
  :is-last-of-lesson="Number(video.id) === Number(lastVideoIdLesson)"
  :next-url="nextVideo ? route('dashboard.courses.lessons.videos.show', { course: course.id, lesson: lesson.id, video: nextVideo.id }) : null"
  :is-course-completed="isCourseCompleted"
  @next="goToNextVideo"
/>
 <LessonCompleteModal
    v-model="showCompleteModal"
    :course-id="course.id"
    :course-title="course.title"
  />
 
<!-- Modal de avance de lección/video -->
 

  </StudentLayout>

</template>

<script setup>
import { Head, router, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, nextTick, watchEffect, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'
import { Modal } from 'bootstrap' // Importante para mostrar/ocultar modal programáticamente

import StudentLayout from '@/Layouts/StudentLayout.vue'
import VideoCardPlayerLesson from '@/Components/Dashboard/Video/VideoCardPlayerLesson.vue'
import VideoCardMini from '@/Components/Dashboard/Video/VideoCardMini.vue'
import VideoCardEvaluations from '@/Components/Dashboard/Video/VideoCardEvaluations.vue'
import VideoCardResources from '@/Components/Dashboard/Video/VideoCardResources.vue'
import VideoCardMaterials from '@/Components/Dashboard/Video/VideoCardMaterials.vue'
import VideoComments from '@/Components/Dashboard/Video/VideoComments.vue'
 
import LessonEvalAdvanceModal from '@/Components/Dashboard/Lesson/LessonEvalAdvanceModal.vue'
import LessonCompleteModal from '@/Components/Dashboard/Lesson/LessonCompleteModal.vue'

import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const page = usePage()

const {
  course,
  lesson,
  video,
  previousVideo,
  nextVideo,
  nextVideoAccessible,
  videos,
  videoEvaluations,
  courseEvaluations,
  videoResources,
  allVideos,
  lastVideoIdLesson,
  firstVideoIdLesson,
  materialsSummary,
 
  videoMaterials,
   isCourseCompleted
} = defineProps({
  course: Object,
  lesson: Object,
  video: Object,
  previousVideo: Object,
  nextVideo: Object,
  nextVideoAccessible: Boolean,
  videos: Array,
  videoEvaluations: { type: Array, default: () => [] },
  courseEvaluations: Object,
  videoResources: { type: Array, default: () => [] },
  allVideos: Array,
  lastVideoIdLesson: Number,
  firstVideoIdLesson: Number,
  materialsSummary: { type: Object, default: () => null },
  videoMaterials: { type: Array, default: () => [] },
    shouldShowContinueAlert: { type: Boolean, default: false },
    isCourseCompleted: { type: Boolean, default: false }
})

const showCompleteModal = ref(false);

const user = computed(() => page.props?.auth?.user ?? null)

// Navegación videos
const lastVideoId = computed(() =>
  Array.isArray(videos) && videos.length ? Number(videos[videos.length - 1].id) : null
)

const showSuccessAlert = ref(Boolean(page.props?.shouldShowContinueAlert ?? false))

function goToNextVideo() {
  if (nextVideo) {
    router.visit(route('dashboard.courses.lessons.videos.show', {
      course: course.id,
      lesson: lesson.id,
      video: nextVideo.id
    }))
  }
}

// Evaluaciones
const evalsUnlocked = ref(Boolean(nextVideoAccessible))
const canShowEvals = computed(() => evalsUnlocked.value || nextVideoAccessible)

const evalSection = ref(null)
const evalHighlighted = ref(false)
function revealEvaluations() {
  evalsUnlocked.value = true
  nextTick(() => {
    const el = evalSection.value?.$el || evalSection.value
    if (el?.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    evalHighlighted.value = true
    setTimeout(() => { evalHighlighted.value = false }, 2000)
  })
}

// Estado y modal para detalle de recurso (Bootstrap)
const selectedResource = ref(null)
let resourceModal = null

function openResourceModal(resource) {
  selectedResource.value = resource
  // Crea/obtiene instancia y muestra el modal
  if (!resourceModal) {
    const el = document.getElementById('modalRecurso')
    if (el) resourceModal = new Modal(el, { backdrop: true })
  }
  resourceModal?.show()
}

function closeResourceModal() {
  resourceModal?.hide()
  selectedResource.value = null
}

// Tabs y banderas de disponibilidad
const activeTab = ref('video')
const hasResources = computed(() => Array.isArray(videoResources) && videoResources.length > 0)
const hasEvals = computed(() =>
  canShowEvals.value && Array.isArray(videoEvaluations) && videoEvaluations.length > 0
)
const availableTabs = computed(() => {
  const t = ['video']
  if (hasEvals.value) t.push('evals')
  if (hasResources.value) t.push('resources')
  return t
})
const showTabNav = computed(() => availableTabs.value.length > 1)

const hasMaterials = computed(() => Array.isArray(videoMaterials) && videoMaterials.length > 0)

// Sticky inteligente contenido/sidebar
const contentCol = ref(null)
const sidebarCol = ref(null)
const stickContent = ref(false)
const stickSidebar = ref(false)
const topOffset = ref(16) // px; recalculado

const stickyStyle = computed(() => ({ '--stick-top': `${topOffset.value}px` }))

let resizeObs
let rafId = null

function computeTopOffset() {
  let offset = 16
  const candidates = [
    document.querySelector('.navbar'),
    document.querySelector('.topnav'),
    document.querySelector('header'),
    document.querySelector('.bg-light.border-bottom')
  ]
  for (const el of candidates) {
    if (el) offset += el.getBoundingClientRect().height
  }
  topOffset.value = Math.max(16, Math.round(offset))
}

function updateStickiness() {
  if (window.innerWidth < 992) {
    stickContent.value = false
    stickSidebar.value = false
    return
  }
  const c = contentCol.value
  const s = sidebarCol.value
  if (!c || !s) return

  const ch = c.scrollHeight
  const sh = s.scrollHeight
  const tol = 24

  if (sh > ch + tol) {
    stickContent.value = true
    stickSidebar.value = false
  } else if (ch > sh + tol) {
    stickContent.value = false
    stickSidebar.value = true
  } else {
    stickContent.value = false
    stickSidebar.value = false
  }
}

function scheduleMeasure() {
  cancelAnimationFrame(rafId)
  rafId = requestAnimationFrame(() => {
    computeTopOffset()
    updateStickiness()
  })
}

const showLessonAdvanceModal = ref(false)

function pickEvalStatus(ev) {
  return ev?.user_status ?? ev?.pivot?.status ?? ev?.evaluation_user?.status ?? ev?.status ?? null
}
function isSentStatus(s) {
  if (s == null) return false
  const v = String(s).trim().toUpperCase()
  return v === 'SEND' || v === 'SENT' || v === 'ENVIADO' || v === 'SUBMITTED' || s === 111
}

function isSubmittedByMe(ev) {
  if (ev?.submitted_by_me) return true
  if ((ev?.submitted_by_me_count ?? 0) > 0) return true
  if (ev?.my_last_submission?.id) return true
  const eu = ev?.evaluation_user ?? ev?.pivot
  if (eu?.user_id) return true
  return false
}

// Puede continuar si NO hay evaluaciones o si TODAS las envió
const canContinueAfterThisVideo = computed(() => {
  if (!hasEvaluations.value) return true
  return videoEvaluations.every(isSubmittedByMe)
})

const allEvalsSent = computed(() => {
  if (!Array.isArray(videoEvaluations) || videoEvaluations.length === 0) return true
  return videoEvaluations.every(isSubmittedByMe)
})

const hasEvaluations = computed(() =>
  Array.isArray(videoEvaluations) && videoEvaluations.length > 0
)



const currentIndexInList = computed(() =>
  Array.isArray(videos) ? videos.findIndex(v => Number(v.id) === Number(video.id)) : -1
)

// ¿Debe bloquearse un video por evaluaciones pendientes del actual?
function isLockedByCurrentEvals(targetIndex) {
  if (currentIndexInList.value < 0) return false
  // Bloqueamos solo los videos posteriores al actual si NO están enviadas TODAS las evals del actual
  return targetIndex > currentIndexInList.value && !allEvalsSent.value
}

/* Abre el modal de avance cuando el video termina y ya hay envíos */
function onVideoCompleted() {
  if (canContinueAfterThisVideo.value) {
    showSuccessAlert.value = true

    // Asegura que el usuario lo vea
    window.scrollTo({ top: 0, behavior: 'smooth' })

    // Ocúltalo después de 6s (ajusta a tu gusto)
    setTimeout(() => { showSuccessAlert.value = false }, 6000)
  } else {
    // Si quieres avisar distinto cuando falten evaluaciones, hazlo aquí
    // Ejemplo: usa otro estado para un alert warning
    // o deja que el usuario las vea en la sección de evaluaciones.
  }
   showLessonAdvanceModal.value = true
}
onMounted(() => {
  scheduleMeasure()

  // Pre-instancia modal de recurso (evita retardo al primer click)
  const el = document.getElementById('modalRecurso')
  if (el) resourceModal = new Modal(el, { backdrop: true })

  if ('ResizeObserver' in window) {
    resizeObs = new ResizeObserver(scheduleMeasure)
    if (contentCol.value) resizeObs.observe(contentCol.value)
    if (sidebarCol.value) resizeObs.observe(sidebarCol.value)
  }
  window.addEventListener('resize', scheduleMeasure, { passive: true })
  window.addEventListener('orientationchange', scheduleMeasure, { passive: true })

    if (showSuccessAlert.value) {
    setTimeout(() => { showSuccessAlert.value = false }, 6000)
    // Asegura visibilidad por si el usuario quedó scrolleado abajo
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
})

onBeforeUnmount(() => {
  cancelAnimationFrame(rafId)
  window.removeEventListener('resize', scheduleMeasure)
  window.removeEventListener('orientationchange', scheduleMeasure)
  if (resizeObs) {
    try {
      if (contentCol.value) resizeObs.unobserve(contentCol.value)
      if (sidebarCol.value) resizeObs.unobserve(sidebarCol.value)
      resizeObs.disconnect()
    } catch {}
  }
})

watchEffect(() => {
  if (!availableTabs.value.includes(activeTab.value)) {
    activeTab.value = availableTabs.value[0] || 'video'
  }
  void nextTick(() => scheduleMeasure())
   showCompleteModal.value = !!isCourseCompleted
})
</script>

<style scoped>
#video-list {
  max-height: 70vh;
  padding-right: 6px;
}

.eval-highlight-wrapper {
  transition: box-shadow .4s ease, outline-color .4s ease;
  border-radius: .5rem;
}
.eval-highlight-wrapper.is-highlighted {
  outline: 3px solid rgba(13,110,253,.2);
  box-shadow: 0 0 0 6px rgba(13,110,253,.12);
}

@media (min-width: 992px) {
  .lg-sticky {
    position: sticky;
    top: var(--stick-top, 16px);
  }
}
</style>
