<template>
  <StudentLayout>
     <Breadcrumbs username="estudiante" :breadcrumbs="[
      { label: 'Dashboard', route: 'dashboard.index' },
      { label: ' Cursos', route: 'dashboard.courses.index' },
       { label: course.title, route: '' },
    ]" />

<section class="section-panel">
  <div class="container-fluid">
    <div
      class="row justify-content-center align-items-center py-4"
      v-if="hasCourseEnded"
      style="background: linear-gradient(90deg, #e0e7ff 60%, #e0fce8 100%); border-radius: 2rem; box-shadow: 0 6px 30px 0 #8bb8f340;"
    >
      <!-- Icono de logro -->
      <div class="col-12 col-md-auto text-center mb-3 mb-md-0">
        <i class="bi bi-patch-check-fill" style="font-size:3.2rem; color:#ff9800; text-shadow: 0 2px 10px #ffd18099;"></i>
      </div>
      <!-- Mensaje y acciones -->
      <div class="col-12 col-md text-center text-md-start">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
          <div class="flex-fill">
            <div class="fs-3 fw-bold mb-1" style="color: #1565c0;">¡Curso finalizado!</div>
            <div class="mb-2" style="color:#444;">Ya puedes realizar la evaluación final para obtener tu certificado.</div>
          </div>
          <!-- Botón/acción solo en pantallas grandes -->
          <div class="d-none d-md-flex flex-column align-items-center ms-4">
            <Link
              :href="route('dashboard.courses.evaluations.index', { course: course.id })"
              class="btn btn-warning btn-lg fw-bold px-4 text-white d-flex align-items-center gap-2"
              style="box-shadow: 0 2px 8px #ffc10777;"
            >
              <i class="bi bi-pencil-square"></i>
              Realizar Evaluaciones
            </Link>
            <span class="small text-warning mt-2" style="letter-spacing:1px;">Acceso a evaluaciones</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- Card con información del curso -->
    <section class="section-panel py-3" v-if="course">
      <div class="container-fluid">
       <div class="row">
         <div class="col-12 col-md-12">
           <CourseCard :course="course" :videos="videos" />
         </div>
          
       </div>
    
      </div>
    </section>




 
 <SectionHeader title="Videos" />
    <!-- Lista de videos -->
    <section class="section-panel" v-if="course && videos.length">
      <div class="container-fluid">
        <div class="row">
         <div class="col-12 col-sm-12 col-md-6 col-lg-6"
                v-for="video in videos"
                :key="video.id"
              >
              <VideoListItem
                :video="video"
                :courseId="course.id"
                :isAccessible="video.is_accessible"
                :isEnded="is_ended"
              />
            </div>

        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
  import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import VideoListItem from '@/Components/Dashboard/Video/VideoListItem.vue'
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue'
import CourseCard from '@/Components/Dashboard/Courses/CourseCard.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';

const props = defineProps({
  course: Object,
  videos: Array,
   completedVideoIds: Array,
   hasCourseEnded:Boolean
})
</script>
