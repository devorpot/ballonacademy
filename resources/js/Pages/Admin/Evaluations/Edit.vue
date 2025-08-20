<template>
  <Head :title="`Editar Evaluación #${props.evaluation.id}`" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Evaluaciones', route: 'admin.evaluations.index' },
            { label: 'Editar', route: '' }
          ]"
        />

        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.evaluations.index')" />
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
                    <!-- Curso -->
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

                    <!-- Tipo de Evaluación (type) -->
                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="type"
                        label="Tipo de Evaluación"
                        v-model="form.type"
                        :required="true"
                        :showValidation="touched.type"
                        :formError="form.errors.type"
                        :validateFunction="() => validateField('type')"
                        :options="typeOptions"
                        @blur="() => handleBlur('type')"
                      />
                    </div>

                    <!-- Video dependiente del curso (solo si type = VIDEO) -->
                    <div class="col-md-6 mb-3" v-if="form.type === 2">
                      <FieldSelect
                        id="video_id"
                        label="Video del curso"
                        v-model="form.video_id"
                        :required="true"
                        :showValidation="touched.video_id"
                        :formError="form.errors.video_id"
                        :validateFunction="() => validateField('video_id')"
                        :options="videoOptions"
                        :disabled="!form.course_id || loadingVideos"
                        @blur="() => handleBlur('video_id')"
                        placeholder="Selecciona un video"
                      />
                      <small v-if="!form.course_id" class="text-muted">Selecciona primero un curso.</small>
                      <small v-else-if="!loadingVideos && videoOptions.length === 0" class="text-muted">Este curso no tiene videos disponibles.</small>
                    </div>

                    <!-- Fecha de envío -->
                    <div class="col-md-4 mb-3">
                 
                      <FieldDate
                        id="eva_send_date"
                        label="Fecha de envío"
                        v-model="form.eva_send_date"
                        :required="true"
                        :showValidation="touched.eva_send_date"
                        :formError="form.errors.eva_send_date"
                        :validateFunction="() => validateField('eva_send_date')"
                        @blur="() => handleBlur('eva_send_date')"
                      />
                    </div>

                    <!-- eva_type (se mantiene, independiente) -->
                    <div class="col-md-4 mb-3">
                      <FieldSelect
                        id="eva_type"
                        label="Tipo de Evaluación (Modalidad)"
                        v-model="form.eva_type"
                        :required="true"
                        :showValidation="touched.eva_type"
                        :formError="form.errors.eva_type"
                        :validateFunction="() => validateField('eva_type')"
                        :options="evaTypeOptions"
                        @blur="() => handleBlur('eva_type')"
                      />
                    </div>
                     <div class="col-md-4 mb-3">
                     <FieldNumber
                        id="points_min"
                        label="Puntaje mínimo"
                        v-model="form.points_min"
                        :min="1"
                        :max="100"
                        :default="1"
                        :required="true"
                        :showValidation="touched.points_min"
                        :validateFunction="() => ''"
                      />
                    </div>

                    <!-- Estatus -->
                    <div class="col-md-4 mb-3">
                      <FieldSelect
                        id="status"
                        label="Estatus"
                        v-model="form.status"
                        :required="true"
                        :showValidation="touched.status"
                        :formError="form.errors?.status"
                        :validateFunction="() => validateField('status')"
                        :options="statusOptions"
                        @blur="() => handleBlur('status')"
                      />
                    </div>

                    <!-- Título -->
                    <div class="col-md-12 mb-3">
                      <FieldText
                        id="title"
                        label="Título de la Evaluación"
                        v-model="form.title"
                        :showValidation="touched.title"
                        :formError="form.errors.title"
                        @blur="() => handleBlur('title')"
                        placeholder="Título de la evaluación"
                      />
                    </div>

                    <!-- Comentarios -->
                    <div class="col-md-12 mb-3">
                      <FieldTextarea
                        id="eva_comments"
                        label="Comentarios"
                        v-model="form.eva_comments"
                        :showValidation="touched.eva_comments"
                        :formError="form.errors.eva_comments"
                        @blur="() => handleBlur('eva_comments')"
                        placeholder="Comentarios de la evaluación"
                      />
                    </div>

                    <!-- Archivo de Video -->
                    <div class="col-md-12 mb-3">
                      <FieldVideo
                        id="eva_video_file"
                        label="Archivo de Video (dejar vacío para conservar el existente)"
                        v-model="form.eva_video_file"
                        v-model:keep="keepVideo"
                        :showValidation="touched.eva_video_file"
                        :formError="form.errors.eva_video_file"
                        :validateFunction="() => validateField('eva_video_file')"
                        :initialPath="initialVideoUrl"
                        accept="video/mp4,video/quicktime,video/x-m4v"
                        @blur="() => handleBlur('eva_video_file')"
                      />
                      <small v-if="props.evaluation.eva_video_file && !form.eva_video_file && keepVideo" class="text-muted">
                        Actual: /storage/{{ props.evaluation.eva_video_file }}
                      </small>
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
      <SpinnerOverlay v-if="form.processing" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldVideo from '@/Components/Admin/Fields/FieldVideo.vue';
