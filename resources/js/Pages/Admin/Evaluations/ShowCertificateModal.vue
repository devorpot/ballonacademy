<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-card-text me-2"></i> Certificado #{{ certificate.id }}
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

          <div v-if="certificate.comments" class="mb-4">
            <label class="form-label text-muted">Comentarios</label>
            <div class="border rounded p-2 bg-light">{{ certificate.comments }}</div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Archivos</h5>
          <div class="d-flex flex-column align-items-center gap-3">
            <div v-if="certificate.photo" class="text-center">
              <label class="form-label text-muted">Foto</label><br>
              <img
                :src="`/storage/${certificate.photo}`"
                alt="Foto"
                class="img-thumbnail"
                style="width: 150px;"
              >
            </div>
            <div v-if="certificate.logo" class="text-center">
              <label class="form-label text-muted">Logo</label><br>
              <img
                :src="`/storage/${certificate.logo}`"
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
  certificate: Object
});

const emit = defineEmits(['close']);

const textFields = computed(() => [
  {
    label: 'Usuario',
    value: `${props.certificate.user.name} (${props.certificate.user.email})`
  },
  {
    label: 'Maestro',
    value: `${props.certificate.master.firstname} ${props.certificate.master.lastname}`
  },
  {
    label: 'Autorizado por',
    value: props.certificate.authorized_by
  },
  {
    label: 'Fecha de Inicio',
    value: props.certificate.date_start ? new Date(props.certificate.date_start).toLocaleDateString() : '-'
  },
  {
    label: 'Fecha de Fin',
    value: props.certificate.date_end ? new Date(props.certificate.date_end).toLocaleDateString() : '-'
  },
  {
    label: 'Fecha de Expedición',
    value: props.certificate.date_expedition ? new Date(props.certificate.date_expedition).toLocaleDateString() : '-'
  },
  {
    label: 'Fecha de Creación',
    value: props.certificate.created_at ? new Date(props.certificate.created_at).toLocaleString() : '-'
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
