<template>
  <Head :title="`Crear Nuevo Video en ${course.title}`" />
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
            <Title :title="`Crear Video en ${course.title}`" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.courses.videos.index', course.id)" />
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
                <div v-for="field in videoFields" :key="field.key" class="col-md-6 mb-3">
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
  course: Object
});

const form = useForm({
  title: '',
  description: '',
  description_short: '',
  comments: '',
  image_cover: '',
  video_url: ''
});

const videoFields = [
  { key: 'title', label: 'Título' },
  { key: 'description', label: 'Descripción' },
  { key: 'description_short', label: 'Descripción corta' },
  { key: 'comments', label: 'Comentarios' },
  { key: 'image_cover', label: 'URL imagen cover' },
  { key: 'video_url', label: 'URL del video' }
];

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (field === 'title' && !form.title.trim()) return 'El título es obligatorio';
  if (field === 'video_url' && !form.video_url.trim()) return 'El URL del video es obligatorio';
  return '';
};

const isFormValid = computed(() => {
  return videoFields.every(f => !validateField(f.key));
});

const submit = () => {
  videoFields.forEach(f => touched.value[f.key] = true);

  if (isFormValid.value) {
    form.post(route('admin.courses.videos.store', props.course.id), {
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};
</script>
