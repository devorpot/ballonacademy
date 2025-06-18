<template>
  <Head title="Editar Usuario" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Usuarios', route: 'admin.users.index' },
        { label: 'Editar', route: '' }
      ]"
    />
      <!--section-heading-->
        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-4">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <Title :title="`Editar Usuario`" />

                <ButtonBack
                  label="Volver"
                  icon="bi bi-arrow-left"
                  :href="route('admin.users.index')"
                />
              </div>
            </div>
          </div>
        </section>
          <!--/section-heading-->

   
          <!--/section-form-->
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
                          <label for="name" class="form-label">Nombre</label>
                          <input
                            type="text"
                            class="form-control"
                            id="name"
                            v-model="form.name"
                            @blur="handleBlur('name')"
                            :class="{ 'is-invalid': (touched.name && validateName()) || form.errors.name }"
                          >
                          <div class="invalid-feedback">
                            {{ touched.name ? validateName() : '' || form.errors.name }}
                          </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                          <label for="email" class="form-label">Email</label>
                          <input
                            type="email"
                            class="form-control"
                            id="email"
                            v-model="form.email"
                            @blur="handleBlur('email')"
                            :class="{ 'is-invalid': (touched.email && validateEmail()) || form.errors.email }"
                          >
                          <div class="invalid-feedback">
                            {{ touched.email ? validateEmail() : '' || form.errors.email }}
                          </div>
                        </div>

                        <!-- Contraseña -->
                        <div class="col-md-6 mb-3">
                          <label for="password" class="form-label">Contraseña</label>
                          <input
                            type="password"
                            class="form-control"
                            id="password"
                            v-model="form.password"
                            @blur="handleBlur('password')"
                            :class="{ 'is-invalid': (touched.password && validatePassword()) || form.errors.password }"
                          >
                          <div class="invalid-feedback">
                            {{ touched.password ? validatePassword() : '' || form.errors.password }}
                          </div>
                          <small class="form-text text-muted">
                            Mínimo 8 caracteres, al menos una mayúscula y un número
                          </small>
                        </div>

                        <!-- Confirmar contraseña -->
                        <div class="col-md-6 mb-3">
                          <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                          <input
                            type="password"
                            class="form-control"
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            @blur="handleBlur('password_confirmation')"
                            :class="{ 'is-invalid': (touched.password_confirmation && validatePasswordConfirmation()) || form.errors.password_confirmation }"
                          >
                          <div class="invalid-feedback">
                            {{ touched.password_confirmation ? validatePasswordConfirmation() : '' || form.errors.password_confirmation }}
                          </div>
                        </div>

                        <!-- Roles -->
                        <div class="col-12 mb-3">
                          <label class="form-label">Roles</label>

                          <div class="form-check mb-2">
                            <input
                              class="form-check-input"
                              type="checkbox"
                              id="select_all_roles"
                              v-model="allRolesSelected"
                              @change="handleRoleChange"
                            >
                            <label class="form-check-label fw-bold" for="select_all_roles">Seleccionar todos los roles</label>
                          </div>

                          <div class="row">
                            <div class="col-md-3 mb-2" v-for="role in roles" :key="role.id">
                              <div class="form-check">
                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  :id="'role_' + role.id"
                                  :value="role.id"
                                  v-model="form.role_ids"
                                  @change="handleRoleChange"
                                >
                                <label class="form-check-label" :for="'role_' + role.id">{{ role.label }}</label>
                              </div>
                            </div>
                          </div>

                          <div v-if="(touched.role_ids && validateRoles()) || form.errors.role_ids" class="text-danger mt-2">
                            {{ touched.role_ids ? validateRoles() : '' || form.errors.role_ids }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer text-end">
                      <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="form.processing || !isFormValid"
                      >
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
          <!--/section-form-->
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';

import Title from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';

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
