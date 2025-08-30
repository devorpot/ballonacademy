<template>
  <Head title="Crear Clase Extra" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'dashboard' },
          { label: 'Clases Extra', route: 'extraclasses.index' },
          { label: 'Crear', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('extraclasses.index')" />
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
                      :validateFunction="() => validateRequired('title')"
                      placeholder="Título de la clase"
                      @blur="() => handleBlur('title')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="extract"
                      label="Extracto"
                      v-model="form.extract"
                      :required="false"
                      :showValidation="touched.extract"
                      :formError="form.errors.extract"
                      :validateFunction="() => ''"
                      placeholder="Resumen corto (opcional)"
                      @blur="() => handleBlur('extract')"
                    />
                  </div>

                  <div class="col-12 mb-3">
                    <FieldTextarea
                      id="description"
                      label="Descripción"
                      v-model="form.description"
                      :required="false"
                      :showValidation="touched.description"
                      :formError="form.errors.description"
                      :validateFunction="() => ''"
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
                      @blur="() => handleBlur('cover')"
                    />
                  </div>

                  <!-- Origen del video -->
                  <div class="col-md-4 mb-3">
                    <FieldSelect
                      id="is_youtube"
                      label="Origen del video"
                      v-model="form.is_youtube"
                      :options="originOptions"
                      :required="true"
                      :showValidation="touched.is_youtube"
                      :formError="form.errors.is_youtube"
                      :validateFunction="() => ''"
                      @blur="() => handleBlur('is_youtube')"
                    />
                    <div class="form-text">0 = Archivo, 1 = YouTube</div>
                  </div>

                  <div class="col-md-8 mb-3" v-if="Number(form.is_youtube) === 1">
                    <FieldText
                      id="youtube_url"
                      label="URL de YouTube"
                      v-model="form.youtube_url"
                      :required="true"
                      :showValidation="touched.youtube_url"
                      :formError="form.errors.youtube_url"
                      :validateFunction="() => validateYoutubeIfNeeded()"
                      placeholder="https://www.youtube.com/watch?v=..."
                      @blur="() => handleBlur('youtube_url')"
                    />
                  </div>

                  <div class="col-md-8 mb-3" v-else>
                    <FieldVideo
                      id="video"
                      label="Archivo de video (MP4/WEBM/OGG)"
                      v-model="form.video"
                      :showValidation="touched.video"
                      :formError="form.errors.video"
                      @blur="() => handleBlur('video')"
                      accept="video/mp4,video/webm,video/ogg"
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <FieldFile
                      id="subt_str"
                      label="Subtítulos (.vtt/.srt)"
                      v-model="form.subt_str"
                      :showValidation="touched.subt_str"
                      :formError="form.errors.subt_str"
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
                      :validateFunction="() => ''"
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
import { ref, computed } from 'vue'

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

const originOptions = [
  { value: 1, label: 'YouTube' },
  { value: 0, label: 'Archivo MP4/WEBM/OGG' }
]

const activeOptions = [
  { value: 1, label: 'Activo' },
  { value: 0, label: 'Inactivo' }
]

const form = useForm({
  title: '',
  extract: '',
  description: '',
  image: null,
  cover: null,
  is_youtube: 0,      // 0 = Archivo, 1 = YouTube
  youtube_url: '',
  video: null,
  subt_str: null,
  category: '',
  tags: '',
  active: 1,          // 1 = Activo
  order: 0
})

const touched = ref({})

const handleBlur = (field) => { touched.value[field] = true }

const validateRequired = (field) => {
  const v = form[field]
  if (v === null || v === undefined) return 'Este campo es obligatorio'
  if (typeof v === 'string' && v.trim() === '') return 'Este campo es obligatorio'
  return ''
}

const validateYoutubeIfNeeded = () => {
  if (Number(form.is_youtube) === 1 && (!form.youtube_url || !form.youtube_url.trim())) {
    return 'URL de YouTube es obligatoria cuando el origen es YouTube'
  }
  return ''
}

const validateVideoIfNeeded = () => {
  if (Number(form.is_youtube) === 0 && !form.video) {
    return 'Debes subir un archivo de video cuando el origen es Archivo'
  }
  return ''
}

const isFormValid = computed(() => {
  const baseOk = !validateRequired('title')
  const originOk =
    (Number(form.is_youtube) === 1 && !validateYoutubeIfNeeded()) ||
    (Number(form.is_youtube) === 0 && !validateVideoIfNeeded())

  const orderOk = Number.isInteger(Number(form.order)) && Number(form.order) >= 0

  return baseOk && originOk && orderOk
})

const submit = () => {
  Object.keys(form).forEach(k => { touched.value[k] = true })
  if (!isFormValid.value) return

  form
    .transform((data) => ({
      ...data,
      order: Number(data.order),
      is_youtube: Number(data.is_youtube),
      active: Number(data.active),
    }))
    .post(route('extraclasses.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => form.reset(
        'title','extract','description','image','cover',
        'youtube_url','video','subt_str','category','tags','order'
      ),
    })
}

</script>

<style scoped>
/* Opcional */
</style>
