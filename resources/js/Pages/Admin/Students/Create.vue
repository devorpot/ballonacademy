<template>
  <Head title="Crear Nuevo Estudiante" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Estudiantes', route: 'admin.students.index' },
        { label: 'Crear', route: '' }
      ]"
    />

    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Crear Estudiante'" />
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
                  <label class="form-label">Contraseña</label>
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
                  <small class="form-text text-muted">Mínimo 8 caracteres, una mayúscula y un número</small>
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

              <h6 class="text-muted mt-4 mb-3">Datos del estudiante</h6>
               <div class="row">
                    <!-- Matrícula -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Matrícula</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.student_id"
                        @blur="handleBlur('student_id')"
                        :class="{ 'is-invalid': (touched.student_id && validateField('student_id')) || form.errors.student_id }"
                      >
                      <div class="invalid-feedback">
                        {{ touched.student_id ? validateField('student_id') : '' || form.errors.student_id }}
                      </div>
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Nombre</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.firstname"
                        @blur="handleBlur('firstname')"
                        :class="{ 'is-invalid': (touched.firstname && validateField('firstname')) || form.errors.firstname }"
                      >
                      <div class="invalid-feedback">
                        {{ touched.firstname ? validateField('firstname') : '' || form.errors.firstname }}
                      </div>
                    </div>

                    <!-- Apellido -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Apellido</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.lastname"
                        @blur="handleBlur('lastname')"
                        :class="{ 'is-invalid': (touched.lastname && validateField('lastname')) || form.errors.lastname }"
                      >
                      <div class="invalid-feedback">
                        {{ touched.lastname ? validateField('lastname') : '' || form.errors.lastname }}
                      </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Teléfono</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.phone"
                        @blur="handleBlur('phone')"
                        :class="{ 'is-invalid': (touched.phone && validateField('phone')) || form.errors.phone }"
                      >
                      <div class="invalid-feedback">
                        {{ touched.phone ? validateField('phone') : '' || form.errors.phone }}
                      </div>
                    </div>

                    <!-- Talla camiseta -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Talla camiseta</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.shirt_size"
                        @blur="handleBlur('shirt_size')"
                        :class="{ 'is-invalid': (touched.shirt_size && validateField('shirt_size')) || form.errors.shirt_size }"
                      >
                      <div class="invalid-feedback">
                        {{ touched.shirt_size ? validateField('shirt_size') : '' || form.errors.shirt_size }}
                      </div>
                    </div>

                    <!-- Dirección -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Dirección</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.address"
                        @blur="handleBlur('address')"
                        :class="{ 'is-invalid': (touched.address && validateField('address')) || form.errors.address }"
                      >
                      <div class="invalid-feedback">
                        {{ touched.address ? validateField('address') : '' || form.errors.address }}
                      </div>
                    </div>

                    <!-- País -->
                    <div class="col-md-6 mb-3">
                      <label class="form-label">País</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.country"
                        @blur="handleBlur('country')"
                        :class="{ 'is-invalid': (touched.country && validateField('country')) || form.errors.country }"
                      >
                      <div class="invalid-feedback">
                        {{ touched.country ? validateField('country') : '' || form.errors.country }}
                      </div>
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
    </section>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  student_id: '',
  firstname: '',
  lastname: '',
  phone: '',
  shirt_size: '',
  address: '',
  country: ''
});

const studentFields = [
  { key: 'student_id', label: 'Matrícula' },
  { key: 'firstname', label: 'Nombre' },
  { key: 'lastname', label: 'Apellido' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'shirt_size', label: 'Talla camiseta' },
  { key: 'address', label: 'Dirección' },
  { key: 'country', label: 'País' }
];

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateName = () => {
  if (!form.name.trim()) return 'El nombre es obligatorio';
  if (form.name.length < 3) return 'Mínimo 3 caracteres';
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
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.students.store'), {
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};
</script>
