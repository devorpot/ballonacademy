<template>
  <div class="form-group" :class="classObject">
    <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
    <input
      :id="id"
      type="tel"
      v-model="inputValue"
      :required="required"
      class="form-control"
      :class="{ 'is-invalid': showValidation && !isValid }"
      :placeholder="placeholder"
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
    modelValue: {
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
      default: "Por favor, ingrese un número de teléfono válido.",
    },
    placeholder: {
      type: String,
      default: "",
    },
    classObject: {
      type: String,
      default: "",
    },
  },
  computed: {
    inputValue: {
      get() {
        return this.modelValue;
      },
      set(newValue) {
        this.$emit("update:modelValue", newValue);
      },
    },
    isValid() {
      return !!this.inputValue;
    },
  },
};
</script>