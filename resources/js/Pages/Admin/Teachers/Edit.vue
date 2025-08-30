<template>
  <Head title="Editar Maestro" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Maestros', route: 'admin.teachers.index' },
            { label: 'Editar', route: '' }
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
                  <i class="bi bi-save me-1"></i> Guardar cambios
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
                        :formError="form.errors.email"
                        :validateFunction="validateEmail"
                        @blur="() => handleBlur('email')"
                      />
                    </div>
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="password"
                        label="Contraseña (opcional)"
                        type="password"
                        v-model="form.password"
                        :showValidation="touched.password"
                        :formError="form.errors.password"
                        :validateFunction="validatePassword"
                        @blur="() => handleBlur('password')"
                      />
                      <small class="form-text text-muted">Dejar en blanco para mantener la actual</small>
                    </div>
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="password_confirmation"
                        label="Confirmar contraseña"
                        type="password"
                        v-model="form.password_confirmation"
                        :showValidation="touched.password_confirmation"
                        :formError="form.errors.password_confirmation"
                        :validateFunction="validatePasswordConfirmation"
                        @blur="() => handleBlur('password_confirmation')"
                      />
                    </div>
                  </div>

                  <h6 class="text-muted mt-4 mb-3">Datos del maestro</h6>
                 <div class="row">
  <!-- Nombre -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="firstname"
      label="Nombre"
      v-model="form.firstname"
      :required="true"
      :showValidation="touched.firstname"
      :formError="form.errors.firstname"
      :validateFunction="() => validateRequiredGeneric(form.firstname, 'Nombre')"
      placeholder="Ingrese el nombre"
      @blur="() => handleBlur('firstname')"
    />
  </div>

  <!-- Apellido -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="lastname"
      label="Apellido"
      v-model="form.lastname"
      :required="true"
      :showValidation="touched.lastname"
      :formError="form.errors.lastname"
      :validateFunction="() => validateRequiredGeneric(form.lastname, 'Apellido')"
      placeholder="Ingrese el apellido"
      @blur="() => handleBlur('lastname')"
    />
  </div>

  <!-- Teléfono -->
  <div class="col-md-6 mb-3">
    <FieldPhone
      id="phone"
      label="Teléfono"
      v-model="form.phone"
      :required="true"
      :showValidation="touched.phone"
      :formError="form.errors.phone"
      :validateFunction="() => validateRequiredGeneric(form.phone, 'Teléfono')"
      placeholder="Ingrese el teléfono"
      @blur="() => handleBlur('phone')"
    />
  </div>

  <!-- Especialidad -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="specialty"
      label="Especialidad"
      v-model="form.specialty"
      :required="true"
      :showValidation="touched.specialty"
      :formError="form.errors.specialty"
      :validateFunction="() => validateRequiredGeneric(form.specialty, 'Especialidad')"
      placeholder="Ingrese la especialidad"
      @blur="() => handleBlur('specialty')"
    />
  </div>

  <!-- Dirección (si la dejas opcional, quita required y la validación) -->
  <div class="col-md-6 mb-3">
    <FieldTextarea
      id="address"
      label="Dirección"
      v-model="form.address"
      :required="false"
      :showValidation="touched.address"
      :formError="form.errors.address"
      :validateFunction="() => ''"
      placeholder="Ingrese la dirección"
      @blur="() => handleBlur('address')"
    />
  </div>

  <!-- País -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="country"
      label="País"
      v-model="form.country"
      :required="true"
      :showValidation="touched.country"
      :formError="form.errors.country"
      :validateFunction="() => validateRequiredGeneric(form.country, 'País')"
      placeholder="Ingrese el país"
      @blur="() => handleBlur('country')"
    />
  </div>

  <!-- Facebook -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="facebook"
      label="Facebook (URL)"
      v-model="form.facebook"
      :required="false"
      :showValidation="touched.facebook"
      :formError="form.errors.facebook"
      :validateFunction="() => validateOptionalUrl(form.facebook)"
      placeholder="https://facebook.com/usuario"
      @blur="() => handleBlur('facebook')"
    />
  </div>

  <!-- Instagram -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="instagram"
      label="Instagram (URL)"
      v-model="form.instagram"
      :required="false"
      :showValidation="touched.instagram"
      :formError="form.errors.instagram"
      :validateFunction="() => validateOptionalUrl(form.instagram)"
      placeholder="https://instagram.com/usuario"
      @blur="() => handleBlur('instagram')"
    />
  </div>

  <!-- TikTok -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="tiktok"
      label="TikTok (URL)"
      v-model="form.tiktok"
      :required="false"
      :showValidation="touched.tiktok"
      :formError="form.errors.tiktok"
      :validateFunction="() => validateOptionalUrl(form.tiktok)"
      placeholder="https://tiktok.com/@usuario"
      @blur="() => handleBlur('tiktok')"
    />
  </div>

  <!-- Sitio web -->
  <div class="col-md-6 mb-3">
    <FieldText
      id="website"
      label="Sitio Web (URL)"
      v-model="form.website"
      :required="false"
      :showValidation="touched.website"
      :formError="form.errors.website"
      :validateFunction="() => validateOptionalUrl(form.website)"
      placeholder="https://mi-sitio.com"
      @blur="() => handleBlur('website')"
    />
  </div>

 <div class="col-md-6 mb-3">
