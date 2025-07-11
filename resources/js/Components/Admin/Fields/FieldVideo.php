<template>
  <div class="form-group" :class="classObject">
    <label :for="id" class="form-label">
      {{ label }} <strong v-if="required">*</strong>
    </label>

    <input
      :id="id"
      type="file"
      class="form-control"
      :accept="accept"
      :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
      @change="onFileChange"
      @blur="onBlur"
    />

    <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
      {{ formError || validationMessage }}
    </div>

    <!-- Vista previa del video -->
    <div v-if="videoUrl" class="mt-3">
      <video
        class="w-100"
        style="max-height: 300px;"
        controls
        :src="videoUrl"
      >
        Tu navegador no soporta la vista previa de video.
      </video>

      <div class="mt-2 d-flex align-items-center">
        <span class="me-2 text-truncate">{{ fileName }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeFile">
          <i class="bi bi-x-circle"></i> Eliminar video
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FieldVideo',
  props: {
    id: { type: String, required: true },
    label: { type: String, required: true },
    required: { type: Boolean, default: false },
    showValidation: { type: Boolean, default: false },
    formError: { type: String, default: '' },
    validateFunction: { type: Function, default: null },
    classObject: { type: String, default: '' },
    accept: {
      type: String,
      default: 'video/mp4,video/x-m4v,video/quicktime'
    }
  },
  emits: ['update:modelValue', 'blur'],
  data() {
    return {
      fileName: null,
      videoUrl: null
    };
  },
  computed: {
    validationMessage() {
      return this.validateFunction ? this.validateFunction() : '';
    }
  },
  methods: {
    onFileChange(event) {
      const file = event.target.files[0];

      if (file && file.type.startsWith('video/')) {
        this.fileName = file.name;
        this.videoUrl = URL.createObjectURL(file);
        this.$emit('update:modelValue', file);
      } else {
        this.removeFile();
      }
    },
    onBlur() {
      this.$emit('blur');
    },
    removeFile() {
      this.fileName = null;
      if (this.videoUrl) URL.revokeObjectURL(this.videoUrl);
      this.videoUrl = null;
      this.$emit('update:modelValue', null);
      const input = document.getElementById(this.id);
      if (input) input.value = '';
    }
  },
  beforeUnmount() {
    if (this.videoUrl) {
      URL.revokeObjectURL(this.videoUrl);
    }
  }
};
</script>
