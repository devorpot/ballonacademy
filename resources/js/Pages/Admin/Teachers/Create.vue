<template>
  <Head title="Crear Nuevo Maestro" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Maestros', route: 'admin.teachers.index' },
            { label: 'Crear', route: '' }
          ]"
        />

        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.teachers.index')" />
                <button
                  class="btn btn-primary"
                  :disabled="form.processing || !isFormValid"
                  @click="submit"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-1"></i> Guardar
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="section-form my-2">
          <div class="container-fluid">
            <form @submit.prevent="submit" novalidate>
              <div class="card">
                <div class="card-body">
                  <h6 class="text-muted mb-3">Datos de usuario</h6>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="name"
                        label="Nombre de usuario"
                        v-model="form.name"
                        :required="true"
                        :showValidation="touched.name"
                        :formError="form.errors.name"
                        :validateFunction="validateName"
                        placeholder="Ingrese el nombre de usuario"
                        @blur="() => handleBlur('name')"
                      />
                    </div>
                    <div class="col-md-6 mb-3">
                      <FieldEmail
                        id="email"
                        label="Email"
                        v-model="form.email"
                        :required="true"
                        :showValidation="touched.email"
                        :formError="form.errors.email"
                        :validateFunction="validateEmail"
                        placeholder="Ingrese el email"
                        @blur="() => handleBlur('email')"
                      />
                    </div>
                    <div class="col-md-6 mb-3">
                      <FieldPassword
                        id="password"
                        confirmId="password_confirmation"
                        label="Contraseña"
                        :password="form.password"
                        :passwordConfirmation="form.password_confirmation"
                        :required="true"
                        :showValidation="touched.password || touched.password_confirmation"
                        :formError="form.errors.password"
                        :confirmFormError="form.errors.password_confirmation"
                        :validateFunction="validatePassword"
                        :validateConfirmFunction="validatePasswordConfirmation"
                        @update:password="val => form.password = val"
                        @update:passwordConfirmation="val => form.password_confirmation = val"
                        @blur="(field) => handleBlur(field)"
                      />
                    </div>
                  </div>

                  <h6 class="text-muted mt-4 mb-3">Datos del maestro</h6>
                  <div class="row">
                    <div
                      v-for="field in teacherFields"
                      :key="field.key"
                      class="col-md-6 mb-3"
                    >
                      <component
                        :is="field.component"
                        :id="field.key"
                        :label="field.label"
                        v-model="form[field.key]"
                        :required="field.required === true"
                        :showValidation="touched[field.key]"
                        :formError="form.errors[field.key]"
                        :validateFunction="() => validateField(field.key)"
                        :placeholder="field.placeholder"
                        v-bind="field.bind || {}"
                        @blur="() => handleBlur(field.key)"
                      />
                    </div>
                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Guardar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>

      <SpinnerOverlay v-if="form.processing" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue'
import FieldPassword from '@/Components/Admin/Fields/FieldPassword.vue'
import FieldPhone from '@/Components/Admin/Fields/FieldPhone.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'

const form = useForm({
  // Usuario
  name: '',
  email: '',
  password: '',
  password_confirmation: '',

  // Maestro (obligatorios)
  firstname: '',
  lastname: '',
  phone: '',
  specialty: '',
  address: '',
  country: '',

  // Opcionales
  facebook: '',
  instagram: '',
  tiktok: '',
  website: '',
  profile_image: '',
  cover_image: ''
})

const teacherFields = [
  { key: 'firstname', label: 'Nombre', component: FieldText, placeholder: 'Ingrese el nombre', required: true },
  { key: 'lastname', label: 'Apellido', component: FieldText, placeholder: 'Ingrese el apellido', required: true },
  { key: 'phone', label: 'Teléfono', component: FieldPhone, placeholder: 'Ingrese el teléfono', required: true },
  { key: 'specialty', label: 'Especialidad', component: FieldText, placeholder: 'Ingrese la especialidad', required: true },
  { key: 'address', label: 'Dirección', component: FieldTextarea, placeholder: 'Ingrese la dirección', required: true },
  { key: 'country', label: 'País', component: FieldText, placeholder: 'Ingrese el país', required: true },

  // Redes / sitio (opcionales)
  { key: 'facebook', label: 'Facebook (URL)', component: FieldText, placeholder: 'https://facebook.com/usuario' },
  { key: 'instagram', label: 'Instagram (URL)', component: FieldText, placeholder: 'https://instagram.com/usuario' },
  { key: 'tiktok', label: 'TikTok (URL)', component: FieldText, placeholder: 'https://tiktok.com/@usuario' },
  { key: 'website', label: 'Sitio Web (URL)', component: FieldText, placeholder: 'https://mi-sitio.com' },

  // Imágenes (opcionales)
  {
    key: 'profile_image',
    label: 'Imagen de perfil',
    component: FieldImage,
    bind: { accept: 'image/*', hint: 'Opcional. JPG/PNG recomendado.' }
  },
  {
    key: 'cover_image',
    label: 'Imagen de portada',
    component: FieldImage,
    bind: { accept: 'image/*', hint: 'Opcional. Formato apaisado recomendado.' }
  }
]

const touched = ref({})

const handleBlur = (field) => {
  touched.value[field] = true
}

const validateName = () => {
  if (!form.name.trim()) return 'El nombre es obligatorio'
  if (form.name.length < 3) return 'Mínimo 3 caracteres'
  return ''
}

const validateEmail = () => {
  if (!form.email.trim()) return 'El email es obligatorio'
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email)) return 'Email inválido'
  return ''
}

// Mantengo FieldPassword como en esta vista
const validatePassword = () => {
  if (!form.password) return 'La contraseña es obligatoria'
  if (form.password.length < 8) return 'Mínimo 8 caracteres'
  return ''
}

const validatePasswordConfirmation = () => {
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden'
  return ''
}

// Igual que en Students: requerido genérico por key
const validateField = (key) => {
  const value = form[key]
  // Requerido si el campo está marcado 'required' en teacherFields
  const meta = teacherFields.find(f => f.key === key)
  if (meta?.required) {
    if (value == null) return `El campo ${meta.label || key} es obligatorio`
    if (typeof value === 'string' && !value.trim()) return `El campo ${meta.label || key} es obligatorio`
  }
  // Validación URL opcional cuando tenga valor
  if (['facebook', 'instagram', 'tiktok', 'website'].includes(key) && value) {
    try {
      new URL(value)
    } catch {
      return 'URL inválida'
    }
  }
  return ''
}

const isFormValid = computed(() => {
  const baseValid =
    !validateName() &&
    !validateEmail() &&
    !validatePassword() &&
    !validatePasswordConfirmation()

  const teacherValid = teacherFields.every(f => !validateField(f.key))
  return baseValid && teacherValid
})

const submit = () => {
  Object.keys(form).forEach((key) => (touched.value[key] = true))
  if (isFormValid.value) {
    form.post(route('admin.teachers.store'), {
      preserveScroll: true,
      onSuccess: () => form.reset()
    })
  }
}
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
