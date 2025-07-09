<template>
  <div class="card shadow-sm h-100">
    <div class="card-body d-flex flex-column justify-content-between">
      
      <!-- Título y número de videos -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-bold text-muted">{{ title }}</h6>
        <small class="text-muted">{{ videosCount }} videos</small>
      </div>

      <!-- Imagen -->
      <div class="text-center my-3">
        <img :src="image" alt="" class="img-fluid" style="max-height: 120px; object-fit: contain;" />
      </div>


      
      <!-- Botón Detalles -->
      <div class="text-end">
        <Link :href="detailsUrl" class="btn btn-sm btn-primary">
          DETALLES <i class="bi bi-chevron-double-right ms-1"></i>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: String,
  videosCount: Number,
  image: String,
  detailsUrl: String
});
</script>
