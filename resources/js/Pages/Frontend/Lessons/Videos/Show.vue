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
 

 
    <!-- Reproductor y Lista -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row g-4">
          <!-- Reproductor -->
          <div class="col-12 col-lg-8" id="content">
            
           <div class="card">
              <!-- Tabs -->
            <div class="card-header" v-if="showTabNav">
                <ul
                  
                  class="nav nav-tabs"
                  role="tablist"
                >
              <li class="nav-item" role="presentation">
                <button
                  id="tab-video"
                  class="nav-link"
                  :class="{ active: activeTab === 'video' }"
                  type="button"
                  role="tab"
                  aria-controls="pane-video"
                  :aria-selected="activeTab === 'video'"
                  @click="activeTab = 'video'"
                >
                  <i class="bi bi-play-circle me-1"></i> Video
                </button>
              </li>

              <li v-if="hasEvals" class="nav-item" role="presentation">
                <button
                  id="tab-evals"
                  class="nav-link"
                  :class="{ active: activeTab === 'evals' }"
                  type="button"
                  role="tab"
                  aria-controls="pane-evals"
                  :aria-selected="activeTab === 'evals'"
                  @click="activeTab = 'evals'"
                >
                  <i class="bi bi-ui-checks-grid me-1"></i> Evaluaciones
                </button>
              </li>

              <li v-if="hasResources" class="nav-item" role="presentation">
                <button
                  id="tab-resources"
                  class="nav-link"
                  :class="{ active: activeTab === 'resources' }"
                  type="button"
                  role="tab"
                  aria-controls="pane-resources"
                  :aria-selected="activeTab === 'resources'"
                  @click="activeTab = 'resources'"
                >
                  <i class="bi bi-paperclip me-1"></i> Recursos
                </button>
              </li>
            </ul>
            </div>

            <!-- Contenido -->
            <div class="card-body">
              <div class="tab-content pt-3">
              <!-- VIDEO -->
              <div
                id="pane-video"
                class="tab-pane fade"
                :class="{ 'show active': activeTab === 'video' || !showTabNav }"
                role="tabpanel"
                aria-labelledby="tab-video"
              >


                <div class="card shadow-sm border-0">
                  <div class="card-body p-0">
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
                    />
                  </div>
                </div>
              </div>

              <!-- EVALUACIONES -->
              <div
                v-if="hasEvals"
                id="pane-evals"
                class="tab-pane fade"
                :class="{ 'show active': activeTab === 'evals' && showTabNav }"
                role="tabpanel"
                aria-labelledby="tab-evals"
              >
                <div class="card shadow-sm border-0">
                  <div class="card-body p-0">
                    <div :class="['eval-highlight-wrapper', { 'is-highlighted': evalHighlighted }]">
                      <CardsVideoEvaluations
                        :video-evaluations="videoEvaluations"
                        route-show-submission="dashboard.evaluation-users.show"
                        route-start-evaluation="dashboard.courses.evaluations.evaluation.preview"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- RECURSOS -->
              <div
                v-if="hasResources"
                id="pane-resources"
                class="tab-pane fade"
                :class="{ 'show active': activeTab === 'resources' && showTabNav }"
                role="tabpanel"
                aria-labelledby="tab-resources"
              >
                <div class="card shadow-sm border-0">
                  <div class="card-body p-0">
                    <div class="resources-highlight-wrapper">
                      <CardsVideoResources
                        :video-resources="videoResources"
                        :video-id="video.id"
                        @show-resource="openResourceModal"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
           </div>
 
  <!-- Videos anterior y siguiente -->
            <div class="row my-3">
              <div class="col-12 col-md-6" v-if="previousVideo">
                <div class="card shadow-sm border-0">
                  <div class="card-header bg-white border-bottom">
                    <h6 class="fw-bold mb-0"><i class="bi bi-arrow-left"></i> Video Anterior</h6>
                  </div>
                  <div class="card-body">
                    <VideoCardMini
                    v-if="previousVideo"
                    :video="previousVideo"
                    :url="route('dashboard.courses.lessons.videos.show', {
                      course: course.id,
                      lesson: lesson.id,
                      video: previousVideo.id
                    })"
                    :is-accessible="true"
                  />
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6 ms-auto" v-if="nextVideo">
                <div class="card shadow-sm border-0">
                  <div class="card-header bg-white border-bottom text-end">
                    <h6 class="fw-bold mb-0">Siguiente Video <i class="bi bi-arrow-right"></i></h6>
                  </div>
                  <div class="card-body">
                    <VideoCardMini
                      v-if="nextVideo"
                      :video="nextVideo"
                      :url="route('dashboard.courses.lessons.videos.show', {
                        course: course.id,
                        lesson: lesson.id,
                        video: nextVideo.id
                      })"
                      :is-accessible="nextVideoAccessible"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Lista de Videos -->
          <div class="col-12 col-lg-4" id="sidebar">
            <div class="card shadow-sm border-0 h-100">
              <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0">Lista de Videos</h6>
              </div>
              <div class="card-body pt-3 pb-0 px-3" id="video-list">
                <VideoCardMini
                  v-for="v in videos"
                  :key="v.id"
                  :video="v"
                  :url="v.is_accessible
                    ? route('dashboard.courses.lessons.videos.show', {
                        course: course.id,
                        lesson: lesson.id,
                        video: v.id
                      })
                    : null
                  "
                  :is-accessible="v.is_accessible"
                  :is-playing="v.id === video.id"
                  class="mb-3"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </StudentLayout>

  <!--   Modal de recurso con Teleport -->
  
 
 
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, nextTick, watchEffect, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

