<template>
  <div class="form-group" :class="classObject">
    <div class="form-floating">
      <input
        :id="id"
        type="text"
        v-model="inputValue"
        class="form-control"
        :readonly="readonly"
        autocomplete="off"
        :placeholder="placeholder"
        :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
        @blur="onBlur"
      />
      <label :for="id">
        {{ label }} <strong v-if="required">*</strong>
      </label>
      <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
        {{ formError || validationMessage }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: String,
    label: String,
    modelValue: String,
    placeholder: String,
    required: Boolean,
    showValidation: Boolean,
    formError: String,
    validateFunction: Function,
    classObject: String,
    readonly: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["update:modelValue", "blur"],
  computed: {
    inputValue: {
      get() {
        return this.modelValue;
      },
      set(val) {
        if (!this.readonly) {
          this.$emit("update:modelValue", val);
        }
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
