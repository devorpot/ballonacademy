<template>
  <Head :title="`Editar Lección: ${form.title || '...'}`" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Cursos', route: 'admin.courses.index' },
            { label: `Lecciones`, route: 'admin.courses.lessons.panel', params: props.lesson?.course_id },
            { label: `Editar - ${props.lesson.title}` }
          ]"
        />

 
        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.lessons.index')" />
                <button
                  class="btn btn-primary"
                  :disabled="form.processing || !isFormValid"
                  @click="submit"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-1"></i> Actualizar lección
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="section-form my-2">
          <div class="container-fluid">
            <form @submit.prevent="submit">
              <div class="card">
                <div class="card-body">
                  <div class="row">
                    <!-- Title -->
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="title"
                        label="Título"
                        v-model="form.title"
                        :required="true"
                        :showValidation="touched.title"
                        :formError="form.errors.title"
                        :validateFunction="() => validateRequired('title', 'Título')"
                        placeholder="Título de la lección"
                        @blur="() => handleBlur('title')"
                      />
                    </div>

                    <!-- Publish On -->
                    <div class="col-md-6 mb-3">
                      <FieldDate
                        id="publish_on"
                        label="Fecha de publicación"
                        v-model="form.publish_on"
                        :required="true"
                        :max="today"
                        :showValidation="touched.publish_on"
                        :formError="form.errors.publish_on || publishOnError"
                        :validateFunction="() => validateRequired('publish_on', 'Fecha de publicación')"
                        @blur="() => handleBlur('publish_on')"
                      />
                    </div>

                    <!-- Instructions -->
                    <div class="col-md-12 mb-3">
                      <FieldTextarea
                        id="instructions"
                        label="Instrucciones"
                        v-model="form.instructions"
                        :showValidation="touched.instructions"
                        :formError="form.errors.instructions"
                        placeholder="Indicaciones para el estudiante (opcional)"
                        @blur="() => handleBlur('instructions')"
                      />
                    </div>

                    <!-- Description short -->
                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="description_short"
                        label="Descripción corta"
                        v-model="form.description_short"
                        :showValidation="touched.description_short"
                        :formError="form.errors.description_short"
                        placeholder="Resumen breve (opcional)"
                        @blur="() => handleBlur('description_short')"
                      />
                    </div>

                    <!-- Course -->
                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="course_id"
                        label="Curso"
                        v-model="form.course_id"
                        :required="true"
                        :options="courseOptions"
                        :showValidation="touched.course_id"
                        :formError="form.errors.course_id"
                        :validateFunction="() => validateRequired('course_id', 'Curso')"
                        @blur="() => handleBlur('course_id')"
                      />
                    </div>

                    <!-- Teacher -->
                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="teacher_id"
                        label="Profesor"
                        v-model="form.teacher_id"
                        :required="true"
                        :options="teacherOptions"
                        :showValidation="touched.teacher_id"
                        :formError="form.errors.teacher_id"
                        :validateFunction="() => validateRequired('teacher_id', 'Profesor')"
                        @blur="() => handleBlur('teacher_id')"
                      />
                    </div>

                    <!-- Order -->
                    <div class="col-md-3 mb-3">
                      <FieldNumber
                        id="order"
                        label="Orden"
                        v-model="form.order"
                        :min="1"
                        :step="1"
                        :readonly="form.processing"
                        :showValidation="true"
                        :validationMessage="form.errors.order"
                        @blur="() => handleBlur('order')"
                      />
                    </div>

                    <!-- Active -->
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                      <FieldSwitch
                        id="active"
                        label="Activar"
                        v-model="form.active"
                        :readonly="form.processing"
                        :showValidation="false"
                        :validationMessage="form.errors.active"
                      />
                    </div>

                    <!-- Image (main) -->
                    <div class="col-md-6 mb-3">
                      <FieldImage
                        id="image"
                        label="Imagen principal"
                        v-model="form.image"
                        :initialPreview="imagePreview"
                        :showValidation="touched.image"
                        :formError="form.errors.image"
                        @blur="() => handleBlur('image')"
                        @image-removed="onRemoveMainImage"
                      />
                    </div>

                    <!-- Image cover -->
                    <div class="col-md-6 mb-3">
                      <FieldImage
                        id="image_cover"
                        label="Imagen de portada"
                        v-model="form.image_cover"
                        :initialPreview="imageCoverPreview"
                        :showValidation="touched.image_cover"
                        :formError="form.errors.image_cover"
                        @blur="() => handleBlur('image_cover')"
                        @image-removed="onRemoveCoverImage"
                      />
                    </div>

                    <!-- Feature toggles -->
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-4 mb-3">
                          <FieldSwitch
                            id="add_video"
                            label="Agregar videos"
                            v-model="form.add_video"
                            :readonly="form.processing"
                            :showValidation="true"
                            :validationMessage="form.errors.add_video"
                          />
                        </div>

                        <div class="col-md-4 mb-3">
                          <FieldSwitch
                            id="add_evaluation"
                            label="Agregar evaluaciones"
                            v-model="form.add_evaluation"
                            :readonly="form.processing"
                            :showValidation="true"
                            :validationMessage="form.errors.add_evaluation"
                          />
                        </div>

                        <div class="col-md-4 mb-3">
                          <FieldSwitch
                            id="add_materials"
                            label="Agregar materiales"
                            v-model="form.add_materials"
                            :readonly="form.processing"
                            :showValidation="true"
                            :validationMessage="form.errors.add_materials"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Actualizar lección
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>

      <section class="section-panel">
        <div class="container-fluid">
          <div class="row">
          <div class="col-12">
          <div class="alert alert-info small mb-0">
            Las listas de videos, evaluaciones y materiales se gestionan aquí o en pantallas específicas del curso.
          </div>

        </div>
        </div>
        
          <div class="row" >
              <div class="col-12">
                <div class="mt-3"  v-if="form.add_video && form.course_id">
                <VideoPicker :lesson="props.lesson" />
                </div>
                <div v-else-if="form.add_video && !form.course_id" class="text-muted">
                  Selecciona primero un curso para poder asignar videos.
                </div>
            </div>
          </div>
          <div class="row" >
              <div class="col-12">
                <div class="mt-3"  v-if="form.add_evaluation && form.course_id">
                <EvaluationsPicker :lesson="props.lesson" />
                </div>
                <div v-else-if="form.add_video && !form.course_id" class="text-muted">
                  Selecciona primero un curso para poder asignar videos.
                </div>
            </div>
          </div>
        </div>
      </section>



      <SpinnerOverlay v-if="form.processing" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { route } from 'ziggy-js'

