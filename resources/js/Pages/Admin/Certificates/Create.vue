<template>
  <Head title="Crear Nuevo Certificado" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Certificados', route: 'admin.certificates.index' },
        { label: 'Crear', route: '' }
      ]"
    />

    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Crear Certificado'" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.certificates.index')" />
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
                <!-- Usuario -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Usuario</label>
                  <select
                    class="form-control"
                    v-model="form.user_id"
                    :class="{ 'is-invalid': touched.user_id && validateField('user_id') }"
                    @blur="handleBlur('user_id')"
                  >
                    <option value="">Seleccione un usuario</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }} ({{ user.email }})
                    </option>
                  </select>
                  <div class="invalid-feedback">
                    {{ validateField('user_id') }}
                  </div>
                </div>

                <!-- Maestro -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Maestro</label>
                  <select
                    class="form-control"
                    v-model="form.master_id"
                    :class="{ 'is-invalid': touched.master_id && validateField('master_id') }"
                    @blur="handleBlur('master_id')"
                  >
                    <option value="">Seleccione un maestro</option>
                    <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                      {{ teacher.firstname }} {{ teacher.lastname }}
                    </option>
                  </select>
                  <div class="invalid-feedback">
                    {{ validateField('master_id') }}
                  </div>
                </div>

                <!-- Autorizado por -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Autorizado por</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.authorized_by"
                    @blur="handleBlur('authorized_by')"
                    :class="{ 'is-invalid': touched.authorized_by && validateField('authorized_by') }"
                  >
                  <div class="invalid-feedback">
                    {{ validateField('authorized_by') }}
                  </div>
                </div>

                <!-- Fechas -->
                <div class="col-md-4 mb-3">
                  <label class="form-label">Fecha de Inicio</label>
                  <input type="date" class="form-control" v-model="form.date_start">
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label">Fecha de Fin</label>
                  <input type="date" class="form-control" v-model="form.date_end">
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label">Fecha de Expedición</label>
                  <input type="date" class="form-control" v-model="form.date_expedition">
                </div>

                <!-- Comentarios -->
                <div class="col-md-12 mb-3">
                  <label class="form-label">Comentarios</label>
                  <input type="text" class="form-control" v-model="form.comments">
                </div>

                <!-- Foto -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Foto (URL o nombre archivo)</label>
                  <input type="text" class="form-control" v-model="form.photo">
                </div>

                <!-- Logo -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Logo (URL o nombre archivo)</label>
                  <input type="text" class="form-control" v-model="form.logo">
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

const props = defineProps({
  users: Array,
  teachers: Array
});

const form = useForm({
  user_id: '',
  master_id: '',
  authorized_by: '',
  date_start: '',
  date_end: '',
  date_expedition: '',
  comments: '',
  photo: '',
  logo: ''
});

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (!form[field] || !form[field].toString().trim()) {
    return `El campo ${field.replace('_', ' ')} es obligatorio`;
  }
  return '';
};

const isFormValid = computed(() => {
  return ['user_id', 'master_id', 'authorized_by'].every(f => !validateField(f));
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.certificates.store'), {
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};
</script>
