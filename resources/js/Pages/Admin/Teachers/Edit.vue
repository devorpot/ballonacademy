<template>
  <Head :title="`Editar Maestro: ${form.user_name || teacher?.user?.name || 'Maestro'}`" />

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
                    <li class="nav-item ms-auto" role="presentation">
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
                  <!-- Datos de cuenta (opcionales) -->
<transition name="fade">
  <div id="account-section" class="alert alert-secondary small">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <FieldText id="user_name" label="Nombre de cuenta" v-model="form.user_name" />
      </div>
      <div class="col-md-4">
        <FieldEmail id="user_email" label="Email de cuenta" v-model="form.user_email" />
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

      <!-- Password opcional -->
      <div class="col-md-8">
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
        <small class="text-muted">Déjala vacía para no cambiarla</small>
      </div>
       
    </div>
  </div>
</transition>


                  <!-- Pestaña: Personal -->
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
                          :validateFunction="() => validateField('firstname')"
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
                          :validateFunction="() => validateField('lastname')"
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
                          :validateFunction="() => validateField('phone')"
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
                          :validateFunction="() => validateField('country')"
                          placeholder="Ingrese país"
                          @blur="() => handleBlur('country')"
                        />
                      </div>

                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="specialty"
                          label="Especialidad"
                          v-model="form.specialty"
                          
                          :showValidation="touched.specialty"
                          :formError="form.errors.specialty"
                          :validateFunction="() => validateField('specialty')"
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
                        <div class="d-flex justify-content-between align-items-center">
                          <label class="form-label mb-1">Foto de perfil</label>
                          <div class="btn-group btn-group-sm">
                            <button
                              v-if="hasCurrentProfileImage && !removeProfileImage"
                              type="button" class="btn btn-outline-danger"
                              @click="removeProfileImage = true"
                              title="Eliminar imagen actual"
                            >
                              Quitar actual
                            </button>
                            <button
                              v-if="removeProfileImage"
                              type="button" class="btn btn-outline-secondary"
                              @click="removeProfileImage = false"
                              title="Restaurar sin eliminar"
                            >
                              Deshacer
                            </button>
                          </div>
                        </div>
                        <FieldImage
                          id="profile_image"
                          label=""
                          v-model="form.profile_image"
                          :initialPreview="profileImagePreview"
                          :disabled="form.processing"
                        />
                        <small v-if="removeProfileImage" class="text-danger d-block mt-1">
                          Se eliminará la imagen de perfil al guardar.
                        </small>
                        <div v-if="form.errors.profile_image" class="invalid-feedback d-block">
                          {{ form.errors.profile_image }}
                        </div>
                      </div>

                      <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                          <label class="form-label mb-1">Foto de portada</label>
                          <div class="btn-group btn-group-sm">
                            <button
                              v-if="hasCurrentCoverImage && !removeCoverImage"
                              type="button" class="btn btn-outline-danger"
                              @click="removeCoverImage = true"
                              title="Eliminar imagen actual"
                            >
                              Quitar actual
                            </button>
                            <button
                              v-if="removeCoverImage"
                              type="button" class="btn btn-outline-secondary"
                              @click="removeCoverImage = false"
                              title="Restaurar sin eliminar"
                            >
                              Deshacer
                            </button>
                          </div>
                        </div>
                        <FieldImage
                          id="cover_image"
                          label=""
                          v-model="form.cover_image"
                          :initialPreview="coverImagePreview"
                          :disabled="form.processing"
                        />
                        <small v-if="removeCoverImage" class="text-danger d-block mt-1">
                          Se eliminará la imagen de portada al guardar.
                        </small>
                        <div v-if="form.errors.cover_image" class="invalid-feedback d-block">
                          {{ form.errors.cover_image }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Pestaña: Redes y Medios -->
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
const props = defineProps({
  teacher: { type: Object, required: true }
})

// Persistencia de pestaña
const TAB_KEY = `teachers.edit.tab.${props.teacher.id}`
const activeTab = ref(localStorage.getItem(TAB_KEY) || 'personal')
function switchTab(tab) {
  activeTab.value = tab
  localStorage.setItem(TAB_KEY, tab)
}

const showAccount = ref(false)
const touched = ref({})

// Previews actuales
const profileImagePreview = props.teacher?.profile_image ? `/storage/${props.teacher.profile_image}` : null
const coverImagePreview   = props.teacher?.cover_image   ? `/storage/${props.teacher.cover_image}`   : null
const hasCurrentProfileImage = !!profileImagePreview
const hasCurrentCoverImage   = !!coverImagePreview

// Flags de eliminación
const removeProfileImage = ref(false)
const removeCoverImage   = ref(false)

// Form
const form = useForm({
  _method: 'PUT',
  // Usuario
  user_name: props.teacher?.user?.name ?? '',
  user_email: props.teacher?.user?.email ?? '',
  user_active: !!props.teacher?.user?.active,
  user_locale: props.teacher?.user?.locale ?? 'es',
  // Password opcional
  password: '',
  password_confirmation: '',
  // Datos personales...
  firstname: props.teacher?.firstname ?? '',
  lastname:  props.teacher?.lastname  ?? '',
  phone:     props.teacher?.phone     ?? '',
  country:   props.teacher?.country   ?? '',
  specialty: props.teacher?.specialty ?? '',
  address:   props.teacher?.address   ?? '',
  // Redes...
  website:  props.teacher?.website  ?? '',
  facebook: props.teacher?.facebook ?? '',
  instagram: props.teacher?.instagram ?? '',
  tiktok: props.teacher?.tiktok ?? '',
  youtube: props.teacher?.youtube ?? '',
  // Archivos
  profile_image: null,
  cover_image: null,
})


// Touched
const handleBlur = (field) => { touched.value[field] = true }

// Validaciones simples
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
const validateField = (key) => {
  const labels = {
    firstname: 'Nombre',
    lastname: 'Apellido',
    phone: 'Teléfono',
    country: 'País',
    specialty: 'Especialidad'
  }
  return validateRequired(form[key], labels[key] || key)
}

const isFormValid = computed(() =>
  !validateRequired(form.user_name || props.teacher?.user?.name || '', 'Nombre de cuenta') &&
  !validateEmail(form.user_email || props.teacher?.user?.email || '') &&
  !validateField('firstname') &&
  !validateField('lastname') &&
  !validateField('country') &&
  !validatePassword() &&
  !validatePasswordConfirmation()
)
// Enviar con transform para incluir flags de eliminación
function submit() {
  Object.keys(form).forEach(k => (touched.value[k] = true))
  if (!isFormValid.value) {
    focusFirstError()
    return
  }
form.transform((data) => {
  const fileOrNull = (v) => (v instanceof File ? v : null)
  const payload = {
    ...data,
    profile_image: fileOrNull(data.profile_image),
    cover_image: fileOrNull(data.cover_image),
    remove_profile_image: removeProfileImage.value ? 1 : 0,
    remove_cover_image: removeCoverImage.value ? 1 : 0,
  }

  // Si password está vacío, no lo mandamos para que el backend no lo procese
  if (!data.password) {
    delete payload.password
    delete payload.password_confirmation
  }

  return payload
}).post(route('admin.teachers.update', { teacher: props.teacher.id }), {
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

const validatePassword = () => {
  // Solo valida si el usuario escribió algo
  if (!form.password) return ''
  if (form.password.length < 8) return 'Debe tener al menos 8 caracteres'
  if (!/[A-Z]/.test(form.password)) return 'Debe contener una mayúscula'
  if (!/[0-9]/.test(form.password)) return 'Debe contener un número'
  return ''
}

const validatePasswordConfirmation = () => {
  // Solo valida si el usuario pretende cambiar password
  if (!form.password) return ''
  if (!form.password_confirmation) return 'Confirma la contraseña'
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden'
  return ''
}


// Mantener pestaña al llegar con errores del servidor
onMounted(() => {
  const errKeys = Object.keys(form.errors || {})
  if (errKeys.length) {
    // Campos de la pestaña social
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

/* Transición suave para sección de cuenta */
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