<FieldImage
  id="profile_image"
  label="Imagen de perfil"
  v-model="form.profile_image"        
  :initialPreview="props.teacher.profile_image ? `/storage/${props.teacher.profile_image}` : null"
  :disabled="form.processing"
/>
<div class="form-check mt-1">
  <input class="form-check-input" type="checkbox" id="remove_profile_image" v-model="form.remove_profile_image" />
  <label class="form-check-label" for="remove_profile_image">Quitar imagen de perfil actual</label>
</div>

</div>
 <div class="col-md-6 mb-3">
        <FieldImage
          id="cover_image"
          label="Imagen de portada"
          v-model="form.cover_image"         
          :initialPreview="props.teacher.cover_image ? `/storage/${props.teacher.cover_image}` : null"
          :disabled="form.processing"
        />
      <div class="form-check mt-1">
        <input class="form-check-input" type="checkbox" id="remove_cover_image" v-model="form.remove_cover_image" />
        <label class="form-check-label" for="remove_cover_image">Quitar imagen de portada actual</label>
      </div>

  </div>
</div>

                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Guardar cambios
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
import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'
import FieldPhone from '@/Components/Admin/Fields/FieldPhone.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'

const props = defineProps({
  teacher: { type: Object, required: true }
})

// Soporta que user venga anidado en teacher.user
const form = useForm({
   _method: 'PUT',
  // Usuario
  name: props.teacher?.user?.name ?? '',
  email: props.teacher?.user?.email ?? '',
  password: '',
  password_confirmation: '',

  // Maestro
  firstname: props.teacher.firstname ?? '',
  lastname: props.teacher.lastname ?? '',
  phone: props.teacher.phone ?? '',
  specialty: props.teacher.specialty ?? '',
  address: props.teacher.address ?? '',
  country: props.teacher.country ?? '',

  // Nuevos campos (opcionales)
  facebook: props.teacher.facebook ?? '',
  instagram: props.teacher.instagram ?? '',
  tiktok: props.teacher.tiktok ?? '',
  website: props.teacher.website ?? '',
  profile_image: null,
  cover_image: null,
  remove_profile_image: false,
  remove_cover_image: false
})

/**
 * Campos del bloque "Datos del maestro"
 * - required true solo en los obligatorios
 * - type: 'text' | 'phone' | 'textarea' | 'url' | 'image'
 */
