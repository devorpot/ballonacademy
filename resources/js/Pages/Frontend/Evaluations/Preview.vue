<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';

import StudentLayout from '@/Layouts/StudentLayout.vue';
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import EvaluationStatusHeader from '@/Components/Dashboard/Evaluation/EvaluationStatusHeader.vue';
import EvaluationForm from '@/Components/Dashboard/Evaluation/EvaluationForm.vue';
import EvaluationVideo from '@/Components/Dashboard/Evaluation/EvaluationVideo.vue';
const props = defineProps({
  evaluation: Object,
  questions: Array,
    course: Object,
  userHasSubmitted: Boolean,
  lastEvaluationUser: Object, 
});

const evaType = computed(() => parseInt(props.evaluation?.eva_type ?? 1))
const isQuiz = computed(() => evaType.value === 1)
const isVideoOnly = computed(() => evaType.value === 2)

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


  
<!-- Dentro del template donde tenías el bloque original -->
<EvaluationStatusHeader
  :lastEvaluationUser="lastEvaluationUser"
  :userScore="userScore"
  :lastCreatedAt="lastCreatedAt"
/>

 
 
<!--si es formulario-->
<section v-if="evaType === 1">
 

  <EvaluationForm
  :evaluation="evaluation"
  :questions="questions"
  :user-has-submitted="userHasSubmitted"
  :last-evaluation-user="lastEvaluationUser"
  :last-comments="lastComments"
  :last-files="lastFiles"
/>
 
</section>

<section v-if="evaType === 2">

    <EvaluationVideo
  :evaluation="evaluation"
  :user-has-submitted="userHasSubmitted"
  :last-evaluation-user="lastEvaluationUser"
  :last-comments="lastComments"
  :last-files="lastFiles"
/>
</section>



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
