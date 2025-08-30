<template>
  <Head title="Crear Clase Extra" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Clases Extra', route: 'admin.extras.index' },
          { label: 'Crear', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.extras.index')" />
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
                  <!-- Básicos -->
                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="title"
                      label="Título"
                      v-model="form.title"
                      :required="true"
                      :showValidation="touched.title"
                      :formError="form.errors.title"
                      :validateFunction="() => validateField('title')"
                      placeholder="Título de la clase"
                      @blur="() => handleBlur('title')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="extract"
                      label="Extracto"
                      v-model="form.extract"
                      :showValidation="touched.extract"
                      :formError="form.errors.extract"
                      :validateFunction="() => validateField('extract')"
                      placeholder="Resumen corto (opcional)"
                      @blur="() => handleBlur('extract')"
                    />
                  </div>

                  <div class="col-12 mb-3">
                    <FieldTextarea
                      id="description"
                      label="Descripción"
                      v-model="form.description"
                      :showValidation="touched.description"
                      :formError="form.errors.description"
                      :validateFunction="() => validateField('description')"
                      placeholder="Describe la clase (opcional)"
                      @blur="() => handleBlur('description')"
                    />
                  </div>

                  <!-- Media -->
                  <div class="col-md-6 mb-3">
                    <FieldImage
                      id="image"
                      label="Imagen"
                      v-model="form.image"
                      :showValidation="touched.image"
                      :formError="form.errors.image"
                      :validateFunction="() => validateField('image')"
                      @blur="() => handleBlur('image')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldImage
                      id="cover"
                      label="Cover"
                      v-model="form.cover"
                      :showValidation="touched.cover"
                      :formError="form.errors.cover"
                      :validateFunction="() => validateField('cover')"
                      @blur="() => handleBlur('cover')"
                    />
                  </div>

                  <!-- Origen -->
                  <div class="col-md-4 mb-3">
                    <FieldSelect
                      id="is_youtube"
                      label="Origen del video"
                      v-model="form.is_youtube"
                      :options="originOptions"
                      :required="true"
                      :showValidation="touched.is_youtube"
                      :formError="form.errors.is_youtube"
                      :validateFunction="() => validateField('is_youtube')"
                      @blur="() => handleBlur('is_youtube')"
                    />
                    <div class="form-text">1 = YouTube, 2 = Archivo</div>
                  </div>

                  <div class="col-md-8 mb-3" v-if="Number(form.is_youtube) === 1">
                    <FieldText
                      id="youtube_url"
                      label="URL de YouTube"
                      v-model="form.youtube_url"
                      :required="true"
                      :showValidation="touched.youtube_url"
                      :formError="form.errors.youtube_url"
                      :validateFunction="() => validateField('youtube_url')"
                      placeholder="https://www.youtube.com/watch?v=..."
                      @blur="() => handleBlur('youtube_url')"
                    />
                  </div>

                  <div class="col-md-8 mb-3" v-else>
                    <FieldVideo
                      id="video"
                      label="Archivo de video"
                      v-model="form.video"
                      :showValidation="touched.video"
                      :formError="form.errors.video"
                      :validateFunction="() => validateField('video')"
                      @blur="() => handleBlur('video')"
                      accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska"
                    />
                  </div>

                  <div class="col-md-4 mb-3" v-if="Number(form.is_youtube) === 2">
                    <FieldFile
                      id="subt_str"
                      label="Subtítulos (.vtt/.srt)"
                      v-model="form.subt_str"
                      :showValidation="touched.subt_str"
                      :formError="form.errors.subt_str"
                      :validateFunction="() => validateField('subt_str')"
                      @blur="() => handleBlur('subt_str')"
                      accept=".vtt,text/vtt,.srt,text/plain"
                    />
                  </div>

                  <!-- Metadatos -->
                  <div class="col-md-4 mb-3">
                    <FieldText
                      id="category"
                      label="Categoría"
                      v-model="form.category"
                      :showValidation="touched.category"
                      :formError="form.errors.category"
                      :validateFunction="() => validateField('category')"
                      @blur="() => handleBlur('category')"
                      placeholder="Opcional"
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <FieldText
                      id="tags"
                      label="Tags"
                      v-model="form.tags"
                      :showValidation="touched.tags"
                      :formError="form.errors.tags"
                      :validateFunction="() => validateField('tags')"
                      @blur="() => handleBlur('tags')"
                      placeholder="separadas por coma"
                    />
                  </div>

                  <div class="col-md-2 mb-3">
                    <FieldSelect
                      id="active"
                      label="Estado"
                      v-model="form.active"
                      :options="activeOptions"
                      :required="true"
                      :showValidation="touched.active"
                      :formError="form.errors.active"
                      :validateFunction="() => validateField('active')"
                      @blur="() => handleBlur('active')"
                    />
                  </div>

                  <div class="col-md-2 mb-3">
                    <FieldText
                      id="order"
                      label="Orden"
                      v-model="form.order"
                      :showValidation="touched.order"
                      :formError="form.errors.order"
                      :validateFunction="() => validateField('order')"
                      @blur="() => handleBlur('order')"
                      placeholder="0"
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
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed, watch } from 'vue'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'
import FieldFile from '@/Components/Admin/Fields/FieldFile.vue'
import FieldVideo from '@/Components/Admin/Fields/FieldVideo.vue'

