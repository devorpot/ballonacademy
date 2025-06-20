<template>
  <Head title="Crear Nuevo Usuario" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Usuarios', route: 'admin.users.index' },
        { label: 'Crear', route: '' }
      ]"
    />
    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div v-if="form.hasErrors" class="alert alert-danger">
              <div v-for="error in Object.values(form.errors).flat()" :key="error">
                {{ error }}
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Crear Usuario'" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.users.index')" />
          </div>
        </div>
      </div>
    </section>

    <section class="section-form my-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <form @submit.prevent="submit" novalidate>
              <div class="card">
                <div class="card-body">
                  <div class="row">

                    <!-- Nombre -->
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="name"
                        label="Nombre"
                        v-model="form.name"
                        :required="true"
                        :showValidation="touched.name"
                        :formError="form.errors.name"
                        :validateFunction="validateName"
                        placeholder="Ingrese el nombre"
                        @blur="() => handleBlur('name')"
                      />
                    </div>

                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                      <FieldEmail
                        id="email"
                        label="Correo Electrónico"
                        v-model="form.email"
                        :required="true"
                        :showValidation="touched.email"
                        :formError="form.errors.email"
                        :validateFunction="validateEmail"
                        placeholder="Ingrese el correo"
                        @blur="() => handleBlur('email')"
                      />
                    </div>

                    <!-- Teléfono -->
                    <div class="col-md-6 mb-3">
                      <FieldPhone
                        id="phone"
                        label="Teléfono"
                        v-model="form.phone"
                        :required="false"
                        :showValidation="touched.phone"
                        :formError="form.errors.phone"
                        :validateFunction="validatePhone"
                        placeholder="Ingrese el teléfono"
                        @blur="() => handleBlur('phone')"
                      />
                    </div>

                    <!-- Contraseña + Confirmación -->
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

                    <!-- Roles -->
                    <div class="col-12 mb-3"> 
                      <FieldCheckboxes
                      v-model="form.role_ids"
                      :items="roles"
                      label="Roles"
                      id-prefix="role_"
                      select-all-id="select_all_roles"
                      select-all-label="Seleccionar todos los roles"
                      :showValidation="touched.role_ids"
                      :formError="form.errors.role_ids || form.errors.role"
                      :validateFunction="validateRoles"
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
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';

// Components
import Title from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue';
import FieldPhone from '@/Components/Admin/Fields/FieldPhone.vue';
import FieldPassword from '@/Components/Admin/Fields/FieldPassword.vue';

import FieldCheckboxes from '@/Components/Admin/Fields/FieldCheckboxes.vue';

const props = defineProps({
  roles: Array
});

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role_ids: []
});

const touched = ref({
  name: false,
  email: false,
  phone: false,
  password: false,
  password_confirmation: false,
  role_ids: false
});

const allRolesSelected = computed({
  get: () => form.role_ids.length === props.roles.length,
  set: (value) => {
    form.role_ids = value ? props.roles.map(r => r.id) : [];
    touched.value.role_ids = true;
  }
});

// Validations
const validateName = () => !form.name.trim() ? 'El nombre es requerido' : (form.name.length < 3 ? 'El nombre debe tener al menos 3 caracteres' : '');
const validateEmail = () => {
  if (!form.email.trim()) return 'El email es requerido';
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return !regex.test(form.email) ? 'Ingrese un email válido' : '';
};
const validatePhone = () => form.phone && !/^\d{10}$/.test(form.phone) ? 'Ingrese un teléfono válido de 10 dígitos' : '';
const validatePassword = () => {
  if (!form.password) return 'La contraseña es requerida';
  if (form.password.length < 8) return 'Debe tener al menos 8 caracteres';
  if (!/[A-Z]/.test(form.password)) return 'Debe contener al menos una mayúscula';
  if (!/[0-9]/.test(form.password)) return 'Debe contener al menos un número';
  return '';
};
const validatePasswordConfirmation = () => form.password !== form.password_confirmation ? 'Las contraseñas no coinciden' : '';
const validateRoles = () => form.role_ids.length === 0 ? 'Seleccione al menos un rol' : '';

const isFormValid = computed(() =>
  !validateName() &&
  !validateEmail() &&
  !validatePhone() &&
  !validatePassword() &&
  !validatePasswordConfirmation() &&
  !validateRoles()
);

const handleBlur = (field) => {
  touched.value[field] = true;
};

const handleRoleChange = () => {
  touched.value.role_ids = true;
};

const submit = () => {
  Object.keys(touched.value).forEach(k => touched.value[k] = true);
  if (isFormValid.value) {
    form.post(route('admin.users.store'), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        Object.keys(touched.value).forEach(k => touched.value[k] = false);
      }
    });
  }
};
</script>
