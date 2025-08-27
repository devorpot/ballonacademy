<template>
  <Head title="Crear Nuevo Video" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Cursos',  route: 'admin.courses.index' },
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
                  <i class="bi bi-save me-1"></i> Guardar video
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
                   
                    <div class="col-md-12 mb-3">
                      <FieldVideo
                        id="video_path"
                        label="Subir video"
                        :required="true"
                        :form-error="form.errors.video_path"
                        :show-validation="touched.video_path"
                        @update:modelValue="form.video_path = $event"
                        @blur="() => handleBlur('video_path')"
                      />
                    </div>
                     <div class="col-md-6 mb-3">
                      <FieldText
                        id="title"
                        label="Título"
                        v-model="form.title"
                        :required="true"
                        :showValidation="touched.title"
                        :formError="form.errors.title"
                        :validateFunction="() => validateField('title')"
                        placeholder="Título del video"
                        @blur="() => handleBlur('title')"
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
                      />
                    </div>

                   
                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="course_id"
                        label="Curso"
                        v-model="form.course_id"
                        :required="true"
                        :options="courseOptions"
                        :showValidation="touched.course_id"
                        :formError="form.errors.course_id"
                        :validateFunction="() => validateField('course_id')"
                        @blur="() => handleBlur('course_id')"
                      />
                    </div>

                    <!-- Nuevo select para lección -->
                    <div class="col-md-6 mb-3">
                      
                        <FieldSelect
                          id="lesson_id"
                          :key="form.course_id"     
                          label="Lección (opcional)"
                          v-model="form.lesson_id"
                          :options="lessonOptions"
                          :disabled="!form.course_id || loadingLessons"
                          :showValidation="touched.lesson_id"
                          :formError="form.errors.lesson_id"
                          @blur="() => handleBlur('lesson_id')"
                          helper="Selecciona primero un curso para cargar sus lecciones"
                        />
                        <small v-if="loadingLessons" class="text-muted">Cargando lecciones…</small>
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
                      <FieldImage
                        id="image_cover"
                        label="Imagen de portada"
                        v-model="form.image_cover"
                        :required="true"
                        :showValidation="touched.image_cover"
                        :formError="form.errors.image_cover"
                        :validateFunction="() => validateField('image_cover')"
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
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
 
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import FieldFile from '@/Components/Admin/Fields/FieldFile.vue';
import FieldVideo from '@/Components/Admin/Fields/FieldVideo.vue';

const props = defineProps({
  courses: Array,
  teachers: Array,
  lessons: Array,
  selected_course:Number
});



const form = useForm({
  title: '',
  description: '',
  description_short: '',
  comments: '',
  lesson_id: '',
  course_id: props.selected_course,
  teacher_id: '',
  image_cover: null,
  video_path: null
});

const touched = ref({});


const loadingLessons = ref(false)

const courseOptions = computed(() =>
  props.courses.map(c => ({ value: c.id, label: c.title }))
);

const lessonOptions = computed(() =>
  props.lessons.map(l => ({ value: l.id, label: l.title }))
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
        
         !validateField('course_id') &&
         !validateField('teacher_id') &&
         !validateField('video_path') &&
         !validateField('image_cover');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.videos.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};

watch(
  () => form.course_id,
  (newVal, oldVal) => {
    // Limpia selección de lección al cambiar curso
    form.lesson_id = ''

    // Si no hay curso seleccionado, deja vacío el listado de lecciones
    if (!newVal) return

    // Recarga solo las props necesarias desde el backend
    router.visit(route('admin.videos.create', { course_id: newVal }), {
      preserveScroll: true,
      preserveState: true,
      replace: true,
      only: ['lessons', 'selected_course'],
      onStart: () => (loadingLessons.value = true),
      onFinish: () => (loadingLessons.value = false),
    })
  }
)
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>