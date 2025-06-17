<script setup>
import { ref } from 'vue';

const props = defineProps({
  show: Boolean,
  user: Object,
  loading: Boolean,

  // Textos personalizables
  title: {
    type: String,
    default: 'Confirmar eliminación'
  },
  confirmText: {
    type: String,
    default: 'Eliminar'
  },
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  confirmMessage: {
    type: String,
    default: '¿Estás seguro de que deseas eliminar al usuario'
  },
  warningText: {
    type: String,
    default: 'Esta acción no se puede deshacer.'
  }
});

const emit = defineEmits(['close', 'confirm']);

const handleClose = () => emit('close');
const handleConfirm = () => emit('confirm');
</script>

<template>
  <div
    class="modal fade"
    :class="{ 'show d-block': show }"
    tabindex="-1"
    v-if="show"
    id="modal-delete"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ title }}
          </h5>
          <button type="button" class="btn-close" @click="handleClose" />
        </div>
        <div class="modal-body">
          <p>
            {{ confirmMessage }}
            <strong>{{ user?.name }}</strong>?
          </p>
          <p class="small text-muted">{{ warningText }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="handleClose">{{ cancelText }}</button>
          <button class="btn btn-danger" @click="handleConfirm" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="handleClose" />
  </div>
</template>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

#modal-delete .modal-content {
  z-index: 9999 !important;
}
</style>
