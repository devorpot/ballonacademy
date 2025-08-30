<script setup>
import { computed } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { route } from 'ziggy-js'

const props = defineProps({
  token: { type: String, required: true },
  email: { type: String, default: '' },
})

const page = usePage()

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

// === Validaciones front ===
const emailValid = computed(() =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email || '')
)
const pwdMinOk = computed(() => (form.password || '').length >= 8)
const pwdMatch = computed(() => form.password === form.password_confirmation)
const isFormValid = computed(() => emailValid.value && pwdMinOk.value && pwdMatch.value)

const submit = () => {
  form.post(route('password.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Establecer nueva contraseña" />

    <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
      <div class="card shadow border-0 p-4 p-md-5" style="max-width: 480px; width: 100%; border-radius: 12px">
         <div class="text-center mb-4">
          <img
            src="/resources/images/logo_login.png"
            alt="Logo Balloons Academy"
            class="img-fluid mb-3"
            style="max-height: 80px"
          />
        </div>
        <h1 class="h5 text-center mb-3">Establecer nueva contraseña</h1>

        <form @submit.prevent="submit" novalidate>
          <input type="hidden" v-model="form.token" />

          <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              class="form-control"
              :readonly="!!props.email"  
              :class="{ 'is-invalid': form.errors.email || (form.email && !emailValid) }"
              autocomplete="email"
            />
            <!-- Error del backend -->
            <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
            <!-- Error del frontend -->
            <div class="invalid-feedback" v-else-if="form.email && !emailValid">Correo no válido</div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Nueva contraseña</label>
            <input
              id="password"
              type="password"
              v-model="form.password"
              class="form-control"
              :class="{ 'is-invalid': form.errors.password || (form.password && !pwdMinOk) }"
              autocomplete="new-password"
              placeholder="Mínimo 8 caracteres"
            />
            <div class="invalid-feedback" v-if="form.errors.password">{{ form.errors.password }}</div>
            <div class="invalid-feedback" v-else-if="form.password && !pwdMinOk">Debe tener al menos 8 caracteres</div>
          </div>

          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input
              id="password_confirmation"
              type="password"
              v-model="form.password_confirmation"
              class="form-control"
              :class="{ 'is-invalid': form.errors.password_confirmation || (form.password_confirmation && !pwdMatch) }"
              autocomplete="new-password"
            />
            <div class="invalid-feedback" v-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</div>
            <div class="invalid-feedback" v-else-if="form.password_confirmation && !pwdMatch">Las contraseñas no coinciden</div>
          </div>

          <div class="d-grid mt-3">
            <button class="btn btn-primary btn-lg" type="submit" :disabled="form.processing || !isFormValid">
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Guardar contraseña
            </button>
          </div>

          <!-- Mensaje de backend (status) tras reset exitoso -->
          <div class="alert alert-success mt-3" role="alert" v-if="$page.props.flash?.status">
            {{ $page.props.flash.status }}
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>
