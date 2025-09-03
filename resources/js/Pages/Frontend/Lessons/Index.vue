<!-- resources/js/Pages/Frontend/Lessons/Index.vue -->
<template>
  <Head :title="`Lecciones de ${course.title}`" />

  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Mis Cursos', route: 'dashboard.courses.index' },
  
        { label: course.title+ ` Lecciones`, route: '' }
      ]"
    />

 

 
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <h3>{{course.title}}</h3>
          </div>
        </div>
      </div>
    </section>
    <section class="section-panel py-3">
      <div class="container-fluid">
        <!-- Empty state -->
        <div v-if="!lessons?.length" class="alert alert-info">
          Este curso aún no tiene lecciones disponibles.
        </div>

        <!-- Lista de lecciones SIEMPRE ABIERTA -->
        <div v-else class="lessons-stack">
          <div
            v-for="lesson in lessons"
            :key="lesson.id"
            class="card shadow-sm mb-3"
          >
            <!-- Encabezado de la lección -->
            <div class="card-header d-flex align-items-center gap-2 flex-wrap">
              <span class="badge rounded-pill fw-semibold bg-dark">
                {{ (lesson.order ?? 0).toString().padStart(2, '0') }}
              </span>

              <span class="fw-semibold d-inline-flex align-items-center gap-2">
                <i
                  v-if="lesson.progress?.is_completed"
                  class="bi bi-check2-circle text-success"
                  title="Lección completada"
                />
                {{ lesson.title }}
              </span>

              <span class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                <span class="badge" :class="lesson.progress?.is_completed ? 'bg-success' : 'bg-secondary'">
                  {{ lesson.progress?.completed_videos || 0 }}/{{ lesson.progress?.total_videos || 0 }}
                </span>
                <span v-if="lesson.add_video" class="badge bg-primary">
                  <i class="bi bi-play-btn-fill me-1"></i>
                  {{ lesson.videos_count ?? (lesson.videos?.length || 0) }} videos
                </span>
                <span v-if="lesson.add_evaluation" class="badge bg-success">
                  <i class="bi bi-card-checklist me-1"></i>
                  {{ lesson.evaluations_count ?? 0 }} evals
                </span>

                <Link
                  class="btn btn-primary rounded-pill  align-items-center px-3 py-2"
                  :href="route('dashboard.courses.lessons.show', { course: course.id, lesson: lesson.id })"
                  title="Ver lección"
                >
                  <i class="bi bi-box-arrow-up-right me-1"></i> Ver lección
                </Link>
              </span>
            </div>

            <!-- Cuerpo de la lección -->
            <div class="card-body">
              <div class="row g-3">
                <!-- Portada y progreso -->
                <div class="col-12 col-md-3">
                  <div class="ratio ratio-16x9 mb-2">
                    <img
                      class="img-fluid rounded object-fit-cover"
                      :src="lesson.thumbnail ? `/storage/${lesson.thumbnail}` : 'https://placehold.co/500x300'"
                      :alt="lesson.title"
                    />
                  </div>

                  <div class="mb-2">
                    <div class="progress" style="height: 6px;">
                      <div
                        class="progress-bar"
                        :class="progressBarClass(lesson)"
                        role="progressbar"
                        :style="{ width: progressPercent(lesson) + '%' }"
                      />
                    </div>
                    <small class="text-muted">
                      {{ lesson.progress?.completed_videos || 0 }} / {{ lesson.progress?.total_videos || 0 }} videos completados
                    </small>
                  </div>
                </div>

                <!-- Resumen y videos -->
                <div class="col-12 col-md-9">
                  <p class="text-muted mb-3" v-if="lesson.description_short">
                    {{ lesson.description_short }}
                  </p>

                  <!-- Lista de videos -->
                  <div v-if="(lesson.videos && lesson.videos.length) || (lesson.videos_count > 0)" class="mt-1">
                    <div class="d-flex align-items-center mb-2">
                      <i class="bi bi-play-circle me-2"></i>
                      <h6 class="mb-0">Clases de la lección</h6>
                    </div>

                    <ul class="list-group list-group-flush">
                      <li
                        v-for="(video, idx) in (lesson.videos || [])"
                        :key="video.id ?? idx"
                        class="list-group-item px-0 py-2"
                      >
                        <div class="d-flex align-items-center gap-3">
                          <span class="text-muted small" style="width: 2rem;">
                            {{ (idx + 1).toString().padStart(2, '0') }}
                          </span>

                          <i
                            class="bi"
                            :class="video.is_ended ? 'bi-check2-circle text-success' : (video.is_accessible ? 'bi-play-btn text-primary' : 'bi-lock-fill text-warning')"
                            :title="videoTooltip(video)"
                          />

                          <div class="flex-grow-1">
                            <div class="fw-semibold" :class="{ 'text-muted': !video.is_accessible }">
                              {{ video.title }}
                            </div>
                    
                          </div>

                          <small class="text-muted text-nowrap" v-if="video.duration">{{ video.duration }}</small>

                          <div class="ms-2 d-flex gap-2">
                            <!--<button
                              class="btn btn-sm btn-outline-primary"
                              @click="openPreview(video)"
                              :disabled="!video.is_accessible || !previewUrl(video)"
                              :title="!video.is_accessible ? 'Completa el anterior para previsualizar' : 'Vista previa'"
                            >
                              <i class="bi bi-eye"></i>
                              <span class="d-none d-sm-inline ms-1">Vista previa</span>
                            </button>-->
                            <Link
                                v-if="video.is_accessible"
                                :href="route('dashboard.courses.lessons.videos.show', {
                                  course: course.id,
                                  lesson: lesson.id,
                                  video: video.id
                                })"
                                class="btn btn-primary rounded-pill  align-items-center px-3 py-2"
                              >
                                Ver video
                              </Link>

                               

                          </div>
                        </div>
                      </li>

                      <!-- Solo conteo si no vino el arreglo -->
                      <li
                        v-if="(!lesson.videos || lesson.videos.length === 0) && (lesson.videos_count > 0)"
                        class="list-group-item px-0 text-muted"
                      >
                        Hay {{ lesson.videos_count }} videos asignados a esta lección.
                      </li>
                    </ul>
                  </div>

                  <!-- Evaluaciones -->
                  <div v-if="lesson.add_evaluation" class="mt-3">
                    <h6 class="fw-semibold mb-0">
                      <i class="bi bi-card-checklist me-1"></i>
                      Evaluaciones: <span class="text-success">{{ lesson.evaluations_count ?? 0 }}</span>
                    </h6>
                    <small class="text-muted">Solo se muestra el total asignado a la lección.</small>
                  </div>
                </div>
              </div>
            </div>
          </div> <!-- /card -->
        </div> <!-- /lessons-stack -->
      </div>
    </section>

    <!-- Modal Vista Previa -->
    <div v-if="showPreview" class="modal fade show" style="display:block;" role="dialog" aria-modal="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-eye me-2"></i> Vista previa — {{ currentVideo?.title }}
            </h5>
            <button type="button" class="btn-close" @click="closePreview" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- archivo directo -->
            <video
              v-if="isDirectVideo(currentVideo)"
              class="w-100 rounded"
              controls
              :poster="currentVideo?.thumbnail ? `/storage/${currentVideo.thumbnail}` : undefined"
            >
              <source :src="previewUrl(currentVideo)" type="video/mp4" />
              Tu navegador no soporta el elemento video.
            </video>

            <!-- embed (YouTube/Vimeo) -->
            <div v-else class="ratio ratio-16x9">
              <iframe
                :src="embedUrl(currentVideo)"
                title="Vista previa"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
              ></iframe>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closePreview">Cerrar</button>
            <Link
              v-if="currentVideo"
              class="btn btn-primary"
              :href="route('dashboard.courses.lessons.show', { course: course.id, lesson: currentVideo.lesson_id || guessLessonId(currentVideo) })"
            >
              Ir a la lección
            </Link>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
    </div>
  </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'
