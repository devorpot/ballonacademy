<template>
  <Head title="Nueva Evaluación" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Evaluaciones', route: 'admin.evaluations.index' },
            { label: 'Nueva', route: '' }
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
                  <i class="bi bi-save me-1"></i> Crear evaluación
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

          <fieldset>
            <legend class="h5 text-uppercase mb-3">Instrucciones</legend>

            <!-- Título -->
            
            <!-- Comentarios -->
            <div class="mb-3">
              <FieldTextarea
                id="eva_comments"
                label="Escribe las instrucciones para la evaluación"
                v-model="form.eva_comments"
                :showValidation="touched.eva_comments"
                :formError="form.errors.eva_comments"
                @blur="() => handleBlur('eva_comments')"
                :rows="6"
                placeholder="Escribe las instrucciones para la evaluación"
              />
            </div>

            <!-- Archivo de Video -->
            <div class="mb-3">
              <FieldVideo
                id="eva_video_file"
                label="Video de Instrucciones"
                v-model="form.eva_video_file"
                v-model:keep="keepVideo"
                :showValidation="touched.eva_video_file"
                :formError="form.errors.eva_video_file"
                :validateFunction="() => validateField('eva_video_file')"
                accept="video/mp4,video/quicktime,video/x-m4v"
                @blur="() => handleBlur('eva_video_file')"
              />
            </div>
          </fieldset>
        </div>

        <div class="card-footer text-end">
          <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
            <i class="bi bi-save me-2"></i> Crear evaluación
          </button>
        </div>
      </div>
    </div>

    <!-- Columna derecha: 3 columnas -->
    <div class="col-lg-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <!-- Curso -->
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

          <!-- Tipo de Evaluación -->
          <div class="mb-3">
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

          <!-- Video dependiente -->
          <div class="mb-3" v-if="form.type === 2">
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

          <!-- Lesson dependiente -->
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

          <!-- Fecha envío -->
          <div class="mb-3">
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

          <!-- Modalidad -->
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
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed, watch } from 'vue'
import axios from 'axios'

// Layouts & UI
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

// Fields
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue'
import FieldVideo from '@/Components/Admin/Fields/FieldVideo.vue'
import FieldNumber from '@/Components/Admin/Fields/FieldNumber.vue'

// Props recibidos desde el controlador (create)
const props = defineProps({
  users: Array,
  courses: Array,
  statusOptions: Array,
  typeOptions: Array // opciones del enum EvaluationsTypes (1=COURSE, 2=VIDEO, 3=LESSON)
})

// Form principal
const form = useForm({
  course_id: '',
  video_id: '',
  lesson_id: '',         // <-- nuevo
  eva_send_date: '',
  eva_type: 1,           // modalidad (quiz/video), independiente del "type"
  type: 1,               // ámbito (Course/Video/Lesson)
  eva_comments: '',
  title: '',
  eva_video_file: '',
  status: 'SEND',
  points_min: 1
})

const keepVideo = ref(false)
const touched = ref({})

// Opciones base para selects
const courseOptions = (props.courses || []).map(c => ({
  value: c.id,
  label: c.title
}))

// Fallback por si no llegan desde backend
const typeOptions = props.typeOptions && props.typeOptions.length
  ? props.typeOptions
  : [
      { value: 1, label: 'Evaluación general del Curso' },
      { value: 2, label: 'Evaluación para el Video' },
      { value: 3, label: 'Evaluación para la Lección' }
    ]

const evaTypeOptions = [
  { value: 1, label: 'Cuestionario' },
  { value: 2, label: 'Video' }
]

// Estado para cargas dependientes
const loadingVideos = ref(false)
const videoOptions = ref([])

const loadingLessons = ref(false)
const lessonOptions = ref([])

// UX helpers
const handleBlur = (field) => {
  touched.value[field] = true
}

