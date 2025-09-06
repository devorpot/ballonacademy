<!-- resources/js/Pages/Admin/Teachers/Create.vue -->
<template>
  <Head title="Nuevo Maestro" />

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
                <div class="card-header">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                      <a
                        class="nav-link"
                        :class="{ active: activeTab === 'personal' }"
                        href="#"
                        role="tab"
                        aria-controls="tab-personal"
                        :aria-selected="activeTab === 'personal'"
                        @click.prevent="switchTab('personal')"
                      >Perfil Personal</a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a
                        class="nav-link"
                        :class="{ active: activeTab === 'social' }"
                        href="#"
                        role="tab"
                        aria-controls="tab-social"
                        :aria-selected="activeTab === 'social'"
                        @click.prevent="switchTab('social')"
                      >Redes y Medios</a>
                    </li>
                    <li class="ms-auto nav-item" role="presentation">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        @click="showAccount = !showAccount"
                        :aria-expanded="showAccount ? 'true' : 'false'"
                        aria-controls="account-section"
                      >
                        <i class="bi bi-person-gear me-1"></i>
                        Datos de cuenta
                      </button>
                    </li>
                  </ul>
                </div>

                <div class="card-body">
                  <!-- Datos de cuenta -->
                  <transition name="fade">
                    <div v-if="showAccount" id="account-section" class="alert alert-secondary small">
                      <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                          <FieldText
                            id="user_name"
                            label="Nombre de cuenta"
                            v-model="form.user_name"
                            :required="true"
                            :showValidation="touched.user_name"
                            :formError="form.errors.user_name || form.errors.name"
                            :validateFunction="() => validateRequired(form.user_name, 'Nombre de cuenta')"
                            @blur="() => handleBlur('user_name')"
                          />
                        </div>
                        <div class="col-md-4">
                          <FieldEmail
                            id="user_email"
                            label="Email de cuenta"
                            v-model="form.user_email"
                            :required="true"
                            :showValidation="touched.user_email"
                            :formError="form.errors.user_email || form.errors.email"
                            :validateFunction="() => validateEmail(form.user_email)"
                            @blur="() => handleBlur('user_email')"
                          />
                        </div>
                        <div class="col-md-2">
                          <label class="form-label d-block">Activo</label>
                          <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="user_active" v-model="form.user_active">
                            <label class="form-check-label" for="user_active">{{ form.user_active ? 'Sí' : 'No' }}</label>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <FieldText id="user_locale" label="Locale" v-model="form.user_locale" placeholder="es, en, ..." />
                        </div>

                        <div class="col-md-4">
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
                    </div>
                  </transition>

                  <!-- Tab: Personal -->
                  <div v-show="activeTab === 'personal'" id="tab-personal" role="tabpanel">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="firstname"
                          label="Nombre"
                          v-model="form.firstname"
                          :required="true"
                          :showValidation="touched.firstname"
                          :formError="form.errors.firstname"
                          :validateFunction="() => validateRequired(form.firstname, 'Nombre')"
                          placeholder="Ingrese el nombre"
                          @blur="() => handleBlur('firstname')"
                        />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="lastname"
                          label="Apellido"
                          v-model="form.lastname"
                          :required="true"
                          :showValidation="touched.lastname"
                          :formError="form.errors.lastname"
                          :validateFunction="() => validateRequired(form.lastname, 'Apellido')"
                          placeholder="Ingrese el apellido"
                          @blur="() => handleBlur('lastname')"
                        />
                      </div>

                      <div class="col-md-6 mb-3">
                        <FieldPhone
                          id="phone"
                          label="Teléfono"
                          v-model="form.phone"
                          
                          :showValidation="touched.phone"
                          :formError="form.errors.phone"
                          :validateFunction="() => validateRequired(form.phone, 'Teléfono')"
                          placeholder="Ingrese el teléfono"
                          @blur="() => handleBlur('phone')"
                        />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="country"
                          label="País"
                          v-model="form.country"
                          :required="true"
                          :showValidation="touched.country"
                          :formError="form.errors.country"
                          :validateFunction="() => validateRequired(form.country, 'País')"
                          placeholder="Ingrese país"
                          @blur="() => handleBlur('country')"
                        />
                      </div>

                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="specialty"
                          label="Especialidad"
                          v-model="form.specialty"
                          :required="true"
                          :showValidation="touched.specialty"
                          :formError="form.errors.specialty"
                          :validateFunction="() => validateRequired(form.specialty, 'Especialidad')"
                          placeholder="Ingrese la especialidad"
                          @blur="() => handleBlur('specialty')"
                        />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldTextarea
                          id="address"
                          label="Dirección"
                          v-model="form.address"
                          :showValidation="touched.address"
                          :formError="form.errors.address"
                          :validateFunction="() => ''"
                          placeholder="Ingrese la dirección"
                          @blur="() => handleBlur('address')"
                        />
                      </div>

                      <!-- Imágenes -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label mb-1">Foto de perfil</label>
                        <FieldImage
                          id="profile_image"
                          label=""
                          v-model="form.profile_image"
                          :initialPreview="null"
                          :disabled="form.processing"
                        />
                        <div v-if="form.errors.profile_image" class="invalid-feedback d-block">
                          {{ form.errors.profile_image }}
                        </div>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label mb-1">Foto de portada</label>
                        <FieldImage
                          id="cover_image"
                          label=""
                          v-model="form.cover_image"
                          :initialPreview="null"
                          :disabled="form.processing"
                        />
                        <div v-if="form.errors.cover_image" class="invalid-feedback d-block">
                          {{ form.errors.cover_image }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Tab: Redes y Medios -->
                  <div v-show="activeTab === 'social'" id="tab-social" role="tabpanel">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <FieldUrl id="website" label="Sitio web" v-model="form.website" />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText id="facebook" label="Facebook" v-model="form.facebook" />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText id="instagram" label="Instagram" v-model="form.instagram" />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText id="tiktok" label="TikTok" v-model="form.tiktok" />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText id="youtube" label="YouTube" v-model="form.youtube" />
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

      <!-- Spinner a body -->
      <teleport to="body">
        <SpinnerOverlay v-if="form.processing" :fullscreen="true" />
      </teleport>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed, nextTick, onMounted } from 'vue'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'
