<!-- resources/js/Pages/Admin/Students/Create.vue -->
<template>
  <Head title="Crear Nuevo Estudiante" />
  <AdminLayout>
 
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Estudiantes', route: 'admin.students.index' },
          { label: 'Crear Estudiante', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row ">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.students.index')" />
              <button 
                type="button" 
                class="btn btn-primary"
                :disabled="form.processing || !isFormValid"
                @click="submit"
              >
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i class="bi bi-save me-2"></i>Guardar
              </button>
            </div>
          </div>
        </div>
      </section>

      <section class="section-panel my-2">
        <div class="container-fluid">
         <div class="row">
           <div class="col-12">
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

                    <h6 class="text-muted mt-4 mb-3">Datos del estudiante</h6>
                    <div class="row">
                      <div v-for="field in studentFields" :key="field.key" class="col-md-6 mb-3">
                        <component
                          :is="field.component"
                          :id="field.key"
                          :label="field.label"
                          v-model="form[field.key]"
                          :required="true"
                          :showValidation="touched[field.key]"
                          :formError="form.errors[field.key]"
                          :validateFunction="() => validateField(field.key)"
                          :options="field.options"
                          :placeholder="field.placeholder"
                          @blur="() => handleBlur(field.key)"
                        />
                      </div>
                    </div>
                  </div>
                  <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                      <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                      <i class="bi bi-save me-2"></i>Guardar
                    </button>
                  </div>
                </div>
              </form>
           </div>
         </div>
        </div>
      </section>

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
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  firstname: '',
  lastname: '',
  phone: '',
  shirt_size: '',
  address: '',
  country: ''
});

const shirtSizes = [
  { value: 'c', label: 'Chica' },
  { value: 'm', label: 'Mediana' },
  { value: 'l', label: 'Grande' },
  { value: 'xl', label: 'Extragrande' }
];

const studentFields = [
  { key: 'firstname', label: 'Nombre(s)', component: FieldText, placeholder: 'Ingrese nombre' },
  { key: 'lastname', label: 'Apellido', component: FieldText, placeholder: 'Ingrese apellido' },
  { key: 'phone', label: 'Teléfono', component: FieldPhone, placeholder: 'Ingrese teléfono' },
  { key: 'shirt_size', label: 'Talla camiseta', component: FieldSelect, options: shirtSizes },
  { key: 'address', label: 'Dirección', component: FieldTextarea, placeholder: 'Ingrese dirección' },
  { key: 'country', label: 'País', component: FieldText, placeholder: 'Ingrese país' }
];

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateName = () => {
  if (!form.name.trim()) return 'El nombre es obligatorio';
  if (form.name.length < 3) return 'Mínimo 3 caracteres';
  return '';
};

const validateEmail = () => {
  if (!form.email.trim()) return 'El email es obligatorio';
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.email)) return 'Email inválido';
  return '';
};

const validatePassword = () => {
  if (!form.password) return 'La contraseña es obligatoria';
  if (form.password.length < 8) return 'Mínimo 8 caracteres';
  return '';
};

const validatePasswordConfirmation = () => {
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden';
  return '';
};

const validateField = (field) => {
  const value = form[field];
  if (!value || (typeof value === 'string' && !value.trim())) return `El campo ${field} es obligatorio`;
  return '';
};

const isFormValid = computed(() => {
  return !validateName() &&
         !validateEmail() &&
         !validatePassword() &&
         !validatePasswordConfirmation() &&
         studentFields.every(f => !validateField(f.key));
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.students.store'), {
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};
</script>
