<template>
  <div class="form-group" :class="classObject">
    <div class="form-check form-switch">
      <input
        class="form-check-input"
        type="checkbox"
        role="switch"
        :id="id"
        :checked="modelValue"
        :disabled="disabled"
        @change="onChange"
      >
      <label class="form-check-label" :for="id">
        {{ label }} <strong v-if="required">*</strong>
      </label>
    </div>

    <div v-if="(showValidation && validationMessage) || formError" class="text-danger mt-2">
      {{ formError || validationMessage }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    modelValue: { type: Boolean, required: true }, // v-model
    id: { type: String, required: true },
    label: { type: String, required: true },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    showValidation: { type: Boolean, default: false },
    formError: { type: String, default: "" },
    validateFunction: { type: Function, default: null },
    classObject: { type: String, default: "" }
  },
  emits: ["update:modelValue"],
  computed: {
    validationMessage() {
      return this.validateFunction ? this.validateFunction() : "";
    }
  },
  methods: {
    onChange(event) {
      this.$emit("update:modelValue", event.target.checked);
    }
  }
};
</script>


<!-- 

<FieldSwitch
  id="activeSwitch"
  label="Activo"
  v-model="form.active"
  :required="false"
  :disabled="false"
  :showValidation="touched.active"
  :formError="form.errors.active"
  :validateFunction="() => validateField('active')"
/>

->