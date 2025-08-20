<script setup>
/**
 * Componente de tarjetas horizontales para evaluaciones.
 * - Muestra badges de tipo y puntos mínimos.
 * - Si el usuario ya envió la evaluación: botón "Mostrar resultados".
 * - Si no la ha enviado: botón "Realizar Evaluación".
 */

import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  videoEvaluations: { type: Array, default: () => [] },

  // Nombres de rutas personalizables por si cambian en el proyecto
  // Ruta para ver resultados de la entrega del usuario: requiere { id }
  routeShowSubmission: { type: String, default: 'dashboard.evaluation-users.show' },
  // Ruta para previsualizar o iniciar la evaluación: requiere { course, evaluation }
  routeStartEvaluation: { type: String, default: 'dashboard.courses.evaluations.evaluation.preview' },
})

/**
 * Mapea el nombre del estatus a clases de Bootstrap.
 * Ajustar los valores a los enums reales del proyecto.
 */
function statusBadgeClass(statusName) {
  switch ((statusName || '').toUpperCase()) {
    case 'APPROVED':
    case 'APROVEED':
      return 'bg-success'
    case 'REVISION':
      return 'bg-warning text-dark'
    case 'NOT_APPROVED':
    case 'NO_APROVEED':
      return 'bg-danger'
    case 'SEND':
    default:
      return 'bg-secondary'
  }
}
</script>

<template>
<div class="d-block" v-if="videoEvaluations.length > 0">
  

  <h5 class="my-3">Evaluaciones</h5>
  <p>Realiza las evaluación para poder continuar el curso.</p>
  <div class="d-flex flex-column gap-3">
    <div
      v-for="eva in videoEvaluations"
      :key="eva.id"
      class="card shadow-sm border-0"
    >
      <div class="row g-0 align-items-stretch flex-md-row flex-column">
        <!-- Banda/ícono lateral en desktop, ícono simple en móvil -->
        <div class="col-auto d-flex align-items-center">
          <div class="h-100 d-none d-md-block bg-primary"
               style="width:6px; border-top-left-radius:.5rem; border-bottom-left-radius:.5rem;"></div>
          <div class="p-3 d-md-none d-flex align-items-center">
            <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center"
                 style="width:48px; height:48px;">
              <i class="bi bi-clipboard-check fs-5"></i>
            </div>
          </div>
        </div>

        <!-- Contenido principal -->
        <div class="col">
          <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
              <!-- Título y badges -->
              <div class="d-flex flex-column">
                <h5 class="card-title mb-1">{{ eva.title }}</h5>

                <div class="d-flex flex-wrap align-items-center gap-2">
                  <span class="badge text-bg-light border">
                    {{ eva.type_label || eva.type_name }}
                  </span>

                  <span class="badge text-bg-light border">
                    {{ eva.eva_type_label || eva.eva_type_name }}
                  </span>

                  <span v-if="eva.points_min" class="badge text-bg-info">
                    Mínimo: {{ eva.points_min }} pts
                  </span>

                  <!-- Estado de la última entrega del usuario -->
                  <span
                    v-if="eva.submitted_by_me && eva.my_last_submission"
                    class="badge"
                    :class="statusBadgeClass(eva.my_last_submission.status_name)"
                  >
                    {{ eva.my_last_submission.status_label || eva.my_last_submission.status_name }}
                  </span>
                  <span v-else class="badge text-bg-secondary">Pendiente</span>
                </div>
              </div>

              <!-- Acciones -->
              <div class="d-flex align-items-center gap-2">
                <!-- Mostrar resultados si ya existe envío -->

                <!--
                 <Link
                  v-if="eva.submitted_by_me && eva.my_last_submission"
                  class="btn btn-outline-primary btn-sm fw-semibold d-inline-flex align-items-center gap-2"
                  :href="route(routeShowSubmission, { id: eva.my_last_submission.id })"
                  title="Ver resultados de mi envío"
                >
                  <i class="bi bi-graph-up"></i>
                  Mostrar resultados
                </Link>-->

                <Link
                  v-if="eva.submitted_by_me && eva.my_last_submission"
                  class="btn btn-outline-primary btn-sm fw-semibold d-inline-flex align-items-center gap-2"
                  :href="route('dashboard.courses.evaluations.evaluation.preview', { course: eva.course_id, evaluation: eva.id })"
                  title="Ver resultados de mi envío"
                >
                  <i class="bi bi-graph-up"></i>
                  Mostrar resultados
                </Link>

          

                <!-- Realizar evaluación si no hay envío -->
                <Link
                  v-else
                  class="btn btn-success btn-sm fw-semibold d-inline-flex align-items-center gap-2"
                  :href="route(routeStartEvaluation, { course: eva.course_id, evaluation: eva.id })"
                  title="Realizar evaluación"
                >
                  <i class="bi bi-pencil-square"></i>
                  Realizar Evaluación
                </Link>
              </div>
            </div>

            <!-- Meta secundaria -->
            <div class="mt-2 small text-muted d-flex flex-wrap gap-3">
              <div v-if="eva.my_last_submission && eva.my_last_submission.created_at">
                Último envío: {{ new Date(eva.my_last_submission.created_at).toLocaleString() }}
              </div>
              <div v-if="eva.date_evaluation">
                Fecha evaluación: {{ eva.date_evaluation }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- /card -->
  </div>
  </div>
</template>

<style scoped>
.card-title { line-height: 1.2; }
</style>
