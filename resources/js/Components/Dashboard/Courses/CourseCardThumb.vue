<!-- resources/js/Components/Dashboard/Courses/CourseCardThumb.vue -->
<template>
  <div class="card shadow-sm h-100 border-0 course-card">
    <!-- Cover -->
    <div class="position-relative">
      <img
        :src="image"
        :alt="alt"
        class="card-img-top img-fluid rounded-top"
        style="object-fit: cover; height: 180px;"
        @error="onImgError"
      />

      <!-- Slot de badge (opcional) -->
      <slot name="badge">
        <span v-if="level" class="badge bg-primary position-absolute top-0 start-0 m-2">
          {{ level }}
        </span>
      </slot>
    </div>

    <!-- Body -->
    <div class="card-body d-flex flex-column">
      <!-- Title -->
      <h5 class="fw-normal text-dark mb-1 text-truncate" :title="title">{{ title }}</h5>

      <!-- Meta -->
      <div class="text-muted small mb-2 d-flex flex-wrap gap-2">
        <span v-if="teacherName">
          <i class="bi bi-person-badge me-1"></i>{{ teacherName }}
        </span>
        <span v-if="lessonsCount">
          <i class="bi bi-collection-play me-1"></i>{{ lessonsCount }} lecciones
        </span>
        <span v-else-if="videosCount">
          <i class="bi bi-play-circle me-1"></i>{{ videosCount }} {{ videosCount === 1 ? 'video' : 'videos' }}
        </span>
        <span v-if="durationTotal">
          <i class="bi bi-clock me-1"></i>{{ durationTotal }}
        </span>
      </div>

      <!-- Description -->
      <p v-if="description_short || description" class="text-muted small mb-3 clamp-2">
        {{ description_short || description }}
      </p>

      <!-- Dates / Price -->
      <ul class="list-unstyled small text-muted mb-3">
        <li v-if="date_start">
          <i class="bi bi-calendar-event me-1"></i>
          Inicio: {{ formatDate(date_start) }}
        </li>
        <li v-if="date_end">
          <i class="bi bi-calendar-check me-1"></i>
          Fin: {{ formatDate(date_end) }}
        </li>
        <li v-if="price">
          <i class="bi bi-cash-coin me-1"></i>
          {{ price }} {{ currency }}
        </li>
      </ul>

      <!-- Progress -->
      <div v-if="hasProgress" class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1 small">
          <span>Progreso</span>
          <strong>{{ progressPct }}%</strong>
        </div>
        <div
          class="progress"
          role="progressbar"
          :aria-valuenow="progressPct"
          aria-valuemin="0"
          aria-valuemax="100"
        >
          <div class="progress-bar" :style="{ width: progressPct + '%' }"></div>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-auto d-flex flex-wrap gap-2 justify-content-end">
        <slot name="actions">
          <Link v-if="detailsUrl" :href="detailsUrl" class="btn btn-sm btn-outline-primary">
            Empezar Curso <i class="bi bi-chevron-double-right ms-1"></i>
          </Link>
        </slot>
      </div>
    </div>

    <!-- Footer -->
    <div class="card-footer bg-white border-0 pt-0" v-if="subscriptionDate">
      <div class="small text-muted d-flex align-items-center gap-2">
        <i class="bi bi-calendar2-check"></i>
        Inscrito el {{ formatDate(subscriptionDate) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  /** Visual */
  image: String,
  alt: { type: String, default: 'Portada del curso' },
  level: String,

  /** Content */
  title: { type: String, required: true },
  description: String,
  description_short: String,
  teacherName: String,
  lessonsCount: Number,
  videosCount: Number,
  durationTotal: String,

  /** Dates & price */
  date_start: String,
  date_end: String,
  price: [String, Number],
  currency: { type: String, default: 'MXN' },
  subscriptionDate: String,

  /** Progress: 0–1 o 0–100 */
  progress: [Number, String],

  /** Default action */
  detailsUrl: String
})

const hasProgress = computed(() => {
  const n = Number(props.progress ?? NaN)
  return !Number.isNaN(n) && n >= 0
})

const progressPct = computed(() => {
  if (!hasProgress.value) return 0
  const n = Number(props.progress)
  const pct = n <= 1 ? n * 100 : n
  return Math.round(Math.min(Math.max(pct, 0), 100))
})

function formatDate(iso) {
  try {
    const d = new Date(iso)
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
  } catch {
    return iso
  }
}

function onImgError(e) {
  e.target.src = 'https://placehold.co/640x360?text=Course+Cover'
}
</script>

<style scoped>
.course-card { border-radius: .75rem; }
.course-card:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.08); transform: translateY(-2px); transition: .15s; }
.clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
