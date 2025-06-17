<template>
  <div class="toast-container-top-center">
    <div
      ref="toastEl"
      class="toast text-white  border-0"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
    >
 
      <div class="d-flex">
        <div class="toast-body">
          {{ message }}
        </div>
        <button
          type="button"
          class="btn-close btn-close-white me-2 m-auto"
          data-bs-dismiss="toast"
          aria-label="Close"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
 

const props = defineProps({
  title: String,
  message: String,
  type: { type: String, default: 'success' },
  duration: { type: Number, default: 3000 }
});

const toastEl = ref(null);

onMounted(() => {
  const toast = new bootstrap.Toast(toastEl.value, {
     trigger: 'hover focus',
   delay: Number(props.duration)  // <-- fuerza número
  });
  toast.show();
});
</script>


<style>
.toast-container-top-center {
  position: fixed;
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1080;
}

.toast{
  background:#26A269FF;
}
</style>