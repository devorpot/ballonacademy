<template>
  <Head title="Mis Cursos" />

  <StudentLayout>
    <section class="section-panel py-3">
      <div class="container-fluid">
        <SectionHeader title="Cursos Inscritos" />

        <div v-if="courses.length === 0" class="alert alert-info">
          No estás inscrito en ningún curso por el momento.
        </div>

        <div v-else class="row">
          <div v-for="course in courses" :key="course.id" class="col-12 col-md-6 col-lg-4 mb-4">
              {{course.id}}
            <CardCourseThumb
              :title="course.title"
              :description="course.description_short"
              :image="course.image_cover"
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
import CardCourseThumb from '@/Components/Dashboard/Cards/CardCourseThumb.vue'
import { route } from 'ziggy-js'

defineProps({
  courses: Array,
})
</script>
