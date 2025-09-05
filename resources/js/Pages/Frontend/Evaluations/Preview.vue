<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'
import EvaluationStatusHeader from '@/Components/Dashboard/Evaluation/EvaluationStatusHeader.vue'
import EvaluationForm from '@/Components/Dashboard/Evaluation/EvaluationForm.vue'
import EvaluationVideo from '@/Components/Dashboard/Evaluation/EvaluationVideo.vue'

const props = defineProps({
  evaluation: { type: Object, required: true },
  questions:  { type: Array,  required: true },
  course:     { type: Object, required: true },
  userHasSubmitted: { type: Boolean, default: false },
  lastEvaluationUser: { type: Object, default: null },
})

/* Tipo de evaluación */
const evaType      = computed(() => parseInt(props.evaluation?.eva_type ?? 1))
const isQuiz       = computed(() => evaType.value === 1)
const isVideoOnly  = computed(() => evaType.value === 2)

/* Datos para cabecera de estado */
const userScore      = computed(() => props.lastEvaluationUser?.data?.score ?? null)
const lastCreatedAt  = computed(() =>
  props.lastEvaluationUser?.created_at
    ? new Date(props.lastEvaluationUser.created_at).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' })
    : '-'
)

/* Toggle instrucciones */
const showIntro = ref(true)

/* Etiquetas visuales */
const typeBadge = computed(() => {
  if (isQuiz.value)      return { text: 'Cuestionario', variant: 'primary', icon: 'bi-ui-checks' }
  if (isVideoOnly.value) return { text: 'Entrega en video', variant: 'info', icon: 'bi-camera-video' }
  return { text: 'Evaluación', variant: 'secondary', icon: 'bi-journal-check' }
})
</script>

<template>
  <Head :title="`Realizar Evaluación - ${evaluation.title || ''}`" />

  <StudentLayout>
    <!-- Migas -->
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Cursos', route: 'dashboard.courses.index' },
        { label: course.title, route: '' },
        { label: 'Evaluaciones', route: '' },
      ]"
    />

    <!-- Hero con información clave -->
    <section class="eva-hero py-3">
      <div class="container">
        <div class="row align-items-center gy-3">
          <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge" :class="`text-bg-${typeBadge.variant}`">
                <i class="bi me-1" :class="typeBadge.icon"></i>{{ typeBadge.text }}
              </span>
              <span class="badge text-bg-light">Curso: {{ course.title }}</span>
            </div>

            <h1 class="eva-title mb-2">{{ evaluation.title }}</h1>

            <!-- Instrucciones con toggle -->
            
          </div>

          <!-- Acciones rápidas -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body d-flex flex-column justify-content-center gap-2">
                <Link :href="route('dashboard.courses.index')" class="btn btn-outline-secondary w-100">
                  Volver a Mis Cursos
                </Link>
                 
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Cabecera de estado del intento -->
    <div class="container mb-3">
      <EvaluationStatusHeader
        :lastEvaluationUser="lastEvaluationUser"
        :userScore="userScore"
        :lastCreatedAt="lastCreatedAt"
      />
    </div>

    <!-- Contenido principal -->
    <section id="eva-section" class="container mb-5">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
          <div class="card border-0 shadow">
            <div class="card-body p-3 p-md-4">
              <!-- Render condicional por tipo -->
              <EvaluationForm
                v-if="isQuiz"
                :evaluation="evaluation"
                :questions="questions"
                :user-has-submitted="userHasSubmitted"
                :last-evaluation-user="lastEvaluationUser"
                :last-comments="lastEvaluationUser?.comments || lastEvaluationUser?.data?.comments || ''"
                :last-files="lastEvaluationUser?.files || lastEvaluationUser?.data?.files || ''"
              />

              <EvaluationVideo
                v-else-if="isVideoOnly"
                :evaluation="evaluation"
                :user-has-submitted="userHasSubmitted"
                :last-evaluation-user="lastEvaluationUser"
                :last-comments="lastEvaluationUser?.comments || lastEvaluationUser?.data?.comments || ''"
                :last-files="lastEvaluationUser?.files || lastEvaluationUser?.data?.files || ''"
              />

              <div v-else class="alert alert-warning mb-0">
                Tipo de evaluación no soportado.
              </div>
            </div>
          </div>

          <!-- Ayuda -->
          <div class="alert alert-info mt-3">
            
          </div>
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<style scoped>
.eva-hero {
  background:
    radial-gradient(1200px 500px at 50% -30%, #eaf4ff 0%, transparent 60%),
    #fff;
}
.eva-title {
  color: #0d4d92;
  letter-spacing: .2px;
}

/* Transición simple */
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
