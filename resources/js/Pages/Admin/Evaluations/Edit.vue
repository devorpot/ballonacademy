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
  <div class="row">
    <!-- Columna izquierda: 9 columnas -->
    <div class="col-lg-9 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <!-- Título -->
          <div class="mb-3">
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
          <div class="mb-3">
            <FieldTextarea
              id="eva_comments"
              label="Instrucciones"
              v-model="form.eva_comments"
              :showValidation="touched.eva_comments"
              :formError="form.errors.eva_comments"
              @blur="() => handleBlur('eva_comments')"
              placeholder="Instrucciones"
            />
          </div>
          <!-- Archivo PDF -->
          <div class="mb-3">
<FieldFile
  id="pdf_file"
  label="Archivo PDF (máx. 20 MB)"
  v-model="form.pdf_file"
  v-model:remove="form.remove_pdf_file"
  :initial-url="currentPdfUrl"
  :showValidation="touched.pdf_file"
  :formError="form.errors.pdf_file"
  @blur="() => handleBlur('pdf_file')"
  accept="application/pdf"
  :onlyPdf="true"
  :maxSizeMB="20"
  :showPreviewToggle="true"
/>

 

          </div>

         

          <!-- Archivo de Video -->
          <div class="mb-3">
            <FieldVideo
              id="eva_video_file"
              label="Video Instrucciones"
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
              Se mantiene el video actual.
            </small>
          </div>
        </div>

        <div class="card-footer text-end">
          <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
            <i class="bi bi-save me-2"></i> Guardar cambios
          </button>
        </div>
      </div>
    </div>

    <!-- Columna derecha: 3 columnas -->
    <div class="col-lg-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="mb-3">
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

          <div class="mb-3">
            <FieldSelect
              id="type"
              label="Tipo de Evaluación"
              v-model="form.type"
              :required="true"
              :showValidation="touched.type"
              :formError="form.errors.type"
              :validateFunction="() => validateField('type')"
              :options="typeOptionsComputed"
              @blur="() => handleBlur('type')"
            />
          </div>


          <!-- Video dependiente del curso -->
          <div class="mb-3" v-if="Number(form.type) === 2">
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

          <!-- Lección dependiente del curso -->
          <div class="mb-3" v-if="Number(form.type) === 3">
            <FieldSelect
              id="lesson_id"
              label="Lección del curso"
              v-model="form.lesson_id"
              :required="true"
              :showValidation="touched.lesson_id"
              :formError="form.errors.lesson_id"
              :validateFunction="() => validateField('lesson_id')"
              :options="lessonOptions"
              :disabled="!form.course_id || loadingLessons"
              @blur="() => handleBlur('lesson_id')"
              placeholder="Selecciona una lección"
            />
            <small v-if="!form.course_id" class="text-muted">Selecciona primero un curso.</small>
            <small v-else-if="!loadingLessons && lessonOptions.length === 0" class="text-muted">Este curso no tiene lecciones disponibles.</small>
          </div>

          <!-- Fecha de publicación -->
          <div class="mb-3">
            <FieldDate
              id="eva_send_date"
              label="Fecha de publicación"
              v-model="form.eva_send_date"
              :required="true"
              :showValidation="touched.eva_send_date"
              :formError="form.errors.eva_send_date"
              :validateFunction="() => validateField('eva_send_date')"
              @blur="() => handleBlur('eva_send_date')"
            />
          </div>

          <!-- Tipo de evaluación modalidad -->
          <div class="mb-3">
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


          <!-- Puntaje mínimo -->
          <div class="mb-3">
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
          <div class="mb-3">
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
        </div>
      </div>
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
import FieldFile from '@/Components/Admin/Fields/FieldFile.vue'
const props = defineProps({
  evaluation: Object,
  courses: Array,
  statusOptions: Array,
  typeOptions: Array
});

// Form inicial
const form = useForm({
  _method: 'PUT',
  course_id: props.evaluation.course_id ?? '',
  video_id: props.evaluation.video_id ?? '',
  lesson_id: props.evaluation.lesson_id ?? '',    // <-- nuevo
  eva_send_date: props.evaluation.eva_send_date ?? '',
  eva_type: props.evaluation.eva_type ?? 1,       // modalidad
  type: props.evaluation.type ?? 1,               // ámbito
  eva_comments: props.evaluation.eva_comments ?? '',
  title: props.evaluation.title ?? '',
  eva_video_file: null,
  points_min: props.evaluation.points_min ?? '',
  status: props.evaluation.status ?? 'SEND',
  
  pdf_file: null,          // archivo PDF nuevo (opcional)
  remove_pdf_file: false,  // flag para eliminar el PDF actual
});

const currentPdfUrl = computed(() => {
  if (props.evaluation?.pdf_file_url) return props.evaluation.pdf_file_url
  if (props.evaluation?.pdf_file) return '/storage/' + props.evaluation.pdf_file
  return null
})

