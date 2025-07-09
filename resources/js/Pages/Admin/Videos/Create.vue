<template>
  <Head title="Crear Video" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Videos', route: 'admin.videos.index' },
            { label: 'Crear', route: '' }
          ]"
        />

        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.videos.index')" />
                <button
                  class="btn btn-primary"
                  :disabled="form.processing || !isFormValid"
                  @click="submit"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-1"></i> Crear video
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="section-form my-2">
          <div class="container-fluid">
            <form @submit.prevent="submit" enctype="multipart/form-data">
              <div class="card">
                <div class="card-body">
                  <div class="row">

                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="title"
                        label="Título del video"
                        v-model="form.title"
                        :required="true"
                        :showValidation="touched.title"
                        :formError="form.errors.title"
                        :validateFunction="() => validateField('title')"
                        placeholder="Ej: Introducción al curso"
                        @blur="() => handleBlur('title')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="video_url"
                        label="URL del video o archivo"
                        v-model="form.video_url"
                        :required="true"
                        :showValidation="touched.video_url"
                        :formError="form.errors.video_url"
                        :validateFunction="() => validateField('video_url')"
                        placeholder="video.mp4"
                        @blur="() => handleBlur('video_url')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="description"
                        label="Descripción"
                        v-model="form.description"
                        :showValidation="touched.description"
                        :formError="form.errors.description"
                        @blur="() => handleBlur('description')"
                        placeholder="Descripción completa"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="description_short"
                        label="Descripción corta"
                        v-model="form.description_short"
                        :showValidation="touched.description_short"
                        :formError="form.errors.description_short"
                        @blur="() => handleBlur('description_short')"
                        placeholder="Breve descripción"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="course_id"
                        label="Curso"
                        v-model="form.course_id"
                        :required="true"
                        :showValidation="touched.course_id"
                        :formError="form.errors.course_id"
                        :validateFunction="() => validateField('course_id')"
                        :options="courseOptions"
                        @blur="() => handleBlur('course_id')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                       <FieldSelect
                        id="teacher_id"
                        label="Profesor"
                        v-model="form.teacher_id"
                        :required="true"
                        :options="teacherOptions"
                        :showValidation="touched.teacher_id"
                        :formError="form.errors.teacher_id"
                        :validateFunction="() => validateField('teacher_id')"
                        @blur="() => handleBlur('teacher_id')"
                      />
                    </div>
 
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="comments"
                        label="Comentarios"
                        v-model="form.comments"
                        :showValidation="touched.comments"
                        :formError="form.errors.comments"
                        @blur="() => handleBlur('comments')"
                        placeholder="Comentarios opcionales"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldImage
                        id="image_cover"
                        label="Imagen de portada"
                        v-model="form.image_cover"
                        :showValidation="touched.image_cover"
                        :formError="form.errors.image_cover"
                        @blur="() => handleBlur('image_cover')"
                      />
                    </div>

                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Crear video
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>

      <SpinnerOverlay v-if="form.processing" />
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
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';

const props = defineProps({
  courses: Array,
  teachers: Array,
  course_id: Number
});

const form = useForm({
  title: '',
  description: '',
  description_short: '',
  comments: '',
  video_url: '',
  course_id: props.course_id || '',
  teacher_id: '',
  image_cover: ''
});

const touched = ref({});

const courseOptions = computed(() =>
  props.courses.map(c => ({ value: c.id, label: c.title }))
);

const teacherOptions = computed(() =>
  props.teachers.map(t => ({
    value: t.id,
    label: `${t.firstname} ${t.lastname}`
  }))
);
const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
    return `El campo ${field.replace('_', ' ')} es obligatorio`;
  }
  return '';
};

const isFormValid = computed(() => {
  return !validateField('title') &&
         !validateField('video_url') &&
         !validateField('course_id') &&
         !validateField('teacher_id');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);
  if (isFormValid.value) {
    form.post(route('admin.videos.store'), {
      forceFormData: true,
      preserveScroll: true,
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
