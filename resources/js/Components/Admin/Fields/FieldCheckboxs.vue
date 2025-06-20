<template>
  <div class="form-group" :class="classObject">
    <label class="form-label">{{ label }} <strong v-if="required">*</strong></label>

    <!-- Checkbox seleccionar todos -->
    <div v-if="showSelectAll" class="form-check mb-2">
      <input
        class="form-check-input"
        type="checkbox"
        :id="selectAllId"
        :checked="allSelected"
        @change="toggleSelectAll"
      >
      <label class="form-check-label fw-bold" :for="selectAllId">
        {{ selectAllLabel }}
      </label>
    </div>

    <!-- Lista de checkboxes -->
    <div class="row">
      <div class="col-md-3 mb-2" v-for="item in items" :key="item.id">
        <div class="form-check">
          <input
            class="form-check-input"
            type="checkbox"
            :id="idPrefix + item.id"
            :value="item.id"
            :checked="modelValue.includes(item.id)"
            @change="onChange(item.id, $event)"
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
    modelValue: { type: Array, required: true }, // v-model
    items: { type: Array, required: true }, // [{ id, label }]
    label: { type: String, required: true },
    selectAllLabel: { type: String, default: "Seleccionar todos" },
    selectAllId: { type: String, default: "select_all_checkbox" },
    idPrefix: { type: String, default: "chk_" },
    required: { type: Boolean, default: false },
    showValidation: { type: Boolean, default: false },
    formError: { type: String, default: "" },
    validateFunction: { type: Function, default: null },
    classObject: { type: String, default: "" },
    showSelectAll: { type: Boolean, default: true },
  },
  emits: ["update:modelValue"],
  computed: {
    allSelected() {
      return this.modelValue.length === this.items.length;
    },
    validationMessage() {
      return this.validateFunction ? this.validateFunction() : "";
    }
  },
  methods: {
    onChange(id, event) {
      const newValue = [...this.modelValue];
      if (event.target.checked) {
        if (!newValue.includes(id)) {
          newValue.push(id);
        }
      } else {
        const index = newValue.indexOf(id);
        if (index !== -1) {
          newValue.splice(index, 1);
        }
      }
      this.$emit("update:modelValue", newValue);
    },
    toggleSelectAll(event) {
      const checked = event.target.checked;
      const newValue = checked ? this.items.map(i => i.id) : [];
      this.$emit("update:modelValue", newValue);
    }
  }
};
</script>