const validateField = (field) => {
  if (field === 'course_id' && !form.course_id) return 'El curso es obligatorio'
  if (field === 'eva_send_date' && !form.eva_send_date) return 'La fecha de envío es obligatoria'
  if (field === 'eva_type' && !form.eva_type) return 'La modalidad de evaluación es obligatoria'
  if (field === 'type' && !form.type) return 'El tipo de evaluación es obligatorio'

  // Dependientes por tipo
  if (Number(form.type) === 2 && field === 'video_id' && !form.video_id) {
    return 'Debes seleccionar un video del curso'
  }
  if (Number(form.type) === 3 && field === 'lesson_id' && !form.lesson_id) {
    return 'Debes seleccionar una lección del curso'
  }

  // Extras
  if (field === 'title' && !form.title) return 'El título es obligatorio'
  if (field === 'points_min') {
    const n = Number(form.points_min)
    if (!Number.isFinite(n) || n < 1 || n > 100) return 'El puntaje mínimo debe estar entre 1 y 100'
  }

  return ''
}

const isFormValid = computed(() => {
  const baseOk = !validateField('course_id') &&
                 !validateField('eva_send_date') &&
                 !validateField('eva_type') &&
                 !validateField('type') &&
                 !validateField('title') &&
                 !validateField('points_min')

  const videoOk  = Number(form.type) === 2 ? !validateField('video_id')  : true
  const lessonOk = Number(form.type) === 3 ? !validateField('lesson_id') : true

  return baseOk && videoOk && lessonOk
})

// Limpieza de FKs al cambiar el tipo
watch(() => form.type, async (newType) => {
  const t = Number(newType)

  // Siempre limpia ambos antes de volver a cargar
  form.video_id = ''
  videoOptions.value = []
  form.lesson_id = ''
  lessonOptions.value = []

  // Cargar opciones según el tipo si ya hay curso seleccionado
  if (form.course_id) {
    if (t === 2) {
      loadingVideos.value = true
      try {
        const url = route('admin.evaluations.videos.combo', { course: Number(form.course_id) || form.course_id })
        const { data } = await axios.get(url)
        videoOptions.value = Array.isArray(data) ? data.map(v => ({ value: v.id, label: v.title })) : []
      } catch (e) {
        console.error('Error cargando videos del curso:', e)
        videoOptions.value = []
      } finally {
        loadingVideos.value = false
      }
    }

    if (t === 3) {
      loadingLessons.value = true
      try {
        const url = route('admin.evaluations.lessons.combo', { course: Number(form.course_id) || form.course_id })
        const { data } = await axios.get(url)
        lessonOptions.value = Array.isArray(data) ? data.map(l => ({ value: l.id, label: l.title })) : []
      } catch (e) {
        console.error('Error cargando lecciones del curso:', e)
        lessonOptions.value = []
      } finally {
        loadingLessons.value = false
      }
    }
  }
})

// Carga dependiente por curso
watch(
  () => form.course_id,
  async (newCourseId) => {
    // Reset de dependientes
    form.video_id = ''
    videoOptions.value = []
    form.lesson_id = ''
    lessonOptions.value = []

    if (!newCourseId) return

    // Si el tipo actual es VIDEO, cargar videos
    if (Number(form.type) === 2) {
      loadingVideos.value = true
      try {
        const url = route('admin.evaluations.videos.combo', { course: Number(newCourseId) || newCourseId })
        const { data } = await axios.get(url)
        videoOptions.value = Array.isArray(data) ? data.map(v => ({ value: v.id, label: v.title })) : []
      } catch (e) {
        console.error('Error cargando videos del curso:', e)
        videoOptions.value = []
      } finally {
        loadingVideos.value = false
      }
    }

    // Si el tipo actual es LESSON, cargar lessons
    if (Number(form.type) === 3) {
      loadingLessons.value = true
      try {
        const url = route('admin.evaluations.lessons.combo', { course: Number(newCourseId) || newCourseId })
        const { data } = await axios.get(url)
        lessonOptions.value = Array.isArray(data) ? data.map(l => ({ value: l.id, label: l.title })) : []
      } catch (e) {
        console.error('Error cargando lecciones del curso:', e)
        lessonOptions.value = []
      } finally {
        loadingLessons.value = false
      }
    }
  }
)

// Envío
const submit = () => {
  // Marcar todo como "tocado" para mostrar validaciones
  Object.keys(form.data()).forEach(key => (touched.value[key] = true))
  if (!isFormValid.value) return

  form.post(route('admin.evaluations.store'), {
    forceFormData: true,
    preserveScroll: true
  })
}
</script>


<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
