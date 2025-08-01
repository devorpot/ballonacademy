<template>
  <Head title="Editar Curso" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
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
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.courses.index')" />
                <button
                  class="btn btn-primary"
                  :disabled="form.processing || !isFormValid"
                  @click="submit"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i> Guardar cambios
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
                      <FieldText id="title" label="Título" v-model="form.title" :required="true" :showValidation="touched.title" :formError="form.errors.title" :validateFunction="() => validateField('title')" placeholder="Ingrese el título" @blur="() => handleBlur('title')" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="description" label="Descripción" v-model="form.description" :required="true" :showValidation="touched.description" :formError="form.errors.description" :validateFunction="() => validateField('description')" placeholder="Ingrese la descripción" @blur="() => handleBlur('description')" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="description_short" label="Descripción corta" v-model="form.description_short" placeholder="Ingrese una descripción corta" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="level" label="Nivel" v-model="form.level" placeholder="Ingrese el nivel" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldDate id="date_start" label="Fecha de inicio" v-model="form.date_start" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldDate id="date_end" label="Fecha de fin" v-model="form.date_end" :showValidation="touched.date_end" :formError="form.errors.date_end" :validateFunction="() => validateField('date_end')" @blur="() => handleBlur('date_end')" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldMoney
                        id="price"
                        label="Precio"
                        v-model="form.price"
                        :required="true"
                        :showValidation="touched.price"
                        :formError="form.errors.price"
                        placeholder="Ingrese el precio"
                        @blur="() => handleBlur('price')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelectApi
                        id="currency_id"
                        label="Moneda"
                        v-model="form.currency_id"
                        :formError="form.errors.currency_id"
                        :showValidation="touched.currency_id"
                        api-url="/admin/options/currencies"
                        @blur="() => handleBlur('currency_id')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="payment_link"
                        label="Enlace de pago"
                        v-model="form.payment_link"
                        :showValidation="touched.payment_link"
                        :formError="form.errors.payment_link"
                        placeholder="URL de pago"
                        @blur="() => handleBlur('payment_link')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSwitch
                        id="active"
                        label="Curso activo"
                        :modelValue="!!form.active"
                        @update:modelValue="val => form.active = val ? 1 : 0"
                        :required="false"
                        :disabled="false"
                        :showValidation="touched.active"
                        :formError="form.errors.active"
                        :validateFunction="() => validateField('active')"
                        @blur="() => handleBlur('active')"
                      />

                    </div>

                    <div class="col-md-6 mb-3">
                     <FieldImage
                        id="image_cover"
                        label="Imagen cover"
                        v-model="form.image_cover"
                        :showValidation="touched.image_cover"
                        :formError="form.errors.image_cover"
                        :initialPreview="imageCoverPreview"
                        @blur="() => handleBlur('image_cover')"
                        @image-removed="() => {
                          form.remove_image_cover = true;
                          form.image_cover = null; // Asegura que no mandas archivo
                        }"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                     
                   <FieldImage
                    id="logo"
                    label="Logo"
                    v-model="form.logo"
                    :showValidation="touched.logo"
                    :formError="form.errors.logo"
                    :initialPreview="logoPreview"
                    @blur="() => handleBlur('logo')"
                    @image-removed="() => {
                      form.remove_logo = true;
                      form.logo = null; // Asegura que no mandas archivo
                    }"
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
<CourseVideosTable
  :course-id="props.course.id"
  :videos="props.videos"
  :courses="props.courses ?? []"
/>
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

// UI Components
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import FieldMoney from '@/Components/Admin/Fields/FieldMoney.vue';
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue';
import FieldSelectApi from '@/Components/Admin/Fields/FieldSelectApi.vue';
import CourseVideosTable from '@/Components/Admin/Courses/CourseVideosTable.vue';

// --- PROPS ---
const props = defineProps({
  course: { type: Object, required: true },
  videos: { type: Array, default: () => [] },
  courses: { type: Array, default: () => [] } // Solo si usas esto en el videos-table/modal
});

// --- FORM ---
const form = useForm({
  _method: 'PUT',
  title: props.course.title ?? '',
  description: props.course.description ?? '',
  description_short: props.course.description_short ?? '',
  level: props.course.level ?? '',
  date_start: props.course.date_start ?? '',
  date_end: props.course.date_end ?? '',
  image_cover: null, // file nuevo
  logo: null,        // file nuevo
  remove_image_cover: false, // flag para backend
  remove_logo: false,        // flag para backend
  active: Boolean(props.course.active ?? 1),
  price: props.course.price ?? '',
  payment_link: props.course.payment_link ?? '',
  currency_id: props.course.currency_id ?? null,
});

const imageCoverPreview = props.course.image_cover ? '/storage/' + props.course.image_cover : null;
const logoPreview = props.course.logo ? '/storage/' + props.course.logo : null;

const touched = ref({});

// --- HANDLERS / VALIDACIONES ---
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
         !validateField('price') &&
         !validateField('date_end');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.transform(data => ({
      ...data,
      active: data.active ? 1 : 0,
    }))
    .post(route('admin.courses.update', props.course.id), {
      forceFormData: true,
      preserveScroll: true,
      onError: (errors) => console.error('Errores al guardar:', errors),
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
