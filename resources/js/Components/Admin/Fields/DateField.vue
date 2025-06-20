<template>
  <div class="form-group" :class="classObject">
    <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
    <input
      :id="id"
      type="date"
      v-model="selectedDate"
      :min="minDate"
      :required="required"
      class="form-control"
      :class="{ 'is-invalid': showValidation && !isValid }"
    />
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
    modelValue: { // Cambia "value" por "modelValue" para Vue 3
      type: String,
      default: "",
    },
    minDate: {
      type: String,
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
      default: "Por favor, seleccione una fecha válida.",
    },
    classObject: {
       type: String, // Cambiar el tipo a String
      default: "", // Valor por defecto: string vacío
    },
  },
  computed: {
    selectedDate: {
      get() {
        return this.modelValue; // Usa "modelValue" en lugar de "value"
      },
      set(newValue) {
        this.$emit("update:modelValue", newValue); // Emite el evento correcto para Vue 3
      },
    },
    isValid() {
      return !!this.selectedDate;
    },
  },
};
</script>