<template>
  <Head :title="headTitle" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Clases Extra', route: 'admin.extras.index' },
          { label: 'Editar', route: '' }
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
                <i class="bi bi-save me-2"></i>Guardar cambios
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
                  <!-- Metadatos simples -->
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
           
                    <div class="mt-3">
                      <FieldSwitch
                        id="active"
                        label="Activado"
                        :modelValue="Number(form.active) === 1"
                        @update:modelValue="val => form.active = val ? 1 : 2"
                        :required="false"
                        :disabled="false"
                        :showValidation="touched.active"
                        :formError="form.errors.active"
                        :validateFunction="() => validateField('active')"
                        @blur="() => handleBlur('active')"
                      />
                    </div>
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

                  <!-- Media (FieldImage con preview y eventos) -->
                  <div class="col-md-6 mb-3">
                    <FieldImage
                      id="image"
                      label="Imagen"
                      v-model="form.image"
                      :showValidation="touched.image"
                      :formError="form.errors.image"
                      :initialPreview="imagePreview"
                      :validateFunction="() => validateField('image')"
                      @blur="() => handleBlur('image')"
                      @image-removed="onRemoveImage"
                      @update:keep="val => form.keep_image = !!val"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldImage
                      id="cover"
                      label="Cover"
                      v-model="form.cover"
                      :showValidation="touched.cover"
                      :formError="form.errors.cover"
                      :initialPreview="coverPreview"
                      :validateFunction="() => validateField('cover')"
                      @blur="() => handleBlur('cover')"
                      @image-removed="onRemoveCover"
                      @update:keep="val => form.keep_cover = !!val"
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

                  <!-- YouTube -->
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

                  <!-- Archivo (FieldVideo como en Video/Edit.vue) -->
                  <div class="col-md-8 mb-3" v-else>
                    <FieldVideo
                      id="video"
                      label="Archivo de video — deja vacío para conservar el actual"
                      :initial-path="initialVideoPath"
                      :subtitles="extraSubtitles"
                      :show-validation="touched.video"
                      :form-error="form.errors.video"
                      @update:modelValue="val => form.video = val"
                      @update:keep="val => form.keep_video = !!val"
                      @blur="() => handleBlur('video')"
                      accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska"
                    />
                  </div>

                  <div class="col-md-4 mb-3" v-if="Number(form.is_youtube) === 2">
                    <FieldFile
                      id="subt_str"
                      label="Subtítulos (.vtt/.srt) — deja vacío para conservar el actual"
                      v-model="form.subt_str"
                      :showValidation="touched.subt_str"
                      :formError="form.errors.subt_str"
                      :validateFunction="() => validateField('subt_str')"
                      @blur="() => handleBlur('subt_str')"
                      accept=".vtt,text/vtt,.srt,text/plain"
                    />
                  </div>
                </div>
              </div>

              <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i>Guardar cambios
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
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue'
import FieldVideo from '@/Components/Admin/Fields/FieldVideo.vue' // CORREGIDO

const props = defineProps({
  extra: { type: Object, default: null },
  item:  { type: Object, default: null }
})

const item = computed(() => props.extra || props.item || {})
const headTitle = computed(() => `Editar Clase Extra: ${item.value.title || ''}`)

// Previews y rutas de media existentes
const imagePreview = computed(() => item.value.image_url || null)
const coverPreview  = computed(() => item.value.cover_url || null)

// Si tu backend expone un stream_url úsalo; si no, cae a video_url
const initialVideoPath = computed(() => item.value.video_stream_url || item.value.video_url || null)

// Subtítulos ya existentes (FieldVideo acepta un arreglo tipo TextTrack)
const extraSubtitles = computed(() => {
  // Si solo tienes un subtítulo plano en item.value.subt_url, lo mapeamos a 'es' por defecto
  if (item.value.subt_url) {
    return [{
      src: item.value.subt_url.startsWith('/storage/')
        ? item.value.subt_url
        : item.value.subt_url, // si ya viene absoluta o storage
      srclang: 'es',
      label: 'Español',
      default: true
    }]
  }
  // Si en un futuro agregas múltiples, conviértelos aquí
  return []
})

