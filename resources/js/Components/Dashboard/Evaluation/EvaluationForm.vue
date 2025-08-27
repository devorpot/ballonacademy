<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import FieldText from '@/Components/Admin/Fields/FieldText.vue'

// Props desde padre
const props = defineProps({
  evaluation: { type: Object, required: true },     // debe tener id y course_id
  questions:  { type: Array,  required: true },
  userHasSubmitted: { type: Boolean, default: false },
  lastEvaluationUser: { type: Object, default: null },
  // estos dos pueden venir ya resueltos desde el padre; si no vienen, los resolvemos abajo
  lastComments: { type: String, default: '' },
  lastFiles:    { type: String, default: '' },
})

// ====== Estados internos ======
const responses   = ref({})
const currentIndex= ref(0)
const completed   = ref(false)
const sending     = ref(false)
const comments    = ref('')
const file        = ref(null)
const retrying    = ref(false)

// ====== Computeds de navegación y datos ======
const currentQuestion = computed(() => props.questions[currentIndex.value] || null)
const isLast          = computed(() => currentIndex.value === props.questions.length - 1)

// Mapeo robusto de respuestas previas (soporta .answers_json y .data.answers)
const userAnswers = computed(() => {
  return props.lastEvaluationUser?.answers_json
      || props.lastEvaluationUser?.data?.answers
      || {}
})

// Comentarios/archivos previos: usa props si vienen; si no, intenta del objeto lastEvaluationUser
const resolvedLastComments = computed(() => {
  if (props.lastComments) return props.lastComments
  return props.lastEvaluationUser?.comments || props.lastEvaluationUser?.data?.comments || ''
})
const resolvedLastFiles = computed(() => {
  if (props.lastFiles) return props.lastFiles
  return props.lastEvaluationUser?.files || props.lastEvaluationUser?.data?.files || ''
})

// Cálculo del puntaje del intento actual (para preguntas de opción múltiple)
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

// ====== Métodos ======
function selectOption(questionId, value) {
  responses.value[questionId] = value
}

function nextQuestion() {
  if (!currentQuestion.value) return
  if (isLast.value) {
    completed.value = true
  } else {
    currentIndex.value++
  }
}

function resetQuiz() {
  responses.value   = {}
  currentIndex.value= 0
  completed.value   = false
  comments.value    = ''
  file.value        = null
}

function onFileChange(event) {
  file.value = event.target.files?.[0] || null
}

// Eliminar intento anterior y recargar limpio
function retryTest() {
  if (!confirm('¿Estás seguro que deseas volver a hacer el test? Tu envío anterior será eliminado.')) return
  retrying.value = true
  router.delete(route('dashboard.evaluation-users.destroy-by-evaluation'), {
    data: { evaluation_id: props.evaluation.id, course_id: props.evaluation.course_id },
    onSuccess: () => {
      // recarga la vista limpia
      window.location.reload()
    },
    onError: () => {
      alert('Ocurrió un error al intentar eliminar tu envío anterior.')
      retrying.value = false
    },
  })
}

// Enviar evaluación contestada
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
  if (file.value) {
    formData.append('files', file.value)
  }

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
</script>

<template>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-8 mx-auto">
        <div class="card mb-3">
         <div class="card-body">
            <h3>{{evaluation.title}}</h3>
            <h6>Curso : {{evaluation.course.title}}</h6>
            <p><strong>Instrucciones : </strong></p>
            <p>{{evaluation.eva_comments}}</p>
         </div>
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
                        {{ q.options_json?.find(opt => opt.value == userAnswers[q.id])?.label || userAnswers[q.id] || 'Sin respuesta' }}
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
                        {{ q.options_json?.find(opt => opt.value == userAnswers[q.id])?.label || userAnswers[q.id] || 'Sin respuesta' }}
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
          <form v-if="!completed && currentQuestion">
            <div class="card mb-4">
              <div class="card-body">
                <div class="mb-2 fw-bold fs-5">
                  {{ currentQuestion.order }}. {{ currentQuestion.question }}
                  <span class="badge bg-secondary ms-2" v-if="currentQuestion.points">Puntos: {{ currentQuestion.points }}</span>
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
                <ul v-else class="list-group mt-3 mb-4">
                  <li
                    v-for="option in currentQuestion.options_json"
                    :key="option.value"
                    class="list-group-item list-group-item-action"
                    :class="{ 'active': responses[currentQuestion.id] == option.value }"
                    style="cursor:pointer; font-size:1.15rem;"
                    @click="selectOption(currentQuestion.id, option.value)"
                  >
                    {{ option.label }}
                  </li>
                </ul>
              </div>
            </div>

            <div class="text-end">
              <button
                type="button"
                class="btn btn-success btn-lg"
                :disabled="!responses[currentQuestion.id] || (currentQuestion.type === 1 && !String(responses[currentQuestion.id] || '').trim())"
                @click="nextQuestion"
              >
                {{ isLast ? 'Finalizar' : 'Siguiente' }}
              </button>
            </div>
          </form>

          <!-- Final -->
          <div v-if="completed" class="card py-5 text-center">
            <div class="card-body">
              <div class="mb-4 fs-4 fw-bold">¡Has terminado la evaluación!</div>

              <div class="mb-2">
                <span class="badge bg-info">Tu puntaje: {{ score }}</span>
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold">¿Comentarios adicionales?</label>
                <textarea v-model="comments" class="form-control" rows="3" placeholder="Opcional"></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold">Adjunta tu archivo/video (opcional):</label>
                <input type="file" class="form-control" @change="onFileChange" />
              </div>

              <button class="btn btn-primary btn-lg" :disabled="sending" @click="submitEvaluation">
                <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
                Enviar Evaluación
              </button>



              <div class="mt-4">
                <button class="btn btn-link text-secondary" @click="resetQuiz">Volver a intentar</button>
              </div>
            </div>
          </div>

          <div class="alert alert-info mt-4" v-if="!completed">
            Contesta cada pregunta y avanza usando el botón. Recuerda dar clic en "Enviar Evaluación" al finalizar.
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
