<template>
  <Head title="Mis Cursos" />
<!--/frontend/courses-->
  <StudentLayout>
   <Breadcrumbs username="estudiante" :breadcrumbs="[
      { label: 'Dashboard', route: 'dashboard.index' },
      { label: 'Mis Cursos', route: '' }
    ]" />

    <section class="section-panel py-3">
      <div class="container-fluid">
       

        <div v-if="courses.length === 0" class="alert alert-info">
          No estás inscrito en ningún curso por el momento.
        </div>

        <div v-else class="row">
          <div v-for="course in courses" :key="course.id" class="col-12 col-md-6 col-lg-4 mb-4">
               
               <CourseCardThumb
                :title="course.title"
                :videosCount="course.videos_count"
                :image="course.image_cover ? `/storage/${course.image_cover}` : '/images/default-cover.jpg'"
                :detailsUrl="route('dashboard.courses.show', course.id)"
              />
          </div>
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue'
import CourseCardThumb from '@/Components/Dashboard/Courses/CourseCardThumb.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';
import { route } from 'ziggy-js'

defineProps({
  courses: Array,
})
</script>
