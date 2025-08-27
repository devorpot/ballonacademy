<script setup>
import { computed } from 'vue'
import FieldText from '@/Components/Admin/Fields/FieldText.vue'

/**
 * Props esperadas:
 * - question: {
 *     id, order, question, type, points, options_json (array {value,label}),
 *     response_option (opcional para marcar correcta en múltiple)
 *   }
 * - modelValue: respuesta actual (string | number)
 * - disabled: boolean (opcional)
 */
const props = defineProps({
  question: { type: Object, required: true },
  modelValue: { type: [String, Number, null], default: null },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const isOpenAnswer = computed(() => parseInt(props.question?.type) === 1) // 1 = abierta, 0 = opción múltiple
const options = computed(() => Array.isArray(props.question?.options_json) ? props.question.options_json : [])

function choose(value) {
  if (props.disabled) return
  emit('update:modelValue', value)
}
</script>

<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="mb-2 fw-bold fs-5 d-flex align-items-center">
        <div class="me-2">
          {{ (question.order ? question.order + '. ' : '') + question.question }}
        </div>
        <span v-if="question.points" class="badge bg-secondary ms-2">Puntos: {{ question.points }}</span>
      </div>

      <!-- Respuesta abierta -->
      <FieldText
        v-if="isOpenAnswer"
        :id="`q-${question.id}`"
        :label="null"
        :disabled="disabled"
        v-model="(/** @type any */ ({})).value"  <!-- evita warning de v-model en FieldText -->
        :modelValue="modelValue"
        @update:modelValue="val => emit('update:modelValue', val)"
        placeholder="Escribe tu respuesta aquí"
      />

      <!-- Opción múltiple -->
      <ul v-else class="list-group mt-3 mb-2">
        <li
          v-for="opt in options"
          :key="opt.value"
          class="list-group-item list-group-item-action d-flex align-items-center justify-content-between"
          :class="{ active: modelValue == opt.value, disabled: disabled }"
          style="cursor:pointer; font-size:1.05rem;"
          @click="choose(opt.value)"
          @keydown.enter="choose(opt.value)"
          tabindex="0"
        >
          <span>{{ opt.label }}</span>
          <i v-if="modelValue == opt.value" class="bi bi-check-circle-fill"></i>
        </li>
      </ul>

      <div v-if="!isOpenAnswer" class="form-text">
        Selecciona una respuesta.
      </div>
    </div>
  </div>
</template>

<style scoped>
.list-group-item.disabled {
  pointer-events: none;
  opacity: .6;
}
</style>
