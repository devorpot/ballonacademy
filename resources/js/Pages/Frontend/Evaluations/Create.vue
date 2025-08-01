<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

import StudentLayout from '@/Layouts/StudentLayout.vue'
import Title from '@/Components/Admin/Ui/Title.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldFile from '@/Components/Admin/Fields/FieldFile.vue'
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue'

const page = usePage()
const course = page.props.course

const form = useForm({
  eva_video_file: null,
  eva_comments: '',
})

const touched = ref({})

const handleBlur = (field) => {
  touched.value[field] = true
}

const validateField = (field) => {
  if (field === 'eva_video_file' && !form.eva_video_file) {
    return 'El video es obligatorio'
  }
  return ''
}

const isFormValid = computed(() => {
  return !validateField('eva_video_file')
})

const submit = () => {
  touched.value.eva_video_file = true

  if (isFormValid.value) {
    form.post(route('dashboard.courses.evaluations.store', course.id), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => form.reset(),
    })
  }
}
</script>

<template>
  <Head title="Enviar Evaluación" />
  <StudentLayout>
 

    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <ButtonBack label="Volver" :href="route('dashboard.courses.evaluations.index', course.id)" />
            <button class="btn btn-primary" :disabled="form.processing || !isFormValid" @click="submit">
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
                  <FieldDate
                    id="eva_send_date"
                    label="Fecha de envío"
                    :modelValue="new Date().toISOString().slice(0, 10)"
                    disabled
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldFile
                    id="eva_video_file"
                    label="Archivo de video (mp4)"
                    v-model="form.eva_video_file"
                    :accept="'video/mp4'"
                    :showValidation="touched.eva_video_file"
                    :formError="form.errors.eva_video_file"
                    :validateFunction="() => validateField('eva_video_file')"
                    @blur="() => handleBlur('eva_video_file')"
                  />
                </div>

                <div class="col-12 mb-3">
                  <FieldTextarea
                    id="eva_comments"
                    label="Comentarios"
                    v-model="form.eva_comments"
                  />
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <button class="btn btn-primary" type="submit" :disabled="form.processing || !isFormValid">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i class="bi bi-save me-2"></i>Guardar
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>

    <SpinnerOverlay v-if="form.processing" />
  </StudentLayout>
</template>
