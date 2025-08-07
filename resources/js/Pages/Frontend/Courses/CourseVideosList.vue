<template>
  <StudentLayout>
     <Breadcrumbs username="estudiante" :breadcrumbs="[
      { label: 'Dashboard', route: 'dashboard.index' },
      { label: ' Cursos', route: 'dashboard.index' },
       { label: course.title, route: '' },
    ]" />

    <!-- Card con información del curso -->
    <section class="section-panel py-3" v-if="course">
      <div class="container-fluid">
       <div class="row">
         <div class="col-12 col-md-12">
           <CourseCard :course="course" :videos="videos" />
         </div>
          
       </div>
       <div class="row"  v-if="hasCourseEnded">
         <div class="col-12 col-md-6">
               <div class="card mb-3" >
                  <h6 class="card-header">Haz finalizado este curso</h6>
                 <div class="card-body">
                  
                    <p>Envia tu evuación para obtener tu certificado.</p>
                  <Link :href="route('dashboard.courses.evaluations.create', { course: course.id })" class="btn btn-primary btn-sm">
                    Enviar Evaluación
                  </Link>
                 </div>
              </div>
             
          </div>
          <div class="col-12 col-md-6">
              <div class="card" >
                  <h6 class="card-header">Evaluaciones</h6>
                   <div class="card-body">
                    <p>Verifica tus evaluaciones</p>
                     
                    <Link :href="route('dashboard.courses.evaluations.index', { course: course.id })" class="btn btn-primary btn-sm">
                      Ver Evaluaciones
                    </Link>
                   </div>
             </div>
          </div>
       </div>
      </div>
    </section>
 
 <SectionHeader title="Videos" />
    <!-- Lista de videos -->
    <section class="section-panel" v-if="course && videos.length">
      <div class="container-fluid">
        <div class="row">
         <div
                class="col-12 col-sm-12 col-md-6 col-lg-6"
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
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';

const props = defineProps({
  course: Object,
  videos: Array,
   completedVideoIds: Array,
   hasCourseEnded:Boolean
})
</script>
