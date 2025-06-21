<template>
  <div class="form-group" :class="classObject">
    <label class="form-label">{{ label }} <strong v-if="required">*</strong></label>

    <div class="row">
      <div class="col-md-3 mb-2" v-for="item in items" :key="item.id">
        <div class="form-check">
          <input
            class="form-check-input"
            type="checkbox"
            :id="idPrefix + item.id"
            :value="item.id"
            :checked="modelValue.includes(item.id)"
            @change="onChange($event, item.id)"
          >
          <label class="form-check-label" :for="idPrefix + item.id">
            {{ item.label }}
          </label>
        </div>
      </div>
    </div>

    <div v-if="(showValidation && validationMessage) || formError" class="text-danger mt-2">
      {{ formError || validationMessage }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    modelValue: { type: Array, required: true }, // v-model is array for checkboxes
    items: { type: Array, required: true }, // [{ id, label }]
    label: { type: String, required: true },
    idPrefix: { type: String, default: "chk_" },
    required: { type: Boolean, default: false },
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
    onChange(event, id) {
      const newVal = [...this.modelValue];
      if (event.target.checked) {
        if (!newVal.includes(id)) newVal.push(id);
      } else {
        const idx = newVal.indexOf(id);
        if (idx !== -1) newVal.splice(idx, 1);
      }
      this.$emit("update:modelValue", newVal);
    }
  }
};
</script>
