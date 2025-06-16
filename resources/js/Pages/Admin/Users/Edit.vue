<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';

const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  }
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_ids: []
});

// Estados para validación en tiempo real
const touched = ref({
  name: false,
  email: false,
  password: false,
  password_confirmation: false
});

const allRolesSelected = computed({
  get: () => form.role_ids.length === props.roles.length,
  set: (value) => {
    form.role_ids = value ? props.roles.map(role => role.id) : [];
  }
});

// Validaciones personalizadas
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
  if (!form.password) return 'La contraseña es requerida';
  if (form.password.length < 8) return 'La contraseña debe tener al menos 8 caracteres';
  if (!/[A-Z]/.test(form.password)) return 'La contraseña debe contener al menos una mayúscula';
  if (!/[0-9]/.test(form.password)) return 'La contraseña debe contener al menos un número';
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

const validateForm = () => {
  const errors = {
    name: validateName(),
    email: validateEmail(),
    password: validatePassword(),
    password_confirmation: validatePasswordConfirmation(),
    role_ids: validateRoles()
  };
  
  return {
    errors,
    isValid: !Object.values(errors).some(error => error !== '')
  };
};

const handleBlur = (field) => {
  touched.value[field] = true;
};

const submit = () => {
  // Marcar todos los campos como tocados al enviar
  Object.keys(touched.value).forEach(key => {
    touched.value[key] = true;
  });
  
  const { isValid } = validateForm();
  
  if (isValid) {
    form.post(route('admin.users.store'), {
      preserveScroll: true,
      onSuccess: () => form.reset(),
      onError: () => {
        // Manejar errores del servidor si es necesario
      }
    });
  }
};
</script>

<template>
  <Head title="Crear Nuevo Usuario" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h1 class="h3 mb-0">
            <i class="bi bi-person-plus me-2"></i>Crear Nuevo Usuario
          </h1>
          <Link :href="route('admin.users.index')" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Volver
          </Link>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form @submit.prevent="submit" novalidate>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  v-model="form.name"
                  @blur="handleBlur('name')"
                  :class="{
                    'is-invalid': (touched.name && validateName()) || form.errors.name
                  }"
                >
                <div class="invalid-feedback">
                  {{ touched.name ? validateName() : '' || form.errors.name }}
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  v-model="form.email"
                  @blur="handleBlur('email')"
                  :class="{
                    'is-invalid': (touched.email && validateEmail()) || form.errors.email
                  }"
                >
                <div class="invalid-feedback">
                  {{ touched.email ? validateEmail() : '' || form.errors.email }}
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  id="password"
                  v-model="form.password"
                  @blur="handleBlur('password')"
                  :class="{
                    'is-invalid': (touched.password && validatePassword()) || form.errors.password
                  }"
                >
                <div class="invalid-feedback">
                  {{ touched.password ? validatePassword() : '' || form.errors.password }}
                </div>
                <small class="form-text text-muted">
                  Mínimo 8 caracteres, al menos una mayúscula y un número
                </small>
              </div>

              <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  @blur="handleBlur('password_confirmation')"
                  :class="{
                    'is-invalid': (touched.password_confirmation && validatePasswordConfirmation()) || form.errors.password_confirmation
                  }"
                >
                <div class="invalid-feedback">
                  {{ touched.password_confirmation ? validatePasswordConfirmation() : '' || form.errors.password_confirmation }}
                </div>
              </div>

              <div class="col-12 mb-3">
                <label class="form-label">Roles</label>

                <div class="form-check mb-2">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="select_all_roles"
                    v-model="allRolesSelected"
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
                      >
                      <label class="form-check-label" :for="'role_' + role.id">{{ role.name }}</label>
                    </div>
                  </div>
                </div>

                <div v-if="validateRoles() && (form.role_ids.length === 0 || form.errors.role_ids)" class="text-danger mt-2">
                  {{ validateRoles() || form.errors.role_ids || form.errors.role }}
                </div>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i>Guardar
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>