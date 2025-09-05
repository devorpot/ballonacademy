<template>
  <teleport to="body">
    <div v-if="modelValue" class="modal-backdrop-custom" role="presentation">
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="lessonEvalAdvanceTitle">
        <div class="modal-header">
          <h5 id="lessonEvalAdvanceTitle" class="modal-title d-flex align-items-center gap-2">
            <i
              class="bi"
              :class="showCourseCompleted || isLastOfLesson ? 'bi-check-circle-fill text-success' : 'bi-play-circle-fill text-primary'"
              aria-hidden="true"
            />
            <span class="fw-semibold">
              {{ showCourseCompleted ? 'Curso completado' : (isLastOfLesson ? 'Lección completada' : 'Video completado') }}
            </span>
          </h5>
          <button type="button" class="btn-close" aria-label="Cerrar" @click="close" />
        </div>

        <div class="modal-body">
          <!-- Prioridad: si el curso está concluido, mostramos este mensaje y omitimos el resto -->
          <template v-if="isCourseCompleted">
            <p class="mb-2">
              Has concluido el curso. En breve revisaremos tus evaluaciones.
              Te avisaremos con el resultado para que puedas solicitar tu certificado.
            </p>
          </template>

          <template v-else-if="isBlockedByEvals">
            <p class="mb-2">Este video tiene evaluaciones, por favor realízalas para poder avanzar.</p>
          </template>

          <template v-else-if="allEvalsSent">
            <p class="mb-2">Has visto este video y enviado tus evaluaciones. Puedes pasar al siguiente video si lo deseas.</p>
          </template>

          <template v-else>
            <p class="mb-2">No hay evaluaciones pendientes para este video.</p>
          </template>
        </div>

        <div class="modal-footer gap-2">
          <button class="btn btn-outline-secondary" @click="close">Cerrar</button>

          <!-- Botón Siguiente solo si no hay bloqueo, no es final de curso/lección y no está marcado como curso concluido -->
          <button
            v-if="!isCourseCompleted && !isBlockedByEvals && !isLastOfCourse && !isLastOfLesson"
            class="btn btn-success"
            @click="goNext"
          >
            Siguiente video
          </button>

          <!-- Link a Evaluaciones si estamos al fin de curso/lección o curso concluido y existen evaluaciones -->
          <Link
            v-if="(isCourseCompleted || isLastOfCourse || isLastOfLesson) && hasEvaluations"
            :href="evaluationsIndexUrl"
            class="btn btn-primary"
          >
            Ver Evaluaciones
          </Link>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const emit = defineEmits(['update:modelValue', 'next'])
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  videoEvaluations: { type: Array, default: () => [] },
  courseId: { type: [Number, String], required: true },
  isLastOfCourse: { type: Boolean, default: false },
  isLastOfLesson: { type: Boolean, default: false },
  nextUrl: { type: String, default: null },
  // NUEVO: bandera para indicar que el usuario concluyó el curso
  isCourseCompleted: { type: Boolean, default: false }
})

const showCourseCompleted = computed(() => props.isCourseCompleted || props.isLastOfCourse)

const hasEvaluations = computed(() => (props.videoEvaluations?.length || 0) > 0)

function isSubmittedByMe(ev) {
  if (ev?.submitted_by_me) return true
  if ((ev?.submitted_by_me_count ?? 0) > 0) return true
  if (ev?.my_last_submission?.id) return true
  const eu = ev?.evaluation_user ?? ev?.pivot
  if (eu?.user_id) return true
  return false
}

const allEvalsSent = computed(() => {
  if (!hasEvaluations.value) return true
  return props.videoEvaluations.every(isSubmittedByMe)
})

const isBlockedByEvals = computed(() => hasEvaluations.value && !allEvalsSent.value)

const evaluationsIndexUrl = computed(() =>
  route('dashboard.courses.evaluations.index', { course: props.courseId })
)

function close() {
  emit('update:modelValue', false)
}
function goNext() {
  if (props.nextUrl) {
    window.location.href = props.nextUrl
    return
  }
  emit('next')
}
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(33, 37, 41, 0.6);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 1080;
}
.modal-card {
  width: 100%;
  max-width: 560px;
  background: var(--bs-body-bg);
  color: var(--bs-body-color);
  border-radius: .75rem;
  box-shadow: 0 20px 50px rgba(0,0,0,.2);
  overflow: hidden;
}
.modal-header, .modal-footer { padding: .875rem 1rem; border: 0; }
.modal-body { padding: .5rem 1rem 1rem; }
.btn-close { outline: none; }
</style>
