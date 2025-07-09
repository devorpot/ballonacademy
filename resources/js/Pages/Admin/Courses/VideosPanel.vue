<template>
  <Head title="Editar Curso" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Cursos', route: 'admin.courses.index' },
          { label: 'Editar', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.courses.index')" />
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

 

      <!-- Tabla de Videos -->
      <CourseVideosTable :course-id="props.course.id" :videos="props.videos" />

      <!-- Spinner global -->
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
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import CourseVideosTable from '@/Pages/Admin/Courses/CourseVideosTable.vue';




const props = defineProps({
  course: { type: Object, required: true },
  videos: { type: Array, default: () => [] }
});

const form = useForm({
  _method: 'PUT',
  title: props.course.title,
  description: props.course.description,
  description_short: props.course.description_short,
  level: props.course.level,
  date_start: props.course.date_start,
  date_end: props.course.date_end,
  image_cover: '',
  logo: '',
  active: props.course.active ?? true,
  price: props.course.price ?? '',
  payment_link: props.course.payment_link ?? '',
});


const imageCoverPreview = props.course.image_cover ? '/storage/' + props.course.image_cover : null;
const logoPreview = props.course.logo ? '/storage/' + props.course.logo : null;

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (field === 'title' && !form.title.trim()) return 'El título es obligatorio';
  if (field === 'description' && !form.description.trim()) return 'La descripción es obligatoria';
    if (field === 'price' && (form.price === '' || isNaN(parseFloat(form.price)))) {
    return 'El precio es obligatorio y debe ser un número válido';
  }
  if (field === 'date_end' && form.date_start && form.date_end && form.date_end < form.date_start) {
    return 'La fecha de fin no puede ser anterior a la fecha de inicio';
  }
  return '';
};

const isFormValid = computed(() => {
  return !validateField('title') &&
         !validateField('description') &&
         !validateField('date_end') &&
         !validateField('price');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  // 🔧 Convertimos el string 'true' o 'false' a booleano real
  form.active = form.active === 'true' || form.active === true;

  if (isFormValid.value) {
    form.post(route('admin.courses.update', props.course.id), {
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
