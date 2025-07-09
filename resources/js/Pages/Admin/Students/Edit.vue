<template>
  <Head title="Editar Estudiante" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Estudiantes', route: 'admin.students.index' },
            { label: 'Editar', route: '' }
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
              <div class="card mb-3">
                <div class="card-body">
                  <!-- DATOS DE ACCESO -->
                  <fieldset class="row">
                    <legend class="h6 text-uppercase mb-4">Datos fiscales</legend>
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
                        :required="false"
                        :showValidation="touched.password || touched.password_confirmation"
                        :formError="form.errors.password"
                        :confirmFormError="form.errors.password_confirmation"
                        :validateFunction="validatePassword"
                        :validateConfirmFunction="validatePasswordConfirmation"
                        @update:password="val => form.password = val"
                        @update:passwordConfirmation="val => form.password_confirmation = val"
                        @blur="(field) => handleBlur(field)"
                      />
                      <small class="form-text text-muted">Dejar en blanco para mantener la actual</small>
                    </div>
                  </fieldset>
                    </div>
                  </div>
                  <!-- DATOS PERSONALES -->
                  <div class="card mb-3">
                    <div class="card-body">
                      <fieldset class="row">
                        <legend class="h6 text-uppercase mb-4">Datos personales</legend>
                        <div class="col-md-6 mb-3">
                          <FieldText id="firstname" label="Nombre" v-model="form.firstname" :formError="form.errors.firstname" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="lastname" label="Apellido" v-model="form.lastname" :formError="form.errors.lastname" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldPhone id="phone" label="Teléfono" v-model="form.phone" :formError="form.errors.phone" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldSelect
                            id="shirt_size"
                            label="Talla de camiseta"
                            v-model="form.shirt_size"
                            :formError="form.errors.shirt_size"
                            :options="shirtSizes"
                          />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldTextarea id="address" label="Dirección" v-model="form.address" :formError="form.errors.address" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="country" label="País" v-model="form.country" :formError="form.errors.country" />
                        </div>
                  
                        <div class="col-md-6 mb-3">
                          <FieldEmail id="personal_email" label="Correo personal" v-model="form.personal_email" :formError="form.errors.personal_email" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="whatsapp" label="WhatsApp" v-model="form.whatsapp" :formError="form.errors.whatsapp" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="nickname" label="Apodo" v-model="form.nickname" :formError="form.errors.nickname" />
                        </div>
                        
                        <div class="col-md-6 mb-3">
                          <FieldText id="website" label="Sitio web" v-model="form.website" :formError="form.errors.website" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="facebook" label="Facebook" v-model="form.facebook" :formError="form.errors.facebook" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="instagram" label="Instagram" v-model="form.instagram" :formError="form.errors.instagram" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="tiktok" label="TikTok" v-model="form.tiktok" :formError="form.errors.tiktok" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="youtube" label="YouTube" v-model="form.youtube" :formError="form.errors.youtube" />
                        </div>
                        <div class="col-12 mb-3">
                          <FieldTextarea id="description" label="Descripción" v-model="form.description" :formError="form.errors.description" />
                        </div>
                      </fieldset>
                    </div>
                  </div>

                  <!-- DATOS FISCALES -->
                  <div class="card mb-3">
                    <div class="card-body">
                      <fieldset class="row">
                        <legend class="h6 text-uppercase mb-4">Datos fiscales</legend>
                        <div class="col-md-6 mb-3">
                          <FieldText id="rfc" label="RFC" v-model="form.rfc" :formError="form.errors.rfc" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="business_name" label="Razón social" v-model="form.business_name" :formError="form.errors.business_name" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="street" label="Calle" v-model="form.street" :formError="form.errors.street" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="external_number" label="Número exterior" v-model="form.external_number" :formError="form.errors.external_number" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="internal_number" label="Número interior" v-model="form.internal_number" :formError="form.errors.internal_number" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="state" label="Estado" v-model="form.state" :formError="form.errors.state" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="municipality" label="Municipio" v-model="form.municipality" :formError="form.errors.municipality" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="neighborhood" label="Colonia" v-model="form.neighborhood" :formError="form.errors.neighborhood" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="postal_code" label="Código postal" v-model="form.postal_code" :formError="form.errors.postal_code" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldEmail id="billing_email" label="Correo para facturación" v-model="form.billing_email" :formError="form.errors.billing_email" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="tax_regime" label="Régimen fiscal" v-model="form.tax_regime" :formError="form.errors.tax_regime" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <FieldText id="cfdi_use" label="Uso de CFDI" v-model="form.cfdi_use" :formError="form.errors.cfdi_use" />
                        </div>
                      </fieldset>
                      </div>
                  </div>

               

                  <div class="row my-3">
                    <div class="col-12 col-md-12 text-end">
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

     
    </div>
     <SpinnerOverlay v-if="form.processing" />
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue';
import FieldPassword from '@/Components/Admin/Fields/FieldPassword.vue';
import FieldPhone from '@/Components/Admin/Fields/FieldPhone.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';

