<!-- resources/js/Pages/Frontend/Lessons/Show.vue -->
<template>
  <Head :title="`Lección: ${lesson.title}`" />

  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Mis Cursos', route: 'dashboard.courses.index' },
        { label: course.title, route: 'dashboard.courses.lessons.index', params: course.id },
        { label: lesson.title, route: '' }
      ]"
    /> 
 
 Lesson/Show
 
 <section class="section-panel">
   <div class="container-fluid">
       <!-- Encabezado lección -->
        <div class="card shadow-sm mb-3">
          <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="mb-2 mb-md-0">
              
              <p class="text-muted mb-0">{{ lesson.description_short }}</p>
            </div>

            <div class="d-flex align-items-center gap-3">
              <div class="text-end">
                <small class="text-muted d-block">Progreso de la lección</small>
                <div class="progress" style="height: 8px; width: 220px;">
                  <div
                    class="progress-bar"
                    :class="progressBarClass"
                    role="progressbar"
                    :style="{ width: progressPct + '%' }"
                    :aria-valuenow="progressPct"
                    aria-valuemin="0"
                    aria-valuemax="100"
                  />
                </div>
                <small class="text-muted">
                  {{ lesson.progress.completed_videos }} / {{ lesson.progress.total_videos }} videos
                </small>
              </div>

              <span
                class="badge"
                :class="lesson.progress.is_completed ? 'bg-success' : 'bg-secondary'"
              >
                {{ lesson.progress.is_completed ? 'Completada' : 'En progreso' }}
              </span>
            </div>
          </div>
        </div>
   </div>
 </section>


 

 <section class="section-panel py-3">
      <div class="container-fluid">
          <div v-if="lesson.instructions" class="card shadow-sm mt-3">
          <div class="card-body">
            <h5 class="fw-bold mb-2">Instrucciones</h5>
            <p class="mb-0">{{ lesson.instructions }}</p>
          </div>
        </div>
      </div>
  </section>

  <section class="section-panel py-3">
      <div class="container-fluid">

       <div class="row g-3">
          <!-- Sección: Videos (siempre visible) -->
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <i class="bi bi-play-circle"></i>
                <h6 class="fw-bold mb-0">Videos</h6>
                <span class="badge bg-secondary ms-2">{{ videos.length }}</span>
              </div>

              <div class="card-body">
                <div v-if="videos.length === 0" class="alert alert-info mb-0">
                  Esta lección no tiene videos asignados.
                </div>

                <div v-else class="row">
                   <div
                    v-for="video in videos"
                    :key="video.id"
                    class="col-12 col-md-6 col-lg-4 mb-4" >
                    <div class="card video-card h-100 shadow-sm">
                  <!-- Thumbnail -->
                      <div class="video-thumb-wrapper">
                        <img
                          :src="video.thumbnail ? `/storage/${video.thumbnail}` : 'https://placehold.co/300x180?text=Sin+imagen'"
                          class="video-thumb"
                          :alt="video.title || 'Video sin título'"
                          :class="{ 'locked': !video.is_accessible }"
                        />

                        <!-- Duración -->
                        <span class="video-duration">{{ video.duration || '00:00' }}</span>

                        <!-- Candado overlay -->
                        <div v-if="!video.is_accessible" class="video-locked-overlay">
                          <i class="bi bi-lock-fill"></i>
                        </div>
                      </div>

                      <!-- Body -->
                      <div class="card-body d-flex flex-column">
                        <h6 class="card-title fw-bold mb-2">{{ video.title }}</h6>

                        <p class="text-muted small mb-2">
                          Tamaño: {{ video.size || '—' }}
                        </p>

                        <!-- Badges -->
                        <div class="mb-3">
                          <span
                            class="badge me-2"
                            :class="video.is_ended ? 'bg-success' : 'bg-secondary'"
                          >
                            {{ video.is_ended ? 'Completado' : 'Pendiente' }}
                          </span>

                          <span
                            class="badge"
                            :class="video.is_accessible ? 'bg-primary' : 'bg-warning text-dark'"
                          >
                            {{ video.is_accessible ? 'Accesible' : 'Bloqueado' }}
                          </span>
                        </div>

                        <!-- Footer -->
                        <div class="mt-auto" > 
                   

                           <Link
                              v-if="video.is_accessible  == '1'"
                              :href="route('dashboard.courses.lessons.videos.show', {
                                course: course.id,
                                lesson: lesson.id,
                                video: video.id
                              })"
                              class="btn btn-outline-primary w-100"
                            >
                              Ver video
                            </Link>

                        </div>
                      </div>
                    </div>
                  </div> <!-- /col -->
                </div>
              </div>
            </div>
          </div>
 
        </div>

        <!-- Instrucciones -->
      

      </div>
    </section>

<pre>
  {{evaluations}}
