<template>
  <div class="form-group" :class="classObject">
    <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
    <select
      :id="id"
      v-model="selectedValue"
      :required="required"
      class="form-control"
      :class="{ 'is-invalid': showValidation && !isValid }"
    >
      <option value="">Seleccione</option>
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
    modelValue: { // Cambia "value" por "modelValue" para Vue 3
      type: [String, Number],
      default: "",
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
      default: "Por favor, seleccione una opción.",
    },
    classObject: {
       type: String, // Cambiar el tipo a String
      default: "", // Valor por defecto: string vacío
    },
  },
  computed: {
    selectedValue: {
      get() {
        return this.modelValue; // Usa "modelValue" en lugar de "value"
      },
      set(newValue) {
        this.$emit("update:modelValue", newValue); // Emite el evento correcto para Vue 3
      },
    },
    isValid() {
      return !!this.selectedValue;
    },
  },
};
</script>