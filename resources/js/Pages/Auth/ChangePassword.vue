<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => form.post(route('password.change'))
</script>


<template>
  <GuestLayout>
    <Head title="Cambiar contraseña" />

    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow-sm">
            <div class="card-header">
              <h1 class="h5 mb-0">Cambiar contraseña</h1>
            </div>
            <div class="card-body">
              <form @submit.prevent="submit" novalidate>
                <div class="mb-3">
                  <label for="current_password" class="form-label">Contraseña actual</label>
                  <input
                    id="current_password"
                    type="password"
                    v-model="form.current_password"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.current_password }"
                    autocomplete="current-password"
                  />
                  <div class="invalid-feedback" v-if="form.errors.current_password">
                    {{ form.errors.current_password }}
                  </div>
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Nueva contraseña</label>
                  <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password || (form.password && !minOk) }"
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                  />
                  <div class="invalid-feedback" v-if="form.errors.password">
                    {{ form.errors.password }}
                  </div>
                  <div class="invalid-feedback" v-else-if="form.password && !minOk">
                    Debe tener al menos 8 caracteres
                  </div>
                </div>

                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                  <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password_confirmation || (form.password_confirmation && !matchOk) }"
                    autocomplete="new-password"
                  />
                  <div class="invalid-feedback" v-if="form.errors.password_confirmation">
                    {{ form.errors.password_confirmation }}
                  </div>
                  <div class="invalid-feedback" v-else-if="form.password_confirmation && !matchOk">
                    Las contraseñas no coinciden
                  </div>
                </div>

                <div class="d-grid">
                  <button class="btn btn-primary" type="submit" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                    Guardar cambio
                  </button>
                </div>

                <div class="alert alert-success mt-3" v-if="$page.props.flash?.status">
                  {{ $page.props.flash.status }}
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>
