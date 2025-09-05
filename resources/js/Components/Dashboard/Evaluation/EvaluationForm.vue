<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import FieldText from '@/Components/Admin/Fields/FieldText.vue'

const props = defineProps({
  evaluation: { type: Object, required: true },
  questions:  { type: Array,  required: true },
  userHasSubmitted: { type: Boolean, default: false },
  lastEvaluationUser: { type: Object, default: null },
  lastComments: { type: String, default: '' },
  lastFiles:    { type: String, default: '' },
})

/* ===== Estado ===== */
const responses    = ref({})
const currentIndex = ref(0)
const completed    = ref(false)
const sending      = ref(false)
const comments     = ref('')
const file         = ref(null)
const retrying     = ref(false)

/* Toggle instrucciones */
const isOpenIntro = ref(true)

/* ===== Derivados ===== */
const total = computed(() => props.questions.length)
const currentQuestion = computed(() => props.questions[currentIndex.value] || null)
const isLast = computed(() => currentIndex.value === total.value - 1)

const userAnswers = computed(() =>
  props.lastEvaluationUser?.answers_json ||
  props.lastEvaluationUser?.data?.answers || {}
)

const resolvedLastComments = computed(() =>
  props.lastComments || props.lastEvaluationUser?.comments || props.lastEvaluationUser?.data?.comments || ''
)
const resolvedLastFiles = computed(() =>
  props.lastFiles || props.lastEvaluationUser?.files || props.lastEvaluationUser?.data?.files || ''
)

const answeredCount = computed(() =>
  props.questions.reduce((acc, q) => {
    const v = responses.value[q.id]
    if (v != null && String(v).trim() !== '') acc++
    return acc
  }, 0)
)
const progressPct = computed(() =>
  total.value ? Math.round((answeredCount.value / total.value) * 100) : 0
)

/* Puntaje (opción múltiple) */
const score = computed(() =>
  props.questions.reduce((acc, q) => {
    if (q.type === 0 && responses.value[q.id] != null && q.response_option != null) {
      if (parseInt(responses.value[q.id]) === parseInt(q.response_option)) {
        acc += parseInt(q.points || 0)
      }
    }
    return acc
  }, 0)
)

/* ===== Métodos ===== */
function selectOption(questionId, value) {
  responses.value[questionId] = value
}

function canAdvance() {
  const q = currentQuestion.value
  if (!q) return false
  const v = responses.value[q.id]
  return v != null && String(v).trim() !== ''
}

function goNext() {
  if (!currentQuestion.value) return
  if (isLast.value) {
    completed.value = true
  } else {
    currentIndex.value++
  }
}

function goPrev() {
  if (currentIndex.value > 0) currentIndex.value--
}

function resetQuiz() {
  responses.value = {}
  currentIndex.value = 0
  completed.value = false
  comments.value = ''
  file.value = null
}

function onFileChange(e) {
  file.value = e.target.files?.[0] || null
}

function retryTest() {
  if (!confirm('¿Estás seguro que deseas volver a hacer el test? Tu envío anterior será eliminado.')) return
  retrying.value = true
  router.delete(route('dashboard.evaluation-users.destroy-by-evaluation'), {
    data: { evaluation_id: props.evaluation.id, course_id: props.evaluation.course_id },
    onSuccess: () => { window.location.reload() },
    onError: () => {
      alert('Ocurrió un error al intentar eliminar tu envío anterior.')
      retrying.value = false
    },
  })
}

function submitEvaluation() {
  if (sending.value) return
  sending.value = true

  const formData = new FormData()
  formData.append('course_id', props.evaluation.course_id)
  formData.append('data', JSON.stringify({
    evaluation_id: props.evaluation.id,
    answers: responses.value,
    score: score.value,
  }))
  formData.append('comments', comments.value || '')
  if (file.value) formData.append('files', file.value)

  router.post(route('dashboard.evaluation-users.store'), formData, {
    forceFormData: true,
    onSuccess: () => {
      window.location.href = route('dashboard.evaluations.thankyou', { evaluation: props.evaluation.id })
    },
    onError: () => {
      alert('Ocurrió un error al enviar la evaluación. Intenta de nuevo.')
      sending.value = false
    }
  })
}

/* ===== Atajos de teclado ===== */
function isTypingTarget(el) {
  if (!el) return false
  const tag = el.tagName?.toLowerCase()
  return tag === 'input' || tag === 'textarea' || tag === 'select' || el.isContentEditable
}

function onKeydown(e) {
  if (isTypingTarget(e.target)) return

  // Numeros 1-9 para seleccionar opción
  if (currentQuestion.value?.type === 0) {
    const idx = parseInt(e.key, 10)
    const options = currentQuestion.value.options_json || []
    if (!Number.isNaN(idx) && idx >= 1 && idx <= Math.min(9, options.length)) {
      const opt = options[idx - 1]
      selectOption(currentQuestion.value.id, opt.value)
      e.preventDefault()
      return
    }
  }

  // Enter o Flecha Derecha -> siguiente
  if (e.key === 'Enter' || e.key === 'ArrowRight') {
    if (canAdvance()) goNext()
    e.preventDefault()
    return
  }

  // Flecha Izquierda -> anterior
  if (e.key === 'ArrowLeft') {
    goPrev()
    e.preventDefault()
  }
}