</pre>
 <section class="section-panel">
  <div class="container-fluid">
    <!-- Evaluaciones en filas -->
    <div class="col-12" v-if="evaluations.length">
      <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
          <i class="bi bi-card-checklist"></i>
          <h6 class="fw-bold mb-0">Evaluaciones</h6>
          <span class="badge bg-secondary ms-2">{{ evaluations.length }}</span>
        </div>

        <div class="card-body p-0">
          <div
            v-for="ev in evaluations"
            :key="ev.id"
            class="eval-row card border-0 border-bottom rounded-0"
          >
            <div class="card-body py-3 px-3 d-flex align-items-center gap-3 flex-wrap">
              <!-- Icono/estado -->
              <div class="eval-icon d-flex align-items-center justify-content-center">
                <i class="bi bi-clipboard-check"></i>
              </div>

              <!-- Contenido -->
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h6 class="mb-0 fw-semibold">{{ ev.title }}</h6>

                  <!-- Status -->
                  <span
                    class="badge"
                    :class="ev.status === 'SEND' ? 'bg-primary' : 'bg-secondary'"
                  >
                    {{ ev.status || '—' }}
                  </span>

                  <!-- Tipo de evaluación -->
                  <span class="badge bg-light text-body border">{{ ev.eva_type ?? '—' }}</span>

                  <!-- Puntos mínimos -->
                  <span class="badge bg-light text-body border">
                   Puntos Min: {{ ev.points_min ?? '—' }}
                  </span>

                  <!-- Tiene video adjunto -->
                  <span v-if="ev.eva_video_file" class="badge bg-light text-body border">
                    <i class="bi bi-camera-video me-1"></i> Video
                  </span>
                </div>

                <!-- Metadatos -->
                <div class="text-muted small mt-1 d-flex align-items-center gap-3 flex-wrap">
                  <span>
                    <i class="bi bi-calendar2-week me-1"></i>
                    {{ ev.eva_send_date || '—' }}
                  </span>
                  <span v-if="ev.video?.title">
                    <i class="bi bi-play-btn me-1"></i> {{ ev.video.title }}
                  </span>
                </div>

                <!-- Comentarios -->
                <p v-if="ev.eva_comments" class="mb-0 text-muted small mt-2 truncate-1">
                  {{ ev.eva_comments }}
                </p>
              </div>

              <!-- Acciones -->
              <div class="ms-auto d-flex align-items-center gap-2">
                <a
                  v-if="ev.eva_video_file"
                  class="btn btn-sm btn-outline-secondary"
                  :href="`/storage/${ev.eva_video_file}`"
                  target="_blank"
                  rel="noopener"
                  title="Ver video adjunto"
                >
                  <i class="bi bi-box-arrow-up-right me-1"></i> Ver adjunto
                </a>

                <a
                  class="btn btn-sm btn-outline-success"
                  :href="route('dashboard.courses.evaluations.evaluation.preview', { course: props.course.id, evaluation: ev.id })"
                  title="Ir a evaluaciones del curso"
                >
                  Ver detalles
                </a>
              </div>
            </div>
          </div>

          <!-- Último borde -->
          <div class="border-bottom-0"></div>
        </div>
      </div>
    </div>
  </div>
</section>

  </StudentLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const props = defineProps({
  course: { type: Object, required: true },
  lesson: { type: Object, required: true },
  videos: { type: Array, required: true },        // [{ id, title, image_cover, duration, size, is_ended, is_accessible }]
  evaluations: { type: Array, required: true }    // [{ id, title, status }]
})

const progressPct = computed(() => {
  const t = props.lesson?.progress?.total_videos || 0
  const c = props.lesson?.progress?.completed_videos || 0
  if (t === 0) return 0
  return Math.round((c / t) * 100)
})

const progressBarClass = computed(() => {
  if (props.lesson?.progress?.is_completed) return 'bg-success'
  return progressPct.value >= 50 ? 'bg-primary' : 'bg-secondary'
})

/**
 * URL al detalle del video.
 * Si ya tienes la ruta Ziggy, reemplaza por:
 *   return route('dashboard.videos.video.show', { course: props.course.id, video: videoId })
 */
function videoDetailUrl(videoId) {
  return `/frontend/courses/${props.course.id}/videos/${videoId}`
}

/**
 * URL índice de evaluaciones del curso.
 * Si tienes una route name, puedes usar:
 *   return route('dashboard.courses.evaluations.index', props.course.id)
 */
const evaluationsIndexUrl = computed(() =>
  route('courses.evaluations.evaluation.preview', { course: props.course.id })
)
</script>

<style scoped>
.card-img-top {
  object-fit: cover;
  height: 160px;
}
/* Card de video */
.video-card {
  border: none;
  overflow: hidden;
  border-radius: 0.5rem;
}

/* Thumbnail wrapper */
.video-thumb-wrapper {
  position: relative;
  width: 100%;
  height: 180px; /* altura uniforme */
  overflow: hidden;
  border-bottom: 1px solid #e9ecef;
}

.video-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.video-thumb.locked {
  filter: blur(6px) brightness(0.8);
  pointer-events: none;
}

.video-thumb-wrapper:hover .video-thumb {
  transform: scale(1.05);
}

/* Duración overlay */
.video-duration {
  position: absolute;
  bottom: 8px;
  right: 8px;
  background: rgba(0, 0, 0, 0.75);
  color: #fff;
  font-size: 0.75rem;
  padding: 2px 6px;
  border-radius: 0.25rem;
}

/* Candado overlay */
.video-locked-overlay {
  position: absolute;
  inset: 0; /* cubre todo el thumbnail */
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #fff;
  background: rgba(0, 0, 0, 0.35);
}

.video-card .card-title {
  font-size: 1rem;
  line-height: 1.3;
}

.video-card p {
  font-size: 0.85rem;
}

/* Fila de evaluación */
.eval-row + .eval-row {
  border-top: 1px solid var(--bs-border-color, #dee2e6);
}
.eval-row:first-child {
  border-top: none;
}

/* Icono a la izquierda, tamaño fijo para alineación */
.eval-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: var(--bs-light, #f8f9fa);
  color: var(--bs-secondary-color, #6c757d);
  font-size: 1.1rem;
}

/* Truncado de una línea para comentarios */
.truncate-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Ajustes de badges “claras” con borde */
.badge.bg-light.border {
  border: 1px solid var(--bs-border-color, #dee2e6) !important;
}

/* Card body compacto en filas */
.eval-row .card-body {
  padding-top: .85rem !important;
  padding-bottom: .85rem !important;
}

</style>
