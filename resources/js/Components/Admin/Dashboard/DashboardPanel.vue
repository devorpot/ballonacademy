<template>
  <div class="dashboard-panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
      <h5 class="panel-title mb-0">{{ title }}</h5>
      <button v-if="showToggle" class="btn btn-sm btn-light">
        <i class="bi bi-chevron-double-down"></i>
      </button>
    </div>
    <div class="panel-body mt-2">
      <slot />
    </div>
    <div v-if="showFooter" class="panel-footer text-end mt-3">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  showFooter: { type: Boolean, default: false },
  showToggle: { type: Boolean, default: false }
});
</script>

<style scoped>
.dashboard-panel {
  background: #fff;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  height: 100%;
}
.panel-header {
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 0.25rem;
}
.panel-title {
  font-size: 1rem;
  font-weight: bold;
  color: #2c2c2c;
}
.panel-footer {
  border-top: 1px solid #e9ecef;
  padding-top: 0.5rem;
}
</style>
