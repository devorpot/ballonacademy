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
import FieldVideo from '@/Components/Admin/Fields/FieldVideo.vue'
// Props desde el backend
const page = usePage()
const course = page.props.course
const evaluation = page.props.evaluation
const video_url = page.props.video_url

// Formulario con datos precargados
const form = useForm({
  _method: 'PUT',
  eva_video_file: null,
  eva_comments: evaluation.eva_comments || '',
  keep_video: true,
})


const touched = ref({})

const handleBlur = (field) => {
  touched.value[field] = true
}

const validateField = (field) => {
  if (field === 'eva_video_file' && !form.eva_video_file) {
    return ''
  }
  return ''
}

const isFormValid = computed(() => {
  return true // No es obligatorio actualizar video
})

const submit = () => {
  form.post(route('dashboard.courses.evaluations.update', {
    course: course.id,
    evaluation: evaluation.id
  }), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
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



 <section class="section-panel">
   <div class="inner">
     <div class="container-fluid">
       <div class="row">
         <div class="col-12">
          <h6><small>Evaluación</small></h6>
           <h3>{{course.title}}</h3>
           <p>{{course.description_short}}</p>
         </div>
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
                 <FieldVideo
                  id="eva_video_file"
                  label="Archivo de video (mp4)"
                  :initial-path="video_url"
                  :form-error="form.errors.eva_video_file"
                  :show-validation="true"
                  @update:modelValue="form.eva_video_file = $event"
                  @update:keep="form.keep_video = $event"
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
