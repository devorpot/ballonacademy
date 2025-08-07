<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';

const props = defineProps({
  evaluation: Object,
  questions: Array
});

const responses = ref({});
const currentIndex = ref(0);
const completed = ref(false);

const totalPoints = computed(() =>
  props.questions.reduce((acc, q) => acc + (parseInt(q.points) || 0), 0)
);

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

const currentQuestion = computed(() => props.questions[currentIndex.value]);
const isLast = computed(() => currentIndex.value === props.questions.length - 1);

function nextQuestion() {
  if (!isLast.value) {
    currentIndex.value++;
  } else {
    completed.value = true;
  }
}

// Para volver a intentar la simulación
function resetQuiz() {
  responses.value = {};
  currentIndex.value = 0;
  completed.value = false;
}
</script>

<template>
  <Head :title="`Previsualización de Evaluación #${evaluation.id}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Evaluaciones', route: 'admin.evaluations.index' },
        { label: `Preguntas Evaluación #${evaluation.id}`, route: 'admin.evaluation-questions.index', params: [evaluation.id] },
        { label: 'Previsualizar', route: '' }
      ]"
    />

    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="bi bi-eye me-2"></i>
                Previsualización de la evaluación
              </h5>
              <Link class="btn btn-outline-secondary" :href="route('admin.evaluation-questions.index', evaluation.id)">
                <i class="bi bi-arrow-left"></i> Volver a preguntas
              </Link>
            </div>
            <div class="card-body">
              <!-- Puntuación -->
              <div class="mb-4 text-end">
                <span class="badge bg-primary fs-6">
                  Puntos: {{ score }} / {{ totalPoints }}
                </span>
              </div>

              <form v-if="!completed && currentQuestion">
                <div class="mb-4">
                  <div class="mb-2 fw-bold">
                    {{ currentQuestion.order }}. {{ currentQuestion.question }}
                    <span class="badge bg-info ms-2" v-if="currentQuestion.points">Puntos: {{ currentQuestion.points }}</span>
                  </div>

                  <!-- Pregunta de texto -->
                  <FieldText
                    v-if="currentQuestion.type === 1"
                    :id="`preview-q-${currentQuestion.id}`"
                    v-model="responses[currentQuestion.id]"
                    :label="null"
                    placeholder="Respuesta abierta"
                  />

                  <!-- Pregunta de opción múltiple como botones -->
                  <div v-else>
                    <div class="btn-group" role="group" :aria-label="`Opciones de la pregunta ${currentQuestion.id}`">
                      <button
                        v-for="option in currentQuestion.options_json"
                        :key="option.value"
                        type="button"
                        class="btn"
                        :class="[
                          'btn-outline-primary',
                          responses[currentQuestion.id] == option.value ? 'active' : ''
                        ]"
                        @click="responses[currentQuestion.id] = option.value"
                      >
                        {{ option.label }}
                      </button>
                    </div>
                    <!-- Feedback de respuesta correcta/incorrecta -->
                    <div v-if="responses[currentQuestion.id]" class="mt-2 small">
                      <span v-if="parseInt(responses[currentQuestion.id]) === parseInt(currentQuestion.response_option)"
                        class="text-success fw-bold"
                      >Correcta</span>
                      <span v-else class="text-danger fw-bold">Incorrecta</span>
                    </div>
                  </div>
                </div>

                <!-- Botón para siguiente pregunta -->
                <div class="text-end">
                  <button
                    type="button"
                    class="btn btn-success"
                    :disabled="!responses[currentQuestion.id] || (currentQuestion.type === 1 && !responses[currentQuestion.id]?.trim())"
                    @click="nextQuestion"
                  >
                    {{ isLast ? 'Finalizar' : 'Siguiente' }}
                  </button>
                </div>
              </form>

              <div v-if="completed" class="text-center py-5">
                <div class="mb-3">
                  <span class="display-2 text-success"><i class="bi bi-trophy-fill"></i></span>
                </div>
                <h4 class="fw-bold mb-3">¡Simulación completada!</h4>
                <div class="mb-3 fs-5">
                  Obtuviste <strong>{{ score }}</strong> de <strong>{{ totalPoints }}</strong> punto{{ totalPoints > 1 ? 's' : '' }}.
                </div>
                <button class="btn btn-primary" @click="resetQuiz">
                  Volver a intentar
                </button>
              </div>

              <div class="alert alert-info mt-4" v-if="!completed">
                Responde cada pregunta y avanza. Al finalizar verás tu puntaje simulado.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.card {
  margin-top: 2rem;
}
.btn.active,
.btn:active {
  background-color: #0d6efd;
  color: #fff;
  border-color: #0d6efd;
}
</style>
