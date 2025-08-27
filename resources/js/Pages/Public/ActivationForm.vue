<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { route } from 'ziggy-js'

// Props que vienen del controlador show()
const props = defineProps({
  name: { type: String, required: true },
  email: { type: String, required: true },
  phone: { type: String, default: '' },
  hash:  { type: String, required: true }
})

const form = useForm({
  shirt_size: '',
  address: '',
  country: '',
  code: '',
  password: '',
  password_confirmation: '',
})

const touched = ref({
  shirt_size: false,
  address: false,
  country: false,
  code: false,
  password: false,
  password_confirmation: false,
})

// Catálogos
const shirtSizes = [
  { value: 'c', label: 'Chica' },
  { value: 'm', label: 'Mediana' },
  { value: 'l', label: 'Grande' },
  { value: 'xl', label: 'Extra Grande' },
]

// Sugerencia simple de países; ajusta o reemplaza por tu catálogo real
const countries = [
  { value: 'MX', label: 'México' },
  { value: 'US', label: 'Estados Unidos' },
  { value: 'AR', label: 'Argentina' },
  { value: 'CO', label: 'Colombia' },
  { value: 'CL', label: 'Chile' },
  { value: 'ES', label: 'España' },
]

const handleBlur = (field) => (touched.value[field] = true)

// Validaciones front
const validateRequired = (v) => (v ? '' : 'Este campo es obligatorio')

const codeError = computed(() => {
  if (!touched.value.code) return ''
  if (!form.code) return 'El código de activación es obligatorio'
  if (form.code.trim().length < 6) return 'El código debe tener 6 caracteres'
  return ''
})

const passwordError = computed(() => {
  if (!touched.value.password) return ''
  if (!form.password) return 'La contraseña es obligatoria'
  // Al menos 8, una letra, un número y un símbolo
  const regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/
  if (!regex.test(form.password)) {
    return 'Mínimo 8 caracteres, con letra, número y un símbolo'
  }
  return ''
})

const passwordConfirmError = computed(() => {
  if (!touched.value.password_confirmation) return ''
  if (!form.password_confirmation) return 'Repite la contraseña'
  if (form.password_confirmation !== form.password) return 'Las contraseñas no coinciden'
  return ''
})

const shirtSizeError = computed(() => {
  if (!touched.value.shirt_size) return ''
  return validateRequired(form.shirt_size)
})

const addressError = computed(() => {
  if (!touched.value.address) return ''
  return validateRequired(form.address?.trim())
})

const countryError = computed(() => {
  if (!touched.value.country) return ''
  return validateRequired(form.country)
})

const isFormValid = computed(() =>
  !shirtSizeError.value &&
  !addressError.value &&
  !countryError.value &&
  !codeError.value &&
  !passwordError.value &&
  !passwordConfirmError.value
)

// Generador de password aleatorio
function secureRandomInt(max) {
  if (window.crypto && window.crypto.getRandomValues) {
    const array = new Uint32Array(1)
    window.crypto.getRandomValues(array)
    return array[0] % max
  }
  return Math.floor(Math.random() * max)
}

function shuffleString(str) {
  const arr = str.split('')
  for (let i = arr.length - 1; i > 0; i--) {
    const j = secureRandomInt(i + 1)
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  return arr.join('')
}

function generatePassword(length = 12) {
  const L = Math.max(8, length) // fuerza mínimo 8
  const letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
  const numbers = '0123456789'
  const symbols = '!@#$%^&*()_+[]{}<>?-=~.,;:/|'

  // Garantizar 1 de cada categoría
  let pass = ''
  pass += letters[secureRandomInt(letters.length)]
  pass += numbers[secureRandomInt(numbers.length)]
  pass += symbols[secureRandomInt(symbols.length)]

  const all = letters + numbers + symbols
  for (let i = pass.length; i < L; i++) {
    pass += all[secureRandomInt(all.length)]
  }

  pass = shuffleString(pass)

  form.password = pass
  form.password_confirmation = pass

  // Dispara validaciones visuales
  touched.value.password = true
  touched.value.password_confirmation = true
}


// Visibilidad y copiado de password
// Visibilidad y copiado de password
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const copied = ref(false)

// Ref al input de password para fallback
const passwordInput = ref(null)

async function copyPassword() {
  if (!form.password) return
  try {
    // Método moderno: requiere HTTPS o localhost
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(form.password)
      copied.value = true
    } else {
      // Fallback 1: seleccionar el input directamente
      const el = passwordInput.value
      if (el) {
        const prevType = el.type
        // Asegura que se pueda seleccionar el texto visible
        el.type = 'text'
        el.focus()
        el.select()
        el.setSelectionRange(0, 99999) // iOS
        document.execCommand('copy')
        // Limpieza
        el.setSelectionRange(0, 0)
        el.blur()
        el.type = prevType
        copied.value = true
      } else {
        // Fallback 2: textarea temporal
        const ta = document.createElement('textarea')
        ta.value = form.password
        ta.style.position = 'fixed'
        ta.style.left = '-9999px'
        document.body.appendChild(ta)
        ta.focus()
        ta.select()
        document.execCommand('copy')
        document.body.removeChild(ta)
        copied.value = true
      }
    }
  } catch (e) {
    // Si todo falla, avisa para copia manual
    alert('No se pudo copiar automáticamente. Selecciona y copia manualmente.')
  } finally {
    if (copied.value) {
      setTimeout(() => { copied.value = false }, 1500)
    }
  }
}


