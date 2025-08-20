<template>
  <Head :title="`Detalles de la Evaluación #${evaluation.id}`" />
   
  <AdminLayout>
<Breadcrumbs
  username="admin"
  :breadcrumbs="[
    { label: 'Dashboard', route: route('admin.dashboard') },
    { label: 'Evaluaciones', route: route('admin.evaluations.index') },
    { label: 'Nueva evaluación', route: '' }
  ]"
/>

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
              <i class="bi bi-clipboard2-data me-2"></i>Detalles de la Evaluación #{{ evaluation.id }}
            </h1>
            <div class="d-flex gap-2">
              <Link
                v-if="evaluation?.eva_type == 1"
                :href="route('admin.evaluation-questions.index', evaluation.id)"
                class="btn btn-outline-info"
                title="Preguntas de evaluación"
              >
                <i class="bi bi-question-circle me-1"></i> Preguntas
              </Link>
              <Link :href="route('admin.evaluations.edit', evaluation.id)" class="btn btn-warning">
                <i class="bi bi-pencil-fill me-1"></i>Editar
              </Link>
              <Link :href="route('admin.evaluations.index')" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Información básica</h5>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Título</label>
              <p class="form-control-plaintext">{{ orDash(evaluation.title) }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Curso</label>
              <p class="form-control-plaintext">{{ evaluation.course?.title ?? '—' }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Ámbito</label>
              <p class="form-control-plaintext">
                <span class="badge bg-primary-subtle text-primary border">{{ typeLabel }}</span>
              </p>
            </div>

            <div class="col-md-4 mb-3" v-if="evaluation.video || typeValue === 2">
              <label class="form-label text-muted">Video / Lección</label>
              <p class="form-control-plaintext">{{ evaluation.video?.title ?? '—' }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Tipo de evaluación</label>
              <p class="form-control-plaintext">
                <span class="badge bg-info-subtle text-info border">{{ evaTypeLabel }}</span>
              </p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Puntaje mínimo</label>
              <p class="form-control-plaintext">{{ evaluation.points_min ?? '—' }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Estatus</label>
              <p class="form-control-plaintext">
                <span :class="statusBadge.class">{{ statusBadge.text }}</span>
              </p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Profesor asignado</label>
              <p class="form-control-plaintext">{{ evaluation.teacher?.name ?? '—' }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Creado por</label>
              <p class="form-control-plaintext">{{ evaluation.user?.name ?? '—' }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Fecha de envío</label>
              <p class="form-control-plaintext">{{ fmtDate(evaluation.eva_send_date) }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Fecha de evaluación</label>
              <p class="form-control-plaintext">{{ fmtDate(evaluation.date_evaluation) }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Creado</label>
              <p class="form-control-plaintext">{{ fmtDateTime(evaluation.created_at) }}</p>
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Actualizado</label>
              <p class="form-control-plaintext">{{ fmtDateTime(evaluation.updated_at) }}</p>
            </div>
          </div>

          <div v-if="evaluation.eva_comments" class="mb-4">
            <label class="form-label text-muted">Comentarios</label>
            <div class="border rounded p-2 bg-light">{{ evaluation.eva_comments }}</div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Archivo de video</h5>
          <div v-if="evaluation.eva_video_file" class="text-center">
            <video
              controls
              style="max-width: 520px;"
              :src="`/storage/${evaluation.eva_video_file}`"
            ></video>
            <div class="text-muted small mt-2">Puedes reproducir o descargar el video desde aquí.</div>
            <a
              class="btn btn-outline-primary btn-sm mt-2"
              :href="`/storage/${evaluation.eva_video_file}`"
              download
              target="_blank"
            >
              <i class="bi bi-download me-1"></i> Descargar video
            </a>
          </div>
          <div v-else class="text-muted mb-4">Sin archivo de video adjunto</div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { route } from 'ziggy-js';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';

const props = defineProps({
  evaluation: { type: Object, required: true }
});

// Helpers de fecha
const fmtDate = (d) => {
  if (!d) return '—';
  const date = new Date(d);
  return isNaN(date) ? '—' : date.toLocaleDateString();
};
const fmtDateTime = (d) => {
  if (!d) return '—';
  const date = new Date(d);
  return isNaN(date) ? '—' : date.toLocaleString();
};
const orDash = (v) => (v ?? '') === '' ? '—' : v;

// Fallbacks de enums si aún recibes 1/2 o strings crudos
const TYPE_MAP = { 1: 'Curso', 2: 'Video' };
const EVA_TYPE_MAP = { 1: 'Cuestionario', 2: 'Video' };
const STATUS_MAP = {
  'SEND': 'Enviado',
  'REVISION': 'En revisión',
  'APROVEED': 'Aprobado',
  'NO APROVEED': 'No aprobado'
};

const rawType = props.evaluation.type;            // puede venir 1/2 o enum serializado
const rawEvaType = props.evaluation.eva_type;     // puede venir 1/2 o enum serializado
const rawStatus = props.evaluation.status;        // puede venir string/enum/value

// Si backend ya expone *_label via $appends, úsalo primero
const typeLabel =
  props.evaluation.type_label ??
  (typeof rawType === 'number' ? TYPE_MAP[rawType] : (TYPE_MAP[Number(rawType)] || rawType)) ??
  '—';

const evaTypeLabel =
  props.evaluation.eva_type_label ??
  (typeof rawEvaType === 'number' ? EVA_TYPE_MAP[rawEvaType] : (EVA_TYPE_MAP[Number(rawEvaType)] || rawEvaType)) ??
  '—';

// Para lógicas condicionales (ej. mostrar bloque de video)
const typeValue = Number(props.evaluation.type ?? 0);

// Badge de estatus
const statusText =
  props.evaluation.status_label ??
  STATUS_MAP[String(rawStatus)] ??
  String(rawStatus ?? '—');

const statusBadge = (() => {
  const base = 'badge border';
  switch (String(rawStatus)) {
    case 'APROVEED':
      return { text: statusText, class: `${base} bg-success-subtle text-success` };
    case 'REVISION':
      return { text: statusText, class: `${base} bg-warning-subtle text-warning` };
    case 'NO APROVEED':
      return { text: statusText, class: `${base} bg-danger-subtle text-danger` };
    case 'SEND':
    default:
      return { text: statusText, class: `${base} bg-secondary-subtle text-secondary` };
  }
})();
</script>

<style scoped>
.badge.border {
  border: 1px solid rgba(0,0,0,.1);
}
</style>