// Selects
const originOptions = [
  { value: 1, label: 'YouTube' },
  { value: 2, label: 'Archivo' }
]
const activeOptions = [
  { value: 1, label: 'Activo' },
  { value: 2, label: 'Inactivo' }
]

// Form en edición
const form = useForm({
  _method: 'PUT',
  title: item.value.title || '',
  extract: item.value.extract || '',
  description: item.value.description || '',

  image: null,
  cover: null,
  video: null,       // nuevo archivo si se sube
  subt_str: null,    // nuevo subtítulo si se sube

  // flags de borrado/conservación
  remove_image: false,
  remove_cover: false,
  remove_video: false,
  remove_subt: false,

  keep_image: !!item.value.image_url,
  keep_cover: !!item.value.cover_url,
  keep_video: !!item.value.video_url,

  is_youtube: item.value.is_youtube ?? 2,
  youtube_url: item.value.youtube_url || '',

  category: item.value.category || '',
  tags: item.value.tags || '',
  active: item.value.active ?? 1,
  order: item.value.order ?? 0
})

const touched = ref({})

const handleBlur = (field) => { touched.value[field] = true }

// Validación centralizada
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
      // Si es Archivo y decides NO conservar el actual, entonces exige subir uno nuevo
      if (Number(form.is_youtube) === 2) {
        const keeping = !!form.keep_video
        if (!keeping && !form.video) {
          return 'Debes subir un archivo de video o marcar conservar el actual'
        }
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

// Cambios de origen
watch(() => form.is_youtube, (val) => {
  if (Number(val) === 1) {
    // Cambian a YouTube: no necesitamos video local
    form.video = null
    form.keep_video = false
    form.remove_video = true // opcional: elige política por defecto
  } else {
    // Cambian a Archivo
    form.youtube_url = form.youtube_url || ''
    // si ya había uno, por defecto lo conservamos
    form.keep_video = !!item.value.video_url
    form.remove_video = false
  }
})

// Borrado de imágenes
const onRemoveImage = () => {
  form.remove_image = true
  form.keep_image = false
  form.image = null
  touched.value.image = true
}
const onRemoveCover = () => {
  form.remove_cover = true
  form.keep_cover = false
  form.cover = null
  touched.value.cover = true
}

const submit = () => {
  // Marca como tocados
  ;[
    'title', 'order', 'is_youtube', 'youtube_url', 'video',
    'active', 'description', 'extract', 'category', 'tags',
    'image', 'cover', 'subt_str'
  ].forEach(k => touched.value[k] = true)

  if (!isFormValid.value) return

  // Sincroniza remove_* con keep_* si no se sube nuevo archivo
  const finalRemoveVideo =
    Number(form.is_youtube) === 2
      ? (!form.keep_video && !form.video) // si no conserva y no sube, borra
      : true // si cambiaste a YouTube, elimina el archivo local

  const finalRemoveImage = !form.keep_image && !form.image
  const finalRemoveCover = !form.keep_cover && !form.cover

  form
    .transform((data) => ({
      ...data,
      order: Number(data.order ?? 0),
      is_youtube: Number(data.is_youtube),
      active: Number(data.active),

      // flags definitivos
      remove_image: Boolean(finalRemoveImage),
      remove_cover: Boolean(finalRemoveCover),
      remove_video: Boolean(finalRemoveVideo),
      remove_subt: Boolean(data.remove_subt),
    }))
    .post(route('admin.extras.update', item.value.id), {
      forceFormData: true,
      preserveScroll: true,
      onBefore: (visit) => {
        visit.data._method = 'put'
      },
      onError: (errors) => {
        Object.keys(errors || {}).forEach(k => { touched.value[k] = true })
      }
    })
}
</script>

<style scoped>
/* Opcional */
</style>
