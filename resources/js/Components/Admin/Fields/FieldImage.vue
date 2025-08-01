<template>
  <div class="form-group" :class="classObject">
    <label :for="id">
      {{ label }} <strong v-if="required">*</strong>
    </label>
    <input
      :id="id"
      type="file"
      accept="image/*"
      class="form-control"
      :multiple="multiple"
      :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
      @change="onFileChange"
      @blur="onBlur"
      ref="fileInput"
    />

    <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
      {{ formError || validationMessage }}
    </div>

    <!-- LISTA DE IMÁGENES -->
    <div v-if="previews.length" class="mt-3 image-list">
      <div v-for="(src, index) in previews" :key="index" class="image-item">
        <img
          :src="src"
          alt="Vista previa"
          class="img-thumbnail mb-1"
          @click="openLightbox(src)"
        >
        <button
          type="button"
          class="btn btn-sm btn-outline-danger w-100"
          @click="removeImage(index)"
        >
          <i class="bi bi-x-circle"></i> Quitar
        </button>
      </div>
    </div>

    <!-- LIGHTBOX -->
    <div v-if="lightboxSrc" class="lightbox" @click.self="closeLightbox">
      <div class="lightbox-content">
        <div class="lightbox-image-wrapper">
          <img :src="lightboxSrc" alt="Imagen ampliada">
          <button
            type="button"
            class="btn btn-light btn-close-lightbox"
            @click="closeLightbox"
          >
            <i class="bi bi-x-circle-fill"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  required: { type: Boolean, default: false },
  showValidation: { type: Boolean, default: false },
  formError: { type: String, default: '' },
  validateFunction: { type: Function, default: null },
  classObject: { type: String, default: '' },
  initialPreview: { type: [String, Array], default: null },
  multiple: { type: Boolean, default: false }
});

const emit = defineEmits([
  'update:modelValue',
  'update:keep',
  'blur',
  'image-removed'
]);

const fileInput = ref(null);
const previews = ref(
  Array.isArray(props.initialPreview)
    ? props.initialPreview
    : props.initialPreview
    ? [props.initialPreview]
    : []
);
const files = ref([]);
const lightboxSrc = ref(null);

const validationMessage = computed(() =>
  props.validateFunction ? props.validateFunction() : ''
);

// Actualiza previews si cambia initialPreview desde el padre
watch(
  () => props.initialPreview,
  (newVal) => {
    previews.value = Array.isArray(newVal)
      ? newVal
      : newVal
      ? [newVal]
      : [];
    if (previews.value.length > 0) {
      emit('update:keep', true);
    } else {
      emit('update:keep', false);
    }
    files.value = [];
    if (fileInput.value) fileInput.value.value = null;
  }
);

function onFileChange(event) {
  const selectedFiles = Array.from(event.target.files);
  const validFiles = selectedFiles.filter(file => file.type.startsWith('image/'));

  files.value = props.multiple ? validFiles : validFiles.slice(0, 1);
  previews.value = files.value.map(file => URL.createObjectURL(file));

  emit('update:modelValue', props.multiple ? files.value : files.value[0] || null);
  emit('update:keep', false);
}

function onBlur() {
  emit('blur');
}

function removeImage(index) {
  // Libera memoria
  if (previews.value[index]?.startsWith('blob:')) {
    URL.revokeObjectURL(previews.value[index]);
  }
  files.value.splice(index, 1);
  previews.value.splice(index, 1);

  // Limpia el input siempre
  if (fileInput.value) fileInput.value.value = null;

  if (files.value.length === 0 && previews.value.length === 0) {
    emit('update:modelValue', null);
    emit('update:keep', false);
    emit('image-removed');
  } else {
    emit('update:modelValue', props.multiple ? files.value : files.value[0]);
  }
}

function openLightbox(src) {
  lightboxSrc.value = src;
}
function closeLightbox() {
  lightboxSrc.value = null;
}
</script>

<style scoped>
.image-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.image-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.img-thumbnail {
  width: 120px;
  height: auto;
  cursor: pointer;
  border: 1px solid #ddd;
  transition: transform 0.2s ease;
}
.img-thumbnail:hover {
  transform: scale(1.05);
}
.lightbox {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}
.lightbox-content {
  position: relative;
  text-align: center;
}
.lightbox-image-wrapper {
  position: relative;
  display: inline-block;
}
.lightbox-image-wrapper img {
  max-width: 90vw;
  max-height: 90vh;
  border: 2px solid #fff;
}
.btn-close-lightbox {
  position: absolute;
  top: -10px;
  right: -10px;
  padding: 0.25rem 0.5rem;
  border-radius: 50%;
  font-size: 1.2rem;
}
</style>
