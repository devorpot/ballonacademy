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
                  </div>

                  <h6 class="text-muted mt-4 mb-3">Datos del estudiante</h6>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="student_id"
                        label="Matrícula"
                        v-model="form.student_id"
                        :required="true"
                        :showValidation="touched.student_id"
                        :formError="form.errors.student_id"
                        :validateFunction="() => validateField('student_id')"
                        placeholder="Ingrese matrícula"
                        @blur="() => handleBlur('student_id')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="firstname"
                        label="Nombre"
                        v-model="form.firstname"
                        :required="true"
                        :showValidation="touched.firstname"
                        :formError="form.errors.firstname"
                        :validateFunction="() => validateField('firstname')"
                        placeholder="Ingrese nombre"
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
                        placeholder="Ingrese apellido"
                        @blur="() => handleBlur('lastname')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldPhone
                        id="phone"
                        label="Teléfono"
                        v-model="form.phone"
                        :required="true"
                        :showValidation="touched.phone"
                        :formError="form.errors.phone"
                        :validateFunction="() => validateField('phone')"
                        placeholder="Ingrese teléfono"
                        @blur="() => handleBlur('phone')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="shirt_size"
                        label="Talla de camiseta"
                        v-model="form.shirt_size"
                        :required="true"
                        :showValidation="touched.shirt_size"
                        :formError="form.errors.shirt_size"
                        :validateFunction="() => validateField('shirt_size')"
                        :options="shirtSizes"
                        @blur="() => handleBlur('shirt_size')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldTextarea
                        id="address"
                        label="Dirección"
                        v-model="form.address"
                        :required="true"
                        :showValidation="touched.address"
                        :formError="form.errors.address"
                        :validateFunction="() => validateField('address')"
                        placeholder="Ingrese dirección"
                        @blur="() => handleBlur('address')"
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
  name: props.student.user.name,
  email: props.student.user.email,
  password: '',
  password_confirmation: '',
  student_id: props.student.student_id,
  firstname: props.student.firstname,
  lastname: props.student.lastname,
  phone: props.student.phone,
  shirt_size: props.student.shirt_size,
  address: props.student.address,
  country: props.student.country
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
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) return `El campo ${field} es obligatorio`;
  return '';
};

const isFormValid = computed(() => {
  return !validateName() &&
         !validateEmail() &&
         !validatePassword() &&
         !validatePasswordConfirmation() &&
         !validateField('student_id') &&
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
