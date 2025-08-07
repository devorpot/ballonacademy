<template>
  <Head :title="`Detalles de la Evaluación #${evaluation.id}`" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
              <i class="bi bi-clipboard2-data me-2"></i>Detalles de la Evaluación #{{ evaluation.id }}
            </h1>
            <Link :href="route('admin.evaluations.index')" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Volver
            </Link>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Información Básica</h5>
          <div class="row">
            <div class="col-md-4 mb-3" v-for="(field, index) in textFields" :key="index">
              <label class="form-label text-muted">{{ field.label }}</label>
              <p class="form-control-plaintext">{{ field.value }}</p>
            </div>
          </div>

          <div v-if="evaluation.eva_comments" class="mb-4">
            <label class="form-label text-muted">Comentarios</label>
            <div class="border rounded p-2 bg-light">{{ evaluation.eva_comments }}</div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Archivo de Video</h5>
          <div v-if="evaluation.eva_video_file" class="text-center">
            <video controls style="max-width: 400px;" :src="`/storage/${evaluation.eva_video_file}`"></video>
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

          <div class="d-flex justify-content-end mt-4 gap-2">
            <Link :href="route('admin.evaluations.edit', evaluation.id)" class="btn btn-warning">
              <i class="bi bi-pencil-fill me-1"></i>Editar
            </Link>
            <Link :href="route('admin.evaluations.index')" class="btn btn-outline-secondary">
              <i class="bi bi-list me-1"></i>Listado
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { route } from 'ziggy-js';

const props = defineProps({
  evaluation: Object
});

const textFields = [
  {
    label: 'Usuario',
    value: props.evaluation.user
      ? `${props.evaluation.user.name} (${props.evaluation.user.email})`
      : ''
  },
  {
    label: 'Curso',
    value: props.evaluation.course ? props.evaluation.course.title : ''
  },
  {
    label: 'Fecha de Envío',
    value: props.evaluation.eva_send_date
      ? new Date(props.evaluation.eva_send_date).toLocaleDateString()
      : ''
  },
  {
    label: 'Fecha de Creación',
    value: props.evaluation.created_at
      ? new Date(props.evaluation.created_at).toLocaleString()
      : ''
  }
];
</script>