// Layout & UI
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

// Fields
import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldNumber from '@/Components/Admin/Fields/FieldNumber.vue'
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue'
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'

// Pickers
import VideoPicker from '@/Components/Admin/Videos/VideoPicker.vue'
import EvaluationsPicker from '@/Components/Admin/Evaluations/EvaluationsPicker.vue'

const props = defineProps({
  lesson: { type: Object, required: true },
  courses: Array,
  teachers: Array
})

const today = new Date().toISOString().slice(0, 10)

function toDateInput(value) {
  if (!value) return ''
  const d = new Date(value)
  if (!isNaN(d.getTime())) {
    const yyyy = d.getFullYear()
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const dd = String(d.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
  }
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value
  return ''
}

/** Previews (coinciden con Courses/Edit.vue) */
const imagePreview = computed(() =>
  props.lesson?.image ? `/storage/${props.lesson.image}` : null
)
const imageCoverPreview = computed(() =>
  props.lesson?.image_cover ? `/storage/${props.lesson.image_cover}` : null
)

const form = useForm({
  _method: 'PUT',
  title: props.lesson?.title ?? '',
  instructions: props.lesson?.instructions ?? '',
  description_short: props.lesson?.description_short ?? '',
  publish_on: toDateInput(props.lesson?.publish_on) || today,

  course_id: props.lesson?.course_id ?? '',
  teacher_id: props.lesson?.teacher_id ?? '',
  order: props.lesson?.order ?? 1,
  active: Boolean(props.lesson?.active),

  add_video: Boolean(props.lesson?.add_video ?? true),
  add_evaluation: Boolean(props.lesson?.add_evaluation ?? false),
  add_materials: Boolean(props.lesson?.add_materials ?? false),

  // Archivos y flags (alineado a Courses/Edit.vue)
  image: null,
  image_cover: null,
  remove_image: false,
  remove_image_cover: false,

  // colecciones (si las consumes en UI hija)
  evaluations: props.lesson?.evaluations ?? null,
  materials: props.lesson?.materials ?? null,
})

const touched = ref({})

onMounted(() => {
  try {
    const params = new URLSearchParams(window.location.search)
    const courseId = params.get('course_id')
    if (courseId && !form.course_id) {
      form.course_id = Number(courseId)
    }
  } catch (e) {}
})

const courseOptions = computed(() =>
  (props.courses || []).map(c => ({ value: c.id, label: c.title }))
)

const teacherOptions = computed(() =>
  (props.teachers || []).map(t => ({ value: t.id, label: `${t.firstname} ${t.lastname}`.trim() }))
)

const handleBlur = (field) => {
  touched.value[field] = true
}

const validateRequired = (field, label) => {
  const val = form[field]
  if (val === undefined || val === null || (typeof val === 'string' && !val.trim())) {
    return `El campo ${label} es obligatorio`
  }
  return ''
}

const publishOnError = computed(() => {
  if (!touched.value.publish_on) return ''
  if (!form.publish_on) return 'La fecha de publicación es obligatoria'
  const d = new Date(form.publish_on)
  if (isNaN(d.getTime())) return 'Fecha inválida'
  return ''
})

const isFormValid = computed(() =>
  !validateRequired('title', 'Título') &&
  !validateRequired('course_id', 'Curso') &&
  !validateRequired('teacher_id', 'Profesor') &&
  !publishOnError.value
)

/** Handlers de eliminación de imágenes (como en Courses/Edit.vue) */
function onRemoveMainImage() {
  form.remove_image = true
  form.image = null
}
function onRemoveCoverImage() {
  form.remove_image_cover = true
  form.image_cover = null
}

/** Refrescar solo el prop 'lesson' después de adjuntar en pickers */
function refreshLesson() {
  router.reload({ only: ['lesson'] })
}
function onVideosAttached() {
  refreshLesson()
}
function onEvaluationsAttached() {
  refreshLesson()
}

const submit = () => {
  Object.keys(form.data()).forEach(key => (touched.value[key] = true))
  if (!isFormValid.value) return

  form.transform(data => ({
    ...data,
    active: data.active ? 1 : 0
  }))
  .post(route('admin.lessons.update', props.lesson.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      form.image = null
      form.image_cover = null
      // Tras guardar, podrías recargar la lección si necesitas estados actualizados
      // router.reload({ only: ['lesson'] })
    }
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
