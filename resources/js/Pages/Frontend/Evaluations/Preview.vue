<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';

import StudentLayout from '@/Layouts/StudentLayout.vue';
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';

const props = defineProps({
  evaluation: Object,
  questions: Array,
    course: Object,
  userHasSubmitted: Boolean,
  lastEvaluationUser: Object, 
});

const responses = ref({});
const currentIndex = ref(0);
const completed = ref(false);
const sending = ref(false);
const comments = ref('');
const file = ref(null);
const retrying = ref(false);

// Para mostrar los datos de la última evaluación enviada
const userAnswers = props.lastEvaluationUser?.data?.answers || {};
const userScore = props.lastEvaluationUser?.data?.score ?? null;

const currentQuestion = computed(() => props.questions[currentIndex.value]);
const isLast = computed(() => currentIndex.value === props.questions.length - 1);

const lastStatusLabel = computed(() => props.lastEvaluationUser?.status_label || '');
const lastCreatedAt = computed(() =>
  props.lastEvaluationUser?.created_at
    ? new Date(props.lastEvaluationUser.created_at).toLocaleString()
    : '-'
);
const lastComments = computed(() => props.lastEvaluationUser?.comments || '');
const lastFiles = computed(() => props.lastEvaluationUser?.files || null);

// Calcula el puntaje del intento actual
const score = computed(() =>
  props.questions.reduce((acc, q) => {
    if (q.type === 0 && responses.value[q.id] && q.response_option) {
      if (parseInt(responses.value[q.id]) === parseInt(q.response_option)) {
        return acc + (parseInt(q.points) || 0);
      }
    }
    return acc;
  }, 0)
);

function nextQuestion() {
  if (!isLast.value) {
    currentIndex.value++;
  } else {
    completed.value = true;
  }
}

function selectOption(questionId, value) {
  responses.value[questionId] = value;
}

function resetQuiz() {
  responses.value = {};
  currentIndex.value = 0;
  completed.value = false;
  comments.value = '';
  file.value = null;
}

// Eliminar intento anterior y recargar vista limpia
function retryTest() {
  if (!confirm("¿Estás seguro que deseas volver a hacer el test? Tu envío anterior será eliminado.")) {
    return;
  }
  retrying.value = true;
  router.delete(route('dashboard.evaluation-users.destroy-by-evaluation'), {
      data: { evaluation_id: props.evaluation.id, course_id: props.evaluation.course_id },
    onSuccess: () => {
      window.location.reload();
    },
    onError: () => {
      alert("Ocurrió un error al intentar eliminar tu envío anterior.");
      retrying.value = false;
    },
  });
}

function onFileChange(e) {
  file.value = e.target.files[0] || null;
}

// Función para enviar evaluación contestada
function submitEvaluation() {
  sending.value = true;
  const formData = new FormData();
  formData.append('course_id', props.evaluation.course_id);
  formData.append('data', JSON.stringify({
    evaluation_id: props.evaluation.id,
    answers: responses.value,
    score: score.value,
  }));
  formData.append('comments', comments.value || '');
  if (file.value) {
    formData.append('files', file.value);
  }

  router.post(route('dashboard.evaluation-users.store'), formData, {
    forceFormData: true,
    onSuccess: () => {
      window.location.href = route('dashboard.evaluations.thankyou', { evaluation: props.evaluation.id });
    },
    onError: () => {
      alert('Ocurrió un error al enviar la evaluación. Intenta de nuevo.');
      sending.value = false;
    }
  });
}
</script>

