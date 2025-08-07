<template>
  <Head title="Editar Evaluación" />
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
                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="user_id"
                        label="Usuario"
                        v-model="form.user_id"
                        :readonly="true"
                        :required="true"
                        :showValidation="touched.user_id"
                        :formError="form.errors.user_id"
                        :validateFunction="() => validateField('user_id')"
                        :options="userOptions"
                        @blur="() => handleBlur('user_id')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelect
                        id="course_id"
                        label="Curso"
                        v-model="form.course_id"
                        :required="true"
                        :readonly="true"
                        :showValidation="touched.course_id"
                        :formError="form.errors.course_id"
                        :validateFunction="() => validateField('course_id')"
                        :options="courseOptions"
                        @blur="() => handleBlur('course_id')"
                      />
                    </div>

                    <div class="col-md-4 mb-3">
                      <FieldDate
                        id="eva_send_date"
                        label="Fecha de envío"
                        :readonly="true"
                        v-model="form.eva_send_date"
                        :required="true"
                        :showValidation="touched.eva_send_date"
                        :formError="form.errors.eva_send_date"
                        :validateFunction="() => validateField('eva_send_date')"
                        @blur="() => handleBlur('eva_send_date')"
                      />
                    </div>

                    <div class="col-md-4 mb-3">
                      <FieldSelect
                        id="eva_type"
                        label="Tipo de Evaluación"
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
                      <FieldSelect
                        id="status"
                        label="Estatus"
                        v-model="form.status"
                        :required="true"
                        :showValidation="touched.status"
                        :formError="form.errors?.status"
                        :validateFunction="() => validateField('status')"
                        :options="statusOptions || []"
                        @blur="() => handleBlur('status')"
                      />
                    </div>

                    <div class="col-md-8 mb-3">
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

                    <div class="col-md-12 mb-3" v-if="form.eva_type == 2">
                      <FieldFile
                        id="eva_video_file"
                        label="Archivo de Video"
                        v-model="form.eva_video_file"
                        :showValidation="touched.eva_video_file"
                        :formError="form.errors.eva_video_file"
                        @blur="() => handleBlur('eva_video_file')"
                        :initialPreview="videoPreview"
                        accept="video/mp4,video/quicktime,video/x-m4v"
                      />
                      <div v-if="videoPreview" class="mt-2">
                        <video controls style="max-width: 320px;" :src="videoPreview"></video>
                      </div>
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
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldFile from '@/Components/Admin/Fields/FieldFile.vue';

const props = defineProps({
  evaluation: Object,
  users: Array,
  courses: Array,
  statusOptions: Array
});

// Opciones para tipo de evaluación
const evaTypeOptions = [
  { value: 1, label: 'Cuestionario' },
  { value: 2, label: 'Video' }
];

const form = useForm({
  _method: 'PUT',
  user_id: props.evaluation.user_id,
  course_id: props.evaluation.course_id,
  eva_send_date: props.evaluation.eva_send_date,
  eva_type: props.evaluation.eva_type ?? 1, // Default a cuestionario si no existe
  eva_comments: props.evaluation.eva_comments,
  eva_video_file: '',
  status: props.evaluation.status || 'SEND'
});

const videoPreview = props.evaluation.eva_video_file
  ? '/storage/' + props.evaluation.eva_video_file
  : null;

const touched = ref({});

const userOptions = props.users.map(u => ({
  value: u.id,
  label: `${u.name} (${u.email})`
}));
const courseOptions = props.courses.map(c => ({
  value: c.id,
  label: c.title
}));

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
    return `El campo ${field.replace('_', ' ')} es obligatorio`;
  }
  return '';
};

const isFormValid = computed(() => {
  return !validateField('user_id') &&
         !validateField('course_id') &&
         !validateField('eva_send_date') &&
         !validateField('eva_type');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);
  if (isFormValid.value) {
    form.post(route('admin.evaluations.update', props.evaluation.id), {
      _method: 'put',
      forceFormData: true,
      preserveScroll: true,
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
