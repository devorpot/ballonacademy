<template>
  <div :class="['spinner-overlay', { fullscreen }]">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  fullscreen: { type: Boolean, default: false },
  small: { type: Boolean, default: false }
});
</script>

<style scoped>
.spinner-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.spinner-overlay.fullscreen {
  position: fixed;
  z-index: 2000;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
}

.spinner-border.small {
  width: 1rem;
  height: 1rem;
}
</style>