const keepVideo = ref(true);
const touched = ref({});

// Opciones selects
const courseOptions = (props.courses || []).map(c => ({
  value: c.id,
  label: c.title
}));

const typeOptionsComputed = computed(() => {
  return (props.typeOptions && props.typeOptions.length)
    ? props.typeOptions
    : [
        { value: 1, label: 'Evaluación general del Curso' },
        { value: 2, label: 'Evaluación para el Video' },
        { value: 3, label: 'Evaluación para la Lección' } // <-- nuevo
      ];
});

const evaTypeOptions = [
  { value: 1, label: 'Cuestionario' },
  { value: 2, label: 'Video' }
];

 

// Alterna la eliminación del PDF
const toggleRemovePdfFile = () => {
  form.remove_pdf_file = !form.remove_pdf_file
  if (form.remove_pdf_file) {
    form.pdf_file = null
  }
  touched.value.pdf_file = true
}

// Estado dependientes
const loadingVideos = ref(false);
const videoOptions = ref([]);

const loadingLessons = ref(false);     // <-- nuevo
const lessonOptions = ref([]);         // <-- nuevo

const initialVideoUrl = computed(() =>
  props.evaluation?.eva_video_file ? `/storage/${props.evaluation.eva_video_file}` : ''
);

// Helpers
const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (field === 'course_id' && !form.course_id) return 'El curso es obligatorio';
  if (field === 'eva_send_date' && !form.eva_send_date) return 'La fecha de publicación es obligatoria';
  if (field === 'eva_type' && !form.eva_type) return 'La modalidad de evaluación es obligatoria';
  if (field === 'type' && !form.type) return 'El tipo de evaluación es obligatorio';

  // Dependientes
  if (Number(form.type) === 2 && field === 'video_id' && !form.video_id) return 'Debes seleccionar un video del curso';
  if (Number(form.type) === 3 && field === 'lesson_id' && !form.lesson_id) return 'Debes seleccionar una lección del curso';

  return '';
};

const isFormValid = computed(() => {
  const baseOk = !validateField('course_id') &&
                 !validateField('eva_send_date') &&
                 !validateField('eva_type') &&
                 !validateField('type');

  const videoOk  = Number(form.type) === 2 ? !validateField('video_id')  : true;
  const lessonOk = Number(form.type) === 3 ? !validateField('lesson_id') : true;

  return baseOk && videoOk && lessonOk;
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

// Cargar lecciones por curso
const loadLessonsForCourse = async (courseId, keepSelection = false) => {
  if (!courseId || Number(form.type) !== 3) {
    lessonOptions.value = [];
    form.lesson_id = '';
    return;
  }

  loadingLessons.value = true;
  try {
    const url = route('admin.evaluations.lessons.combo', { course: Number(courseId) || courseId });
    const { data } = await axios.get(url);
    lessonOptions.value = Array.isArray(data) ? data.map(l => ({ value: l.id, label: l.title })) : [];

    if (keepSelection && form.lesson_id) {
      const exists = lessonOptions.value.some(o => String(o.value) === String(form.lesson_id));
      if (!exists) form.lesson_id = '';
    } else if (!keepSelection) {
      form.lesson_id = '';
    }
  } catch (e) {
    console.error('Error cargando lecciones del curso:', e);
    lessonOptions.value = [];
    if (!keepSelection) form.lesson_id = '';
  } finally {
    loadingLessons.value = false;
  }
};

// Inicial
onMounted(() => {
  if (Number(form.type) === 2) loadVideosForCourse(form.course_id, true);
  if (Number(form.type) === 3) loadLessonsForCourse(form.course_id, true);
});

// Watchers
watch(() => form.course_id, (newCourseId, oldCourseId) => {
  if (String(newCourseId) === String(oldCourseId)) return;

  // Reinicia dependientes
  videoOptions.value = [];
  lessonOptions.value = [];
  form.video_id = '';
  form.lesson_id = '';

  if (Number(form.type) === 2) loadVideosForCourse(newCourseId, false);
  if (Number(form.type) === 3) loadLessonsForCourse(newCourseId, false);
});

watch(() => form.type, (newType, oldType) => {
  const t = Number(newType);

  // Limpia ambos dependientes siempre que cambia
  videoOptions.value = [];
  lessonOptions.value = [];
  form.video_id = '';
  form.lesson_id = '';

  if (!form.course_id) return;

  if (t === 2) loadVideosForCourse(form.course_id, true);
  if (t === 3) loadLessonsForCourse(form.course_id, true);
});

// publicación
const submit = () => {
  Object.keys(form).forEach(key => (touched.value[key] = true));
  if (!isFormValid.value) return;

  // Si usas bandera para conservar video en backend:
  const payload = { ...form.data(), keep_video: keepVideo.value ? 1 : 0 };

  form.transform(() => payload).post(route('admin.evaluations.update', props.evaluation.id), {
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
