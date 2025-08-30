<template>
  <Head :title="`Perfil fiscal de ${user.name}`" />

  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Estudiantes', route: 'admin.students.index' },
            { label: 'Perfil', route: '' }
          ]"
        />

        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.students.index')" />
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
                        :class="{ active: activeTab === 'billing' }"
                        href="#"
                        role="tab"
                        aria-controls="tab-billing"
                        :aria-selected="activeTab === 'billing'"
                        @click.prevent="switchTab('billing')"
                      >Datos </a>
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
                    <div v-if="showAccount" id="account-section" class="alert alert-secondary small">
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
                      </div>
                    </div>
                  </transition>

                  <!-- Pestañas -->
                  <div v-show="activeTab === 'personal'" id="tab-personal" role="tabpanel">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="firstname"
                          label="Nombre(s)"
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
                          label="Apellido(s)"
                          v-model="form.lastname"
                          :required="true"
                          :showValidation="touched.lastname"
                          :formError="form.errors.lastname"
                          :validateFunction="() => validateField('lastname')"
                          placeholder="Ingrese los apellidos"
                          @blur="() => handleBlur('lastname')"
                        />
                      </div>

                      <div class="col-md-6 mb-3">
                        <FieldEmail
                          id="personal_email"
                          label="Email personal"
                          v-model="form.personal_email"
                          :required="true"
                          :showValidation="touched.personal_email"
                          :formError="form.errors.personal_email"
                          :validateFunction="validateEmail"
                          placeholder="Ingrese el email personal"
                          @blur="() => handleBlur('personal_email')"
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
                          id="whatsapp"
                          label="Whatsapp"
                          v-model="form.whatsapp"
                          :showValidation="touched.whatsapp"
                          :formError="form.errors.whatsapp"
                          placeholder="Ingrese WhatsApp"
                          @blur="() => handleBlur('whatsapp')"
                        />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="nickname"
                          label="Nickname"
                          v-model="form.nickname"
                          :required="true"
                          :showValidation="touched.nickname"
                          :formError="form.errors.nickname"
                          :validateFunction="() => validateField('nickname')"
                          placeholder="Ingrese nickname"
                          @blur="() => handleBlur('nickname')"
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
                      <div class="col-md-12 mb-3">
                        <FieldTextarea id="description" label="Descripción" v-model="form.description" />
                      </div>
                    </div>
                  </div>

                  <div v-show="activeTab === 'billing'" id="tab-billing" role="tabpanel">
                    <div class="row">
                      
                  
                      <div class="col-md-6 mb-3">
                        <FieldText id="street" label="Calle" v-model="form.street" />
                      </div>
                      <div class="col-md-3 mb-3">
                        <FieldText id="external_number" label="Número exterior" v-model="form.external_number" />
                      </div>
                      <div class="col-md-3 mb-3">
                        <FieldText id="internal_number" label="Número interior" v-model="form.internal_number" />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText id="state" label="Estado" v-model="form.state" />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText id="municipality" label="Municipio" v-model="form.municipality" />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText id="neighborhood" label="Colonia" v-model="form.neighborhood" />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText id="postal_code" label="Código postal" v-model="form.postal_code" />
                      </div>
                     
                       <div class="col-md-4 mb-3">
                        <FieldText id="bussines_own" label="¿Cuentas con negocio propio?" v-model="form.bussines_own" />
                      </div>
                       <div class="col-md-4 mb-3">
                        <FieldText id="activity" label="¿A qué te dedicas actualmente" v-model="form.activity" />
                      </div>
                       <div class="col-md-4 mb-3">
                        <FieldText id="experiencie" label="¿Cuentas con experiencia en decoración?" v-model="form.experiencie" />
                      </div>
                          <div class="col-md-6 mb-3">
                        <FieldText id="business_name" label="Nombre del Negocio" v-model="form.business_name" />
                      </div>
                       <div class="col-md-4 mb-3">
                        <FieldText id="bussines_name" label="Giro del negocio" v-model="form.bussines_category" />
                      </div>
                       <div class="col-md-4 mb-3">
                        <FieldUrl id="bussines_website" label="sitio web del negocio" v-model="form.bussines_website" />
                      </div>
                      <FieldImage
                          id="bussines_logo"
                          label=""
                          v-model="form.bussines_logo"
                          :initialPreview="bussinesLogoPreview"
                          :disabled="form.processing"
                        />
                        <small v-if="removeBussinesLogo" class="text-danger d-block mt-1">
                          Se eliminará la imagen de perfil al guardar.
                        </small>
                        <div v-if="form.errors.bussines_logo" class="invalid-feedback d-block">
                          {{ form.errors.bussines_logo }}
                        </div>
                    
                      <div class="col-md-4 mb-3">
                        <FieldEmail id="billing_email" label="Correo del negocio" v-model="form.billing_email" />
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
import { ref, computed, watch, nextTick, onMounted } from 'vue'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'
import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'
import FieldUrl from '@/Components/Admin/Fields/FieldUrl.vue'
const props = defineProps({
  user: { type: Object, required: true },
  profile: { type: Object, required: true }
})