import FieldNumber from '@/Components/Admin/Fields/FieldNumber.vue';
const props = defineProps({
  evaluation: Object,
  courses: Array,
  statusOptions: Array,
  typeOptions: Array
});

// Form inicial
const form = useForm({
  course_id: props.evaluation.course_id ?? '',
  video_id: props.evaluation.video_id ?? '',
  eva_send_date: props.evaluation.eva_send_date ?? '',
  eva_type: props.evaluation.eva_type ?? 1, // modalidad
  type: props.evaluation.type ?? 1,         // ámbito
  eva_comments: props.evaluation.eva_comments ?? '',
  title: props.evaluation.title ?? '',
  eva_video_file: null,
  points_min: props.evaluation.points_min ?? '',
  status: props.evaluation.status ?? 'SEND',
  _method: 'PUT'
});

const keepVideo = ref(true);
const touched = ref({});

// Opciones selects
const courseOptions = props.courses.map(c => ({
  value: c.id,
  label: c.title
}));

const typeOptions = props.typeOptions && props.typeOptions.length
  ? props.typeOptions
  : [
      { value: 1, label: 'Evaluación general del Curso' },
      { value: 2, label: 'Evaluación para el Video' }
    ];

const evaTypeOptions = [
  { value: 1, label: 'Cuestionario' },
  { value: 2, label: 'Video' }
];

const loadingVideos = ref(false);
const videoOptions = ref([]);

const initialVideoUrl = computed(() =>
  props.evaluation?.eva_video_file ? `/storage/${props.evaluation.eva_video_file}` : ''
);

// Helpers
const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (field === 'course_id' && !form.course_id) return 'El curso es obligatorio';
  if (field === 'eva_send_date' && !form.eva_send_date) return 'La fecha de envío es obligatoria';
  if (field === 'eva_type' && !form.eva_type) return 'La modalidad de evaluación es obligatoria';
  if (field === 'type' && !form.type) return 'El tipo de evaluación es obligatorio';
  if (form.type === 2 && field === 'video_id' && !form.video_id) return 'Debes seleccionar un video del curso';
  return '';
};

const isFormValid = computed(() => {
  const baseOk = !validateField('course_id') &&
                 !validateField('eva_send_date') &&
                 !validateField('eva_type') &&
                 !validateField('type');
  const videoOk = form.type === 2 ? !validateField('video_id') : true;
  return baseOk && videoOk;
});

// Cargar videos por curso
const loadVideosForCourse = async (courseId, keepSelection = false) => {
  if (!courseId || Number(form.type) !== 2) {
    videoOptions.value = [];
    form.video_id = '';
    return;
  }

  loadingVideos.value = true;
  try {
    const url = route('admin.evaluations.videos.combo', { course: Number(courseId) || courseId });
    const { data } = await axios.get(url);
    videoOptions.value = Array.isArray(data) ? data.map(v => ({ value: v.id, label: v.title })) : [];

    if (keepSelection && form.video_id) {
      const exists = videoOptions.value.some(o => String(o.value) === String(form.video_id));
      if (!exists) form.video_id = '';
    } else if (!keepSelection) {
      form.video_id = '';
    }
  } catch (e) {
    console.error('Error cargando videos del curso:', e);
    videoOptions.value = [];
    if (!keepSelection) form.video_id = '';
  } finally {
    loadingVideos.value = false;
  }
};

// Inicial
onMounted(() => {
  loadVideosForCourse(form.course_id, true);
});

// Watchers
watch(() => form.course_id, (newCourseId, oldCourseId) => {
  if (String(newCourseId) !== String(oldCourseId)) {
    loadVideosForCourse(newCourseId, false);
  }
});

watch(() => form.type, (newType, oldType) => {
  if (Number(newType) !== 2) {
    // Pasó a COURSE: limpiar selección de video
    videoOptions.value = [];
    form.video_id = '';
  } else if (Number(newType) === 2 && form.course_id) {
    // Pasó a VIDEO: cargar lista para el curso actual
    loadVideosForCourse(form.course_id, true);
  }
});

// Envío
const submit = () => {
  Object.keys(form).forEach(key => (touched.value[key] = true));
  if (!isFormValid.value) return;

  form.post(route('admin.evaluations.update', props.evaluation.id), {
    forceFormData: true,
    preserveScroll: true
  });
};
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
