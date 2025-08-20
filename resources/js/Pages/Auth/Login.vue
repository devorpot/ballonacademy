<script setup>
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { route } from 'ziggy-js'

const form = useForm({
  email: '',
  password: '',
})

const touched = ref({
  email: false,
  password: false,
})

// Validaciones
const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
const passwordValid = computed(() => form.password.length > 0)
const isFormValid = computed(() => emailValid.value && passwordValid.value)

const emailError = computed(() => {
  if (!touched.value.email) return ''
  if (!form.email) return 'El correo es obligatorio'
  if (!emailValid.value) return 'Correo no válido'
  return ''
})

const passwordError = computed(() => {
  if (!touched.value.password) return ''
  if (!form.password) return 'La contraseña es obligatoria'
  return ''
})

const submit = () => {
  touched.value.email = true
  touched.value.password = true
  if (!isFormValid.value) return
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Iniciar Sesión" />
    <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
      <div class="card shadow border-0 p-4 p-md-5" style="max-width: 400px; width: 100%; border-radius: 12px">
        <div class="text-center mb-4">
          <img
            src="/resources/images/logo_login.png"
            alt="Logo Balloons Academy"
            class="img-fluid mb-3"
            style="max-height: 80px"
          />
        </div>

        <form @submit.prevent="submit" novalidate>
          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              class="form-control"
              :class="{ 'is-invalid': (touched.email && emailError) || form.errors.email }"
              autocomplete="username"
              autofocus
              @blur="touched.email = true"
            />
            <div class="invalid-feedback" v-if="touched.email && emailError">
              {{ emailError }}
            </div>
            <div class="invalid-feedback" v-else-if="form.errors.email">
              {{ form.errors.email }}
            </div>
          </div>

          <!-- Password -->
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              type="password"
              v-model="form.password"
              class="form-control"
              :class="{ 'is-invalid': (touched.password && passwordError) || form.errors.password }"
              autocomplete="current-password"
              @blur="touched.password = true"
            />
            <div class="invalid-feedback" v-if="touched.password && passwordError">
              {{ passwordError }}
            </div>
            <div class="invalid-feedback" v-else-if="form.errors.password">
              {{ form.errors.password }}
            </div>
          </div>

          <!-- Botón login -->
          <div class="d-grid mt-4">
            <button
              type="submit"
              class="btn btn-primary btn-lg"
              :disabled="form.processing || !isFormValid"
            >
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Entrar
            </button>
          </div>
        </form>

        <!-- Recuperar contraseña -->
        <div class="text-center mt-4">
          <a href="#" class="text-muted small">¿Olvide mi contraseña?</a>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>
