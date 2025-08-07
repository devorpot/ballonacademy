<template>
  <div class="form-group" :class="classObject">
    <label :for="id" class="form-label">
      {{ label }} <strong v-if="required">*</strong>
    </label>

    <!-- Input para subir nuevo video -->
    <input
      :id="id"
      type="file"
      class="form-control"
      :accept="accept"
      :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
      @change="onFileChange"
      @blur="onBlur"
      :disabled="readonly"
    />

    <!-- Errores -->
    <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
      {{ formError || validationMessage }}
    </div>

    <!-- Vista previa del nuevo video seleccionado -->
    <div v-if="videoUrl" class="mt-3">
      <label class="form-label text-muted">Vista previa del nuevo video:</label>
      <video
        controls
        class="w-100"
        style="max-height: 300px;"
        :src="videoUrl"
      />
      <button
        v-if="!readonly"
        type="button"
        class="btn btn-sm btn-outline-danger mt-2"
        @click="removeNewFile"
      >
        <i class="bi bi-x-circle"></i> Quitar video
      </button>
    </div>

    <!-- Vista previa del video actual -->
    <div v-else-if="initialPath && !hideInitial" class="mt-3">
      <label class="form-label text-muted">Video actual:</label>
      <video
        controls
        class="w-100"
        style="max-height: 300px;"
        :src="initialPath"
      />
      <button
        v-if="!readonly"
        type="button"
        class="btn btn-sm btn-outline-danger mt-2"
        @click="removeExistingVideo"
      >
        <i class="bi bi-x-circle"></i> Quitar video actual
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FieldVideo',
  props: {
    id: { type: String, required: true },
    label: { type: String, default: 'Subir video' },
    required: { type: Boolean, default: false },
    formError: { type: String, default: '' },
    showValidation: { type: Boolean, default: false },
    validateFunction: { type: Function, default: null },
    classObject: { type: String, default: '' },
    accept: {
      type: String,
      default: 'video/mp4,video/x-m4v,video/quicktime'
    },
    initialPath: { type: String, default: '' }, // Puede ser URL absoluta
    readonly: { type: Boolean, default: false }, // <-- añadido aquí
  },
  emits: ['update:modelValue', 'update:keep', 'blur'],
  data() {
    return {
      videoUrl: null,
      hideInitial: false
    };
  },
  computed: {
    validationMessage() {
      return this.validateFunction ? this.validateFunction() : '';
    }
  },
  methods: {
    onFileChange(event) {
      if (this.readonly) return; // Previene cambios si readonly
      const file = event.target.files[0];
      if (file && file.type.startsWith('video/')) {
        this.videoUrl = URL.createObjectURL(file);
        this.hideInitial = true;
        this.$emit('update:modelValue', file);
        this.$emit('update:keep', false);
      }
    },
    removeNewFile() {
      if (this.readonly) return; // Previene eliminar si readonly
      if (this.videoUrl) URL.revokeObjectURL(this.videoUrl);
      this.videoUrl = null;
      this.$emit('update:modelValue', null);
      this.$emit('update:keep', false);
      const input = document.getElementById(this.id);
      if (input) input.value = '';
    },
    removeExistingVideo() {
      if (this.readonly) return; // Previene eliminar si readonly
      this.hideInitial = true;
      this.$emit('update:modelValue', null);
      this.$emit('update:keep', false);
    },
    onBlur() {
      this.$emit('blur');
    }
  },
  beforeUnmount() {
    if (this.videoUrl) {
      URL.revokeObjectURL(this.videoUrl);
    }
  }
};
</script>
