<template>
  <Head title="Crear Nuevo Certificado" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Certificados', route: 'admin.certificates.index' },
          { label: 'Crear', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.certificates.index')" />
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
                    <FieldDate
                      id="date_start"
                      label="Fecha de inicio"
                      v-model="form.date_start"
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <FieldDate
                      id="date_end"
                      label="Fecha de fin"
                      v-model="form.date_end"
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <FieldDate
                      id="date_expedition"
                      label="Fecha de expedición"
                      v-model="form.date_expedition"
                    />
                  </div>

                  <div class="col-md-12 mb-3">
                    <FieldTextarea
                      id="comments"
                      label="Comentarios"
                      v-model="form.comments"
                    />
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

    <SpinnerOverlay v-if="form.processing" />
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';

const props = defineProps({
  users: Array,
  teachers: Array
});

const userOptions = props.users.map(user => ({
  value: user.id,
  label: `${user.name} (${user.email})`
}));

const teacherOptions = props.teachers.map(teacher => ({
  value: teacher.id,
  label: `${teacher.firstname} ${teacher.lastname}`
}));

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
      onSuccess: () => form.reset()
    });
  }
};
</script>
