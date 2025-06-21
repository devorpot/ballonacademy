<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-plus-circle me-2"></i> Crear Maestro
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': form.processing }">
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
                    placeholder="Nombre de usuario"
                    @blur="() => handleBlur('name')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="email"
                    label="Email"
                    type="email"
                    v-model="form.email"
                    :required="true"
                    :showValidation="touched.email"
                    :formError="form.errors.email"
                    :validateFunction="validateEmail"
                    placeholder="Correo electrónico"
                    @blur="() => handleBlur('email')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="password"
                    label="Contraseña"
                    type="password"
                    v-model="form.password"
                    :required="true"
                    :showValidation="touched.password"
                    :formError="form.errors.password"
                    :validateFunction="validatePassword"
                    placeholder="Contraseña"
                    @blur="() => handleBlur('password')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="password_confirmation"
                    label="Confirmar Contraseña"
                    type="password"
                    v-model="form.password_confirmation"
                    :required="true"
                    :showValidation="touched.password_confirmation"
                    :formError="form.errors.password_confirmation"
                    :validateFunction="validatePasswordConfirmation"
                    placeholder="Confirmar Contraseña"
                    @blur="() => handleBlur('password_confirmation')"
                  />
                </div>

                <div v-for="field in teacherFields" :key="field.key" class="col-md-6 mb-3">
                  <FieldText
                    :id="field.key"
                    :label="field.label"
                    v-model="form[field.key]"
                    :required="true"
                    :showValidation="touched[field.key]"
                    :formError="form.errors[field.key]"
                    :validateFunction="() => validateField(field.key)"
                    :placeholder="field.label"
                    @blur="() => handleBlur(field.key)"
                  />
                </div>
              </div>
            </div>
            <SpinnerOverlay v-if="form.processing" />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">
              <i class="bi bi-x-lg me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
              <i class="bi bi-save me-1"></i> Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  teacher_id: '',
  firstname: '',
  lastname: '',
  phone: '',
  specialty: '',
  address: '',
  country: ''
});

const touched = ref({});
const teacherFields = [
  { key: 'teacher_id', label: 'Clave Maestro' },
  { key: 'firstname', label: 'Nombre' },
  { key: 'lastname', label: 'Apellido' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'specialty', label: 'Especialidad' },
  { key: 'address', label: 'Dirección' },
  { key: 'country', label: 'País' }
];

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateName = () => {
  if (!form.name.trim()) return 'El nombre de usuario es obligatorio';
  if (form.name.length < 3) return 'Mínimo 3 caracteres';
  return '';
};

const validateEmail = () => {
  if (!form.email.trim()) return 'El email es obligatorio';
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.email)) return 'Formato de email inválido';
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
         teacherFields.every(f => !validateField(f.key));
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.teachers.store'), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        emit('saved');
        emit('close');
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
