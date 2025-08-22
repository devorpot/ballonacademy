<template>
  <Head title="Crear Nueva Lección" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Cursos',  route: 'admin.courses.index' },
            { label: 'Crear lección', route: '' }
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
                  <i class="bi bi-save me-1"></i> Guardar lección
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
                      <FieldTextarea
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
                        :required="false"
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
                        :accept="['image/jpeg','image/png']"
                        :maxSizeMB="10"
                        :showValidation="touched.image"
                        :formError="form.errors.image || imageClientError"
                        :validateFunction="() => validateImage('image', 'Imagen principal')"
                        :preview="true"
                        helperText="Formatos permitidos: JPG/PNG. Máx 10MB."
                        @blur="() => handleBlur('image')"
                      />
                    </div>

                    <!-- Image cover -->
                    <div class="col-md-6 mb-3">
                      <FieldImage
                        id="image_cover"
                        label="Imagen de portada"
                        v-model="form.image_cover"
                        :accept="['image/jpeg','image/png']"
                        :maxSizeMB="10"
                        :showValidation="touched.image_cover"
                        :formError="form.errors.image_cover || imageCoverClientError"
                        :validateFunction="() => validateImage('image_cover', 'Imagen de portada')"
                        :preview="true"
                        helperText="Formatos permitidos: JPG/PNG. Máx 10MB."
                        @blur="() => handleBlur('image_cover')"
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

                    <!-- Hint -->
                    <div class="col-12">
                      <div class="alert alert-info small mb-0">
                        Las listas de videos, evaluaciones y materiales se pueden gestionar posteriormente en la edición de la lección o con pantallas específicas.
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Crear lección
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
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldNumber from '@/Components/Admin/Fields/FieldNumber.vue';
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'; // << Added

const props = defineProps({
  courses: Array,
  teachers: Array
});

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
  title: '',
  instructions: '',
  description_short: '',
  publish_on: today,

  course_id: '',
  teacher_id: '',
  order: 1,
  active: false,

  add_video: true,
  add_evaluation: false,
  add_materials: false,

  // files
  image: null,
  image_cover: null,

  // colecciones opcionales (backend normaliza)
  videos: null,
  evaluations: null,
  materials: null,
});

const touched = ref({});

// Errores de validación cliente para imágenes
const imageClientError = ref('');
const imageCoverClientError = ref('');

onMounted(() => {
  // Autoplenado de course_id desde query (?course_id=123)
  try {
    const params = new URLSearchParams(window.location.search);
    const courseId = params.get('course_id');
    if (courseId && !form.course_id) {
      form.course_id = Number(courseId);
    }
  } catch (e) {}
});

const courseOptions = computed(() =>
  (props.courses || []).map(c => ({ value: c.id, label: c.title }))
);

const teacherOptions = computed(() =>
  (props.teachers || []).map(t => ({ value: t.id, label: `${t.firstname} ${t.lastname}`.trim() }))
);

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateRequired = (field, label) => {
  const val = form[field];
  if (val === undefined || val === null || (typeof val === 'string' && !val.trim())) {
    return `El campo ${label} es obligatorio`;
  }
  return '';
};

// Valida tipo y tamaño si hay archivo (no es obligatorio)
function validateImage(field, label) {
  const file = form[field];
  const setClientError = field === 'image' ? imageClientError : imageCoverClientError;
  setClientError.value = '';

  if (!file) return '';

  const allowed = ['image/jpeg', 'image/png'];
  const maxBytes = 10 * 1024 * 1024; // 10MB

  if (!allowed.includes(file.type)) {
    setClientError.value = `${label}: formato inválido. Usa JPG o PNG.`;
    return setClientError.value;
  }
  if (file.size > maxBytes) {
    setClientError.value = `${label}: excede 10MB.`;
    return setClientError.value;
  }
  return '';
}

const publishOnError = computed(() => {
  if (!touched.value.publish_on) return '';
  if (!form.publish_on) return 'La fecha de publicación es obligatoria';
  const d = new Date(form.publish_on);
  if (isNaN(d.getTime())) return 'Fecha inválida';
  return '';
});

const isFormValid = computed(() => {
  const imgErr1 = validateImage('image', 'Imagen principal');
  const imgErr2 = validateImage('image_cover', 'Imagen de portada');

  return !validateRequired('title', 'Título') &&
         !validateRequired('course_id', 'Curso') &&
         !validateRequired('teacher_id', 'Profesor') &&
         !publishOnError.value &&
         !imgErr1 && !imgErr2;
});

const submit = () => {
  // marcar todos como tocados para mostrar validaciones
  Object.keys(form.data()).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.lessons.store'), {
      preserveScroll: true,
      forceFormData: true, // necesario para enviar File
      onSuccess: () => form.reset(
        'title','instructions','description_short','order','active',
        'add_video','add_evaluation','add_materials',
        'videos','evaluations','materials',
        'image','image_cover'
      ),
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
