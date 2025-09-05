<!-- resources/js/Components/Dashboard/Video/EvalAdvanceModal.vue -->
<template>
  <teleport to="body">
    <div v-if="modelValue" class="modal-backdrop-custom" role="presentation">
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="evalAdvanceTitle">
        <div class="modal-header">
          <h5 id="evalAdvanceTitle" class="modal-title d-flex align-items-center gap-2">
            <i
              class="bi"
              :class="(isLastOfCourse || isLastOfLesson) ? 'bi-check-circle-fill text-success' : 'bi-play-circle-fill text-primary'"
              aria-hidden="true"
            />
            <span class="fw-semibold">
              {{
                isLastOfCourse
                  ? 'Curso completado'
                  : (isLastOfLesson ? 'Lección completada' : 'Video completado')
              }}
            </span>
          </h5>
          <button type="button" class="btn-close" aria-label="Cerrar" @click="close" />
        </div>

        <div class="modal-body">
          <!-- Regla prioritaria: evaluaciones del video -->
          <template v-if="isBlockedByEvals">
            <p class="mb-2">
              Este video tiene evaluaciones, por favor realízalas para poder avanzar.
            </p>
          </template>

          <!-- Ya envió sus evaluaciones (aunque no estén aprobadas) -->
          <template v-else-if="allEvalsSent">
            <p class="mb-2">
              Has visto este video y enviado tus evaluaciones. Puedes pasar al siguiente video si lo deseas.
            </p>
          </template>

          <!-- Sin evaluaciones -->
          <template v-else>
            <p class="mb-2">
              No hay evaluaciones pendientes para este video.
            </p>
          </template>
        </div>

        <div class="modal-footer gap-2">
          <button class="btn btn-outline-secondary" @click="close">Cerrar</button>

          <!-- Botón Siguiente solo si NO está bloqueado por evaluaciones -->
          <button
            v-if="!isBlockedByEvals && !isLastOfCourse && !isLastOfLesson"
            class="btn btn-success"
            @click="goNext"
          >
            Siguiente video
          </button>

          <!-- Acceso a listado de evaluaciones cuando aplica -->
          <Link
            v-if="(isLastOfCourse || isLastOfLesson) && hasEvaluations"
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
  /* Control externo del modal */
  modelValue: { type: Boolean, default: false },

  /* Datos para reglas */
  videoEvaluations: { type: Array, default: () => [] },
  courseId: { type: [Number, String], required: true },
  isLastOfCourse: { type: Boolean, default: false },
  isLastOfLesson: { type: Boolean, default: false },

  /* Opcional: si quieres que el botón "Siguiente" sea un <Link> directo */
  nextUrl: { type: String, default: null }
})

/* Helpers estado de envío */
function pickEvalStatus(ev) {
  return ev?.user_status ?? ev?.pivot?.status ?? ev?.evaluation_user?.status ?? ev?.status ?? null
}
function isSentStatus(s) {
  if (s == null) return false
  const v = String(s).trim().toUpperCase()
  return v === 'SEND' || v === 'SENT' || v === 'ENVIADO' || v === 'SUBMITTED' || s === 111
}

const hasEvaluations = computed(() => (props.videoEvaluations?.length || 0) > 0)

/* ¿TODAS enviadas? acepta flags o status */
const allEvalsSent = computed(() => {
  if (!hasEvaluations.value) return false
  return props.videoEvaluations.every(ev =>
    isSentStatus(pickEvalStatus(ev)) ||
    Boolean(ev?.submitted_by_me) ||
    (ev?.submitted_by_me_count ?? 0) > 0 ||
    Boolean(ev?.my_last_submission?.id)
  )
})

/* ¿Bloquea avance? hay evals y no todas están enviadas */
const isBlockedByEvals = computed(() => hasEvaluations.value && !allEvalsSent.value)

/* URL índice de evaluaciones del curso, si quieres mostrar ese acceso rápido */
const evaluationsIndexUrl = computed(() =>
  route('dashboard.courses.evaluations.index', { course: props.courseId })
)

/* Acciones */
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
.modal-header,
.modal-footer { padding: .875rem 1rem; border: 0; }
.modal-body { padding: .5rem 1rem 1rem; }
.btn-close { outline: none; }
</style>
