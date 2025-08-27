<script setup>
import { computed } from 'vue'

/**
 * Props:
 * - lastEvaluationUser: { status: number, ... } | null
 * - userScore: number | null
 * - lastCreatedAt: string (fecha formateada)
 */
const props = defineProps({
  lastEvaluationUser: { type: Object, default: null },
  userScore: { type: [Number, null], default: null },
  lastCreatedAt: { type: String, default: '-' },
})

const status = computed(() => props.lastEvaluationUser?.status ?? null)
const hasData = computed(() => props.lastEvaluationUser != null)

const iconClass = computed(() => {
  switch (status.value) {
    case 999: return 'bi bi-award-fill text-success'
    case 111: return 'bi bi-send-check-fill text-primary'
    case 222: return 'bi bi-hourglass-split text-warning'
    case 333: return 'bi bi-x-octagon-fill text-danger'
    default:  return 'bi bi-question-circle text-muted'
  }
})

const statusText = computed(() => {
  switch (status.value) {
    case 111: return 'Enviado'
    case 222: return 'En revisión'
    case 333: return 'No aprobado'
    case 999: return 'Aprobado'
    default:  return 'Desconocido'
  }
})

const statusClass = computed(() => {
  switch (status.value) {
    case 999: return 'text-success fw-bold'
    case 111: return 'text-primary fw-bold'
    case 222: return 'text-warning fw-bold'
    case 333: return 'text-danger fw-bold'
    default:  return 'text-muted'
  }
})

const isApproved = computed(() => status.value === 999 && typeof props.userScore === 'number')
const isRejected = computed(() => status.value === 333 && typeof props.userScore === 'number')
</script>

<template>

	<section class="section-panel py-3">
 <div class="container-fluid">
 	 <div class="pt-4 pb-2 text-center">
    <!-- Ícono según estado -->
    <span v-if="hasData">
      <i :class="iconClass" class="status-icon"></i>
    </span>

    <!-- Estado -->
    <div class="fs-5 mb-1 mt-2" v-if="hasData">
      Estado:
      <span :class="statusClass">{{ statusText }}</span>
    </div>

    <div class="small text-muted mb-3" v-if="hasData">
      Fecha de envío: {{ lastCreatedAt }}
    </div>

    <!-- Puntaje si es APROBADO -->
    <div v-if="isApproved" class="mb-2">
      <div class="d-flex flex-column align-items-center justify-content-center my-3">
        <div class="display-2 fw-bold text-success" style="line-height:1;">
          {{ userScore }}
        </div>
        <div class="fs-5 fw-bold text-success mt-1">Has aprobado</div>
        <div class="text-muted" style="font-size:1.1rem;">Buen trabajo</div>
      </div>
    </div>

    <!-- Puntaje si es NO APROBADO -->
    <div v-if="isRejected" class="mb-2">
      <div class="d-flex flex-column align-items-center justify-content-center my-3">
        <div class="display-4 fw-bold text-danger" style="line-height:1;">
          {{ userScore }}
        </div>
        <div class="fs-5 fw-bold text-danger mt-1">No aprobado</div>
        <div class="text-muted mb-1" style="font-size:1.1rem;">Puedes volver a intentarlo</div>
      </div>
    </div>
  </div>
 </div>
</section>
</template>

<style scoped>
.status-icon {
  font-size: 2.5rem;
}
</style>
