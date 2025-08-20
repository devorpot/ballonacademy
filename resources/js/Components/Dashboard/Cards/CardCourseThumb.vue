<template>
  <div class="card shadow-sm h-100 border-0">
    <!-- Imagen -->
    <div class="position-relative">
      <img
        :src="image"
        alt="Portada del curso"
        class="card-img-top img-fluid rounded-top"
        style="object-fit: cover; height: 180px;"
      />
      <span
        v-if="level"
        class="badge bg-primary position-absolute top-0 start-0 m-2"
      >
        {{ level }}
      </span>
    </div>

    <!-- Contenido -->
    <div class="card-body d-flex flex-column">
      <!-- Título -->
      <h5 class="fw-bold text-dark mb-2">{{ title }}</h5>
      <p class="text-muted small mb-2">{{ description_short || description || 'Sin descripción.' }}</p>

      <!-- Datos extras -->
      <ul class="list-unstyled small text-muted mb-3">
        <li v-if="videosCount">
          <i class="bi bi-play-circle me-1"></i>
          {{ videosCount }} {{ videosCount === 1 ? 'video' : 'videos' }}
        </li>
        <li v-if="date_start">
          <i class="bi bi-calendar-event me-1"></i>
          Inicio: {{ date_start }}
        </li>
        <li v-if="date_end">
          <i class="bi bi-calendar-check me-1"></i>
          Fin: {{ date_end }}
        </li>
        <li v-if="price">
          <i class="bi bi-cash-coin me-1"></i>
          {{ price }} {{ currency }}
        </li>
      </ul>

      <!-- Botón -->
      <div class="mt-auto text-end">
        <Link :href="detailsUrl" class="btn btn-sm btn-outline-primary">
          Empezar Curso <i class="bi bi-chevron-double-right ms-1"></i>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  title: String,
  description: String,
  description_short: String,
  level: String,
  date_start: String,
  date_end: String,
  price: [String, Number],
  currency: { type: String, default: 'MXN' },
  videosCount: Number,
  image: String,
  detailsUrl: String
})
</script>
