<template>
  <Head title="Editar Usuario" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Usuarios', route: 'admin.users.index' },
            { label: 'Editar', route: '' }
          ]"
        />

        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-4">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <Title :title="`Editar Usuario`" />
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.users.index')" />
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
                        label="Email"
                        v-model="form.email"
                        :required="true"
                        :showValidation="touched.email"
                        :formError="form.errors.email"
                        :validateFunction="validateEmail"
                        placeholder="Ingrese el correo"
                        @blur="() => handleBlur('email')"
                      />
                    </div>

                    <!-- Contraseña -->
                    <div class="col-md-6 mb-3">
                      <FieldPassword
                        id="password"
                        confirmId="password_confirmation"
                        label="Contraseña (opcional)"
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
                      <small class="form-text text-muted">
                        Mínimo 8 caracteres, al menos una mayúscula y un número (si desea cambiar la contraseña)
                      </small>
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
                        @change="handleRoleChange"
                      />
                    </div>
                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i>Guardar cambios
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
import Title from '@/Components/Admin/Ui/Title.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue';
import FieldPassword from '@/Components/Admin/Fields/FieldPassword.vue';
import FieldCheckboxes from '@/Components/Admin/Fields/FieldCheckboxes.vue';

const props = defineProps({
  user: { type: Object, required: true },
  roles: { type: Array, required: true },
  user_roles: { type: Array, default: () => [] }
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  role_ids: props.user_roles
});

const touched = ref({
  name: false,
  email: false,
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

const validateName = () => {
  if (!form.name.trim()) return 'El nombre es requerido';
  if (form.name.length < 3) return 'El nombre debe tener al menos 3 caracteres';
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
  if (form.password && !/[A-Z]/.test(form.password)) return 'Debe contener al menos una mayúscula';
  if (form.password && !/[0-9]/.test(form.password)) return 'Debe contener al menos un número';
  return '';
};

const validatePasswordConfirmation = () => {
  if (form.password && form.password !== form.password_confirmation) return 'Las contraseñas no coinciden';
  return '';
};

const validateRoles = () => {
  if (form.role_ids.length === 0) return 'Seleccione al menos un rol';
  return '';
};

const isFormValid = computed(() => {
  return !validateName() &&
         !validateEmail() &&
         !validatePassword() &&
         !validatePasswordConfirmation() &&
         !validateRoles();
});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const handleRoleChange = () => {
  touched.value.role_ids = true;
};

const submit = () => {
  Object.keys(touched.value).forEach(key => {
    touched.value[key] = true;
  });

  if (isFormValid.value) {
    form.put(route('admin.users.update', props.user.id), {
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
