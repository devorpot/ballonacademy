<!-- resources/js/Components/Dashboard/Evaluation/CardsVideoEvaluations.vue -->
<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  videoEvaluations: { type: Array, required: true, default: () => [] },
  routeShowSubmission: { type: String, required: true },
  routeStartEvaluation: { type: String, required: true }
})

/* Toggle del cuerpo de la card */
const isOpen = ref(true)

const items = computed(() => {
  return (props.videoEvaluations || []).map(e => {
    const id = e.id
    const courseId = e.course_id ?? e.course?.id ?? null

    const hasSubmission =
      Boolean(e?.submitted_by_me) ||
      (e?.submitted_by_me_count ?? 0) > 0 ||
      Boolean(e?.my_last_submission?.id)

    const url = (courseId && id)
      ? route(props.routeStartEvaluation, { course: courseId, evaluation: id })
      : null

    return { ...e, hasSubmission, url }
  })
})
</script>

<template>
   <div class="card border-0 shadow my-3">
    <!-- Header con botón toggle alineado al título -->
   <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
      <h6 class="fw-bold mb-0">
        <i class="bi bi-journal-bookmark"></i>  Evaluaciones del Video 
      </h6>

      <button
        type="button"
        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center"
        @click="isOpen = !isOpen"
        :aria-expanded="isOpen"
        aria-controls="card-eva-body"
        title="Mostrar/Ocultar"
      >
        <i v-if="isOpen" class="bi bi-chevron-down"></i>
           <i v-else class="bi bi-dash-lg"></i>
      </button>
    </div>

    <!-- Cuerpo colapsable -->
    <Transition name="collapse-fade">
      <div v-show="isOpen" id="card-eva-body" class="card-body p-0">
        <div v-if="items.length" class="list-group list-group-flush">
          <div
            v-for="ev in items"
            :key="ev.id"
            class="list-group-item w-100"
          >
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">

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
              <div class="flex-grow-1" style="min-width:150px">
                <h6 class="mb-0">{{ ev.title || 'Evaluación' }}</h6>
                <div class="text-muted small mt-1 text-truncate">
                  {{ ev.eva_comments }}
                </div>
              </div>

              <!-- Acción principal: centro en móvil, derecha en md+ -->
              <div class="w-100 text-center text-md-end ms-md-auto">
                <Link
                  v-if="ev.url"
                  :href="ev.url"
                  class="btn btn-sm rounded-pill"
                  :class="ev.hasSubmission ? 'btn-outline-primary' : 'btn-primary'"
                >
                  <i :class="ev.hasSubmission ? 'bi bi-file-check' : 'bi bi-play-circle'"></i>
                  <span class="ms-1">
                    {{ ev.hasSubmission ? 'Ver resultado' : 'Realizar Evaluación' }}
                  </span>
                </Link>
                <button v-else class="btn btn-sm btn-secondary" disabled>
                  No disponible
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- Estado vacío -->
        <div v-else class="p-3">
          <div class="alert alert-light border text-muted mb-0">
            No hay evaluaciones para este video.
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.list-group-item { padding-top: .9rem; padding-bottom: .9rem; }

/* Animación del colapso */
.collapse-fade-enter-active,
.collapse-fade-leave-active {   }
.collapse-fade-enter-from,
.collapse-fade-leave-to { opacity: 0; }
</style>

