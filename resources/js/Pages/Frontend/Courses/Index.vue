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
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title">
                   Cursos en la Academia 
                </h5>
              </div>
              <div class="card-body">
                <!-- Empty -->
                <div v-if="!courses || courses.length === 0" class="alert alert-info">
                  No estás inscrito en ningún curso por el momento.
                </div>

                <!-- Grid -->
                <div v-else class="row g-4">
                  <div v-for="course in courses" :key="course.id" class="col-12 col-lg-6">
                      <CourseCardIndex :course="course" />
                  </div>
                </div>
              </div>
            </div>
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
import CourseCardIndex from '@/Components/Dashboard/Courses/CourseCardIndex.vue'

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
