<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-journal-text me-2"></i> Curso: {{ course.title }}
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <h5 class="fw-bold mb-3">Información Básica</h5>
          <div class="row">
            <div class="col-md-4 mb-3" v-for="(field, index) in textFields" :key="index">
              <label class="form-label text-muted">{{ field.label }}</label>
              <p class="form-control-plaintext">{{ field.value }}</p>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label text-muted">Descripción</label>
            <div class="border rounded p-2 bg-light">
              {{ course.description || 'N/A' }}
            </div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Archivos</h5>
          <div class="d-flex flex-column align-items-center gap-3">
            <div v-if="course.image_cover" class="text-center">
              <label class="form-label text-muted">Imagen de Portada</label><br>
              <img
                :src="course.image_cover"
                alt="Imagen de Portada"
                class="img-thumbnail"
                style="width: 200px;"
              >
            </div>
            <div v-if="course.logo" class="text-center">
              <label class="form-label text-muted">Logo</label><br>
              <img
                :src="course.logo"
                alt="Logo"
                class="img-thumbnail"
                style="width: 150px;"
              >
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" @click="emit('close')">
            <i class="bi bi-x-lg me-1"></i> Cerrar
          </button>
        </div>
      </div>
    </div>

    <div class="modal-backdrop fade show" />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: Boolean,
  course: Object
});

const emit = defineEmits(['close']);

const textFields = computed(() => [
  {
    label: 'Nivel',
    value: props.course.level || 'N/A'
  },
  {
    label: 'Fecha de Inicio',
    value: props.course.date_start ? new Date(props.course.date_start).toLocaleDateString() : '-'
  },
  {
    label: 'Fecha de Fin',
    value: props.course.date_end ? new Date(props.course.date_end).toLocaleDateString() : '-'
  },
  {
    label: 'Fecha de Creación',
    value: props.course.created_at ? new Date(props.course.created_at).toLocaleString() : '-'
  }
]);
</script>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td, .table th {
  vertical-align: middle;
}
</style>
