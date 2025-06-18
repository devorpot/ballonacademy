<template>
  <Head title="Editar Estudiante" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Estudiantes', route: 'admin.students.index' },
        { label: 'Editar', route: '' }
      ]"
    />

    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="`Editar Estudiante`" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.students.index')" />
          </div>
        </div>
      </div>
    </section>

    <section class="section-form my-2">
      <div class="container-fluid">
        <form @submit.prevent="submit" novalidate>
          <div class="card">
            <div class="card-body">
              <!-- SECCIÓN USUARIO -->
              <h6 class="text-muted mb-3">Datos de usuario</h6>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nombre de usuario</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.name"
                    @blur="handleBlur('name')"
                    :class="{ 'is-invalid': (touched.name && validateName()) || form.errors.name }"
                  >
                  <div class="invalid-feedback">
                    {{ touched.name ? validateName() : '' || form.errors.name }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Email</label>
                  <input
                    type="email"
                    class="form-control"
                    v-model="form.email"
                    @blur="handleBlur('email')"
                    :class="{ 'is-invalid': (touched.email && validateEmail()) || form.errors.email }"
                  >
                  <div class="invalid-feedback">
                    {{ touched.email ? validateEmail() : '' || form.errors.email }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Contraseña <small class="text-muted">(opcional)</small></label>
                  <input
                    type="password"
                    class="form-control"
                    v-model="form.password"
                    @blur="handleBlur('password')"
                    :class="{ 'is-invalid': (touched.password && validatePassword()) || form.errors.password }"
                  >
                  <div class="invalid-feedback">
                    {{ touched.password ? validatePassword() : '' || form.errors.password }}
                  </div>
                  <small class="form-text text-muted">Dejar en blanco para mantener la actual</small>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Confirmar contraseña</label>
                  <input
                    type="password"
                    class="form-control"
                    v-model="form.password_confirmation"
                    @blur="handleBlur('password_confirmation')"
                    :class="{ 'is-invalid': (touched.password_confirmation && validatePasswordConfirmation()) || form.errors.password_confirmation }"
                  >
                  <div class="invalid-feedback">
                    {{ touched.password_confirmation ? validatePasswordConfirmation() : '' || form.errors.password_confirmation }}
                  </div>
                </div>
              </div>

              <!-- SECCIÓN ESTUDIANTE -->
              <h6 class="text-muted mt-4 mb-3">Datos del estudiante</h6>
              <div class="row">
                <div v-for="field in studentFields" :key="field.key" class="col-md-6 mb-3">
                  <label class="form-label">{{ field.label }}</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form[field.key]"
                    @blur="handleBlur(field.key)"
                    :class="{ 'is-invalid': (touched[field.key] && validateField(field.key)) || form.errors[field.key] }"
                  >
                  <div class="invalid-feedback">
                    {{ touched[field.key] ? validateField(field.key) : '' || form.errors[field.key] }}
                  </div>
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
  </AdminLayout>
</template>


<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';

const props = defineProps({
  student: { type: Object, required: true }
});

const form = useForm({
  name: props.student.user.name,
  email: props.student.user.email,
  password: '',
  password_confirmation: '',
  student_id: props.student.student_id,
  firstname: props.student.firstname,
  lastname: props.student.lastname,
  phone: props.student.phone,
  shirt_size: props.student.shirt_size,
  address: props.student.address,
  country: props.student.country
});

const touched = ref({});
const studentFields = [
  { key: 'student_id', label: 'Matrícula' },
  { key: 'firstname', label: 'Nombre' },
  { key: 'lastname', label: 'Apellido' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'shirt_size', label: 'Talla de camiseta' },
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
  Object.keys(form).forEach(key => {
    touched.value[key] = true;
  });

  if (isFormValid.value) {
    form.put(route('admin.students.update', props.student.id), {
      preserveScroll: true
    });
  }
};
</script>
