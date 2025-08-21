<template>
  <Teleport to="body">
    <div v-show="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="z-index: 9999 !important;">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-lightning-charge me-2"></i> Crear Activación
            </h5>
            <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
          </div>

          <!-- Paso 1: Formulario -->
          <form v-if="step === 'form'" @submit.prevent="submit" novalidate>
            <div class="modal-body position-relative">
              <div :class="{ 'blur-overlay': loading }">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="name"
                      label="Nombre"
                      v-model="form.name"
                      :required="true"
                      :showValidation="touched.name"
                      :formError="errors.name"
                      :validateFunction="validateName"
                      placeholder="Nombre completo"
                      @blur="() => handleBlur('name')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="email"
                      label="Email"
                      type="email"
                      v-model="form.email"
                      :required="true"
                      :showValidation="touched.email"
                      :formError="errors.email"
                      :validateFunction="validateEmail"
                      placeholder="correo@dominio.com"
                      @blur="() => handleBlur('email')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="phone"
                      label="Teléfono"
                      type="tel"
                      v-model="form.phone"
                      :required="false"
                      :showValidation="touched.phone"
                      :formError="errors.phone"
                      placeholder="+52 55 1234 5678"
                      @blur="() => handleBlur('phone')"
                    />
                  </div>

                  <!-- Nuevo: Selección de curso -->
                  <div class="col-md-6 mb-3">
                    <FieldSelect
                      id="course_id"
                      label="Curso"
                      v-model="form.course_id"
                      :required="true"
                      :showValidation="touched.course_id"
                      :formError="errors.course_id || courseError"
                      :options="courseOptions"
                      @blur="() => handleBlur('course_id')"
                    />
                  </div>
                </div>
              </div>

              <SpinnerOverlay v-if="loading" />
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="emit('close')">
                Cerrar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading || !isFormValid">
                <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                Crear activación
              </button>
            </div>
          </form>

          <!-- Paso 2: Confirmación -->
          <div v-else-if="step === 'done'">
            <div class="modal-body">
              <div class="alert alert-success">
                Activación creada. Se envió un correo con el enlace y el código.
              </div>

              <div class="card">
                <div class="card-body">
                  <p class="mb-2"><strong>Nombre:</strong> {{ form.name }}</p>
                  <p class="mb-2"><strong>Email:</strong> {{ form.email }}</p>
                  <p class="mb-2"><strong>Curso:</strong> {{ selectedCourseLabel }}</p>
                  <p class="mb-2 d-flex align-items-center gap-2">
                    <strong>Enlace:</strong>
                    <a :href="result.link" target="_blank" rel="noopener">{{ result.link }}</a>
                    <button class="btn btn-sm btn-outline-secondary" @click="copy(result.link)">Copiar</button>
                  </p>
                  <p class="mb-0 d-flex align-items-center gap-2">
                    <strong>Código:</strong> <span class="badge bg-dark">{{ result.code }}</span>
                    <button class="btn btn-sm btn-outline-secondary" @click="copy(result.code)">Copiar</button>
                  </p>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="emit('close')">
                Cerrar
              </button>
              <button type="button" class="btn btn-primary" @click="resetAndStay">
                Crear otra activación
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-backdrop fade show" @click="emit('close')" />
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'

import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

const props = defineProps({
  show: Boolean,
  courses: { type: Array, default: () => [] } // [{id, title}, ...]
})
const emit = defineEmits(['close'])

const form = ref({
  name: '',
  email: '',
  phone: '',
  course_id: '' // nuevo
})

const touched = ref({})
const errors  = ref({})
const loading = ref(false)
const step    = ref('form') // 'form' | 'done'
const result  = ref({ link: '', code: '', hash: '' })

const handleBlur = (field) => { touched.value[field] = true }

const validateName = () => {
  if (!form.value.name.trim()) return 'El nombre es obligatorio'
  if (form.value.name.trim().length < 3) return 'Debe tener al menos 3 caracteres'
  return ''
}

const validateEmail = () => {
  const email = form.value.email.trim()
  if (!email) return 'El email es obligatorio'
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email)) return 'Email inválido'
  return ''
}

const courseError = computed(() => {
  if (!touched.value.course_id) return ''
  if (!form.value.course_id) return 'Selecciona un curso'
  return ''
})

const isFormValid = computed(() =>
  !validateName() &&
  !validateEmail() &&
  !!form.value.course_id
)

const courseOptions = computed(() =>
  (props.courses || []).map(c => ({ value: c.id, label: c.title || c.name || `Curso #${c.id}` }))
)

const selectedCourseLabel = computed(() => {
  const found = courseOptions.value.find(o => o.value === form.value.course_id)
  return found ? found.label : ''
})

const submit = async () => {
  touched.value = { name: true, email: true, phone: true, course_id: true }
  errors.value = {}
  if (!isFormValid.value) return

  try {
    loading.value = true
    const { data } = await axios.post(route('admin.activations.store'), {
      name: form.value.name,
      email: form.value.email,
      phone: form.value.phone,
      course_id: form.value.course_id   // nuevo
    })

    result.value = data.activation
    step.value = 'done'
  } catch (e) {
    if (e.response?.data?.errors) {
      errors.value = e.response.data.errors
    } else if (e.response?.data?.message) {
      errors.value = { general: e.response.data.message }
    } else {
      errors.value = { general: 'Ocurrió un error. Intenta nuevamente.' }
    }
  } finally {
    loading.value = false
  }
}

const resetAndStay = () => {
  form.value = { name: '', email: '', phone: '', course_id: '' }
  touched.value = {}
  errors.value = {}
  step.value = 'form'
}

const copy = async (text) => {
  try { await navigator.clipboard.writeText(text) } catch {}
}
</script>

<style scoped>
.blur-overlay { filter: blur(3px); pointer-events: none; user-select: none; }
</style>
