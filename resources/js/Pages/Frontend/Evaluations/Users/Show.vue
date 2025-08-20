<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';

const props = defineProps({
  evaluationUser: Object,
  questions: Array,
  statusOptions: Array,
  status_label: String,
});

// Acceso a las respuestas del usuario
const userAnswers = props.evaluationUser.data?.answers || {};
const userScore = props.evaluationUser.data?.score ?? null;

// Modal control
const showModal = ref(false);
const formStatus = ref(props.evaluationUser.status);
const loading = ref(false);

function openStatusModal() {
  formStatus.value = props.evaluationUser.status;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  loading.value = false;
}

function updateStatus() {
  loading.value = true;
  router.put(
    route('admin.evaluation-users.update-status', props.evaluationUser.id),
    { status: formStatus.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
      },
      onError: () => {
        loading.value = false;
      }
    }
  );
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
        { label: 'Usuarios', route: 'admin.evaluation-users.index' },
        { label: `Evaluación #${evaluationUser.id}`, route: '' }
      ]"
    />

    <section class="section-data">
      <div class="container-fluid py-4">
        <div class="row mb-4">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <h1 class="h3 mb-0">
                <i class="bi bi-clipboard2-data me-2"></i>
                Detalles de la Evaluación de Usuario #{{ evaluationUser.id }}
              </h1>
              <Link :href="route('admin.evaluation-users.index')" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
              </Link>
            </div>
          </div>
        </div>

        <!-- Card: Información general -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
            Información Básica
            <button class="btn btn-sm btn-outline-primary" @click="openStatusModal">
              <i class="bi bi-pencil"></i> Cambiar estado
            </button>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label text-muted">Usuario</label>
                <p class="form-control-plaintext">{{ evaluationUser.user?.name || '-' }}</p>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label text-muted">Curso</label>
                <p class="form-control-plaintext">{{ evaluationUser.course?.title || '-' }}</p>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label text-muted">Estado</label>
                <p class="form-control-plaintext">
                  {{ status_label }}
                </p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label text-muted">Fecha de Envío</label>
                <p class="form-control-plaintext">
                  {{ evaluationUser.created_at ? new Date(evaluationUser.created_at).toLocaleString() : '-' }}
                </p>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label text-muted">Puntaje</label>
                <p class="form-control-plaintext">
                  {{ userScore !== null ? userScore : '-' }}
                </p>
              </div>
            </div>
            <div v-if="evaluationUser.comments" class="mb-2">
              <label class="form-label text-muted">Comentarios del estudiante</label>
              <div class="border rounded p-2 bg-light">{{ evaluationUser.comments }}</div>
            </div>
          </div>
        </div>

        <!-- Card: Respuestas -->
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
                  <!-- Opciones (option) -->
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
                        <span
                          v-if="userAnswers[question.id] == opt.value"
                          class="badge bg-info ms-2"
                          >Seleccionada</span
                        >
                        <span
                          v-if="opt.value == question.response_option"
                          class="badge bg-success ms-2"
                          >Correcta</span
                        >
                      </li>
                    </ul>
                  </div>
                  <!-- Abiertas y fallback -->
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

        <!-- Card: Archivos -->
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light fw-bold">Archivo adjunto</div>
          <div class="card-body text-center">
            <div v-if="evaluationUser.files">
              <video
                v-if="/\.(mp4|webm|ogg)$/i.test(evaluationUser.files)"
                controls
                style="max-width: 400px;"
                :src="`/storage/${evaluationUser.files}`"
              ></video>
              <div v-else-if="/\.(jpe?g|png|gif|webp)$/i.test(evaluationUser.files)">
                <img
                  :src="`/storage/${evaluationUser.files}`"
                  style="max-width: 400px; max-height: 320px; border-radius: 0.75rem; object-fit: contain;"
                  alt="Imagen adjunta"
                  class="shadow mb-2"
                />
                <br>
                <a
                  class="btn btn-outline-primary btn-sm mt-2"
                  :href="`/storage/${evaluationUser.files}`"
                  download
                  target="_blank"
                >
                  <i class="bi bi-download me-1"></i> Descargar archivo
                </a>
              </div>
              <div v-else>
                <i class="bi bi-file-earmark-arrow-down fs-2 text-secondary"></i>
                <div class="mt-2">
                  <a
                    class="btn btn-outline-primary btn-sm"
                    :href="`/storage/${evaluationUser.files}`"
                    download
                    target="_blank"
                  >
                    <i class="bi bi-download me-1"></i> Descargar archivo
                  </a>
                </div>
                <div class="small text-muted mt-1">
                  Archivo: {{ evaluationUser.files.split('/').pop() }}
                </div>
              </div>
              <div class="text-muted small mt-2">
                Puedes visualizar o descargar el archivo desde aquí.
              </div>
            </div>
            <div v-else class="text-muted mb-4">Sin archivo adjunto</div>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-4 gap-2">
          <Link :href="route('admin.evaluation-users.edit', evaluationUser.id)" class="btn btn-warning">
            <i class="bi bi-pencil-fill me-1"></i>Editar
          </Link>
          <Link :href="route('admin.evaluation-users.index')" class="btn btn-outline-secondary">
            <i class="bi bi-list me-1"></i>Listado
          </Link>
        </div>

        <!-- MODAL para cambiar estado -->
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

      </div>
    </section>
  </AdminLayout>
</template>
