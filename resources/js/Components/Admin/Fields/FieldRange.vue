<template>
  <div class="form-group" :class="classObject">
    <label :for="id" class="form-label">
      {{ label }} <strong v-if="required">*</strong>
    </label>
    <div class="d-flex align-items-center gap-2">
      <input
        type="range"
        class="form-range"
        :id="id"
        :min="min"
        :max="max"
        :step="step"
        :disabled="disabled"
        :value="modelValue"
        @input="onInput"
      >
      <output :for="id" :aria-hidden="true">{{ modelValue }}</output>
    </div>

    <div v-if="(showValidation && validationMessage) || formError" class="text-danger mt-2">
      {{ formError || validationMessage }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    modelValue: { type: [Number, String], required: true }, // v-model
    id: { type: String, required: true },
    label: { type: String, required: true },
    min: { type: [Number, String], default: 0 },
    max: { type: [Number, String], default: 100 },
    step: { type: [Number, String], default: 1 },
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
    onInput(event) {
      this.$emit("update:modelValue", event.target.value);
    }
  }
};
</script>


<!-- 
<FieldRange
  id="range1"
  label="Selecciona un valor"
  v-model="form.rangeValue"
  :min="0"
  :max="10"
  :step="0.5"
  :required="true"
  :showValidation="touched.rangeValue"
  :formError="form.errors.rangeValue"
  :validateFunction="() => validateField('rangeValue')"
/>
-->