import { route } from 'ziggy-js'
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  course: Object,
  lessons: Array
})

/** Progreso y estilos */
function progressPercent(lesson) {
  const total = lesson?.progress?.total_videos || 0
  const done = lesson?.progress?.completed_videos || 0
  if (!total) return 0
  return Math.min(100, Math.round((done / total) * 100))
}
function progressBarClass(lesson) {
  if (lesson?.progress?.is_completed) return 'bg-success'
  const pct = progressPercent(lesson)
  if (pct >= 50) return 'bg-primary'
  return 'bg-secondary'
}

/** Vista previa */
const showPreview = ref(false)
const currentVideo = ref(null)

function previewUrl(video) {
  return video?.preview_url || video?.embed_url || video?.youtube_id || video?.vimeo_id || null
}
function isDirectVideo(video) {
  const url = video?.preview_url || ''
  return /\.(mp4|mov|webm|m4v)(\?.*)?$/i.test(url)
}
function embedUrl(video) {
  if (video?.embed_url) return video.embed_url
  if (video?.youtube_id) return `https://www.youtube.com/embed/${video.youtube_id}`
  if (video?.vimeo_id) return `https://player.vimeo.com/video/${video.vimeo_id}`
  return ''
}
function openPreview(video) {

  /*
  if (!previewUrl(video) || !video.is_accessible) return
  currentVideo.value = video
  showPreview.value = true
  document.body.classList.add('modal-open')
  */




}
function closePreview() {
  showPreview.value = false
  currentVideo.value = null
  document.body.classList.remove('modal-open')
}
function guessLessonId(video) {
  const entry = props.lessons?.find(l => (l.videos || []).some(v => (v.id || v.title) === (video.id || video.title)))
  return entry?.id
}
function videoTooltip(video) {
  if (video.is_ended) return 'Completado'
  if (!video.is_accessible) return 'Bloqueado hasta terminar el anterior'
  return 'Disponible'
}

/** Escape para cerrar modal */
function onKey(e) { if (e.key === 'Escape' && showPreview.value) closePreview() }
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<style scoped>
.object-fit-cover { object-fit: cover; }
.lessons-stack .card-header { background: var(--bs-light); }
</style>
