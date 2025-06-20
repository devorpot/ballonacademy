<template>
  <Head title="Crear Nuevo Estudiante" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Estudiantes', route: 'admin.students.index' },
        { label: 'Crear', route: '' }
      ]"
    />

    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Crear Estudiante'" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.students.index')" />
          </div>
        </div>
      </div>
    </section>

    <section class="section-form my-2">
      <div class="container-fluid">
        <form @submit.prevent="submit" class="form " novalidate>
          <div class="card">
            <div class="card-body">
              <h6 class="text-muted mb-3">Datos de usuario</h6>
              <div class="row">
                   <div class="col-md-6 mb-3">
                         <FieldText
                          id="firstname"
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
                    <!-- Matrícula -->
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

                   <!-- Nombre -->
                    <div class="col-md-6 mb-3">
                      <FieldText
                          id="firstname"
                          label="Nombre (s)"
                          v-model="form.firstname"
                          :required="true"
                          :showValidation="touched.firstname"
                          :formError="form.errors.firstname"
                          :validateFunction="() => validateField('firstname')"
                          placeholder="Ingrese nombre"
                          @blur="() => handleBlur('firstname')"
                      />
                    </div>

                    <!-- Apellido -->
                   <!-- Apellido -->
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
                  <!-- Teléfono -->
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

                    <!-- Talla camiseta -->
                  <div class="col-md-6 mb-3 ">
                      <FieldSelect
                          id="shirt_size"
                          label="Talla camiseta"
                          v-model="form.shirt_size"
                          :required="true"
                          :showValidation="touched.shirt_size"
                          :formError="form.errors.shirt_size"
                          :validateFunction="() => validateField('shirt_size')"
                          :options="shirtSizes"
                          @blur="() => handleBlur('shirt_size')"
                        />
                  </div>

                  <!-- Dirección -->
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
                <i class="bi bi-save me-2"></i>Guardar
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';


import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue';
import FieldPassword from '@/Components/Admin/Fields/FieldPassword.vue';
import FieldPhone from '@/Components/Admin/Fields/FieldPhone.vue'; 
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'; 
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'; 


const shirtSizes = [
  { value: 'c', label: 'Chica' },
  { value: 'm', label: 'Mediana' },
  { value: 'l', label: 'Grande' },
  { value: 'xl', label: 'Extragrande' }
]; 

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  student_id: '',
  firstname: '',
  lastname: '',
  phone: '',
  shirt_size: '',
  address: '',
  country: ''
});

const studentFields = [
  { key: 'student_id', label: 'Matrícula' },
  { key: 'firstname', label: 'Nombre' },
  { key: 'lastname', label: 'Apellido' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'shirt_size', label: 'Talla camiseta' },
  { key: 'address', label: 'Dirección' },
  { key: 'country', label: 'País' }
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
  if (!/[A-Z]/.test(form.password)) return 'Debe incluir una mayúscula';
  if (!/[0-9]/.test(form.password)) return 'Debe incluir un número';
  return '';
};

const validatePasswordConfirmation = () => {
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden';
  return '';
};

const validateField = (field) => {
  if (!form[field] || !form[field].trim()) return `El campo ${field} es obligatorio`;
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
