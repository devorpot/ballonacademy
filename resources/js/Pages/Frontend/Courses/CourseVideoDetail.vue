<template>
  <Head :title="video.title" />

  <StudentLayout>
    <!-- Encabezado con logo y profesor -->
     
    <section class="bg-light border-bottom py-3 mb-4">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h1 class="h5 mb-1">{{ course.title }}</h1>
            <p class="mb-0 text-muted small">Profesor: {{ course.teacher?.name || 'Sin asignar' }}</p>
          </div>
           
        </div>
      </div>
    </section>
    <!-- Video -->
    <section class="my-4">
      <div class="container-fluid">
         <div class="row">
           <div class="col-12 col-md-10 mx-auto">
                <div class="card">
                <div @contextmenu.prevent>
                   <video-player
                     :src="'/storage/' + video.video_path"
                      :poster="'/storage/' + video.image_cover"
                      controls
                      :loop="true"
                      :volume="0.6"
                      class="video_player"
                       controlsList="nodownload"
                        disablePictureInPicture
                        playsinline
   
                    />

                  </div>

                    <div class="card-body">
                            <h2 class="h5 fw-bold">{{ video.title }}</h2>
                            <p class="text-muted">{{ video.description }}</p>
                    </div>
               </div>
           </div>
         </div>
 
      </div>
    </section>

    <!-- Descripción -->
 
  </StudentLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';  

import StudentLayout from '@/Layouts/StudentLayout.vue';


import { VideoPlayer } from '@videojs-player/vue'
import 'video.js/dist/video-js.css'

defineProps({
  course: Object,
  video: Object,
});
</script>

<style scoped>
  .video_player{
    width:100%;
    height: 80vh;

  }
.ratio {
  max-width: 100%;
}
</style>
