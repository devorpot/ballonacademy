<template>
  <div class="form-group" :class="classObject">
    <div class="form-floating">
      <input
        :id="id"
        type="number"
        class="form-control"
        inputmode="numeric"
        step="1"
        :min="min"
        :max="max"
        :placeholder="placeholder"
        :readonly="readonly"
        :disabled="readonly"
        :value="displayValue"
        :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
        @keydown="onKeydown"
        @input="onInput"
        @blur="onBlur"
        autocomplete="off"
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
  name: 'FieldNumber',
  props: {
    id: { type: String, required: true },
    label: { type: String, default: '' },
    modelValue: { type: [Number, String], default: '' },
    placeholder: { type: String, default: '' },
    required: { type: Boolean, default: false },
    showValidation: { type: Boolean, default: false },
    formError: { type: String, default: '' },
    validateFunction: { type: Function, default: null },
    classObject: { type: [String, Object, Array], default: '' },
    readonly: { type: Boolean, default: false },

    // Nuevos
    min: { type: Number, default: Number.NEGATIVE_INFINITY },
    max: { type: Number, default: Number.POSITIVE_INFINITY },
    // Nota: se usa 'default' porque así lo pediste.
    // En JS es palabra reservada en ciertos contextos, pero en props es válido.
    default: { type: Number, default: null }
  },
  emits: ['update:modelValue', 'blur'],
  computed: {
    displayValue() {
      // Mostrar cadena vacía si no hay valor para permitir limpiar el input
      return this.modelValue === '' || this.modelValue === null || typeof this.modelValue === 'undefined'
        ? ''
        : String(this.modelValue);
    },
    validationMessage() {
      if (this.validateFunction) return this.validateFunction();
      // Validación básica si no envías validateFunction
      const v = this.toIntOrNull(this.modelValue);
      if (v === null && this.required) return 'Este campo es requerido';
      if (v !== null) {
        if (v < this.min) return `El valor debe ser mayor o igual a ${this.min}`;
        if (v > this.max) return `El valor debe ser menor o igual a ${this.max}`;
      }
      return '';
    }
  },
  watch: {
    // Si el valor llega vacío y existe default, inicializa
    modelValue: {
      immediate: true,
      handler(val) {
        if ((val === '' || val === null || typeof val === 'undefined') && this.default !== null) {
          const init = this.clamp(this.default);
          this.$emit('update:modelValue', init);
        }
      }
    }
  },
  methods: {
    toIntOrNull(value) {
      if (value === '' || value === null || typeof value === 'undefined') return null;
      const n = parseInt(value, 10);
      return Number.isNaN(n) ? null : n;
    },
    clamp(n) {
      if (n < this.min) return this.min;
      if (n > this.max) return this.max;
      return n;
    },
    onKeydown(e) {
      // Permitir teclas de control
      const allowedControl = [
        'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'
      ];
      if (allowedControl.includes(e.key)) return;

      // Permitir signo menos solo si min < 0 y solo al inicio
      if (e.key === '-') {
        if (this.min < 0) {
          const el = e.target;
          const value = el.value;
          const selectionStart = el.selectionStart;
          const selectionEnd = el.selectionEnd;
          const willInsertAtStart = selectionStart === 0 && selectionEnd === 0;
          const alreadyHasMinus = value.startsWith('-');
          if (willInsertAtStart && !alreadyHasMinus) return; // permitir
        }
        e.preventDefault();
        return;
      }

      // Permitir solo dígitos
      if (!/^\d$/.test(e.key)) {
        e.preventDefault();
      }
    },
    onInput(e) {
      if (this.readonly) return;

      let raw = e.target.value;

      // Normaliza: permitir posible '-' al inicio si min < 0
      if (this.min < 0) {
        // Quitar todo excepto dígitos y un '-' al inicio
        raw = raw.replace(/[^\d-]/g, '');
        // Si hay más de un '-', o '-' no es el primero, normaliza
        raw = raw.replace(/(?!^)-/g, '');
      } else {
        // Quitar todo lo que no sea dígito
        raw = raw.replace(/\D+/g, '');
      }

      // Si quedó vacío, emite '' (permite borrar y dejar vacío temporalmente)
      if (raw === '' || raw === '-') {
        this.$emit('update:modelValue', '');
        return;
      }

      const num = parseInt(raw, 10);
      if (Number.isNaN(num)) {
        this.$emit('update:modelValue', '');
        return;
      }

      const clamped = this.clamp(num);
      // Actualiza el input si cambió por clamp
      if (String(clamped) !== raw) {
        e.target.value = String(clamped);
      }
      this.$emit('update:modelValue', clamped);
    },
    onBlur() {
      // Al perder foco: si quedó vacío, aplica default si existe; si no, deja vacío
      if (this.readonly) {
        this.$emit('blur');
        return;
      }

      let v = this.toIntOrNull(this.modelValue);

      if (v === null) {
        if (this.default !== null) {
          v = this.clamp(this.default);
          this.$emit('update:modelValue', v);
        } else {
          // Sin default: no forzamos, pero mantenemos vacío
          this.$emit('update:modelValue', '');
        }
      } else {
        // Asegurar clamp final
        const clamped = this.clamp(v);
        if (clamped !== v) this.$emit('update:modelValue', clamped);
      }

      this.$emit('blur');
    }
  }
};
</script>
