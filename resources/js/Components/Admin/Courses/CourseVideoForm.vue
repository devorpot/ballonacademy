<!-- src/Components/Admin/Forms/VideoForm.vue -->
<template>
  <form @submit.prevent="submit" enctype="multipart/form-data">
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
      <div class="col-md-6 mb-3"></div>
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
  v-model="form.value.course_id"
  :options="courseOptions"
  :required="true"
  :showValidation="touched.value.course_id"
  :formError="props.errors?.course_id"
  :validateFunction="() => validateField('course_id')"
  @blur="() => handleBlur('course_id')"
/>
      </div>
      <div class="col-md-6 mb-3">
       <FieldSelect
  id="teacher_id"
  label="Profesor"
  v-model="form.value.teacher_id"
  :options="teacherOptions"
  :required="true"
  :showValidation="touched.value.teacher_id"
  :formError="props.errors?.teacher_id"
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
    <slot name="footer">
      <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
          <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
          <i class="bi bi-save me-2"></i> {{ submitLabel }}
        </button>
      </div>
    </slot>
  </form>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import FieldVideo from '@/Components/Admin/Fields/FieldVideo.vue';

// Definir el default fuera del setup
const videoDefault = {
  id: null,
  title: '',
  description: '',
  description_short: '',
  comments: '',
  video_path: null,
  image_cover: null,
  teacher_id: '',
  course_id: '',
  // Puedes agregar otros campos que necesites aquí
};

const props = defineProps({
  courses: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  modelValue: { type: Object, default: () => ({}) }, // ¡No poner videoDefault aquí!
  submitLabel: { type: String, default: 'Guardar video' },
  processing: Boolean,
  errors: Object,
});

const emit = defineEmits(['update:modelValue', 'submit']);

// Al crear el form, siempre mergea el default
const form = ref({ ...videoDefault, ...props.modelValue });
const touched = ref({});

const courseOptions = computed(() =>
  Array.isArray(props.courses)
    ? props.courses.map(c => ({ value: c.id, label: c.title }))
    : []
);

const teacherOptions = computed(() =>
  Array.isArray(props.teachers)
    ? props.teachers.map(t => ({
        value: t.id,
        label: `${t.firstname ?? ''} ${t.lastname ?? ''}`.trim() || t.name
      }))
    : []
);

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (!form.value[field] || (typeof form.value[field] === 'string' && !form.value[field].trim())) {
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

// Sincroniza el form cada que cambie el prop modelValue (por ejemplo, al editar)
watch(
  () => props.modelValue,
  (val) => {
    form.value = { ...videoDefault, ...val };
  },
  { immediate: true, deep: true }
);

// Emitir el update:modelValue para v-model
watch(
  form,
  (val) => emit('update:modelValue', val),
  { deep: true }
);

const submit = () => {
  Object.keys(form.value).forEach(key => touched.value[key] = true);
  if (isFormValid.value) emit('submit', form.value);
};
</script>
