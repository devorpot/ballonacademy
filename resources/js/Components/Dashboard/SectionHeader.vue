<template>
  <div class="d-flex justify-content-between align-items-center section-header mb-3">
    <h3 class="h5 text-uppercase mb-0">{{ title }}</h3>
    <a :href="link" class="btn btn-sm btn-outline-primary" v-if="link">Ver más</a>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  link: {
    type: String,
    required: true
  }
});
</script>

<style scoped>
.section-header {
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 0.5rem;
}
</style>
