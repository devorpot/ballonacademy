<!-- resources/js/Components/Dashboard/Evaluation/CardsVideoEvaluations.vue -->
<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  videoEvaluations: {
    type: Array,
    required: true,
    default: () => []
  },
  routeShowSubmission: {
    type: String,
    required: true
  },
  routeStartEvaluation: {
    type: String,
    required: true
  }
})

const items = computed(() => {
  return (props.videoEvaluations || []).map(e => {
    const id = e.id
    const courseId = e.course_id ?? e.course?.id ?? null

    // Del objeto: submitted_by_me o my_last_submission
    const hasSubmission =
      Boolean(e?.submitted_by_me) ||
      (e?.submitted_by_me_count ?? 0) > 0 ||
      Boolean(e?.my_last_submission?.id)

    // Siempre usamos la misma ruta de inicio
    const url = (courseId && id)
      ? route(props.routeStartEvaluation, { course: courseId, evaluation: id })
      : null

    return {
      ...e,
      hasSubmission,
      url
    }
  })
})
</script>

<template>
  <div class="card border-0 shadow h-100">
    <div class="card-header">
      <h6 class="mb-0">Evaluaciones del Video</h6>
    </div>

    <div v-if="items.length" class="list-group list-group-flush">
      <div
        v-for="ev in items"
        :key="ev.id"
        class="list-group-item"
      >
        <div class="d-flex align-items-start gap-3">
          <!-- Icono -->
          <div class="flex-shrink-0">
            <div
              class="rounded bg-light d-inline-flex align-items-center justify-content-center"
              style="width:44px;height:44px;"
            >
              <i class="bi" :class="ev.eva_type_name === 'VIDEO' ? 'bi-camera-video' : 'bi-ui-checks'"></i>
            </div>
          </div>

          <!-- Contenido -->
          <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <h6 class="mb-0 me-2 text-truncate">
                {{ ev.title || 'Evaluación' }}
              </h6>
            </div>

            <!-- Comentarios -->
            <div class="text-muted small mt-1">
              {{ ev.eva_comments }}
            </div>

            <!-- Acción principal (siempre mismo link) -->
            <div class="mt-3 d-flex">
              <Link
                v-if="ev.url"
                :href="ev.url"
                class="btn btn-sm"
                :class="ev.hasSubmission ? 'btn-outline-primary' : 'btn-primary'"
              >
                <i :class="ev.hasSubmission ? 'bi bi-file-check' : 'bi bi-play-circle'"></i>
                <span class="ms-1">
                  {{ ev.hasSubmission ? 'Ver resultado' : 'Realizar Evaluación' }}
                </span>
              </Link>
              <button
                v-else
                class="btn btn-sm btn-secondary"
                disabled
              >
                No disponible
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado vacío -->
    <div v-else class="card-body">
      <div class="alert alert-light border text-muted mb-0">
        No hay evaluaciones para este video.
      </div>
    </div>
  </div>
</template>

<style scoped>
.list-group-item { padding-top: 0.9rem; padding-bottom: 0.9rem; }
.card-body .bi { font-size: 1rem; }
</style>
