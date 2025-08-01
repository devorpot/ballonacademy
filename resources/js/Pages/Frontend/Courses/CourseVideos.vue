<template>
  <StudentLayout>
     <SectionHeader title="Videos " />
    <section v-if="course && videos.length" class="container py-4">
      <h1 class="h4 mb-4">Videos del curso: {{ course.title }}</h1>

      <div class="container">
        <div class="row">
          <div
            class="col-12"
            v-for="video in videos"
            :key="video.id"
          >
         <VideoListItem
          :video="video"
          :courseId="course.id"
          :isAccessible="video.is_accessible"
        />


          </div>
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'
import VideoListItem from '@/Components/Dashboard/Video/VideoListItem.vue'
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue'
const props = defineProps({
  course: Object,
  videos: Array
})
</script>
