<script setup>
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
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
  code: '',
  nickname: '',
  password: '',
  password_confirmation: '',
})

const touched = ref({
  code: false,
  nickname: false,
  password: false,
  password_confirmation: false,
})

const handleBlur = (field) => (touched.value[field] = true)

// Validaciones front
const codeError = computed(() => {
  if (!touched.value.code) return ''
  if (!form.code) return 'El código de activación es obligatorio'
  if (form.code.trim().length < 6) return 'El código debe tener 6 caracteres'
  return ''
})

const nicknameError = computed(() => {
  if (!touched.value.nickname) return ''
  if (!form.nickname) return 'El nickname es obligatorio'
  if (form.nickname.trim().length < 3) return 'Debe tener al menos 3 caracteres'
  return ''
})

const passwordError = computed(() => {
  if (!touched.value.password) return ''
  if (!form.password) return 'La contraseña es obligatoria'
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

const isFormValid = computed(() =>
  !codeError.value &&
  !nicknameError.value &&
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
  const L = Math.max(8, length)
  const letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
  const numbers = '0123456789'
  const symbols = '!@#$%^&*()_+[]{}<>?-=~.,;:/|'

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

  touched.value.password = true
  touched.value.password_confirmation = true
}

// Visibilidad y copiado de password
const showPassword = ref(false)
const copied = ref(false)
const passwordInput = ref(null)

async function copyPassword() {
  if (!form.password) return
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(form.password)
      copied.value = true
    } else {
      const el = passwordInput.value
      if (el) {
        const prevType = el.type
        el.type = 'text'
        el.focus()
        el.select()
        el.setSelectionRange(0, 99999)
        document.execCommand('copy')
        el.setSelectionRange(0, 0)
        el.blur()
        el.type = prevType
        copied.value = true
      } else {
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
        <div class="col-lg-8 text-center">
          <div class="hero py-4">
            <img
            src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-internacional.png"
            alt="Logo Balloons Academy"
            class="img-fluid mb-3"
            style="max-height: 80px"
          />
          </div>
        </div>
        <div class="col-lg-8">
          <div class="card shadow-sm rounded-4">
      
            <div class="card-body">
              <!-- Datos prellenados -->
              <div class="row">
                <div class="col-12 col-md-6 mb-3">
                  <label class="form-label">Nombre Completo</label>
                  <input class="form-control" :value="props.name" readonly />
                </div>

                <div class="col-12 col-md-6 mb-3">
                  <label class="form-label">Email</label>
                  <input class="form-control" :value="props.email" readonly />
                </div>

                <input type="hidden" class="form-control" :value="props.phone" />

                <div class="col-12 col-md-6 mb-3">
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

                <!-- Nickname obligatorio -->
                <div class="col-12 col-md-6 mb-3">
                  <label for="nickname" class="form-label">Nickname</label>
                  <input
                    id="nickname"
                    type="text"
                    class="form-control"
                    v-model="form.nickname"
                    placeholder="@usuario"
                    :class="{ 'is-invalid': nicknameError || form.errors.nickname }"
                    @blur="handleBlur('nickname')"
                  />
                  <div class="invalid-feedback" v-if="nicknameError">{{ nicknameError }}</div>
                  <div class="invalid-feedback" v-else-if="form.errors.nickname">{{ form.errors.nickname }}</div>
                </div>
              </div>

              <!-- Password -->
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
                      ref="passwordInput"
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
<style lang="less" scoped>
/* Paleta basada en el HTML original */
@brand-dark:  #0b2e57; // header/hero
@brand-deep:  #0B2B60; // header sólido
@brand-accent:#e4ae4e; // botón/footer
@text-muted:  #8a8a8a;


.hero{
  background:@brand-dark;
  border-radius:1rem;
  margin-bottom:1rem;
}
</style>