<template>
  <div
    v-show="show"
    class="modal fade show d-block"
    tabindex="-1"
    aria-labelledby="createCourseModalLabel"
    aria-modal="true"
    role="dialog"
  >
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="z-index: 9999 !important;">
        <div class="modal-header">
          <h5 class="modal-title" id="createCourseModalLabel">
            <i class="bi bi-plus-circle me-2"></i> Crear Curso
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': form.processing }">
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
                    placeholder="Título del curso"
                    @blur="() => handleBlur('title')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="level"
                    label="Nivel"
                    v-model="form.level"
                    placeholder="Nivel del curso"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="description_short"
                    label="Descripción corta"
                    v-model="form.description_short"
                    placeholder="Descripción breve"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldTextarea
                    id="description"
                    label="Descripción"
                    v-model="form.description"
                    :required="true"
                    :showValidation="touched.description"
                    :formError="form.errors.description"
                    :validateFunction="() => validateField('description')"
                    placeholder="Descripción detallada"
                    @blur="() => handleBlur('description')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldImage
                    id="image_cover"
                    label="Imagen de Portada"
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
                  />
                </div>
              </div>
            </div>
            <SpinnerOverlay v-if="form.processing" />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">
              <i class="bi bi-x-lg me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
              <i class="bi bi-save me-1"></i> Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'saved']);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
  title: '',
  level: '',
  description_short: '',
  description: '',
  image_cover: null,
  logo: null,
  date_start: today,
  date_end: today
});

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (field === 'title' && !form.title.trim()) return 'El título es obligatorio';
  if (field === 'description' && !form.description.trim()) return 'La descripción es obligatoria';
  return '';
};

const isFormValid = computed(() => {
  return !validateField('title') && !validateField('description');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.courses.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        emit('saved');
        emit('close');
      }
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
