<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';

const props = defineProps({
  evaluationUser: Object,
  questions: Array,
  statusOptions: Array,
  status_label: String, // etiqueta del status del EvaluationUser (no del Evaluation)
});

// Aliases cortos
const eu = props.evaluationUser || {};
const course = eu.course || {};
const eva = eu.evaluation || {};
const user = eu.user || {};

// Respuestas / score
const userAnswers = eu.data?.answers || {};
const userScore = eu.data?.score ?? null;

// Umbral de aprobación desde Evaluation (points_min)
const passMin = eva.points_min ?? null;
const passed = computed(() =>
  passMin != null && userScore != null ? userScore >= passMin : null
);

const goBack = () => {
  window.history.back()
}

// Helpers UI
function formatDate(value) {
  if (!value) return '-';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return '-';
  return d.toLocaleString('es-MX', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
}

const fileUrl = computed(() => {
  const f = eu.files;
  if (!f) return null;
  if (/^https?:\/\//i.test(f)) return f;
  return `/storage/${f}`;
});

function isVideoPath(path) {
  if (!path) return false;
  return /\.(mp4|webm|ogg|m4v|mov)$/i.test(path);
}
const isVideo = computed(() => isVideoPath(eu.files));

const isImage = computed(() => {
  if (!eu.files) return false;
  return /\.(jpe?g|png|gif|webp)$/i.test(eu.files);
});

// Badge de status del EvaluationUser
const statusBadgeClass = computed(() => {
  const label = (props.status_label || '').toLowerCase();
  if (label.includes('aprob')) return 'bg-success';
  if (label.includes('revis')) return 'bg-warning text-dark';
  if (label.includes('env')) return 'bg-info text-dark';
  if (label.includes('no')) return 'bg-danger';
  return 'bg-secondary';
});

// Modal estado
const showModal = ref(false);
const formStatus = ref(eu.status);
const loading = ref(false);

function openStatusModal() {
  formStatus.value = eu.status;
  showModal.value = true;
}
function closeModal() {
  showModal.value = false;
  loading.value = false;
}
function updateStatus() {
  loading.value = true;
  router.put(
    route('admin.evaluation-users.update-status', eu.id),
    { status: formStatus.value },
    {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => { loading.value = false; }
    }
  );
}

// Modal único para ver videos
const videoRef = ref(null);
const videoModal = ref({ show: false, src: null, title: '' });

function openVideo(src, title = 'Reproduciendo video') {
  videoModal.value = { show: true, src, title };
}
function closeVideo() {
  if (videoRef.value) {
    try {
      videoRef.value.pause();
      videoRef.value.currentTime = 0;
    } catch (_) {}
  }
  videoModal.value.show = false;
  videoModal.value.src = null;
  videoModal.value.title = '';
}
</script>



<template>
  <Head :title="`Detalles de la Evaluación #${evaluationUser.id}`" />

  <AdminLayout>
   <Breadcrumbs
  username="admin"
  :breadcrumbs="[
    { label: 'Dashboard', route: 'admin.dashboard' },
    { label: 'Evaluaciones', route: 'admin.evaluations.index' },
    {
      label: `Curso #${(props.evaluationUser?.course?.id ?? '-')}`,
      route: 'admin.evaluation-users.course.index',   // nombre de ruta
      params: { course: props.evaluationUser?.course?.id } // <-- aquí va el ID
    },
    { label: `Evaluación #${props.evaluationUser.id}`, route: '' }
  ]"
/>


    <section class="section-data">
      <div class="container-fluid py-4">
        <div class="row mb-4">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <h1 class="h5 mb-0">
                <i class="bi bi-clipboard2-data me-2"></i>
                Detalles de la Evaluación de Usuario <strong> #{{ evaluationUser.evaluation.title }}</strong>
              </h1>
              <button type="button" class="btn btn-secondary" @click="goBack">
  <i class="bi bi-arrow-left me-2"></i>Volver
</button>
            </div>
          </div>
        </div>

        <!-- ====== GRID SUPERIOR (4 CARDS): Curso, Evaluación, Usuario, Entrega ====== -->
        <div class="row gy-4">
          <!-- Curso -->
          <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                Curso
                 
              </div>
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-4 text-center mb-3 mb-md-0">
                    <img
                      :src="course?.image_cover ? `/storage/${course.image_cover}` : '/images/default-cover.jpg'"
                      alt="Portada del curso"
                      class="img-fluid rounded shadow-sm"
                      style="max-height: 160px; object-fit: cover;"
                    />
                  </div>
                  <div class="col-md-8">
                    <h5 class="fw-bold mb-1">{{ course?.title || 'Sin título' }}</h5>
                    <div class="text-muted small mb-2">
                      ID: {{ course?.id || '-' }}
                    </div>
                    <p class="mb-0">{{ course?.description_short || 'Sin descripción disponible.' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
           <!-- Usuario -->
          <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-light fw-bold">Usuario</div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-2">
                    <div class="text-muted small">Nombre</div>
                    <div>{{ user?.name || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="text-muted small">Email</div>
                    <div>{{ user?.email || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="text-muted small">ID Usuario</div>
                    <div>{{ user?.id || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <div class="text-muted small">Registrado</div>
                    <div>{{ formatDate(user?.created_at) }}</div>
                  </div>

                  <div class="col-12 mt-2" v-if="evaluationUser.approved_user">
                    <div class="text-muted small">Aprobado por</div>
                    <div>{{ evaluationUser.approved_user.name }} (ID: {{ evaluationUser.approved_user.id }})</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

         
        </div><!-- /row top -->
      </div>
    </section>
<section class="section-data">
  <div class="container-fluid">
    <div class="row">
      <!-- Evaluación -->
      <div class="col-12 col-xl-6">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
            Evaluación
            <span v-if="eva?.status_label" class="badge" :class="{
              'bg-success': eva.status === 'APROVEED',
              'bg-danger': eva.status === 'NO APROVEED',
              'bg-info text-dark': eva.status === 'SEND',
              'bg-warning text-dark': eva.status === 'REVISION'
            }">
              {{ eva.status_label }}
            </span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <th class="w-40">Título</th>
                    <td>{{ eva?.title || 'Sin título' }}</td>
                  </tr>
                  <tr>
                    <th>Tipo</th>
                    <td>{{ eva?.type_label || '-' }} <span v-if="eva?.type_name" class="text-muted">({{ eva.type_name }})</span></td>
                  </tr>
                  <tr>
                    <th>Modalidad</th>
                    <td>{{ eva?.eva_type_label || '-' }} <span v-if="eva?.eva_type_name" class="text-muted">({{ eva.eva_type_name }})</span></td>
                  </tr>
                  <tr>
                    <th>Fecha de envío programada</th>
                    <td>{{ eva?.eva_send_date || '-' }}</td>
                  </tr>
                  <tr>
                    <th>Puntos mínimos</th>
                    <td>{{ passMin ?? '-' }}</td>
                  </tr>
                  <tr v-if="eva?.eva_video_file">
                    <th>Video de instrucciones</th>
                    <td>
                      <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        @click="openVideo(`/storage/${eva.eva_video_file}`, 'Video de instrucciones')"
                      >
                        <i class="bi bi-play-circle me-1"></i> Ver video
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Entrega (archivo, comentarios, estado) -->
      <div class="col-12 col-xl-6">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
            Entrega del Alumno
            <div class="d-flex align-items-center gap-2">
              <span class="badge" :class="statusBadgeClass">{{ status_label }}</span>
              <button class="btn btn-sm btn-outline-primary" @click="openStatusModal">
                <i class="bi bi-pencil"></i> Cambiar estado
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <th class="w-40">Fecha de envío</th>
                    <td>{{ formatDate(evaluationUser.created_at) }}</td>
                  </tr>
                  <tr>
                    <th>ID Evaluación Usuario</th>
                    <td>#{{ evaluationUser.id }}</td>
                  </tr>
                  <tr v-if="evaluationUser.comments">
                    <th>Comentarios del estudiante</th>
                    <td class="text-break">{{ evaluationUser.comments }}</td>
                  </tr>
                  <tr>
                    <th>Archivo adjunto</th>
                    <td>
                      <template v-if="evaluationUser.files">
                        <!-- Si es video: solo botón para abrir modal -->
                        <button
                          v-if="isVideo && fileUrl"
                          type="button"
                          class="btn btn-outline-primary btn-sm me-2"
                          @click="openVideo(fileUrl, 'Video enviado por el alumno')"
                        >
                          <i class="bi bi-play-circle me-1"></i> Ver video
                        </button>

                        <!-- Siempre ofrecer descarga -->
                        <a
                          v-if="fileUrl"
                          class="btn btn-outline-secondary btn-sm"
                          :href="fileUrl"
                          download
                          target="_blank"
                        >
                          <i class="bi bi-download me-1"></i> Descargar
                        </a>
                        <div class="small text-muted mt-1">
                          Archivo: {{ evaluationUser.files.split('/').pop() }}
                        </div>
                      </template>
                      <span v-else class="text-muted">Sin archivo adjunto</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div><!-- /.table-responsive -->
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


    <section class="section-data">
      <div class="container-fluid">
        <div class="row">
            <!-- ====== Resultados ====== -->
        <div class="card shadow-sm my-4">
          <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
            Resultados
            <div v-if="passed !== null">
              <span class="badge" :class="passed ? 'bg-success' : 'bg-danger'">
                {{ passed ? 'Cumple umbral' : 'No cumple umbral' }}
              </span>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <div class="text-muted small">Puntaje del alumno</div>
                <div class="fs-5 fw-bold">{{ userScore ?? '-' }}</div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small">Puntos mínimos (Evaluation)</div>
                <div class="fs-5 fw-bold">{{ passMin ?? '-' }}</div>
              </div>
              <div class="col-md-4" v-if="evaluationUser.data?.total_points">
                <div class="text-muted small">Puntos totales posibles</div>
                <div class="fs-5 fw-bold">{{ evaluationUser.data.total_points }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ====== Preguntas y respuestas ====== -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light fw-bold">Preguntas y respuestas del usuario</div>
          <div class="card-body">
            <div v-if="questions && questions.length">
              <div v-for="question in questions" :key="question.id" class="mb-4">
                <label class="form-label text-muted fw-semibold">
                  {{ question.order ? question.order + '. ' : '' }}{{ question.question }}
                </label>
                <div
                  class="border rounded px-3 py-2 bg-light"
                  :class="{
                    'border-success bg-success bg-opacity-10': question.type === 0 && userAnswers[question.id] != null && userAnswers[question.id] == question.response_option,
                    'border-danger bg-danger bg-opacity-10': question.type === 0 && userAnswers[question.id] != null && userAnswers[question.id] != question.response_option
                  }"
                >
                  <div v-if="question.type === 0 && question.options_json && question.options_json.length">
                    <ul class="list-group mb-2">
                      <li
                        v-for="opt in question.options_json"
                        :key="opt.value"
                        class="list-group-item d-flex align-items-center"
                        :class="{
                          'list-group-item-success': userAnswers[question.id] == opt.value && opt.value == question.response_option,
                          'list-group-item-danger': userAnswers[question.id] == opt.value && opt.value != question.response_option,
                          'fw-bold': userAnswers[question.id] == opt.value
                        }"
                      >
                        <i
                          v-if="userAnswers[question.id] == opt.value && opt.value == question.response_option"
                          class="bi bi-check-circle-fill text-success me-2"
                        ></i>
                        <i
                          v-else-if="userAnswers[question.id] == opt.value && opt.value != question.response_option"
                          class="bi bi-x-circle-fill text-danger me-2"
                        ></i>
                        <i
                          v-else-if="opt.value == question.response_option"
                          class="bi bi-check-circle text-success me-2"
                        ></i>
                        <span>{{ opt.label }}</span>
                        <span v-if="userAnswers[question.id] == opt.value" class="badge bg-info ms-2">Seleccionada</span>
                        <span v-if="opt.value == question.response_option" class="badge bg-success ms-2">Correcta</span>
                      </li>
                    </ul>
                  </div>
                  <div v-else>
                    <span v-if="userAnswers[question.id] !== undefined">
                      <template v-if="question.type === 0">
                        <strong>
                          {{
                            question.options_json?.find(opt => opt.value == userAnswers[question.id])?.label
                            || 'Respuesta: ' + userAnswers[question.id]
                          }}
                        </strong>
                      </template>
                      <template v-else>
                        {{ userAnswers[question.id] }}
                        <template v-if="question.response_text">
                          <span v-if="userAnswers[question.id].trim().toLowerCase() === question.response_text.trim().toLowerCase()" class="ms-2 text-success">
                            <i class="bi bi-check-circle"></i> Correcto
                          </span>
                          <span v-else class="ms-2 text-danger">
                            <i class="bi bi-x-circle"></i> Incorrecto
                          </span>
                          <div v-if="userAnswers[question.id].trim().toLowerCase() !== question.response_text.trim().toLowerCase()" class="small text-muted mt-1">
                            Respuesta correcta: <strong>{{ question.response_text }}</strong>
                          </div>
                        </template>
                      </template>
                    </span>
                    <span v-else class="text-muted fst-italic">Sin respuesta</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-muted">No se encontraron preguntas para esta evaluación.</div>
          </div>
        </div>

        </div>
      </div>
    </section>

  <!-- MODAL cambio de estado -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.4);">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Cambiar Estado de la Evaluación</h5>
                <button type="button" class="btn-close" @click="closeModal"></button>
              </div>
              <div class="modal-body">
                <FieldSelect
                  v-model="formStatus"
                  :options="props.statusOptions"
                  label="Nuevo estado"
                  id="status"
                  :required="true"
                />
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" @click="closeModal" :disabled="loading">Cancelar</button>
                <button class="btn btn-primary" @click="updateStatus" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  Guardar cambios
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- /MODAL -->
<!-- MODAL: Ver video -->
<div
  v-if="videoModal.show"
  class="modal fade show d-block"
  tabindex="-1"
  style="background: rgba(0,0,0,.4);"
>
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ videoModal.title || 'Video' }}</h5>
        <button type="button" class="btn-close" @click="closeVideo"></button>
      </div>
      <div class="modal-body">
        <video
          ref="videoRef"
          v-if="videoModal.src"
          :src="videoModal.src"
          controls
          style="width: 100%; border-radius: .5rem;"
        ></video>
        <div v-else class="text-muted">No se pudo cargar el video.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="closeVideo">Cerrar</button>
      </div>
    </div>
  </div>
</div>

  </AdminLayout>
</template>
