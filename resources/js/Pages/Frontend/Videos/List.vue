<template>
    <Head title="Videos del Curso" />
  <StudentLayout> 
   

    <!-- Card con información del curso -->
    <section class="section-panel py-3" v-if="course">
      <div class="container-fluid">
       
          <div class="row g-0">
            <!-- Imagen -->
            <div class="col-md-4">
              <img
                :src="course.image_cover ? `/storage/${course.image_cover}` : '/images/course-placeholder.jpg'"
                alt="Imagen del curso"
                class="img-fluid h-100 w-100 object-fit-cover"
                style="max-height: 220px"
              />
            </div>
            <!-- Contenido -->
            <div class="col-md-8">
              <div class="card-body">
                <h5 class="card-title mb-2">{{ course.title }}</h5>
                
                <!--<p class="text-muted mb-1">
                  <strong>Profesor:</strong> {{ course.teacher?.name || 'Sin asignar' }}
                </p>-->
                <p class="card-text small mb-2">{{ course.description_short }}</p>
                <p class="card-text">
                  <span class="badge bg-primary">
                    {{ videos.length }} Videos disponibles
                  </span>
                </p>
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
import StudentLayout from '@/Layouts/StudentLayout.vue'
import VideoListItem from '@/Components/Dashboard/Video/VideoListItem.vue'
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue'

const props = defineProps({
  course: Object,
  videos: Array,
   completedVideoIds: Array
})
</script>