const TAB_KEY = `students.edit.tab.${props.user.id}`
const activeTab = ref(localStorage.getItem(TAB_KEY) || 'personal')
const showAccount = ref(false)
const touched = ref({})

const profileImagePreview = props.profile?.profile_image ? `/storage/${props.profile.profile_image}` : null

const bussinesLogoPreview = props.profile?.bussines_logo ? `/storage/${props.profile.bussines_logo}` : null

const coverImagePreview   = props.profile?.cover_image   ? `/storage/${props.profile.cover_image}`   : null
const hasCurrentProfileImage = !!profileImagePreview
const hasCurrentCoverImage   = !!coverImagePreview

// Flags de eliminación de imágenes
const removeProfileImage = ref(false)
const removeCoverImage   = ref(false)
const removeBussinesLogo  = ref(false)
const form = useForm({
  _method: 'PUT',

  // Campos opcionales de User
  user_name: props.user?.name ?? '',
  user_email: props.user?.email ?? '',
  user_active: !!props.user?.active,
  user_locale: props.user?.locale ?? 'es',

  // Requeridos por validateProfile()
  firstname: props.profile?.firstname || '',
  lastname:  props.profile?.lastname  || '',

  // Fiscales
  rfc: props.profile?.rfc || '',
  business_name: props.profile?.business_name || '',
  street: props.profile?.street || '',
  external_number: props.profile?.external_number || '',
  internal_number: props.profile?.internal_number || '',
  state: props.profile?.state || '',
  municipality: props.profile?.municipality || '',
  neighborhood: props.profile?.neighborhood || '',
  postal_code: props.profile?.postal_code || '',
  billing_email: props.profile?.billing_email || props.user.email || '',
  tax_regime: props.profile?.tax_regime || '',
  cfdi_use: props.profile?.cfdi_use || '',

  // Personales requeridos
  personal_email: props.profile?.personal_email || props.user.email || '',
  country: props.profile?.country || '',
  whatsapp: props.profile?.whatsapp || '',
  nickname: props.profile?.nickname || '',

  // Archivos (nuevos)
  profile_image: null,
  cover_image: null,
  bussines_logo: null,
  // Redes
  website: props.profile?.website || '',
  facebook: props.profile?.facebook || '',
  instagram: props.profile?.instagram || '',
  tiktok: props.profile?.tiktok || '',
  youtube: props.profile?.youtube || '',
  description: props.profile?.description || '',
  activity: props.profile?.activity || '',
  experiencie: props.profile?.experiencie || '',
  bussines_own: props.profile?.bussines_own || '',
  bussines_website: props.profile?.bussines_website || '',
  bussines_category: props.profile?.bussines_category || ''
})

// Persistencia de pestaña
function switchTab(tab) {
  activeTab.value = tab
  localStorage.setItem(TAB_KEY, tab)
}

// Touched
const handleBlur = (field) => {
  touched.value[field] = true
}

// Validaciones simples de front
const validateEmail = () => {
  const val = (form.personal_email || '').trim()
  if (!val) return 'El email es requerido'
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(val)) return 'Ingrese un email válido'
  return ''
}

const validateField = (field) => {
  const v = form[field]
  if (v === undefined || v === null) return `El campo ${field} es obligatorio`
  if (typeof v === 'string' && v.trim() === '') return `El campo ${field} es obligatorio`
  return ''
}

const isFormValid = computed(() =>
  !validateEmail()
  && !validateField('firstname')
  && !validateField('lastname')
  && !validateField('country')
  && !validateField('nickname')
)

// Enviar con transform para incluir flags de eliminación
function submit() {
  Object.keys(form).forEach(k => (touched.value[k] = true))
  if (!isFormValid.value) {
    focusFirstError()
    return
  }

  form.transform((data) => ({
    ...data,
    remove_profile_image: removeProfileImage.value ? 1 : 0,
    remove_cover_image: removeCoverImage.value ? 1 : 0
  })).post(route('admin.students.update', { user: props.user.id }), {
    preserveScroll: true,
    forceFormData: true,
    onError: () => nextTick(() => focusFirstError()),
  })
}

// UX: llevar el foco al primer error devuelto por el servidor
function focusFirstError() {
  const keys = Object.keys(form.errors || {})
  if (!keys.length) return
  const first = keys[0]
  const el = document.getElementById(first)
  if (el && typeof el.focus === 'function') el.focus()
}

// Guardar pestaña activa en cambios de hash o navegación interna
onMounted(() => {
  // Si el servidor devuelve errores, permanecer en la pestaña que contiene el primer error
  const errKeys = Object.keys(form.errors || {})
  if (errKeys.length) {
    const billingSet = new Set(['rfc','business_name','street','external_number','internal_number','state','municipality','neighborhood','postal_code','billing_email','tax_regime','cfdi_use'])
    const first = errKeys[0]
    activeTab.value = billingSet.has(first) ? 'billing' : 'personal'
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
