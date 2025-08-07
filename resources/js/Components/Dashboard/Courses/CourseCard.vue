<script setup>
const props = defineProps({
  course: {
    type: Object,
    required: true
  },
  videos: {
    type: Array,
    default: () => []
  } 
})
</script>

<template>
  <div class="card mb-4 shadow-sm border-0">
    <div class="row g-0 align-items-center">
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
          <h3 class="card-title mb-2">{{ course.title }}</h3>
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
</template>
