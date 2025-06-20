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
    modelValue: { // Cambia "value" por "modelValue" para Vue 3
      type: Array, // Ahora es un array para soportar múltiples valores
      default: () => [], // Valor por defecto: array vacío
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
      type: String, // Cambiar el tipo a String
      default: "", // Valor por defecto: string vacío
    },
  },
  computed: {
    selectedValues: {
      get() {
        return this.modelValue; // Usa "modelValue" en lugar de "value"
      },
      set(newValues) {
        this.$emit("update:modelValue", newValues); // Emite el evento correcto para Vue 3
      },
    },
    isValid() {
      return this.selectedValues.length > 0; // Valida que al menos se haya seleccionado una opción
    },
  },
};
</script>

<style scoped>
/* Estilos adicionales si es necesario */
.form-control[multiple] {
  height: auto; /* Ajusta la altura para mostrar múltiples opciones */
}
</style>