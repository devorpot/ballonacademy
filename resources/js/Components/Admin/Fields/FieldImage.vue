<template>
  <div class="form-group" :class="classObject">
    <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
    <input
      :id="id"
      type="file"
      accept="image/*"
      class="form-control"
      :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
      @change="onFileChange"
      @blur="onBlur"
      ref="fileInput"
    />
    <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
      {{ formError || validationMessage }}
    </div>

    <div v-if="preview" class="mt-2">
     <img :src="preview" v-if="preview" alt="Vista previa" class="img-thumbnail mb-2" style="max-height: 150px;">

      <div>
        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeImage">
          <i class="bi bi-x-circle"></i> Eliminar imagen
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: { type: String, required: true },
    label: { type: String, required: true },
    required: { type: Boolean, default: false },
    showValidation: { type: Boolean, default: false },
    formError: { type: String, default: '' },
    validateFunction: { type: Function, default: null },
    classObject: { type: String, default: '' },
      initialPreview: { type: String, default: null }
  },
  emits: ['update:modelValue', 'blur'],
  data() {
    return {
      preview: this.initialPreview
    };
  },
  computed: {
    validationMessage() {
      return this.validateFunction ? this.validateFunction() : '';
    }
  },
  methods: {
    onFileChange(event) {
      const file = event.target.files[0] || null;
      if (file && file.type.startsWith('image/')) {
        this.preview = URL.createObjectURL(file);
        this.$emit('update:modelValue', file);
      } else {
        this.preview = null;
        this.$emit('update:modelValue', null);
      }
    },
    onBlur() {
      this.$emit('blur');
    },
    removeImage() {
      this.preview = null;
      this.$emit('update:modelValue', null);
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = null; // Limpia el input
      }
    }
  }
};
</script>

<style scoped>
.img-thumbnail {
  max-width: 100%;
  height: auto;
}
</style>
