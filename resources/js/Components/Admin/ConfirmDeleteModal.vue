<script setup>
const props = defineProps({
  show: Boolean,
  user: Object,
  loading: Boolean,
  title: { type: String, default: 'Confirmar eliminación' },
  confirmText: { type: String, default: 'Eliminar' },
  cancelText: { type: String, default: 'Cancelar' },
  confirmMessage: { type: String, default: '¿Estás seguro de que deseas eliminar al usuario' },
  warningText: { type: String, default: 'Esta acción no se puede deshacer.' }
});

const emit = defineEmits(['close', 'confirm']);

const handleClose = () => emit('close');
const handleConfirm = () => emit('confirm');
</script>

<template>
  <div v-if="show">
    <!-- Capa de fondo con blur -->
    <div class="modal-blur-layer"></div>

    <!-- Modal -->
    <div class="modal fade show d-block" tabindex="-1" id="modal-delete">
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
              {{ confirmMessage }} <strong>{{ user?.name }}</strong>?
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
    </div>
  </div>
</template>

<style scoped>
.modal-blur-layer {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.4); /* un poco de blanco semitransparente */
  backdrop-filter: blur(6px);
  z-index: 1040; /* un poco menos que el modal para que el modal esté arriba */
}

/* Asegúrate que el modal esté arriba de la capa */
.modal {
  z-index: 1050;
  pointer-events: auto;
}
</style>
