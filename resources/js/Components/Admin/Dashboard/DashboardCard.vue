<template>
  <div class="dashboard-card text-center">
    <div class="title">{{ title }}</div>
    <div class="value">{{ value }}</div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  value: [String, Number]
});
</script>

<style scoped>
.dashboard-card {
  background: #fff;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  min-height: 100px;
}
.title {
  font-size: 0.85rem;
  color: #6c757d;
}
.value {
  font-size: 1.8rem;
  font-weight: bold;
}
</style>
