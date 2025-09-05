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

    <!-- Video / Lista -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row g-4">
          <!-- Columna de contenido -->
          <div class="col-12 col-lg-8" id="content" ref="contentCol" :style="stickyStyle" :class="{'lg-sticky': stickContent}">
            <!-- Reproductor -->
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

            <!-- Botones de acciones (arriba, alineados a la derecha) -->
            <section class="py-2">
              <div class="card border-0 shadow my-3">
                <div class="card-body">
                  <div class="d-flex justify-content-end gap-2">
                <button
                  v-if="hasEvals"
                  type="button"
                  class="btn btn-primary  btn-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#modalEvaluaciones"
                  title="Ver evaluaciones"
                >
                  <i class="bi bi-clipboard-check me-1"></i> Evaluaciones
                </button>

                <button
                  v-if="hasResources"
                  type="button"
                  class="btn btn-primary  btn-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#modalRecursos"
                  title="Ver recursos"
                >
                  <i class="bi bi-link-45deg me-1"></i> Recursos
                </button>

                <button
                  v-if="hasMaterials"
                  type="button"
                  class="btn btn-primary  btn-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#modalMateriales"
                  title="Ver materiales"
                >
                  <i class="bi bi-bag-check me-1"></i> Materiales
                </button>
              </div>
                </div>
              </div>
            </section>

 

            <!-- Comentarios (debajo del video) -->
            <div class="card border-0 shadow my-3">
              <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots me-1"></i> Comentarios</h6>
              </div>
              <div class="card-body">
                <VideoComments
                  :video-id="video.id"
                  :course-id="course.id"
                  :can-comment="user != null"
                />
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

          <!-- Sidebar (lista de videos intacta) -->
          <div class="col-12 col-lg-4" id="sidebar" ref="sidebarCol" :style="stickyStyle" :class="{'lg-sticky': stickSidebar}">
            <div class="card border-0 shadow mb-3">
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
                    : null"
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

  <!-- MODALES DE BOOTSTRAP -->

  <!-- Modal: Evaluaciones -->
  <div class="modal fade" id="modalEvaluaciones" tabindex="-1" aria-labelledby="modalEvaluacionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="modalEvaluacionesLabel" class="modal-title"><i class="bi bi-clipboard-check me-1"></i> Evaluaciones del video</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <VideoCardEvaluations
            :video-evaluations="videoEvaluations"
            route-show-submission="dashboard.evaluation-users.show"
            route-start-evaluation="dashboard.courses.evaluations.evaluation.preview"
          />
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Recursos -->
  <div class="modal fade" id="modalRecursos" tabindex="-1" aria-labelledby="modalRecursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="modalRecursosLabel" class="modal-title"><i class="bi bi-link-45deg me-1"></i> Recursos del video</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <VideoCardResources
            :video-resources="videoResources"
            :video-id="video.id"
            @show-resource="openResourceModal"
          />
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Materiales -->
  <div class="modal fade" id="modalMateriales" tabindex="-1" aria-labelledby="modalMaterialesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="modalMaterialesLabel" class="modal-title"><i class="bi bi-bag-check me-1"></i> Materiales del video</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <VideoCardMaterials
            :video-id="video.id"
            :video-materials="videoMaterials"
            :materials-summary="materialsSummary"
            currency="MXN"
            locale="es-MX"
          />
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Detalle de Recurso (al hacer click desde la lista) -->
  <div class="modal fade" id="modalRecurso" tabindex="-1" aria-labelledby="modalRecursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" v-if="selectedResource">
        <div class="modal-header">
          <h5 id="modalRecursoLabel" class="modal-title">
            <i class="bi bi-file-earmark-text me-1"></i> {{ selectedResource.title || 'Recurso' }}
          </h5>
          <button type="button" class="btn-close" @click="closeResourceModal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p v-if="selectedResource.description" class="mb-3">{{ selectedResource.description }}</p>
          <div v-if="selectedResource.url" class="mb-2">
            <a :href="selectedResource.url" target="_blank" class="btn btn-primary btn-sm">
              <i class="bi bi-box-arrow-up-right me-1"></i> Abrir recurso
            </a>
          </div>
          <div v-if="selectedResource.file_url" class="mb-2">
            <a :href="selectedResource.file_url" target="_blank" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-download me-1"></i> Descargar archivo
            </a>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" @click="closeResourceModal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
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
import CardsVideoEvaluations from '@/Components/Dashboard/Cards/CardsVideoEvaluations.vue'
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
  videoMaterials
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
  videoMaterials: { type: Array, default: () => [] }
})

const user = computed(() => page.props?.auth?.user ?? null)

// Navegación videos
const lastVideoId = computed(() =>
  Array.isArray(videos) && videos.length ? Number(videos[videos.length - 1].id) : null
)
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
</style>