<template>
  <Head :title="`Realizar Evaluación - ${evaluation.title || ''}`" />
  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Cursos', route: 'dashboard.index' },
        { label: course.title, route: '' },
        { label: 'Evaluaciones', route: '' },
      ]"
    />
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

          <!-- Encabezado de estado, ícono y puntaje -->
          <div class="pt-4 pb-2 text-center">
            <!-- Ícono según estado -->
            <span v-if="lastEvaluationUser && lastEvaluationUser.status === 999">
              <i class="bi bi-award-fill text-success" style="font-size:2.5rem;"></i>
            </span>
            <span v-else-if="lastEvaluationUser && lastEvaluationUser.status === 111">
              <i class="bi bi-send-check-fill text-primary" style="font-size:2.5rem;"></i>
            </span>
            <span v-else-if="lastEvaluationUser && lastEvaluationUser.status === 222">
              <i class="bi bi-hourglass-split text-warning" style="font-size:2.5rem;"></i>
            </span>
            <span v-else-if="lastEvaluationUser && lastEvaluationUser.status === 333">
              <i class="bi bi-x-octagon-fill text-danger" style="font-size:2.5rem;"></i>
            </span>

            <!-- Estado -->
            <div class="fs-5 mb-1 mt-2" v-if="lastEvaluationUser">
              Estado:
              <span :class="{
                  'text-success fw-bold': lastEvaluationUser.status === 999,
                  'text-primary fw-bold': lastEvaluationUser.status === 111,
                  'text-warning fw-bold': lastEvaluationUser.status === 222,
                  'text-danger fw-bold': lastEvaluationUser.status === 333
                }">
                {{
                  lastEvaluationUser.status === 111 ? 'Enviado' :
                  lastEvaluationUser.status === 222 ? 'En revisión' :
                  lastEvaluationUser.status === 333 ? 'No aprobado' :
                  lastEvaluationUser.status === 999 ? 'Aprobado' : 'Desconocido'
                }}
              </span>
            </div>
            <div class="small text-muted mb-3" v-if="lastEvaluationUser">
              Fecha de envío: {{ lastCreatedAt }}
            </div>

            <!-- Puntaje si es APROBADO -->
            <div v-if="lastEvaluationUser && lastEvaluationUser.status === 999 && typeof userScore === 'number'" class="mb-2">
              <div class="d-flex flex-column align-items-center justify-content-center my-3">
                <div class="display-2 fw-bold text-success" style="line-height: 1;">
                  {{ userScore }}
                </div>
                <div class="fs-5 fw-bold text-success mt-1">¡Felicidades, has aprobado!</div>
                <div class="text-muted" style="font-size:1.1rem;">¡Excelente trabajo!</div>
              </div>
            </div>

            <!-- Puntaje si es NO APROBADO -->
            <div v-if="lastEvaluationUser && lastEvaluationUser.status === 333 && typeof userScore === 'number'" class="mb-2">
              <div class="d-flex flex-column align-items-center justify-content-center my-3">
                <div class="display-4 fw-bold text-danger" style="line-height: 1;">
                  {{ userScore }}
                </div>
                <div class="fs-5 fw-bold text-danger mt-1">No aprobado</div>
                <div class="text-muted mb-1" style="font-size:1.1rem;">Puedes volver a intentarlo</div>
              </div>
            </div>
          </div>

          <!-- Si ha enviado alguna evaluación -->
          <div v-if="userHasSubmitted && lastEvaluationUser">
            <div class="row g-3">
              <!-- Comentarios -->
              <div class="col-12" v-if="lastComments">
                <div class="card mb-2">
                  <div class="card-header">Comentarios</div>
                  <div class="card-body">
                    <div class="border rounded p-2 bg-light">{{ lastComments }}</div>
                  </div>
                </div>
              </div>
              <!-- Archivo -->
              <div class="col-12" v-if="lastFiles">
                <div class="card mb-2">
                  <div class="card-header">Archivo adjunto</div>
                  <div class="card-body text-center">
                    <video
                      v-if="/\.(mp4|webm|ogg)$/i.test(lastFiles)"
                      controls style="max-width:300px;"
                      :src="`/storage/${lastFiles}`"
                    ></video>
                    <img
                      v-else-if="/\.(jpe?g|png|gif|webp)$/i.test(lastFiles)"
                      :src="`/storage/${lastFiles}`"
                      style="max-width: 300px; border-radius: 0.5rem;"
                      class="shadow"
                    />
                    <div v-else>
                      <a :href="`/storage/${lastFiles}`" download class="btn btn-outline-secondary btn-sm" target="_blank">
                        Descargar archivo
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Respuestas SOLO si es APROBADO -->
              <div class="col-12" v-if="lastEvaluationUser.status === 999">
                <div class="card mb-3">
                  <div class="card-header">Tus respuestas</div>
                  <ul class="list-group list-group-flush">
                    <li
                      v-for="q in questions"
                      :key="q.id"
                      class="list-group-item d-flex justify-content-between align-items-center"
                    >
                      <span>
                        {{ q.order ? q.order + '. ' : '' }}{{ q.question }}
                      </span>
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

              <!-- Respuestas detalladas SI es NO APROBADO -->
              <div class="col-12" v-if="lastEvaluationUser.status === 333">
                <div class="card mb-3">
                  <div class="card-header">Tus respuestas</div>
                  <ul class="list-group list-group-flush">
                    <li
                      v-for="q in questions"
                      :key="q.id"
                      class="list-group-item d-flex justify-content-between align-items-center"
                    >
                      <span>
                        {{ q.order ? q.order + '. ' : '' }}{{ q.question }}
                      </span>
                      <span>
                        <template v-if="q.type === 0">
                          {{ q.options_json?.find(opt => opt.value == userAnswers[q.id])?.label || userAnswers[q.id] || 'Sin respuesta' }}
                          <span v-if="q.response_option">
                            <i
                              v-if="userAnswers[q.id] == q.response_option"
                              class="bi bi-check-circle-fill text-success ms-2"></i>
                            <i
                              v-else
                              class="bi bi-x-circle-fill text-danger ms-2"></i>
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

              <!-- Botón para volver a realizar test SI NO ES APROBADO -->
              <div class="col-12 text-center" v-if="lastEvaluationUser.status !== 999">
                <button
                  class="btn btn-outline-danger btn-lg"
                  :disabled="retrying"
                  @click="retryTest"
                >
                  <span v-if="retrying" class="spinner-border spinner-border-sm me-2"></span>
                  Quiero hacer el test otra vez
                </button>
              </div>
            </div>
          </div>

          <!-- Si nunca ha enviado ninguna evaluación -->
          <div v-else-if="userHasSubmitted && !lastEvaluationUser">
            <div class="alert alert-info text-center my-4">
              No se encontró información de tu evaluación. Si crees que es un error, contacta a soporte.
            </div>
          </div>

          <!-- FORMULARIO SOLO SI NO HA ENVIADO -->
          <template v-else>
            <form v-if="!completed && currentQuestion">
              <div class="card mb-4">
                <div class="card-body">
                  <div class="mb-2 fw-bold fs-5">
                    {{ currentQuestion.order }}. {{ currentQuestion.question }}
                    <span class="badge bg-secondary ms-2" v-if="currentQuestion.points">Puntos: {{ currentQuestion.points }}</span>
                  </div>
                  <FieldText
                    v-if="currentQuestion.type === 1"
                    :id="`q-${currentQuestion.id}`"
                    v-model="responses[currentQuestion.id]"
                    :label="null"
                    placeholder="Escribe tu respuesta aquí"
                  />
                  <ul v-else class="list-group mt-3 mb-4">
                    <li
                      v-for="option in currentQuestion.options_json"
                      :key="option.value"
                      class="list-group-item list-group-item-action"
                      :class="{ 'active': responses[currentQuestion.id] == option.value }"
                      style="cursor:pointer; font-size:1.15rem;"
                      @click="selectOption(currentQuestion.id, option.value)"
                      tabindex="0"
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
                  :disabled="!responses[currentQuestion.id] || (currentQuestion.type === 1 && !responses[currentQuestion.id]?.trim())"
                  @click="nextQuestion"
                >
                  {{ isLast ? 'Finalizar' : 'Siguiente' }}
                </button>
              </div>
            </form>
            <!-- Formulario final para comentarios y archivo -->
            <div v-if="completed" class="card py-5 text-center">
              <div class="card-body">
                <div class="mb-4 fs-4 fw-bold">¡Has terminado la evaluación!</div>
                <div class="mb-4">
                  <label class="form-label fw-bold">¿Comentarios adicionales?</label>
                  <textarea v-model="comments" class="form-control" rows="3"></textarea>
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
  </StudentLayout>
</template>



<style scoped>
.celebration-icon {
  animation: pop-in 0.9s cubic-bezier(.8,2,.2,.8);
  display: flex;
  align-items: center;
  justify-content: center;
}
@keyframes pop-in {
  0% { transform: scale(0.2) rotate(-10deg); opacity: 0; }
  70% { transform: scale(1.1) rotate(4deg); opacity: 1; }
  90% { transform: scale(0.95); }
  100% { transform: scale(1); }
}

.card {
  margin-top: 2rem;
}
.list-group-item {
  transition: background 0.2s, color 0.2s;
  user-select: none;
}
.list-group-item.active,
.list-group-item:active {
  background: #0d6efd;
  color: #fff;
}
.btn-lg {
  font-size: 1.15rem;
  padding: 0.7rem 2rem;
}
</style>
