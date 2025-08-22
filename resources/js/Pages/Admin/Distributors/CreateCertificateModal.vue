<template>
  <div
  v-show="show"
  class="modal fade show d-block"
  tabindex="-1"
  aria-labelledby="createCertificateModalLabel"
  aria-modal="true"
  role="dialog"
>
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style=" z-index:9999!important;">
        <div class="modal-header">
          <h5 class="modal-title" id="createCertificateModalLabel">
            <i class="bi bi-plus-circle me-2"></i> Crear Certificado
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': form.processing }">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <FieldSelect
                    id="user_id"
                    label="Usuario"
                    v-model="form.user_id"
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
                    id="master_id"
                    label="Maestro"
                    v-model="form.master_id"
                    :required="true"
                    :showValidation="touched.master_id"
                    :formError="form.errors.master_id"
                    :validateFunction="() => validateField('master_id')"
                    :options="teacherOptions"
                    @blur="() => handleBlur('master_id')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="authorized_by"
                    label="Autorizado por"
                    v-model="form.authorized_by"
                    :required="true"
                    :showValidation="touched.authorized_by"
                    :formError="form.errors.authorized_by"
                    :validateFunction="() => validateField('authorized_by')"
                    placeholder="Nombre del autorizador"
                    @blur="() => handleBlur('authorized_by')"
                  />
                </div>

                <div class="col-md-4 mb-3">
                  <FieldDate id="date_start" label="Fecha de inicio" v-model="form.date_start" />
                </div>
                <div class="col-md-4 mb-3">
                  <FieldDate id="date_end" label="Fecha de fin" v-model="form.date_end" />
                </div>
                <div class="col-md-4 mb-3">
                  <FieldDate id="date_expedition" label="Fecha de expedición" v-model="form.date_expedition" />
                </div>

                <div class="col-md-12 mb-3">
                  <FieldTextarea id="comments" label="Comentarios" v-model="form.comments" />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldImage
                    id="photo"
                    label="Foto"
                    v-model="form.photo"
                    :showValidation="touched.photo"
                    :formError="form.errors.photo"
                    @blur="() => handleBlur('photo')"
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
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

const props = defineProps({
  show: Boolean,
  users: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] }
});


const emit = defineEmits(['close', 'saved']);

const form = useForm({
  user_id: '',
  master_id: '',
  authorized_by: '',
  date_start: '',
  date_end: '',
  date_expedition: '',
  comments: '',
  photo: null,
  logo: null
});

const touched = ref({});
const userOptions = computed(() => props.users.map(u => ({ value: u.id, label: `${u.name} (${u.email})` })));
const teacherOptions = computed(() => props.teachers.map(t => ({ value: t.id, label: `${t.firstname} ${t.lastname}` })));

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
         !validateField('master_id') &&
         !validateField('authorized_by');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.certificates.store'), {
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
