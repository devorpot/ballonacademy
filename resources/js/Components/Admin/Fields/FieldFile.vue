<template>
  <div class="form-group" :class="classObject">
    <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
    <input
      :id="id"
      type="file"
      :accept="accept"
      class="form-control"
      :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
      @change="onFileChange"
      @blur="onBlur"
    />
    <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
      {{ formError || validationMessage }}
    </div>

    <div v-if="fileName" class="mt-2">
      <div class="d-flex align-items-center">
        <span class="me-2">{{ fileName }}</span>
        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeFile">
          <i class="bi bi-x-circle"></i> Eliminar archivo
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
    accept: { type: String, default: '.pdf,.doc,.docx' }  // formatos por defecto
  },
  emits: ['update:modelValue', 'blur'],
  data() {
    return {
      fileName: null
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
      if (file) {
        this.fileName = file.name;
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
      this.$emit('update:modelValue', null);
      const input = document.getElementById(this.id);
      if (input) input.value = '';
    }
  }
};
</script>
