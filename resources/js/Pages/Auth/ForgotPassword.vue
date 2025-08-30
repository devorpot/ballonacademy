<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { route } from 'ziggy-js'

const page = usePage()

const form = useForm({ email: '' })
const touched = ref({ email: false })

// Estado de UI
const sent = ref(false)                // ¿Ya mostramos confirmación?
const successMsg = ref('')             // Mensaje de éxito/confirmación
const errorMsg = ref('')               // Mensaje de error general (throttle u otros)
const countdown = ref(0)               // Segundos para reenviar
let timer = null

const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
const isFormValid = computed(() => !!form.email && emailValid.value)

const emailError = computed(() => {
  if (!touched.value.email) return ''
  if (!form.email) return 'El correo es obligatorio'
  if (!emailValid.value) return 'Correo no válido'
  return ''
})

function startCooldown(seconds = 60) {
  clearInterval(timer)
  countdown.value = seconds
  timer = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) clearInterval(timer)
  }, 1000)
}

function showSuccess(message) {
  successMsg.value = message || 'Si el correo existe, hemos enviado un enlace para restablecer tu contraseña.'
  errorMsg.value = ''
  sent.value = true
  startCooldown(60)
  nextTick(() => {
    const el = document.getElementById('successAlert')
    if (el) el.focus()
  })
}

function showError(message) {
  errorMsg.value = message || 'Ocurrió un problema. Inténtalo de nuevo en unos minutos.'
  successMsg.value = ''
  sent.value = false
  nextTick(() => {
    const el = document.getElementById('errorAlert')
    if (el) el.focus()
  })
}

const submit = () => {
  touched.value.email = true
  if (!isFormValid.value) return

  form.post(route('password.email'), {
    preserveScroll: true,
    onSuccess: () => {
      // Lee flash desde el backend (status/success) o usa fallback neutro
      const flash = page.props.flash || {}
      showSuccess(flash.success || flash.status || '')
      form.clearErrors()
      // Opcional: limpiar el email para evitar reintentos automáticos
      // form.reset('email')
    },
    onError: (errors) => {
      // Errores de validación o throttle desde el backend
      const msg = errors?.email || 'No pudimos procesar tu solicitud.'
      // Si es throttled, iniciamos cooldown para que la UI guíe al usuario
      if (/throttled|espera|wait/i.test(msg)) {
        showError('Has solicitado recientemente el enlace. Inténtalo de nuevo en unos minutos.')
        startCooldown(60)
      } else {
        showError(msg)
      }
    },
  })
}

const resend = () => {
  if (countdown.value > 0 || form.processing) return
  // Intentar reenviar manteniendo el mismo flujo
  submit()
}

onMounted(() => {
  // Si el backend ya mandó flash de éxito (p. ej. tras un redirect back), muéstralo
  const flash = page.props.flash || {}
  if (flash.success || flash.status) {
    showSuccess(flash.success || flash.status)
  }
})

</script>

<template>
  <GuestLayout>
    <Head title="Restaurar contraseña" />

    <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
      <div class="card shadow border-0 p-4 p-md-5" style="max-width: 520px; width: 100%; border-radius: 12px">
         <div class="text-center mb-4">
          <img
            src="/resources/images/logo_login.png"
            alt="Logo Balloons Academy"
            class="img-fluid mb-3"
            style="max-height: 80px"
          />
        </div>
        <h1 class="h5 text-center mb-3">Restaurar contraseña</h1>

        <!-- Estado de éxito / confirmación -->
        <div
          v-if="sent"
          id="successAlert"
          class="alert alert-success"
          tabindex="-1"
          role="alert"
          aria-live="polite"
        >
          <strong>Revisa tu correo.</strong> {{ successMsg }}
          <ul class="mb-0 mt-2 ps-3">
            <li>Puede tardar algunos minutos en llegar.</li>
            <li>Revisa también tu carpeta de SPAM o Promociones.</li>
          </ul>
        </div>

        <!-- Estado de error general -->
        <div
          v-if="errorMsg"
          id="errorAlert"
          class="alert alert-warning"
          tabindex="-1"
          role="alert"
          aria-live="polite"
        >
          {{ errorMsg }}
        </div>

        <!-- Formulario (se oculta cuando sent = true, pero dejamos botón de reenviar abajo) -->
        <div v-if="!sent" class="text-muted text-center mb-4">
          Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
        </div>

        <form v-if="!sent" @submit.prevent="submit" novalidate>
          <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              class="form-control"
              :class="{ 'is-invalid': (touched.email && emailError) || form.errors.email }"
              @blur="touched.email = true"
              autocomplete="email"
              autofocus
            />
            <div class="invalid-feedback" v-if="touched.email && emailError">{{ emailError }}</div>
            <div class="invalid-feedback" v-else-if="form.errors.email">{{ form.errors.email }}</div>
          </div>

          <div class="d-grid mt-3">
            <button class="btn btn-primary btn-lg" type="submit" :disabled="form.processing || !isFormValid">
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Enviar enlace
            </button>
          </div>
        </form>

        <!-- Acciones posteriores al envío -->
        <div class="mt-4 d-flex flex-column gap-3">
          <div class="d-flex justify-content-between align-items-center">
            <a :href="route('login')" class="text-muted small">Volver a iniciar sesión</a>

            <button
              class="btn btn-outline-secondary btn-sm"
              :disabled="form.processing || countdown > 0 || !form.email"
              @click="resend"
            >
              <span v-if="countdown > 0">Reenviar en {{ countdown }}s</span>
              <span v-else>Reenviar enlace</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>
