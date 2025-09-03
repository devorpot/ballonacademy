<script setup>
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
  label: {
    type: String,
    default: 'Regresar'
  },
  icon: {
    type: String,
    default: ''
  },
  href: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'sm'
  },
  color: {
    type: String,
    default: 'secondary'
  }
});

const emit = defineEmits(['click']);

const handleClick = () => {
  if (props.href) {
    Inertia.visit(props.href);
  } else {
    emit('click');
  }
};
</script>

<template>
  <button
  class=""
    :class="['btn', `btn-${color}`,  ]"
    @click="handleClick"
  >
    <i v-if="icon" :class="[icon, 'me-2']"></i>
    {{ label }}
  </button>
</template>