import StudentLayout from '@/Layouts/StudentLayout.vue'
import VideoCardPlayerLesson from '@/Components/Dashboard/Video/VideoCardPlayerLesson.vue'
import VideoCardMini from '@/Components/Dashboard/Video/VideoCardMini.vue'
import CardsVideoEvaluations from '@/Components/Dashboard/Cards/CardsVideoEvaluations.vue'
import CardsVideoResources from '@/Components/Dashboard/Cards/CardsVideoResources.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

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
  firstVideoIdLesson
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
  lastVideoIdLesson:Number,
  firstVideoIdLesson:Number
})

// Navegación videos
const lastVideoId = computed(() => videos.length ? Number(videos[videos.length - 1].id) : null)
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

// 🔹 Modal recurso
const showResource = ref(false)
const selectedResource = ref(null)

function openResourceModal(resource) {
  selectedResource.value = resource
  showResource.value = true
}
function closeResourceModal() {
  showResource.value = false
  selectedResource.value = null
}


// estado del tab activo
const activeTab = ref('video')

// booleans para mostrar/ocultar tabs
const hasResources = computed(() => Array.isArray(videoResources) && videoResources.length > 0)
const hasEvals = computed(() =>
  canShowEvals.value && Array.isArray(videoEvaluations) && videoEvaluations.length > 0
)
// si solo hay una pestaña, ocultamos la barra de tabs
const availableTabs = computed(() => {
  const t = ['video']
  if (hasEvals.value) t.push('evals')
  if (hasResources.value) t.push('resources')
  return t
})
const showTabNav = computed(() => availableTabs.value.length > 1)

 

const contentCol = ref(null)
const sidebarCol = ref(null)
const stickContent = ref(false)
const stickSidebar = ref(false)
const topOffset = ref(16) // px; se recalcula

const stickyStyle = computed(() => ({ '--stick-top': `${topOffset.value}px` }))

let resizeObs
let rafId = null

function computeTopOffset() {
  // Suma alturas de barras superiores que empujan el contenido
  let offset = 16
  const candidates = [
    document.querySelector('.navbar'),
    document.querySelector('.topnav'),
    document.querySelector('header'),
    document.querySelector('.bg-light.border-bottom') // breadcrumb wrapper si lo usas
  ]
  for (const el of candidates) {
    if (el) offset += el.getBoundingClientRect().height
  }
  topOffset.value = Math.max(16, Math.round(offset))
}

function updateStickiness() {
  // Evita sticky en móviles
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
  const tol = 24 // tolerancia

  if (sh > ch + tol) {
    // Sidebar es más alto → pegamos el CONTENT
    stickContent.value = true
    stickSidebar.value = false
  } else if (ch > sh + tol) {
    // Content es más alto → pegamos el SIDEBAR
    stickContent.value = false
    stickSidebar.value = true
  } else {
    // Alturas similares → sin sticky para evitar rarezas
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

onMounted(() => {
  scheduleMeasure()

  // Observa cambios de tamaño de cada columna (contenido dinámico)
  if ('ResizeObserver' in window) {
    resizeObs = new ResizeObserver(scheduleMeasure)
    if (contentCol.value) resizeObs.observe(contentCol.value)
    if (sidebarCol.value) resizeObs.observe(sidebarCol.value)
  }

  window.addEventListener('resize', scheduleMeasure, { passive: true })
  window.addEventListener('orientationchange', scheduleMeasure, { passive: true })
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

/* Resaltado de evaluaciones (ya lo tenías) */
.eval-highlight-wrapper {
  transition: box-shadow .4s ease, outline-color .4s ease;
  border-radius: .5rem;
}
.eval-highlight-wrapper.is-highlighted {
  outline: 3px solid rgba(13,110,253,.2);
  box-shadow: 0 0 0 6px rgba(13,110,253,.12);
}
</style>
