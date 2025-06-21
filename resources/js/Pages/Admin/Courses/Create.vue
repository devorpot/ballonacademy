<template>
  <Head title="Crear Nuevo Curso" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Cursos', route: 'admin.courses.index' },
          { label: 'Crear', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.courses.index')" />
              <button 
                type="button" 
                class="btn btn-primary"
                :disabled="form.processing || !isFormValid"
                @click="submit"
              >
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i class="bi bi-save me-2"></i>Guardar
              </button>
            </div>
          </div>
        </div>
      </section>

      <section class="section-form my-2">
        <div class="container-fluid">
          <form @submit.prevent="submit" enctype="multipart/form-data" novalidate>
            <div class="card">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="title"
                      label="Título"
                      v-model="form.title"
                      :required="true"
                      :showValidation="touched.title"
                      :formError="form.errors.title"
                      :validateFunction="() => validateField('title')"
                      placeholder="Ingrese el título"
                      @blur="() => handleBlur('title')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="description"
                      label="Descripción"
                      v-model="form.description"
                      :required="true"
                      :showValidation="touched.description"
                      :formError="form.errors.description"
                      :validateFunction="() => validateField('description')"
                      placeholder="Ingrese la descripción"
                      @blur="() => handleBlur('description')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="description_short"
                      label="Descripción corta"
                      v-model="form.description_short"
                      placeholder="Ingrese una descripción corta"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="level"
                      label="Nivel"
                      v-model="form.level"
                      placeholder="Ingrese el nivel"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldDate
                      id="date_start"
                      label="Fecha de inicio"
                      v-model="form.date_start"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldDate
                      id="date_end"
                      label="Fecha de fin"
                      v-model="form.date_end"
                      :showValidation="touched.date_end"
                      :formError="form.errors.date_end"
                      :validateFunction="() => validateField('date_end')"
                      @blur="() => handleBlur('date_end')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldImage
                      id="image_cover"
                      label="Imagen cover"
                      v-model="form.image_cover"
                      :showValidation="touched.image_cover"
                      :formError="form.errors.image_cover"
                      @blur="() => handleBlur('image_cover')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldImage
                      id="logo"
                      label="Logo"
                      v-model="form.logo"
                      :showValidation="touched.logo"
                      :formError="form.errors.logo"
                      @blur="() => handleBlur('logo')"
                    />
                  </div>
                </div>
              </div>

              <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i>Guardar
                </button>
              </div>
            </div>
          </form>
        </div>
      </section>
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

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';

const today = new Date().toISOString().split('T')[0];

const form = useForm({
  title: '',
  description: '',
  description_short: '',
  level: '',
  date_start: today,
  date_end: today,
  image_cover: null,
  logo: null
});

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (field === 'title' && !form.title.trim()) return 'El título es obligatorio';
  if (field === 'description' && !form.description.trim()) return 'La descripción es obligatoria';
  if (field === 'date_end' && form.date_start && form.date_end && form.date_end < form.date_start) {
    return 'La fecha de fin no puede ser anterior a la fecha de inicio';
  }
  return '';
};

const isFormValid = computed(() => {
  return !validateField('title') &&
         !validateField('description') &&
         !validateField('date_end');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);
  if (isFormValid.value) {
    form.post(route('admin.courses.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => form.reset()
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
