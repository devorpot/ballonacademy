<template>
  <Head :title="`Editar Video: ${form.title}`" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Cursos', route: 'admin.courses.index' },
            { label: `Videos - ${props.course.title}`, route: 'admin.courses.videos.panel', params: props.course.id },
            { label: `Editar - ${props.video.title}` } // Último breadcrumb solo con label, sin route
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
                  <i class="bi bi-save me-1"></i> Guardar cambios
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
                          label="Reemplazar video (opcional)"
                          :initial-path="props.video.stream_url"
                          :form-error="form.errors.video_path"
                          :show-validation="true"
                          @update:modelValue="form.video_path = $event"
                          @update:keep="form.keep_video = $event"
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
                      <FieldText
                        id="comments"
                        label="Comentarios"
                        v-model="form.comments"
                        :showValidation="touched.comments"
                        :formError="form.errors.comments"
                        @blur="() => handleBlur('comments')"
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
                      :showValidation="touched.image_cover"
                      :formError="form.errors.image_cover"
                      :initialPreview="imagePreview"
                      @blur="() => handleBlur('image_cover')"
                      @update:keep="form.keep_image = $event"
     
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

        <section class="section-materials my-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-box-seam me-1"></i> Materiales del video</span>
      
    </div>
    <div class="card-body">
     <VideoMaterialRow :video-id="props.video.id" />

    </div>
  </div>
</section>


      </div>

      <SpinnerOverlay v-if="form.processing" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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


import VideoMaterialRow from '@/Components/Admin/Videos/VideoMaterialRow.vue';

const props = defineProps({
  video: Object,
  courses: Array,
  teachers: Array,
  course:Object,
  initialMaterials: {
    type: Array,
    default: () => []
  }
});

const form = useForm({
  _method: 'PUT',
  title: props.video.title,
  description: props.video.description,
  description_short: props.video.description_short,
  comments: props.video.comments,
 
  course_id: props.video.course_id,
  teacher_id: props.video.teacher_id,
  image_cover: null,     // si suben una nueva
  video_path: null,      // si suben un nuevo video
  keep_image: !!props.video.image_cover,
  keep_video: !!props.video.video_path
});

const imagePreview = props.video.image_cover ? '/storage/' + props.video.image_cover : null;
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


 
 

const breadcrumbs = computed(() => {
  // Si course no está listo, usa textos genéricos y evita acceder a sus props
  if (!props.course) {
    return [
      { label: 'Dashboard', route: route('admin.dashboard') },
      { label: 'Cursos', route: route('admin.courses.index') },
      { label: 'Videos', route: '' },
      { label: 'Editar', route: '' }
    ];
  }
  // Cuando course ya existe
  return [
    { label: 'Dashboard', route: route('admin.dashboard') },
    { label: 'Cursos', route: route('admin.courses.index') },
    { label: `Videos - ${props.course.title}`, route: route('admin.courses.videos.panel', props.course.id) },
    { label: 'Editar', route: '' }
  ];
});


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
         !validateField('teacher_id');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.videos.update', props.video.id), {
      forceFormData: true,
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
