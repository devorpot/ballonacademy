<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" id="modal-add">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0">

        <div class="modal-header bg-light border-bottom-0">
          <h5 class="modal-title text-primary">
            <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
          </h5>
          <button type="button" class="btn-close" @click="close"></button>
        </div>

        <div class="modal-body">
          <!-- Nombre -->
          <div class="mb-3">
            <label for="username" class="form-label fw-bold">Nombre</label>
            <input
              v-model="form.name"
              id="username"
              type="text"
              class="form-control"
              @blur="handleBlur('name')"
              :class="{ 'is-invalid': (touched.name && validateName()) || form.errors.name }"
              placeholder="Ingrese el nombre"
            >
            <div class="invalid-feedback">
              {{ form.errors.name || (touched.name ? validateName() : '') }}
            </div>
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label fw-bold">Email</label>
            <input
              type="email"
              class="form-control"
              id="email"
              v-model="form.email"
              @blur="handleBlur('email')"
              :class="{ 'is-invalid': (touched.email && validateEmail()) || form.errors.email }"
              placeholder="usuario@ejemplo.com"
            >
            <div class="invalid-feedback">
              {{ form.errors.email || (touched.email ? validateEmail() : '') }}
            </div>
          </div>

          <!-- Contraseña -->
          <div class="mb-3">
            <label for="password" class="form-label fw-bold">Contraseña</label>
            <input
              v-model="form.password"
              id="password"
              type="password"
              class="form-control"
              @blur="handleBlur('password')"
              :class="{ 'is-invalid': (touched.password && validatePassword()) || form.errors.password }"
              placeholder="********"
            >
            <div class="invalid-feedback">
              {{ form.errors.password || (touched.password ? validatePassword() : '') }}
            </div>
            <small class="form-text text-muted">
              Mínimo 8 caracteres, una mayúscula y un número.
            </small>
          </div>

          <!-- Confirmar Contraseña -->
          <div class="mb-3">
            <label for="password_confirmation" class="form-label fw-bold">Confirmar Contraseña</label>
            <input
              type="password"
              class="form-control"
              id="password_confirmation"
              v-model="form.password_confirmation"
              @blur="handleBlur('password_confirmation')"
              :class="{ 'is-invalid': (touched.password_confirmation && validatePasswordConfirmation()) || form.errors.password_confirmation }"
              placeholder="********"
            >
            <div class="invalid-feedback">
              {{ form.errors.password_confirmation || (touched.password_confirmation ? validatePasswordConfirmation() : '') }}
            </div>
          </div>

          <!-- Roles -->
          <div class="mb-3">
            <label class="form-label fw-bold">Roles</label>

            <div class="form-check mb-2">
              <input
                type="checkbox"
                class="form-check-input"
                id="select_all_roles"
                v-model="allRolesSelected"
                @change="handleRoleChange"
              >
              <label for="select_all_roles" class="form-check-label">
                Seleccionar todos los roles
              </label>
            </div>

            <div class="row g-1">
              <div class="col-6" v-for="role in roles" :key="role.id">
                <div class="form-check">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :id="'role_' + role.id"
                    :value="role.id"
                    v-model="form.role_ids"
                    @change="handleRoleChange"
                  >
                  <label :for="'role_' + role.id" class="form-check-label text-truncate" style="max-width: 100%;">
                    {{ role.label }}
                  </label>
                </div>
              </div>
            </div>

            <div class="text-danger mt-2 small" v-if="(touched.role_ids && validateRoles()) || form.errors.role_ids || form.errors.role">
              {{ form.errors.role_ids || form.errors.role || (touched.role_ids ? validateRoles() : '') }}
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light border-top-0">
          <button class="btn btn-outline-secondary" @click="close">
            <i class="bi bi-x-lg me-1"></i>Cancelar
          </button>
          <button class="btn btn-primary" @click="submit" :disabled="form.processing || !isFormValid">
            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
            <i class="bi bi-save me-1"></i>Crear Usuario
          </button>
        </div>

      </div>
    </div>

    <div class="modal-backdrop fade show" style="z-index: 1040;" @click="close"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  show: Boolean,
  roles: {
    type: Array,
    default: () => []
  }
});
const emit = defineEmits(['close']);

const showToast = useToast();

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_ids: []
});

const touched = ref({
  name: false,
  email: false,
  password: false,
  password_confirmation: false,
  role_ids: false
});

// Funciones de validación
const validateName = () => {
  if (!form.name.trim()) return 'El nombre es requerido';
  if (form.name.length < 3) return 'Debe tener al menos 3 caracteres';
  return '';
};

const validateEmail = () => {
  if (!form.email.trim()) return 'El correo es requerido';
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.email)) return 'Ingrese un correo válido';
  return '';
};

const validatePassword = () => {
  const val = form.password;
  if (!val) return 'La contraseña es requerida';
  if (val.length < 8) return 'Debe tener al menos 8 caracteres';
  if (!/[A-Z]/.test(val)) return 'Debe contener al menos una mayúscula';
  if (!/[0-9]/.test(val)) return 'Debe contener al menos un número';
  return '';
};

const validatePasswordConfirmation = () => {
  if (!form.password_confirmation) return 'Confirme la contraseña';
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden';
  return '';
};

const validateRoles = () => {
  if (form.role_ids.length === 0) return 'Seleccione al menos un rol';
  return '';
};

const isFormValid = computed(() => {
  return (
    !validateName() &&
    !validateEmail() &&
    !validatePassword() &&
    !validatePasswordConfirmation() &&
    !validateRoles()
  );
});

const allRolesSelected = computed({
  get() {
    return form.role_ids.length === props.roles.length;
  },
  set(value) {
    form.role_ids = value ? props.roles.map(role => role.id) : [];
    touched.value.role_ids = true;
  }
});

const resetForm = () => {
  form.reset();
  Object.keys(touched.value).forEach(key => {
    touched.value[key] = false;
  });
  form.clearErrors();
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm();
  }
});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const handleRoleChange = () => {
  touched.value.role_ids = true;
};

const close = () => {
  emit('close');
  resetForm();
};

const submit = () => {
  Object.keys(touched.value).forEach(key => {
    touched.value[key] = true;
  });

  if (isFormValid.value) {
    form.post(route('admin.users.store'), {
      preserveScroll: true,
      onSuccess: () => {
        showToast('Usuario creado exitosamente');
        close();
      },
      onError: () => {
        showToast('Por favor corrige los errores en el formulario');
      }
    });
  }
};

// Exponer las funciones de validación al template
defineExpose({
  validateName,
  validateEmail,
  validatePassword,
  validatePasswordConfirmation,
  validateRoles
});
</script>

<style scoped>
#modal-add {
  .modal-content {
    z-index: 9999 !important;
  }
}
</style>