const teacherFields = [
  { key: 'firstname', label: 'Nombre', component: FieldText, placeholder: 'Ingrese el nombre', required: true, type: 'text' },
  { key: 'lastname', label: 'Apellido', component: FieldText, placeholder: 'Ingrese el apellido', required: true, type: 'text' },
  { key: 'phone', label: 'Teléfono', component: FieldPhone, placeholder: 'Ingrese el teléfono', required: true, type: 'phone' },
  { key: 'specialty', label: 'Especialidad', component: FieldText, placeholder: 'Ingrese la especialidad', required: true, type: 'text' },
  { key: 'address', label: 'Dirección', component: FieldTextarea, placeholder: 'Ingrese la dirección', required: true, type: 'textarea' },
  { key: 'country', label: 'País', component: FieldText, placeholder: 'Ingrese el país', required: true, type: 'text' },

  // Redes / sitio (opcionales, validan URL si tienen valor)
  { key: 'facebook', label: 'Facebook (URL)', component: FieldText, placeholder: 'https://facebook.com/usuario', required: false, type: 'url' },
  { key: 'instagram', label: 'Instagram (URL)', component: FieldText, placeholder: 'https://instagram.com/usuario', required: false, type: 'url' },
  { key: 'tiktok', label: 'TikTok (URL)', component: FieldText, placeholder: 'https://tiktok.com/@usuario', required: false, type: 'url' },
  { key: 'website', label: 'Sitio Web (URL)', component: FieldText, placeholder: 'https://mi-sitio.com', required: false, type: 'url' },

  // Imágenes (opcionales)
  {
    key: 'profile_image',
    label: 'Imagen de perfil',
    component: FieldImage,
    required: false,
    type: 'image',
    bind: {
      accept: 'image/*',
      hint: 'Opcional. JPG/PNG recomendado.'
    }
  },
  {
    key: 'cover_image',
    label: 'Imagen de portada',
    component: FieldImage,
    required: false,
    type: 'image',
    bind: {
      accept: 'image/*',
      hint: 'Opcional. Formato apaisado recomendado.'
    }
  }
]

const touched = ref({})

const handleBlur = (field) => {
  touched.value[field] = true
}

const validateName = () => {
  if (!form.name.trim()) return 'El nombre es requerido'
  if (form.name.length < 3) return 'Debe tener al menos 3 caracteres'
  return ''
}

const validateEmail = () => {
  if (!form.email.trim()) return 'El email es requerido'
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email)) return 'Ingrese un email válido'
  return ''
}

const validatePassword = () => {
  if (form.password && form.password.length < 8) return 'Debe tener al menos 8 caracteres'
  if (form.password && !/[A-Z]/.test(form.password)) return 'Debe contener una mayúscula'
  if (form.password && !/[0-9]/.test(form.password)) return 'Debe contener un número'
  return ''
}

const validatePasswordConfirmation = () => {
  if (form.password && form.password !== form.password_confirmation) return 'Las contraseñas no coinciden'
  return ''
}

// Requerido genérico
const validateRequiredGeneric = (value, label = 'Este campo') => {
  if (value == null) return `${label} es obligatorio`
  if (typeof value === 'string' && !value.trim()) return `${label} es obligatorio`
  return ''
}

// URL opcional: pasa si está vacío; si tiene valor, valida formato
const validateOptionalUrl = (value) => {
  if (!value) return ''
  try {
    new URL(value)
    return ''
  } catch {
    return 'URL inválida'
  }
}

const validateField = (field) => {
  const value = form[field.key]
  if (field.required) {
    const err = validateRequiredGeneric(value, field.label)
    if (err) return err
  }
  if (field.type === 'url') {
    return validateOptionalUrl(value)
  }
  return ''
}

const isFormValid = computed(() => {
  const baseValid =
    !validateName() &&
    !validateEmail() &&
    !validatePassword() &&
    !validatePasswordConfirmation()

  const teacherValid = teacherFields.every((f) => !validateField(f))
  return baseValid && teacherValid
})

const submit = () => {
  Object.keys(form).forEach(k => (touched.value[k] = true))
  if (!isFormValid.value) return

  form.transform((data) => {
    
    const coerceFile = (v) => (v instanceof File ? v : null)
    return {
      ...data,
      profile_image: coerceFile(data.profile_image),
      cover_image: coerceFile(data.cover_image),
      remove_profile_image: data.remove_profile_image ? 1 : 0,
      remove_cover_image: data.remove_cover_image ? 1 : 0,
    }
  }).post(route('admin.teachers.update', props.teacher.id), {
    preserveScroll: true,
    forceFormData: true,
  })
}

</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
