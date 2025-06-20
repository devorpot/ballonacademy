<template>
  <Head title="Crear Nuevo Certificado" />
  <AdminLayout>
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
            <Title :title="'Crear Certificado'" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.certificates.index')" />
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
                <!-- Usuario -->
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

                <!-- Maestro -->
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

                <!-- Autorizado por -->
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

                <!-- Fechas -->
                <div class="col-md-4 mb-3">
                 <FieldDate
                    id="date_start"
                    label="Fecha de inicio"
                    v-model="form.date_start"
                    :required="false"
                    :showValidation="touched.date_start"
                    :formError="form.errors.date_start"
                    :validateFunction="() => ''"   
                    @blur="() => handleBlur('date_start')"
                  />
                </div>

                <div class="col-md-4 mb-3">
                  <FieldDate
                    id="date_end"
                    label="Fecha de Finalización"
                    v-model="form.date_end"
                    :required="false"
                    :showValidation="touched.date_end"
                    :formError="form.errors.date_end"
                    :validateFunction="() => ''"   
                    @blur="() => handleBlur('date_end')"
                  />
                </div>

                <div class="col-md-4 mb-3">
                   <FieldDate
                    id="date_expedition"
                    label="Fecha de Expedición"
                    v-model="form.date_expedition"
                    :required="false"
                    :showValidation="touched.date_expedition"
                    :formError="form.errors.date_expedition"
                    :validateFunction="() => ''"   
                    @blur="() => handleBlur('date_expedition')"
                  />
                  
                </div>

                <!-- Comentarios -->
                <div class="col-md-12 mb-3">
                  <FieldTextarea
                    id="comments"
                    label="Comentarios"
                    v-model="form.comments"
                    :required="false"
                    :showValidation="false"
                    placeholder="Ingrese comentarios"
                  />
                </div>

                <!-- Foto -->
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

                <!-- Logo -->
                <div class="col-md-6 mb-3">
                    <FieldImage
                        id="logo"
                        label="Foto del certificado"
                        v-model="form.logo"
                        :required="false"
                        :showValidation="touched.logo"
                        :formError="form.errors.logo"
                        :validateFunction="() => ''"
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

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldUrl from '@/Components/Admin/Fields/FieldUrl.vue';
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
  if (!form[field] || !form[field].toString().trim()) {
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
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};
</script>
