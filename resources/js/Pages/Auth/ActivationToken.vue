<script setup>
import { computed } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { route } from 'ziggy-js'

const form = useForm({
  name: '',
  email: '',
  phone: '',
  course_id: '' // opcional, si no lo usas elimínalo
})

const page = usePage()
const activation = computed(() => page.props.flash?.activation || null)

const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
const isFormValid = computed(() => form.name && form.email && emailValid.value)

const submit = () => {
  if (!isFormValid.value) return
  form.post(route('auth.activation.create'), { preserveScroll: true })
}
</script>

<template>
  <GuestLayout>
    <Head title="Generar token de activación" />

    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
          <div class="card shadow-sm">
            <div class="card-header">
              <h1 class="h5 mb-0">Generar token de activación</h1>
            </div>
            <div class="card-body">
              <form @submit.prevent="submit" novalidate>
                <div class="row g-3">
                  <div class="col-12">
                    <label for="name" class="form-label">Nombre completo</label>
                    <input
                      id="name"
                      type="text"
                      v-model="form.name"
                      class="form-control"
                      :class="{ 'is-invalid': form.errors.name }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                  </div>

                  <div class="col-md-6">
                    <label for="email" class="form-label">E-Mail</label>
                    <input
                      id="email"
                      type="email"
                      v-model="form.email"
                      class="form-control"
                      :class="{ 'is-invalid': form.errors.email || (form.email && !emailValid) }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
                    <div class="invalid-feedback" v-else-if="form.email && !emailValid">Correo no válido</div>
                  </div>

                  <div class="col-md-6">
                    <label for="phone" class="form-label">Teléfono (opcional)</label>
                    <input
                      id="phone"
                      type="text"
                      v-model="form.phone"
                      class="form-control"
                      :class="{ 'is-invalid': form.errors.phone }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.phone">{{ form.errors.phone }}</div>
                  </div>

                  <div class="col-md-6">
                    <label for="course_id" class="form-label">ID Curso (opcional)</label>
                    <input
                      id="course_id"
                      type="number"
                      v-model="form.course_id"
                      class="form-control"
                      :class="{ 'is-invalid': form.errors.course_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.course_id">{{ form.errors.course_id }}</div>
                  </div>
                </div>

                <div class="d-grid mt-4">
                  <button class="btn btn-primary" type="submit" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                    Generar token
                  </button>
                </div>
              </form>

              <div class="alert alert-success mt-4" v-if="activation">
                <div class="fw-bold mb-2">Token generado</div>
                <div><strong>Código:</strong> {{ activation.code }}</div>
                <div><strong>Hash:</strong> {{ activation.hash }}</div>
                <!-- Si en el backend envías la URL, la muestras aquí -->
                <div v-if="activation.url" class="mt-2">
                  <a :href="activation.url" target="_blank" class="link-primary">Abrir formulario de activación</a>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <a :href="route('login')" class="btn btn-outline-secondary">Volver</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>
