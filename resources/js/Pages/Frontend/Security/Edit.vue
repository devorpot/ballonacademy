<!-- resources/js/Pages/Frontend/Security/Edit.vue -->
<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue' // o el layout que uses
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'
const props = defineProps({
  user: { type: Object, required: true },
  locales: { type: Object, required: true } // { es: "Español", en: "English" }
})

const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  locale: props.user.locale || 'es',
  password: '',
  password_confirmation: ''
})

const localesList = computed(() =>
  Object.entries(props.locales).map(([value, label]) => ({ value, label }))
)

function submit() {
  form.put(route('dashboard.security.update'))
}
</script>

<template>
  <Head title="Seguridad de la cuenta" />

  <StudentLayout>

        <Breadcrumbs
          username="estudiante"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'dashboard.index' },
            { label: 'Seguridad', route: '' },
     
          ]"
        />
 

 <section class="section-panel py-3">
    <div class="container-fluid  ">
      <div class="row justify-content-center">
        <div class="col-lg-12">
          <div class="card shadow-sm">
            <div class="card-header">
               <h5 class="card-title">Seguridad de la cuenta</h5>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="name">Nombre</label>
                <input id="name" type="text" v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" />
                <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" v-model="form.email" class="form-control" :class="{ 'is-invalid': form.errors.email }" />
                <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="locale">Idioma</label>
                <select id="locale" v-model="form.locale" class="form-select" :class="{ 'is-invalid': form.errors.locale }">
                  <option v-for="opt in localesList" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <div class="invalid-feedback" v-if="form.errors.locale">{{ form.errors.locale }}</div>
              </div>

              <hr />

              <div class="mb-3">
                <label class="form-label" for="password">Nueva contraseña</label>
                <input id="password" type="password" v-model="form.password" class="form-control" :class="{ 'is-invalid': form.errors.password }" autocomplete="new-password" />
                <div class="form-text">Déjalo vacío si no deseas cambiarla.</div>
                <div class="invalid-feedback" v-if="form.errors.password">{{ form.errors.password }}</div>
              </div>

              <div class="mb-4">
                <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="form-control" />
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-pill" :disabled="form.processing" @click="submit">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  Guardar cambios
                </button>
                <button class="btn btn-outline-secondary rounded-pill" type="button" @click="form.reset()">Restablecer</button>
              </div>

              <div class="mt-3 text-success" v-if="$page.props.flash?.success">
                {{ $page.props.flash.success }}
              </div>
              <div class="mt-3 text-danger" v-if="$page.props.flash?.error">
                {{ $page.props.flash.error }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  </StudentLayout>
</template>