const submit = () => {
  Object.keys(touched.value).forEach(k => (touched.value[k] = true))
  if (!isFormValid.value) return
  form.post(route('public.register.student.store', { hash: props.hash }), {
    preserveScroll: true,
    onFinish: () => {
      // Limpia contraseñas del estado
      form.reset('password', 'password_confirmation')
    }
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Completar registro" />

    <section class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-header">
              <h1 class="h5 mb-0">Completar registro</h1>
            </div>

            <div class="card-body">
              <!-- Datos prellenados (solo lectura) -->
              <div class="mb-3">
                <label class="form-label">Nombre Completo</label>
                <input class="form-control" :value="props.name" readonly />
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" :value="props.email" readonly />
              </div>
              <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input class="form-control" :value="props.phone"   />
              </div>

              <!-- Campos requeridos -->
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="shirt_size" class="form-label">Talla de Playera</label>
                  <select
                    id="shirt_size"
                    class="form-control"
                    v-model="form.shirt_size"
                    :class="{ 'is-invalid': shirtSizeError || form.errors.shirt_size }"
                    @blur="handleBlur('shirt_size')"
                  >
                    <option value="" disabled>Selecciona una opción</option>
                    <option v-for="opt in shirtSizes" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                  <div class="invalid-feedback" v-if="shirtSizeError">{{ shirtSizeError }}</div>
                  <div class="invalid-feedback" v-else-if="form.errors.shirt_size">{{ form.errors.shirt_size }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="country" class="form-label">País</label>
                  <select
                    id="country"
                    class="form-control"
                    v-model="form.country"
                    :class="{ 'is-invalid': countryError || form.errors.country }"
                    @blur="handleBlur('country')"
                  >
                    <option value="" disabled>Selecciona tu país</option>
                    <option v-for="c in countries" :key="c.value" :value="c.value">{{ c.label }}</option>
                  </select>
                  <div class="invalid-feedback" v-if="countryError">{{ countryError }}</div>
                  <div class="invalid-feedback" v-else-if="form.errors.country">{{ form.errors.country }}</div>
                </div>
              </div>

              <div class="mb-3">
                <label for="address" class="form-label">Dirección completa</label>
                <textarea
                  id="address"
                  class="form-control"
                  rows="3"
                  v-model="form.address"
                  :class="{ 'is-invalid': addressError || form.errors.address }"
                  @blur="handleBlur('address')"
                  placeholder="Calle, número, colonia, ciudad, estado, CP"
                />
                <div class="invalid-feedback" v-if="addressError">{{ addressError }}</div>
                <div class="invalid-feedback" v-else-if="form.errors.address">{{ form.errors.address }}</div>
              </div>

              <div class="mb-3">
                <label for="code" class="form-label">Código de Activación</label>
                <input
                  id="code"
                  class="form-control"
                  v-model="form.code"
                  maxlength="6"
                  placeholder="Ingresa el código de 6 caracteres"
                  :class="{ 'is-invalid': codeError || form.errors.code }"
                  @blur="handleBlur('code')"
                />
                <div class="invalid-feedback" v-if="codeError">{{ codeError }}</div>
                <div class="invalid-feedback" v-else-if="form.errors.code">{{ form.errors.code }}</div>
              </div>

              <div class="row">
                 <div class="col-md-6 mb-3">
  <label for="password" class="form-label d-flex justify-content-between align-items-center">
    <span>Password</span>
    <div class="btn-group btn-group-sm">
      <button type="button" class="btn btn-outline-secondary" @click="generatePassword(12)">Generar</button>
      <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword">
        <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
      </button>
      <button type="button" class="btn btn-outline-secondary" @click="copyPassword">
        <i class="bi bi-clipboard"></i>
      </button>
    </div>
  </label>

  <div class="input-group">
    <input
      id="password"
      :type="showPassword ? 'text' : 'password'"
      class="form-control"
      v-model="form.password"
      :class="{ 'is-invalid': passwordError || form.errors.password }"
      @blur="handleBlur('password')"
      placeholder="Mín. 8, con letra, número y símbolo"
    />
    <span class="input-group-text" v-if="copied">Copiado</span>
  </div>

  <div class="invalid-feedback d-block" v-if="passwordError">{{ passwordError }}</div>
  <div class="invalid-feedback d-block" v-else-if="form.errors.password">{{ form.errors.password }}</div>
  <small class="text-muted">Mínimo 8 caracteres, con letra, número y un símbolo</small>
</div>

                <div class="col-md-6 mb-3">
                  <label for="password_confirmation" class="form-label">Repite el password</label>
                  <input
                    id="password_confirmation"
                    type="password"
                    class="form-control"
                    v-model="form.password_confirmation"
                    :class="{ 'is-invalid': passwordConfirmError || form.errors.password_confirmation }"
                    @blur="handleBlur('password_confirmation')"
                  />
                  <div class="invalid-feedback" v-if="passwordConfirmError">{{ passwordConfirmError }}</div>
                  <div class="invalid-feedback" v-else-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</div>
                </div>
              </div>


            </div>


            <div class="card-footer d-flex justify-content-end gap-2">
              <a href="/" class="btn btn-outline-secondary">Cancelar</a>
              <button class="btn btn-primary" :disabled="form.processing || !isFormValid" @click="submit">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" />
                Completar registro
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>

<style scoped>
/* puedes mover estilos a tu hoja global si lo prefieres */
</style>
