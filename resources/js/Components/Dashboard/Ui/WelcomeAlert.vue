<!-- resources/js/Components/Dashboard/Ui/WelcomeAlert.vue -->
<script setup>
defineProps({
  message: { type: String, required: true }
})
</script>

<template>
  <Transition name="slide-fade">
    <div
      class="welcome-alert position-fixed bottom-0 end-0 m-3 p-3 rounded shadow"
      style="z-index: 1050; min-width: 280px; background-color: rgba(255,255,255,0.9);"
    >
      <i class="bi bi-balloon me-2 text-primary"></i>
      <span class="text-dark">{{ message }}</span>
    </div>
  </Transition>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.5s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
