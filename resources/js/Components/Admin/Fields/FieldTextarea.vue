<template>
  <div class="form-group" :class="classObject">
    <div class="form-floating">
      <textarea
        :id="id"
        v-model="inputValue"
        class="form-control"
        :placeholder="placeholder"
        :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
        @blur="onBlur"
        rows="5"
        :readonly="readonly"
        :disabled="readonly"
      ></textarea>
      <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
      <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
        {{ formError || validationMessage }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: { type: String, required: true },
    label: { type: String, required: true },
    modelValue: { type: String, default: "" },
    placeholder: { type: String, default: "" },
    required: { type: Boolean, default: false },
    showValidation: { type: Boolean, default: false },
    formError: { type: String, default: "" },
    validateFunction: { type: Function, default: null },
    classObject: { type: String, default: "" },
    readonly: { type: Boolean, default: false }, // <-- Añadido aquí
  },
  emits: ["update:modelValue", "blur"],
  computed: {
    inputValue: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit("update:modelValue", val);
      }
    },
    validationMessage() {
      return this.validateFunction ? this.validateFunction() : "";
    }
  },
  methods: {
    onBlur() {
      this.$emit("blur");
    }
  }
};
</script>