const props = defineProps({
  student: { type: Object, required: true }
});

const form = useForm({
  _method: 'PUT',

  // Datos de usuario
  name: props.student.user.name,
  email: props.student.user.email,
  password: '',
  password_confirmation: '',

  // Datos del estudiante
  student_id: props.student.student_id,
  firstname: props.student.firstname,
  lastname: props.student.lastname,
  phone: props.student.phone,
  shirt_size: props.student.shirt_size,
  address: props.student.address,
  country: props.student.country,

  // Datos del perfil
  rfc: props.student.user.profile?.rfc ?? '',
  business_name: props.student.user.profile?.business_name ?? '',
  street: props.student.user.profile?.street ?? '',
  external_number: props.student.user.profile?.external_number ?? '',
  internal_number: props.student.user.profile?.internal_number ?? '',
  state: props.student.user.profile?.state ?? '',
  municipality: props.student.user.profile?.municipality ?? '',
  neighborhood: props.student.user.profile?.neighborhood ?? '',
  postal_code: props.student.user.profile?.postal_code ?? '',
  billing_email: props.student.user.profile?.billing_email ?? '',
  tax_regime: props.student.user.profile?.tax_regime ?? '',
  cfdi_use: props.student.user.profile?.cfdi_use ?? '',
 
  personal_email: props.student.user.profile?.personal_email ?? '',
  whatsapp: props.student.user.profile?.whatsapp ?? '',
  nickname: props.student.user.profile?.nickname ?? '',
  profile_image: null,
  cover_image: null,
  website: props.student.user.profile?.website ?? '',
  facebook: props.student.user.profile?.facebook ?? '',
  instagram: props.student.user.profile?.instagram ?? '',
  tiktok: props.student.user.profile?.tiktok ?? '',
  youtube: props.student.user.profile?.youtube ?? '',
  description: props.student.user.profile?.description ?? '',
});



const shirtSizes = [
  { value: 'c', label: 'Chica' },
  { value: 'm', label: 'Mediana' },
  { value: 'l', label: 'Grande' },
  { value: 'xl', label: 'Extragrande' }
];

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateName = () => {
  if (!form.name.trim()) return 'El nombre es requerido';
  if (form.name.length < 3) return 'Debe tener al menos 3 caracteres';
  return '';
};

const validateEmail = () => {
  if (!form.email.trim()) return 'El email es requerido';
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.email)) return 'Ingrese un email válido';
  return '';
};

const validatePassword = () => {
  if (form.password && form.password.length < 8) return 'Debe tener al menos 8 caracteres';
  if (form.password && !/[A-Z]/.test(form.password)) return 'Debe contener una mayúscula';
  if (form.password && !/[0-9]/.test(form.password)) return 'Debe contener un número';
  return '';
};

const validatePasswordConfirmation = () => {
  if (form.password && form.password !== form.password_confirmation) return 'Las contraseñas no coinciden';
  return '';
};

const validateField = (field) => {
  const requiredFields = ['firstname', 'lastname', 'phone', 'shirt_size', 'address', 'country',, 'personal_email', 'nickname'];
  if (requiredFields.includes(field) && !form[field]) {
    return `El campo ${field} es obligatorio`;
  }
  return '';
};


const isFormValid = computed(() => {
  return !validateName() &&
         !validateEmail() &&
         !validatePassword() &&
         !validatePasswordConfirmation() &&
         !validateField('firstname') &&
         !validateField('lastname') &&
         !validateField('phone') &&
         !validateField('shirt_size') &&
         !validateField('address') &&
         !validateField('country');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);
  if (isFormValid.value) {
    form.post(route('admin.students.update', props.student.id), {
      forceFormData: true,
      preserveScroll: true
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
