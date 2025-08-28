<script setup>
import { Inertia } from '@inertiajs/inertia';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import StudentLayout from '@/Layouts/StudentLayout.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue';

const toast = ref(null);

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
  rfc: props.profile?.rfc || '',
  business_name: props.profile?.business_name || '',
  firstname: props.profile?.firstname || '',
  lastname: props.profile?.lastname || '',
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
  remove_profile_image: false,
  remove_cover_image: false,
  website: props.profile?.website || '',
  facebook: props.profile?.facebook || '',
  instagram: props.profile?.instagram || '',
  tiktok: props.profile?.tiktok || '',
  youtube: props.profile?.youtube || '',
  description: props.profile?.description || '',
  locale: props.user?.locale || 'es',
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
         ['es','en'].includes(form.locale) &&
         !validateField('country') &&
         !validateField('nickname');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    // Solo borra archivos si realmente son nulos y no hay flag de borrado
 form.transform((data) => {
  const payload = { ...data };

  // No envíes el campo si el usuario quitó la imagen (flag true) o si es null
  if (payload.remove_profile_image || payload.profile_image === null) {
    delete payload.profile_image;
  }
  if (payload.remove_cover_image || payload.cover_image === null) {
    delete payload.cover_image;
  }

  return payload;
}).post(route('dashboard.profile.update', props.user.id), {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        toast.value = { message: 'Perfil actualizado correctamente.', type: 'success' };
        // Resetear flags y campos de archivos si lo deseas aquí
        form.remove_profile_image = false;
        form.remove_cover_image = false;
        // Puedes recargar la página si quieres refrescar previews
        Inertia.visit(route('dashboard.profile.edit', props.user.id), { preserveScroll: true, replace: true })
      },
      onError: () => {
        toast.value = { message: 'Ocurrió un error al guardar el perfil.', type: 'danger' };
      }
    });
  }
};

const page = usePage();

onMounted(() => {
  if (page.props.flash.success) {
    toast.value = { message: page.props.flash.success, type: 'success' };
  }
  if (page.props.flash.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' };
  }
});
</script>

<template>
  <Head :title="'Editar Perfil'" />
  <StudentLayout>
  <Breadcrumbs username="estudiante" :breadcrumbs="[
      { label: 'Dashboard', route: 'dashboard.index' },
       
       { label: 'Mi Perfil', route: '' },
    ]" />
      
    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
    <section class="section-panel mb-4">
      <div class="container-fluid">
         
        <form @submit.prevent="submit" novalidate>
          <div class="card">
            <div class="card-header">
                <h5 class="card-title">Editar Perfil</h5>
            </div>
            <div class="card-body">
              <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                  <a class="nav-link my-2" :class="{ active: activeTab === 'personal' }" href="#" @click.prevent="activeTab = 'personal'">Perfil Personal</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link my-2" :class="{ active: activeTab === 'billing' }" href="#" @click.prevent="activeTab = 'billing'">Datos de Facturación</a>
                </li>
              </ul>
              <div v-if="activeTab === 'personal'">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <FieldText id="firstname" label="Nombre" v-model="form.firstname" :required="true" :showValidation="touched.firstname" :formError="form.errors.firstname" placeholder="Ingrese su Nombre" @blur="() => handleBlur('firstname')" />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldText id="lastname" label="Apellido" v-model="form.lastname" :required="true" :showValidation="touched.lastname" :formError="form.errors.lastname" placeholder="Ingrese su Apellido" @blur="() => handleBlur('lastname')" />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldEmail id="personal_email" label="Email personal" v-model="form.personal_email" :required="true" :showValidation="touched.personal_email" :formError="form.errors.personal_email" :validateFunction="validateEmail" placeholder="Ingrese el email personal" @blur="() => handleBlur('personal_email')" />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldText id="country" label="País" v-model="form.country" :required="true" :showValidation="touched.country" :formError="form.errors.country" :validateFunction="() => validateField('country')" placeholder="Ingrese país" @blur="() => handleBlur('country')" />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldText id="whatsapp" label="Whatsapp" v-model="form.whatsapp" :showValidation="touched.whatsapp" :formError="form.errors.whatsapp" placeholder="Ingrese WhatsApp" @blur="() => handleBlur('whatsapp')" />
                  </div>

                <div class="col-md-6 mb-3">
                  <FieldSelect
                    id="locale"
                    label="Idioma de la interfaz"
                    v-model="form.locale"
                    :options="[
                      { value: 'es', label: 'Español (MX)' },
                      { value: 'en', label: 'English (US)' }
                    ]"
                    :required="true"
                    :showValidation="touched.locale"
                    :formError="form.errors.locale"
                    @blur="() => handleBlur('locale')"
                  />
                </div>

                  <div class="col-md-6 mb-3">
                    <FieldText id="nickname" label="Nickname" v-model="form.nickname" :required="true" :showValidation="touched.nickname" :formError="form.errors.nickname" :validateFunction="() => validateField('nickname')" placeholder="Ingrese nickname" @blur="() => handleBlur('nickname')" />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldImage id="profile_image" label="Foto de perfil" v-model="form.profile_image" :initialPreview="profileImagePreview" @image-removed="form.remove_profile_image = true" />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldImage id="cover_image" label="Foto de portada" v-model="form.cover_image" :initialPreview="coverImagePreview" @image-removed="form.remove_cover_image = true" />
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
  </StudentLayout>
</template>

<style scoped>
.video_player {
  width: 100%;
  height: 80vh;
}
.ratio {
  max-width: 100%;
}
</style>
