<!-- resources/js/Pages/Frontend/Courses/Index.vue -->
<template>
  <Head title="Mis Cursos" />
  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Mis Cursos', route: '' }
      ]"
    />

    <section class="section-panel py-3">
      <div class="container-fluid">
        <!-- Empty -->
        <div v-if="!courses || courses.length === 0" class="alert alert-info">
          No estás inscrito en ningún curso por el momento.
        </div>

        <!-- Grid -->
        <div v-else class="row g-4">
          <div v-for="course in courses" :key="course.id" class="col-12 col-lg-6">
            <article class="course-hero h-100 rounded-4 overflow-hidden bg-light border d-flex flex-column flex-lg-row">
              <!-- Media -->
              <div class="course-hero__media position-relative">
                <img
                  :src="coverUrl(course.image_cover)"
                  :alt="course.title"
                  class="w-100 h-100 object-cover"
                  loading="lazy"
                  @error="onImgError"
                />
                <img
                  v-if="course.logo"
                  :src="coverUrl(course.logo)"
                  alt="logo"
                  class="position-absolute top-0 start-0 m-2 bg-white p-1 rounded"
                  style="max-height:50px; max-width:80px;"
                />
                <span
                  v-if="course.level"
                  class="badge bg-primary position-absolute top-0 end-0 m-2"
                >
                  Nivel {{ course.level }}
                </span>
              </div>

              <!-- Content -->
              <div class="course-hero__content p-4 d-flex flex-column flex-grow-1">
                <h3 class="h5 fw-bold mb-2 text-body text-wrap">{{ course.title }}</h3>

                <p v-if="course.description_short" class="text-secondary mb-3 clamp-2">
                  {{ course.description_short }}
                </p>

                <ul class="list-unstyled small text-secondary mb-4">
                  <li v-if="course.date_start">
                    <i class="bi bi-calendar-event me-1"></i>
                    Inicio: {{ formatDate(course.date_start) }}
                  </li>
                  <li v-if="course.date_end">
                    <i class="bi bi-calendar-check me-1"></i>
                    Fin: {{ formatDate(course.date_end) }}
                  </li>
                </ul>

                <div class="mt-auto d-flex gap-2">
                  <Link
                    :href="lessonsUrl(course.id)"
                    class="btn btn-primary btn-sm"
                    :title="`Ver lecciones de ${course.title}`"
                  >
                    Ver Lecciones
                  </Link>

                  <Link
                    :href="showUrl(course.id)"
                    class="btn btn-outline-primary btn-sm"
                    :title="`Ver curso ${course.title}`"
                  >
                    Ver curso
                  </Link>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const props = defineProps({
  courses: { type: Array, default: () => [] }
})

function lessonsUrl(courseId) {
  return route('dashboard.courses.lessons.index', { course: courseId })
}
function showUrl(courseId) {
  return route('dashboard.courses.show', courseId)
}
function formatDate(iso) {
  try {
    const d = new Date(iso)
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return iso
  }
}
function onImgError(e) {
  e.target.src = 'https://placehold.co/600x400?text=Course'
}
function coverUrl(path) {
  if (!path) return 'https://placehold.co/600x400?text=Course'
  if (/^https?:\/\//i.test(path) || path.startsWith('/storage/')) return path
  return `/storage/${path.replace(/^\/+/, '')}`
}
</script>

<style scoped>
.course-hero {
  background: #fff; /* Card blanca */
  border: 1px solid #dee2e6; /* gris Bootstrap */
  border-radius: .75rem;
  box-shadow: 0 .25rem .5rem rgba(0,0,0,.05);
  transition: transform .15s ease, box-shadow .15s ease;
}
.course-hero:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
}

/* Media (imagen del curso) */
.course-hero__media {
  width: 100%;
  min-height: 200px;
  max-height: 260px;
  background: #f8f9fa; /* fallback gris claro */
}
@media (min-width: 992px) {
  .course-hero__media {
    width: 40%;
    min-height: 100%;
  }
}
.object-cover {
  object-fit: cover;
  width: 100%;
  height: 100%;
  display: block;
}

/* Contenido */
.course-hero__content {
  min-height: 200px;
  color: #212529; /* texto negro Bootstrap */
}

/* Clamp utilitario para limitar texto */
.clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
