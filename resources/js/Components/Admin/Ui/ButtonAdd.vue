<script setup>
const props = defineProps({
  label: {
    type: String,
    default: 'Botón'
  },
  icon: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'sm'
  }
});

const emit = defineEmits(['click']);
</script>

<template>
  <button
    :class="['btn', 'btn-primary', `btn-${size}`, 'rounded']"
    @click="$emit('click')"
  >
    <i v-if="icon" :class="[icon, 'me-2']"></i>
    {{ label }}
  </button>
</template>
