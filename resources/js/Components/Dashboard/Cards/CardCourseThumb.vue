<template>
  <div class="card shadow-sm h-100">
    <div class="card-body d-flex flex-column justify-content-between">
      
      <!-- Título y número de videos -->
      <div class="mb-2">
        <h5 class="fw-bold text-dark mb-1">{{ title }}</h5>
        <p class="text-muted small mb-1">{{ description || 'Sin descripción.' }}</p>
        <small class="text-muted">{{ videosCount }} {{ videosCount === 1 ? 'video' : 'videos' }}</small>
      </div>

      <!-- Imagen -->
      <div class="text-center my-3">
        <img :src="image" alt="Miniatura del curso" class="img-fluid rounded" style="max-height: 140px; object-fit: contain;" />
      </div>

      <!-- Botón Detalles -->
      <div class="text-end mt-auto">
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
  description: String,
  videosCount: Number,
  image: String,
  detailsUrl: String
});
</script>