onMounted(() => {
  // Prefill si hubiera datos (no visible si ya envió, pero por si acaso)
  Object.entries(userAnswers.value || {}).forEach(([qid, val]) => {
    responses.value[qid] = val
  })
  window.addEventListener('keydown', onKeydown)
})
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <!-- Encabezado + Instrucciones con toggle -->
      <div class="col-md-10 col-lg-8 mx-auto">
        <div class="card mb-3">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column">
              <h5 class="mb-0">{{ evaluation.title }}</h5>
              <small class="text-muted">Curso: {{ evaluation.course.title }}</small>
            </div>

            <button
              type="button"
              class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center"
              @click="isOpenIntro = !isOpenIntro"
              :aria-expanded="isOpenIntro"
              aria-controls="intro-body"
              :title="isOpenIntro ? 'Ocultar instrucciones' : 'Mostrar instrucciones'"
            >
              <i v-if="isOpenIntro" class="bi bi-chevron-down"></i>
              <i v-else class="bi bi-dash-lg"></i>
            </button>
          </div>

          <Transition name="collapse-fade">
            <div v-show="isOpenIntro" id="intro-body" class="card-body">
              <p class="mb-2"><strong>Instrucciones:</strong></p>
              <p class="mb-0">{{ evaluation.eva_comments }}</p>
            </div>
          </Transition>
        </div>
      </div>

      <div class="col-md-10 col-lg-8">
        <!-- Caso: ya envió y existe registro -->
        <div v-if="userHasSubmitted && lastEvaluationUser">
          <div class="row g-3">
            <!-- APROBADO -->
            <div class="col-12" v-if="lastEvaluationUser.status === 999">
              <div class="card mb-3">
                <div class="card-header">Tus respuestas</div>
                <ul class="list-group list-group-flush">
                  <li
                    v-for="q in questions"
                    :key="q.id"
                    class="list-group-item d-flex justify-content-between align-items-center"
                  >
                    <span>{{ q.order ? q.order + '. ' : '' }}{{ q.question }}</span>
                    <span>
                      <template v-if="q.type === 0">
                        {{ q.options_json?.find(opt => opt.value == (userAnswers[q.id]))?.label || userAnswers[q.id] || 'Sin respuesta' }}
                        <span v-if="q.response_option && userAnswers[q.id] == q.response_option" class="text-success ms-2">
                          <i class="bi bi-check-circle-fill"></i>
                        </span>
                        <span v-else-if="q.response_option" class="text-danger ms-2">
                          <i class="bi bi-x-circle-fill"></i>
                        </span>
                      </template>
                      <template v-else>
                        {{ userAnswers[q.id] || 'Sin respuesta' }}
                      </template>
                    </span>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Comentarios -->
            <div class="col-12" v-if="resolvedLastComments">
              <div class="card mb-2">
                <div class="card-header">Comentarios</div>
                <div class="card-body">
                  <div class="border rounded p-2 bg-light">{{ resolvedLastComments }}</div>
                </div>
              </div>
            </div>

            <!-- Archivo -->
            <div class="col-12" v-if="resolvedLastFiles">
              <div class="card mb-2">
                <div class="card-header">Archivo adjunto</div>
                <div class="card-body text-center">
                  <video
                    v-if="/\.(mp4|webm|ogg)$/i.test(resolvedLastFiles)"
                    controls style="max-width:300px;"
                    :src="`/storage/${resolvedLastFiles}`"
                  ></video>
                  <img
                    v-else-if="/\.(jpe?g|png|gif|webp)$/i.test(resolvedLastFiles)"
                    :src="`/storage/${resolvedLastFiles}`"
                    style="max-width: 300px; border-radius: 0.5rem;"
                    class="shadow"
                  />
                  <div v-else>
                    <a :href="`/storage/${resolvedLastFiles}`" download class="btn btn-outline-secondary btn-sm" target="_blank">
                      Descargar archivo
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- NO APROBADO -->
            <div class="col-12" v-if="lastEvaluationUser.status === 333">
              <div class="card mb-3">
                <div class="card-header">Tus respuestas</div>
                <ul class="list-group list-group-flush">
                  <li
                    v-for="q in questions"
                    :key="q.id"
                    class="list-group-item d-flex justify-content-between align-items-center"
                  >
                    <span>{{ q.order ? q.order + '. ' : '' }}{{ q.question }}</span>
                    <span>
                      <template v-if="q.type === 0">
                        {{ q.options_json?.find(opt => opt.value == (userAnswers[q.id]))?.label || userAnswers[q.id] || 'Sin respuesta' }}
                        <span v-if="q.response_option">
                          <i v-if="userAnswers[q.id] == q.response_option" class="bi bi-check-circle-fill text-success ms-2"></i>
                          <i v-else class="bi bi-x-circle-fill text-danger ms-2"></i>
                        </span>
                      </template>
                      <template v-else>
                        {{ userAnswers[q.id] || 'Sin respuesta' }}
                      </template>
                    </span>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Botón volver a intentar -->
            <div class="col-12 text-center" v-if="lastEvaluationUser.status !== 999">
              <div class="alert alert-warning">
                Ya tienes un envío registrado. Para subir otro video debes <strong>eliminar</strong> tu evaluación actual.
              </div>
              <button
                class="btn btn-outline-danger btn-lg"
                :disabled="retrying"
                @click="retryTest"
              >
                <span v-if="retrying" class="spinner-border spinner-border-sm me-2"></span>
                Eliminar mi evaluación y hacer el test otra vez
              </button>
            </div>
          </div>
        </div>

        <!-- Caso: ya envió pero no se encontró registro -->
        <div v-else-if="userHasSubmitted && !lastEvaluationUser">
          <div class="alert alert-info text-center my-4">
            No se encontró información de tu evaluación. Si crees que es un error, contacta a soporte.
          </div>
        </div>

        <!-- Caso: nunca ha enviado -->
        <template v-else>
          <form v-if="!completed && currentQuestion" @submit.prevent>
            <!-- Barra de progreso y contador -->
            <div class="d-flex align-items-center justify-content-between mb-2">
              <small class="text-muted">Pregunta {{ currentIndex + 1 }} de {{ total }}</small>
              <small class="text-muted">{{ progressPct }}%</small>
            </div>
            <div class="progress mb-3" style="height: 6px;">
              <div class="progress-bar" role="progressbar" :style="{ width: progressPct + '%' }"
                   :aria-valuenow="progressPct" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <div class="card mb-3">
              <div class="card-body">

                <div class="mb-2 fw-bold fs-5">
                  {{ currentQuestion.order }}. {{ currentQuestion.question }}
                <!--   <span class="badge bg-secondary ms-2" v-if="currentQuestion.points">Puntos: {{ currentQuestion.points }}</span> -->
                </div>

                <!-- Texto abierto -->
                <FieldText
                  v-if="currentQuestion.type === 1"
                  :id="`q-${currentQuestion.id}`"
                  v-model="responses[currentQuestion.id]"
                  :label="null"
                  placeholder="Escribe tu respuesta aquí"
                />

                <!-- Opción múltiple -->
                <div v-else class="list-group mt-3">
                  <label
                    v-for="(option, idx) in currentQuestion.options_json"
                    :key="option.value"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2"
                    :class="{ active: responses[currentQuestion.id] == option.value }"
                    role="button"
                  >
                    <input
                      class="form-check-input me-2"
                      type="radio"
                      :name="`q-${currentQuestion.id}`"
                      :value="option.value"
                      :checked="responses[currentQuestion.id] == option.value"
                      @change="selectOption(currentQuestion.id, option.value)"
                    />
                    <span class="flex-grow-1">
                      {{ idx + 1 }}. {{ option.label }}
                    </span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Navegación inferior -->
            <div class="d-flex justify-content-between align-items-center">
              <button
                type="button"
                class="btn btn-outline-secondary"
                :disabled="currentIndex === 0"
                @click="goPrev"
              >
                Anterior
              </button>

              <button
                type="button"
                class="btn btn-success"
                :disabled="!canAdvance()"
                @click="goNext"
              >
                {{ isLast ? 'Finalizar' : 'Siguiente' }}
              </button>
            </div>
          </form>

          <!-- Final -->
          <div v-if="completed" class="card py-4">
            <div class="card-body">
              <div class="mb-3 fs-5 fw-bold text-center">Has terminado la evaluación</div>

              <!--
              <div class="mb-3 text-center">
                <span class="badge bg-info">Puntaje: {{ score }}</span>
              </div>
              -->
              <div class="mb-3">
                <label class="form-label fw-bold">¿Comentarios adicionales?</label>
                <textarea v-model="comments" class="form-control" rows="3" placeholder="Opcional"></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold">Adjunta tu archivo/video (opcional):</label>
                <input type="file" class="form-control" @change="onFileChange" />
              </div>

              <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-primary btn-lg" :disabled="sending" @click="submitEvaluation">
                  <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
                  Enviar Evaluación
                </button>
                <button class="btn btn-link text-secondary" type="button" @click="resetQuiz">Volver a intentar</button>
              </div>
            </div>
          </div>

          <div class="alert alert-info mt-4" v-if="!completed">
            Contesta cada pregunta y avanza usando el botón o con Enter. También puedes usar 1–9 para elegir opción y las flechas ← → para navegar.
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.collapse-fade-enter-active,
.collapse-fade-leave-active { transition: opacity .2s ease; }
.collapse-fade-enter-from,
.collapse-fade-leave-to { opacity: 0; }
</style>
