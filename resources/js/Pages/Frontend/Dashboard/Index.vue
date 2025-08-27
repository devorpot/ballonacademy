<template>
  <StudentLayout>
    Dashboard/Index
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[{ label: 'Dashboard', route: '' }]"
    />



    <!-- MIS CURSOS -->
    <section class="section-panel py-3" >
      <div class="container-fluid">
        <div class="row my-3">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Mis Cursos</h2>
            <a :href="route('dashboard.courses.index')" class="btn btn-outline-primary btn-sm">
              Ver todos
            </a>
          </div>
        </div>

        <div class="row" v-if="courses && courses.length">
          <div
            class="col-12 col-md-6 mb-3"
            v-for="course in courses"
            :key="course.id"
          >
            <div class="card h-100 shadow-sm">
              <img
                class="card-img-top course-cover"
                :src="course.image_cover ? `/storage/${course.image_cover}` : '/images/default-cover.jpg'"
                :alt="course.title" style="min-height:300px;"
              />
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-truncate mb-2">{{ course.title }}</h5>
                <p class="card-subtitle text-muted small mb-3">
                  {{ course.videos_count }} video(s)
                </p>
                <div class="mt-auto d-flex gap-2">
                  <Link :href="route('dashboard.courses.show', course.id)" class="btn btn-primary btn-sm w-100">
                    Ver curso
                  </Link>
                </div>
              </div>
            </div>
          </div>          
        </div>
        <div class="row"  v-else>
          <div class="col-12">
             <p class="text-muted mb-0">Aún no estás inscrito en ningún curso.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- MENSAJE SI NO HAY CURSOS -->
 

    <!-- ÚLTIMOS VIDEOS TERMINADOS -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row my-3">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Últimos videos terminados</h2>
          </div>
        </div>

        <!-- Lista de videos -->
        <div class="row my-3" v-if="videos && videos.length">
          <div class="col-12 col-sm-6 col-lg-3 mb-3" v-for="v in videos" :key="v.id">
            <div class="card h-100 shadow-sm">

             
              <img
                class="card-img-top video-cover"
                :src="v.image_cover ? `/storage/${v.image_cover}` : '/images/default-cover.jpg'"
                :alt="v.title"
              />
              <div class="card-body d-flex flex-column">
                <h5 class="card-title two-lines mb-2" :title="v.title">{{ v.title }}</h5>
                <p class="card-subtitle text-muted small mb-2">
                  Curso: {{ v.course?.title }}
                </p>
                <div class="d-flex justify-content-between small text-muted mb-2">
                  <span><i class="bi bi-clock me-1"></i>{{ v.duration }}</span>
                  <span><i class="bi bi-hdd me-1"></i>{{ v.size }}</span>
                </div>
                <p class="text-muted small mb-3">
                  Última vez visto: {{ formatDate(v.last_seen) }}
                </p>
                <div class="mt-auto">
                  <Link class="btn btn-primary w-100" :href="videoUrl(v)">
                    <i class="bi bi-play-circle me-1"></i> Ver de nuevo
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado vacío -->
        <div class="row my-3" v-else>
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-body text-center text-muted py-5">
                Aún no has terminado de ver ningún video recientemente.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- BLOG DE GLOBOFLEXIA -->
 
  </StudentLayout>
</template>

<script setup>
  import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const props = defineProps({
  courses: { type: Array, default: () => [] },
  videos:  { type: Array, default: () => [] },
})

function videoUrl(v) {
  try {
    return route('dashboard.courses.videos.show', { course: v.course?.id, video: v.id })
  } catch (e) {
    return `/frontend/videos/${v.id}`
  }
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleString('es-MX', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}
</script>

<style scoped>
.course-cover,
.video-cover,
.blog-cover {
  height: 180px;
  object-fit: cover;
}

.two-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
