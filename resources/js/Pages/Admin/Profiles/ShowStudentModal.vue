<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-person-vcard me-2"></i> Estudiante #{{ student.id }}
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <h5 class="fw-bold mb-3">Información de Usuario</h5>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Nombre de usuario</label>
              <p class="form-control-plaintext">{{ student.user.name }}</p>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Email</label>
              <p class="form-control-plaintext">{{ student.user.email }}</p>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label text-muted">Fecha de creación</label>
              <p class="form-control-plaintext">{{ formatDateTime(student.user.created_at) }}</p>
            </div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Información del Estudiante</h5>
          <div class="row">
            <div class="col-md-4 mb-3" v-for="(field, index) in studentFields" :key="index">
              <label class="form-label text-muted">{{ field.label }}</label>
              <p class="form-control-plaintext">{{ field.value }}</p>
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
  student: Object
});

const emit = defineEmits(['close']);

const formatDateTime = (date) => {
  return date ? new Date(date).toLocaleString() : '-';
};

const studentFields = computed(() => [
   
  { label: 'Nombre', value: props.student.firstname || '-' },
  { label: 'Apellido', value: props.student.lastname || '-' },
  { label: 'Teléfono', value: props.student.phone || '-' },
  { label: 'Talla de camiseta', value: props.student.shirt_size || '-' },
  { label: 'Dirección', value: props.student.address || '-' },
  { label: 'País', value: props.student.country || '-' }
]);
</script>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td,
.table th {
  vertical-align: middle;
}
</style>