import FieldUrl from '@/Components/Admin/Fields/FieldUrl.vue'
import FieldPhone from '@/Components/Admin/Fields/FieldPhone.vue'
import FieldPassword from '@/Components/Admin/Fields/FieldPassword.vue'
// Create no recibe teacher
const TAB_KEY = 'teachers.create.tab'
const activeTab = ref(localStorage.getItem(TAB_KEY) || 'personal')
function switchTab(tab) {
  activeTab.value = tab
  localStorage.setItem(TAB_KEY, tab)
}

const showAccount = ref(true) // en create, por defecto abierto
const touched = ref({})

// Form (store)
const form = useForm({
  // Cuenta de usuario
  user_name: '',
  user_email: '',
  user_active: true,
  user_locale: 'es',
  password: '',
  password_confirmation: '',

  // Datos personales
  firstname: '',
  lastname: '',
  phone: '',
  country: 'México',
  specialty: '--------',
  address: '--------',

  // Redes
  website: '',
  facebook: '',
  instagram: '',
  tiktok: '',
  youtube: '',

  // Archivos
  profile_image: null,
  cover_image: null,
})

// Touched
const handleBlur = (field) => { touched.value[field] = true }

// Validaciones
const validateEmail = (val) => {
  const v = String(val || '').trim()
  if (!v) return 'El email es requerido'
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!re.test(v)) return 'Ingrese un email válido'
  return ''
}
const validateRequired = (val, label) => {
  if (val === undefined || val === null) return `${label} es obligatorio`
  if (typeof val === 'string' && !val.trim()) return `${label} es obligatorio`
  return ''
}
const validatePassword = () => {
  const v = form.password || ''
  if (!v.trim()) return 'La contraseña es obligatoria'
  if (v.length < 8) return 'Debe tener al menos 8 caracteres'
  if (!/[A-Z]/.test(v)) return 'Debe contener una mayúscula'
  if (!/[0-9]/.test(v)) return 'Debe contener un número'
  return ''
}
const validatePasswordConfirmation = () => {
  if (!form.password_confirmation?.trim()) return 'Confirma la contraseña'
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden'
  return ''
}

const isFormValid = computed(() =>
  !validateRequired(form.user_name, 'Nombre de cuenta') &&
  !validateEmail(form.user_email) &&
  !validatePassword() &&
  !validatePasswordConfirmation() &&
  !validateRequired(form.firstname, 'Nombre') &&
  !validateRequired(form.lastname, 'Apellido') &&
  !validateRequired(form.country, 'País') 
 
)

function submit() {
  Object.keys(form).forEach(k => (touched.value[k] = true))
  if (!isFormValid.value) {
    focusFirstError()
    return
  }

  form.transform((data) => {
    const fileOrNull = (v) => (v instanceof File ? v : null)
    // Mapea a los nombres que espera el store:
    return {
      // user
      name: data.user_name,            // store usa name/email/password
      email: data.user_email,
      password: data.password,
      password_confirmation: data.password_confirmation,
      user_active: data.user_active,
      user_locale: data.user_locale,

      // teacher
      firstname: data.firstname,
      lastname: data.lastname,
      phone: data.phone,
      specialty: data.specialty,
      country: data.country,
      address: data.address || null,

      website: data.website || null,
      facebook: data.facebook || null,
      instagram: data.instagram || null,
      tiktok: data.tiktok || null,
      youtube: data.youtube || null,

      profile_image: fileOrNull(data.profile_image),
      cover_image: fileOrNull(data.cover_image),
    }
  }).post(route('admin.teachers.store'), {
    preserveScroll: true,
    forceFormData: true,
    onError: () => nextTick(() => focusFirstError()),
  })
}

// UX: foco al primer error del servidor
function focusFirstError() {
  const keys = Object.keys(form.errors || {})
  if (!keys.length) return
  const first = keys[0]
  const el = document.getElementById(first)
  if (el && typeof el.focus === 'function') el.focus()
}

// Mantener pestaña al llegar con errores del servidor
onMounted(() => {
  const errKeys = Object.keys(form.errors || {})
  if (errKeys.length) {
    const socialSet = new Set(['website','facebook','instagram','tiktok','youtube'])
    const first = errKeys[0]
    activeTab.value = socialSet.has(first) ? 'social' : 'personal'
    localStorage.setItem(TAB_KEY, activeTab.value)
  }
})
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
