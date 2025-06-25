<template>
  <Head title="Editar Maestro" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Maestros', route: 'admin.teachers.index' },
            { label: 'Editar', route: '' }
          ]"
        />

        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.teachers.index')" />
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
                        @blur="() => handleBlur('email')"
                      />
                    </div>
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="password"
                        label="Contraseña (opcional)"
                        type="password"
                        v-model="form.password"
                        :showValidation="touched.password"
                        :formError="form.errors.password"
                        :validateFunction="validatePassword"
                        @blur="() => handleBlur('password')"
                      />
                      <small class="form-text text-muted">Dejar en blanco para mantener la actual</small>
                    </div>
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="password_confirmation"
                        label="Confirmar contraseña"
                        type="password"
                        v-model="form.password_confirmation"
                        :showValidation="touched.password_confirmation"
                        :formError="form.errors.password_confirmation"
                        :validateFunction="validatePasswordConfirmation"
                        @blur="() => handleBlur('password_confirmation')"
                      />
                    </div>
                  </div>

                  <h6 class="text-muted mt-4 mb-3">Datos del maestro</h6>
                  <div class="row">
                    <div v-for="field in teacherFields" :key="field.key" class="col-md-6 mb-3">
                      <FieldText
                        :id="field.key"
                        :label="field.label"
                        v-model="form[field.key]"
                        :required="true"
                        :showValidation="touched[field.key]"
                        :formError="form.errors[field.key]"
                        :validateFunction="() => validateField(field.key)"
                        @blur="() => handleBlur(field.key)"
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

const props = defineProps({
  teacher: { type: Object, required: true }
});

const form = useForm({
  name: props.teacher.user.name,
  email: props.teacher.user.email,
  password: '',
  password_confirmation: '',
   
  firstname: props.teacher.firstname,
  lastname: props.teacher.lastname,
  phone: props.teacher.phone,
  specialty: props.teacher.specialty,
  address: props.teacher.address,
  country: props.teacher.country
});

const touched = ref({});
const teacherFields = [
 
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
  if (!form[field] || !form[field].trim()) return `El campo ${field.replace('_', ' ')} es obligatorio`;
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
    form.put(route('admin.teachers.update', props.teacher.id), {
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