// Backend: 1 = YouTube, 2 = Archivo
const originOptions = [
  { value: 1, label: 'YouTube' },
  { value: 2, label: 'Archivo' }
]

// Backend: 1 = Activo, 2 = Inactivo
const activeOptions = [
  { value: 1, label: 'Activo' },
  { value: 2, label: 'Inactivo' }
]

// --- FORM ---
const form = useForm({
  title: '',
  extract: '',
  description: '',

  image: null,
  cover: null,

  is_youtube: 1,   // YouTube por default
  youtube_url: '',
  video: null,
  subt_str: null,

  category: '',
  tags: '',
  active: 1,       // Activo
  order: 0
})

const touched = ref({})
const FIELDS = [
  'title','extract','description',
  'image','cover','is_youtube','youtube_url','video','subt_str',
  'category','tags','active','order'
]

const handleBlur = (field) => { touched.value[field] = true }

// --- Validaciones centralizadas (estilo Courses/Edit) ---
const validateField = (field) => {
  switch (field) {
    case 'title':
      return form.title && form.title.trim() ? '' : 'El título es obligatorio'

    case 'order': {
      const n = Number(form.order)
      if (Number.isNaN(n) || !Number.isInteger(n)) return 'El orden debe ser un número entero'
      if (n < 0) return 'El orden no puede ser negativo'
      return ''
    }

    case 'is_youtube':
      return (Number(form.is_youtube) === 1 || Number(form.is_youtube) === 2)
        ? '' : 'Origen inválido'

    case 'youtube_url':
      if (Number(form.is_youtube) === 1) {
        if (!form.youtube_url || !form.youtube_url.trim()) return 'La URL de YouTube es obligatoria'
      }
      return ''

    case 'video':
      if (Number(form.is_youtube) === 2 && !form.video) {
        return 'Debes subir un archivo de video cuando el origen es Archivo'
      }
      return ''

    case 'active':
      return (Number(form.active) === 1 || Number(form.active) === 2)
        ? '' : 'Estado inválido'

    // Opcionales
    case 'description':
    case 'extract':
    case 'category':
    case 'tags':
    case 'image':
    case 'cover':
    case 'subt_str':
    default:
      return ''
  }
}

const isFormValid = computed(() => {
  return (
    !validateField('title') &&
    !validateField('order') &&
    !validateField('is_youtube') &&
    !validateField('youtube_url') &&
    !validateField('video') &&
    !validateField('active')
  )
})

// Mantener consistencia al cambiar origen
watch(() => form.is_youtube, (val) => {
  if (Number(val) === 1) {
    // Cambiaron a YouTube: limpia archivo
    form.video = null
  } else {
    // Cambiaron a Archivo: asegura string
    form.youtube_url = form.youtube_url || ''
  }
})

const submit = () => {
  FIELDS.forEach(k => { touched.value[k] = true })
  if (!isFormValid.value) return

  form
    .transform((data) => ({
      ...data,
      order: Number(data.order ?? 0),
      is_youtube: Number(data.is_youtube),
      active: Number(data.active),
    }))
    .post(route('admin.extras.store'), {
      forceFormData: true,
      preserveScroll: true,
      onError: (errors) => {
        // Marca los campos con error del backend como "tocados"
        Object.keys(errors || {}).forEach(k => (touched.value[k] = true))
      },
      onFinish: () => {
        form.transform((d) => d)
      }
    })
}
</script>

<style scoped>
/* Opcional */
</style>
