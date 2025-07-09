<template>
  <StudentLayout>
    <section class="section-panel">
        <div class="container-fluid ">
          <SectionHeader :title="course.title" />

          <div class="row">
            <div class="col-12 col-md-12">
              <div class="row">
                <div
                  v-for="video in videos"
                  :key="video.id"
                  class="col-12 col-md-6 mb-4"
                >
                  <CardVideoThumbList
                    :video="video"
                    :course-id="course.id"
                    :is-accessible="true"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue'
import CardVideoThumbList from '@/Components/Dashboard/Cards/CardVideoThumbList.vue'

defineProps({
  course: Object,
  videos: Array,
})
</script>
