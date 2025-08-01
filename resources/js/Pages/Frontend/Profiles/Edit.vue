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
                  <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                      <a class="nav-link" :class="{ active: activeTab === 'personal' }" href="#" @click.prevent="activeTab = 'personal'">Perfil Personal</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" :class="{ active: activeTab === 'billing' }" href="#" @click.prevent="activeTab = 'billing'">Datos de Facturación</a>
                    </li>
                  </ul>
                </div>

                <div class="card-body">
                  <div v-if="activeTab === 'personal'">
                    <div class="row">
                          <div class="col-md-6 mb-3">
                              <FieldText id="firstname" label="Nombre" v-model="form.firstname" :showValidation="touched.firstname" :formError="form.errors.firstname" placeholder="Ingrese su Nombre" @blur="() => handleBlur('firstname')" />
                            </div>
                             <div class="col-md-6 mb-3">
                              <FieldText id="lastname" label="Apellido" v-model="form.lastname" :showValidation="touched.lastname" :formError="form.errors.lastname" placeholder="Ingrese su Apellido" @blur="() => handleBlur('lastname')" />
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
                      <div class="col-md-6 mb-3">
                        <FieldImage
                          id="profile_image"
                          label="Foto de perfil"
                          v-model="form.profile_image"
                          :initialPreview="profileImagePreview"
                        />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldImage
                          id="cover_image"
                          label="Foto de portada"
                          v-model="form.cover_image"
                          :initialPreview="coverImagePreview"
                        />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText id="website" label="Sitio web" v-model="form.website" />
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

                  <div v-else>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <FieldText id="rfc" label="RFC" v-model="form.rfc" />
                      </div>
                      <div class="col-md-6 mb-3">
                        <FieldText id="business_name" label="Razón social" v-model="form.business_name" />
                      </div>
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
                        <FieldEmail id="billing_email" label="Correo para factura" v-model="form.billing_email" />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText id="tax_regime" label="Régimen fiscal" v-model="form.tax_regime" />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText id="cfdi_use" label="Uso de CFDI" v-model="form.cfdi_use" />
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
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';

const props = defineProps({
  user: Object,
  profile: Object
});

const activeTab = ref('personal');
const touched = ref({});

const profileImagePreview = props.profile?.profile_image
  ? `/storage/${props.profile.profile_image}`
  : null;

const coverImagePreview = props.profile?.cover_image
  ? `/storage/${props.profile.cover_image}`
  : null;

const form = useForm({
  _method: 'PUT',
  firstname: props.profile?.firstname || '',
  lastname: props.profile?.lastname || '',
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

 
  personal_email: props.profile?.personal_email || '',
  country: props.profile?.country || '',
  whatsapp: props.profile?.whatsapp || '',
  nickname: props.profile?.nickname || '',

  profile_image: null,
  cover_image: null,

  website: props.profile?.website || '',
  facebook: props.profile?.facebook || '',
  instagram: props.profile?.instagram || '',
  tiktok: props.profile?.tiktok || '',
  youtube: props.profile?.youtube || '',
  description: props.profile?.description || ''
});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateEmail = () => {
  if (!form.personal_email.trim()) return 'El email es requerido';
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.personal_email)) return 'Ingrese un email válido';
  return '';
};

const validateField = (field) => {
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) return `El campo ${field} es obligatorio`;
  return '';
};

const isFormValid = computed(() => {
  return  !validateEmail() &&
         !validateField('country') &&
         !validateField('nickname');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);
  if (isFormValid.value) {
    form.post(route('admin.profiles.update', props.user.id), {
      preserveScroll: true,
      forceFormData: true
    });
  }
};
</script>


<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
