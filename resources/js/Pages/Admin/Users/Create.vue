<template>
  <Head title="Crear Nuevo Usuario" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
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
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.users.index')" />
                <button
                  class="btn btn-primary"
                  :disabled="form.processing || !isFormValid"
                  @click="submit"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-1"></i> Guardar
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
                        :showValidation="touched.phone"
                        :formError="form.errors.phone"
                        :validateFunction="validatePhone"
                        placeholder="Ingrese el teléfono"
                        @blur="() => handleBlur('phone')"
                      />
                    </div>

                    <!-- Contraseña -->
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

                    <!-- Roles múltiple -->
                    <div class="col-12 mb-3">
                      <FieldCheckboxes
                        v-model="form.role_ids"
                        :items="roles"
                        label="Roles (selección múltiple)"
                        id-prefix="role_"
                        select-all-id="select_all_roles"
                        select-all-label="Seleccionar todos los roles"
                        :multiple="true"
                        :showValidation="touched.role_ids"
                        :formError="form.errors.role_ids || form.errors.role"
                        :validateFunction="validateRoles"
                        @change="handleRoleChange"
                      />
                    </div>
                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Guardar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>
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

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const handleRoleChange = () => {
  touched.value.role_ids = true;
};

const validateName = () => {
  if (!form.name.trim()) return 'El nombre es requerido';
  if (form.name.length < 3) return 'El nombre debe tener al menos 3 caracteres';
  return '';
};

const validateEmail = () => {
  if (!form.email.trim()) return 'El email es requerido';
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!regex.test(form.email)) return 'Ingrese un email válido';
  return '';
};

const validatePhone = () => {
  if (form.phone && !/^\d{10}$/.test(form.phone)) return 'Ingrese un teléfono válido de 10 dígitos';
  return '';
};

const validatePassword = () => {
  if (!form.password) return 'La contraseña es requerida';
  if (form.password.length < 8) return 'Debe tener al menos 8 caracteres';
  if (!/[A-Z]/.test(form.password)) return 'Debe contener al menos una mayúscula';
  if (!/[0-9]/.test(form.password)) return 'Debe contener al menos un número';
  return '';
};

const validatePasswordConfirmation = () => {
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden';
  return '';
};

const validateRoles = () => {
  if (form.role_ids.length === 0) return 'Seleccione al menos un rol';
  return '';
};

const isFormValid = computed(() =>
  !validateName() &&
  !validateEmail() &&
  !validatePhone() &&
  !validatePassword() &&
  !validatePasswordConfirmation() &&
  !validateRoles()
);

const submit = () => {
  Object.keys(form).forEach(k => touched.value[k] = true);
  if (isFormValid.value) {
    form.post(route('admin.users.store'), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        touched.value = {};
      }
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
