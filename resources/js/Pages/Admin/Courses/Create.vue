<template>
  <Head title="Crear Nuevo Curso" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Cursos', route: 'admin.courses.index' },
        { label: 'Crear', route: '' }
      ]"
    />

    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Crear Curso'" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.courses.index')" />
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

                <div class="col-md-6 mb-3">
                  <label class="form-label">Título</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.title"
                    @blur="handleBlur('title')"
                    :class="{ 'is-invalid': (touched.title && validateField('title')) || form.errors.title }"
                  >
                  <div class="invalid-feedback">
                    {{ touched.title ? validateField('title') : '' || form.errors.title }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Descripción</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.description"
                    @blur="handleBlur('description')"
                    :class="{ 'is-invalid': (touched.description && validateField('description')) || form.errors.description }"
                  >
                  <div class="invalid-feedback">
                    {{ touched.description ? validateField('description') : '' || form.errors.description }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Descripción corta</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.description_short"
                  >
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Nivel</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.level"
                  >
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">URL imagen cover</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.image_cover"
                  >
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">URL logo</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.logo"
                  >
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha de inicio</label>
                  <input
                    type="date"
                    class="form-control"
                    v-model="form.date_start"
                  >
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha de fin</label>
                  <input
                    type="date"
                    class="form-control"
                    v-model="form.date_end"
                    @blur="handleBlur('date_end')"
                    :class="{ 'is-invalid': (touched.date_end && validateField('date_end')) || form.errors.date_end }"
                  >
                  <div class="invalid-feedback">
                    {{ touched.date_end ? validateField('date_end') : '' || form.errors.date_end }}
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

const today = new Date().toISOString().split('T')[0];

const form = useForm({
  title: '',
  description: '',
  description_short: '',
  level: '',
  image_cover: '',
  logo: '',
  date_start: today,
  date_end: today
});

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (field === 'title' && !form.title.trim()) return 'El título es obligatorio';
  if (field === 'description' && !form.description.trim()) return 'La descripción es obligatoria';
  if (field === 'date_end' && form.date_start && form.date_end && form.date_end < form.date_start) {
    return 'La fecha de fin no puede ser anterior a la fecha de inicio';
  }
  return '';
};

const isFormValid = computed(() => {
  return !validateField('title') && !validateField('description') && !validateField('date_end');
});

const submit = () => {
  touched.value.title = true;
  touched.value.description = true;
  touched.value.date_end = true;

  if (isFormValid.value) {
    form.post(route('admin.courses.store'), {
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};
</script>
