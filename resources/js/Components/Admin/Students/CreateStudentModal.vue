<template>
  <div v-show="show" class="modal fade show d-block" tabindex="-1" aria-labelledby="createStudentModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered"> 
      <div class="modal-content" style="z-index: 9999 !important;">
        <div class="modal-header">
          <h5 class="modal-title" id="createStudentModalLabel">
            <i class="bi bi-plus-circle me-2"></i> Crear Estudiante
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': form.processing }">
              <h6 class="text-muted mb-3">Datos de usuario</h6>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <FieldText id="name" label="Nombre de usuario" v-model="form.name" :required="true" :showValidation="touched.name"
                    :formError="form.errors.name" :validateFunction="validateName" placeholder="Ingrese el nombre de usuario"
                    @blur="() => handleBlur('name')" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText id="email" label="Email" v-model="form.email" :required="true" :showValidation="touched.email"
                    :formError="form.errors.email" :validateFunction="validateEmail" placeholder="Ingrese el email"
                    @blur="() => handleBlur('email')" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldPassword id="password" confirmId="password_confirmation" label="Contraseña" :password="form.password"
                    :passwordConfirmation="form.password_confirmation" :required="true"
                    :showValidation="touched.password || touched.password_confirmation" :formError="form.errors.password"
                    :confirmFormError="form.errors.password_confirmation" :validateFunction="validatePassword"
                    :validateConfirmFunction="validatePasswordConfirmation" @update:password="val => form.password = val"
                    @update:passwordConfirmation="val => form.password_confirmation = val"
                    @blur="(field) => handleBlur(field)" />
                </div>

             <div class="col-md-6 mb-3">
                  <FieldSwitch
                    id="active"
                    label="Usuario activo"
                    v-model="form.active"
                    :required="false"
                    :showValidation="touched.active"
                    :formError="form.errors.active"
                    @blur="() => handleBlur('active')"
                  />
                </div>
              </div>

              <h6 class="text-muted mt-4 mb-3">Datos del estudiante</h6>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <FieldText id="firstname" label="Nombre" v-model="form.firstname" :required="true"
                    :showValidation="touched.firstname" :formError="form.errors.firstname"
                    :validateFunction="() => validateField('firstname')" placeholder="Ingrese nombre"
                    @blur="() => handleBlur('firstname')" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText id="lastname" label="Apellido" v-model="form.lastname" :required="true"
                    :showValidation="touched.lastname" :formError="form.errors.lastname"
                    :validateFunction="() => validateField('lastname')" placeholder="Ingrese apellido"
                    @blur="() => handleBlur('lastname')" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText id="phone" label="Teléfono" v-model="form.phone" :required="true" :showValidation="touched.phone"
                    :formError="form.errors.phone" :validateFunction="() => validateField('phone')"
                    placeholder="Ingrese teléfono" @blur="() => handleBlur('phone')" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldSelect id="shirt_size" label="Talla de camiseta" v-model="form.shirt_size" :required="true"
                    :showValidation="touched.shirt_size" :formError="form.errors.shirt_size"
                    :validateFunction="() => validateField('shirt_size')" :options="shirtSizes"
                    @blur="() => handleBlur('shirt_size')" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldTextarea id="address" label="Dirección" v-model="form.address" :required="true"
                    :showValidation="touched.address" :formError="form.errors.address"
                    :validateFunction="() => validateField('address')" placeholder="Ingrese dirección"
                    @blur="() => handleBlur('address')" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText id="country" label="País" v-model="form.country" :required="true"
                    :showValidation="touched.country" :formError="form.errors.country"
                    :validateFunction="() => validateField('country')" placeholder="Ingrese país"
                    @blur="() => handleBlur('country')" />
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
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldPassword from '@/Components/Admin/Fields/FieldPassword.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue';
const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'saved']);

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
  firstname: '',
  lastname: '',
  phone: '',
  shirt_size: '',
  address: '',
  country: '',
  active:   false  
});

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateName = () => {
  if (!form.name.trim()) return 'El nombre de usuario es obligatorio';
  if (form.name.length < 3) return 'Debe tener al menos 3 caracteres';
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
  const value = form[field];
  if (typeof value === 'string') {
    if (!value.trim()) return `El campo ${field} es obligatorio`;
  } else if (value === null || value === '' || typeof value === 'undefined') {
    return `El campo ${field} es obligatorio`;
  }
  return '';
};

const isFormValid = computed(() => {
  return !validateName() &&
         !validateEmail() &&
         !validatePassword() &&
         !validatePasswordConfirmation() &&
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
    form.post(route('admin.students.store'), {
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
