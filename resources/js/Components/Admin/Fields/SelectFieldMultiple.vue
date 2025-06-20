<template>
  <div class="form-group" :class="classObject">
    <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
    <select
      :id="id"
      v-model="selectedValues"
      :required="required"
      class="form-control"
      :class="{ 'is-invalid': showValidation && !isValid }"
      multiple
    >
      <option v-for="option in options" :value="option.value" :key="option.value">
        {{ option.label }}
      </option>
    </select>
    <div v-if="showValidation && !isValid" class="invalid-feedback">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    options: {
      type: Array,
      required: true,
    },
    modelValue: {
      type: Array,
      default: () => [],
    },
    required: {
      type: Boolean,
      default: false,
    },
    showValidation: {
      type: Boolean,
      default: false,
    },
    errorMessage: {
      type: String,
      default: "Por favor, seleccione al menos una opción.",
    },
    classObject: {
      type: String,
      default: "",
    },
  },
  computed: {
    selectedValues: {
      get() {
        return this.modelValue;
      },
      set(newValues) {
        this.$emit("update:modelValue", newValues);
      },
    },
    isValid() {
      return this.selectedValues.length > 0;
    },
  },
};
</script>

<style scoped>
.form-control[multiple] {
  height: auto;
}
</style>