<template>
  <transition name="toast-fade-slide">
    <div v-if="show" :class="['toast-container', typeClass]">
      <i :class="icon" class="me-2"></i>
      <span class="flex-grow-1">{{ message }}</span>
      <button class="btn-close ms-2" @click="close" aria-label="Cerrar"></button>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  message: { type: String, required: true },
  type: { type: String, default: 'success' }, // success, danger, info, warning
  duration: { type: Number, default: 3000 }
});

const emit = defineEmits(['hidden']);
const show = ref(true);

const typeClass = {
  success: 'toast-success',
  danger: 'toast-danger',
  info: 'toast-info',
  warning: 'toast-warning'
}[props.type] || 'toast-success';

const icon = {
  success: 'bi bi-check-circle-fill',
  danger: 'bi bi-exclamation-triangle-fill',
  info: 'bi bi-info-circle-fill',
  warning: 'bi bi-exclamation-circle-fill'
}[props.type] || 'bi bi-check-circle-fill';

const close = () => {
  show.value = false;
};

watch(show, (val) => {
  if (!val) {
    emit('hidden');
  }
});

onMounted(() => {
  setTimeout(() => {
    close();
  }, props.duration);
});
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 2rem;
  left: 50%;
  transform: translateX(-50%);
  background-color: #198754; /* Default success */
  color: #fff;
  padding: 0.75rem 1.25rem;
  border-radius: 0.5rem;
  box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
  z-index: 1055;
  display: flex;
  align-items: center;
  min-width: 260px;
  max-width: 90%;
}

.toast-success { background-color: #198754; }
.toast-danger { background-color: #dc3545; }
.toast-info { background-color: #0d6efd; }
.toast-warning { background-color: #ffc107; color: #212529; }

.btn-close {
  background: none;
  border: none;
  color: inherit;
  font-size: 1rem;
  cursor: pointer;
}

/* Animación combinada de fade + slide */
.toast-fade-slide-enter-active,
.toast-fade-slide-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.toast-fade-slide-enter-from {
  opacity: 0;
  transform: translate(-50%, -1rem);
}
.toast-fade-slide-leave-to {
  opacity: 0;
  transform: translate(-50%, -1rem);
}
</style>
