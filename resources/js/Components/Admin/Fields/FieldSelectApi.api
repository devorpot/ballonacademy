<template>
  <div class="form-group" :class="classObject">
    <div class="form-floating">
      <select
        :id="id"
        v-model="inputValue"
        class="form-control"
        :class="{ 'is-invalid': (showValidation && validationMessage) || formError }"
        @blur="onBlur"
      >
        <option value="">Seleccione una opción</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
      <label :for="id">{{ label }} <strong v-if="required">*</strong></label>
      <div v-if="(showValidation && validationMessage) || formError" class="invalid-feedback">
        {{ formError || validationMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  modelValue: [String, Number],
  required: { type: Boolean, default: false },
  placeholder: { type: String, default: '' },
  showValidation: { type: Boolean, default: false },
  formError: { type: String, default: '' },
  validateFunction: { type: Function, default: null },
  apiUrl: { type: String, required: true }, // 👈 URL para obtener opciones
  classObject: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'blur'])

const options = ref([])

const inputValue = computed({
  get() {
    return props.modelValue
  },
  set(val) {
    emit('update:modelValue', val)
  }
})

const validationMessage = computed(() => {
  return props.validateFunction ? props.validateFunction() : ''
})

const onBlur = () => {
  emit('blur')
}

onMounted(async () => {
  try {
    const response = await axios.get(props.apiUrl)
    options.value = response.data // espera un array de { value, label }
  } catch (error) {
    console.error('Error al cargar opciones desde API:', error)
  }
})
</script>
