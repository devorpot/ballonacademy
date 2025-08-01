<template>
  <div
    class="d-flex justify-content-center align-items-center bg-light border position-relative overflow-hidden"
    :style="containerStyle"
  >
    <img
      v-if="hasImage"
      :src="imageUrl"
      :alt="alt"
      class="img-fluid position-absolute top-50 start-50 translate-middle"
      :style="imageStyle"
      @error="onError"
      draggable="false"
    />
    <i
      v-else
      class="bi bi-image text-secondary position-absolute top-50 start-50 translate-middle"
      :style="iconStyle"
      aria-label="Imagen no disponible"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  src:    { type: String, default: '' },        // URL relativa o absoluta
  size:   { type: String, default: 'medium' },  // thumb | medium | large
  alt:    { type: String, default: 'Imagen' },
  rounded: { type: [String, Boolean], default: 'rounded-3' }
});

// Medidas por defecto
const sizeMap = {
  thumb:  { w: 100, h: 100 },
  medium: { w: 320, h: 320 },
  large:  { w: 600, h: 600 }
};

const showImage = ref(true);

const dims = computed(() => sizeMap[props.size] || sizeMap.medium);
const hasImage = computed(() => !!props.src && showImage.value);
const imageUrl = computed(() => props.src);

// Contenedor SIEMPRE cuadrado y centrado
const containerStyle = computed(() => ({
  width: `${dims.value.w}px`,
  height: `${dims.value.h}px`,
  minWidth: `${dims.value.w}px`,
  minHeight: `${dims.value.h}px`,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  background: '#f8f9fa',
  position: 'relative',
  overflow: 'hidden',
  borderRadius: typeof props.rounded === 'string' ? undefined : '0.5rem'
}));

// La imagen NO sale nunca del cuadro, se centra perfectamente
const imageStyle = computed(() => ({
  width: '100%',
  height: '100%',
  maxWidth: `${dims.value.w}px`,
  maxHeight: `${dims.value.h}px`,
  objectFit: 'contain',
  borderRadius: typeof props.rounded === 'string' ? undefined : '0.5rem',
  boxShadow: '0 0.15rem 0.75rem #00000011'
}));

const iconStyle = computed(() => ({
  fontSize: dims.value.w > 120 ? '3rem' : '2rem',
  opacity: 0.5
}));

function onError() {
  showImage.value = false;
}

watch(() => props.src, () => {
  showImage.value = true;
});
</script>
