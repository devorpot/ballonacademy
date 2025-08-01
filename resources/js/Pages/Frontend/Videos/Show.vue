<template>
  <Head :title="video.title" />

  <StudentLayout>
   

    <!-- Encabezado del curso -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h1 class="h5 mb-1">{{ course.title }}</h1>
            <p class="mb-0 text-muted small">
              Profesor: {{ video.teacher?.name || 'Sin asignar' }}
            </p>
          </div>
        </div>
      </div>
    </section>



 
 
    <!-- Reproductor de video -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-md-8 mx-auto">
            <aside id="video-player" class="mb-3">
              <h3 class="text-uppercase h5 mb-3"> {{ video.title}}</h3>
              <VideoCardPlayer
                :video="video"
                :course-id="course.id"
                :source-url="`/storage/${video.video_path}`"
                @next="goToNextVideo"
                 :last-video-id="lastVideoId"
              />

            </aside>
          </div>
          <div class="col-12 col-md-4">
             <h3 class="text-uppercase h5 mb-3">Lista de videos</h3>
            <aside id="video-list" class="mb-3">
             
              <hr />
             <VideoCardMini
              v-for="v in videos"
              :key="v.id"
              :video="v"
              :url="v.is_accessible ? route('dashboard.videos.video.show', { course: course.id, video: v.id }) : null"
              :is-accessible="v.is_accessible"
              :is-playing="v.id === video.id"
              class="mb-2"
            />

            </aside>
            
          </div>
        </div>
      </div>
    </section>


    

    <!-- Videos anterior y siguiente 
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-md-6">
            <VideoCardMini
              v-if="previousVideo"
              :video="previousVideo"
              :url="route('dashboard.videos.video.show', {
                course: course.id,
                video: previousVideo.id
              })"
              :is-accessible="true"
            />
          </div>
          <div class="col-12 col-md-6">
            <VideoCardMini
              v-if="nextVideo"
              :video="nextVideo"
              :url="route('dashboard.videos.video.show', {
                course: course.id,
                video: nextVideo.id
              })"
              :is-accessible="nextVideoAccessible"
            />
          </div>
        </div>
      </div>
    </section>-->
  </StudentLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue'
import VideoCardPlayer from '@/Components/Dashboard/Video/VideoCardPlayer.vue'
import VideoCardMini from '@/Components/Dashboard/Video/VideoCardMini.vue'

const {
  course,
  video,
  previousVideo,
  nextVideo,
  nextVideoAccessible,
  videos
} = defineProps({
  course: Object,
  video: Object,
  previousVideo: Object,
  nextVideo: Object,
  nextVideoAccessible: Boolean,
  videos: Array
})

const lastVideoId = computed(() => {
  return videos.length ? videos[videos.length - 1].id : null
})

function goToNextVideo() {
  if (nextVideo) {
    router.visit(route('dashboard.videos.video.show', {
      course: course.id,
      video: nextVideo.id
    }))


  }
 
 
 
}

</script>
<style scoped>
 html, body {
  height: 100%;
   overflow-y:hidden!important;
}

.layout-content ,
.layout-wrapper,
.layout-body  {
 
  overflow-y:hidden!important;
}

 
 
#video-player {
  height: auto;
 
  overflow: hidden;
}

#video-list {
  height: 100%;
  max-height: 60vh;
  overflow-y: auto;
  padding-right: 6px;
}

 
</style>
