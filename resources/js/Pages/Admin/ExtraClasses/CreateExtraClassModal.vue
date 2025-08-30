<template>
  <div
    v-show="show"
    class="modal fade show d-block"
    tabindex="-1"
    aria-labelledby="createExtraClassModalLabel"
    aria-modal="true"
    role="dialog"
  >
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content" style="z-index:9999!important;">
        <div class="modal-header">
          <h5 class="modal-title" id="createExtraClassModalLabel">
            <i class="bi bi-plus-circle me-2"></i> Crear Clase Extra
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': form.processing }">
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
                    :validateFunction="() => ''"
                    @blur="() => handleBlur('is_youtube')"
                  />
                  <div class="form-text">1 = YouTube, 2 = Archivo</div>
                </div>

                <div class="col-md-8 mb-3" v-if="Number(form.is_youtube) === 1">
                  <FieldText
                    id="youtube_url"
                    label="URL de YouTube"
                    v-model="form.youtube_url"
                    :required="Number(form.is_youtube) === 1"
                    :showValidation="touched.youtube_url"
                    :formError="form.errors.youtube_url"
                    :validateFunction="() => validateYoutubeIfNeeded()"
                    placeholder="https://www.youtube.com/watch?v=..."
                    @blur="() => handleBlur('youtube_url')"
                  />
                </div>

                <div class="col-md-8 mb-3" v-else>
                  <FieldFile
                    id="video"
                    label="Archivo de video"
                    v-model="form.video"
                    :showValidation="touched.video"
                    :formError="form.errors.video"
                    @blur="() => handleBlur('video')"
                    accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska"
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
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'
import FieldFile from '@/Components/Admin/Fields/FieldFile.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

const props = defineProps({
  show: { type: Boolean, default: false }
})

const emit = defineEmits(['close', 'saved'])

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

const form = useForm({
  title: '',
  extract: '',
  description: '',
  image: null,
  cover: null,
  is_youtube: 2,     // por defecto Archivo (coincide con tu modal original)
  youtube_url: '',
  video: null,
  subt_str: null,
  category: '',
  tags: '',
  active: 1,
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
  if (Number(form.is_youtube) === 2 && !form.video) {
    return 'Debes subir un archivo de video cuando el origen es Archivo'
  }
  return ''
}

// Sincronizar campos al alternar origen
watch(() => form.is_youtube, (val) => {
  if (Number(val) === 1) {
    form.video = null
  } else {
    form.youtube_url = ''
  }
})

const isFormValid = computed(() => {
  const baseOk = !validateRequired('title')
  const originOk =
    (Number(form.is_youtube) === 1 && !validateYoutubeIfNeeded()) ||
    (Number(form.is_youtube) === 2 && !validateVideoIfNeeded())
  const orderOk = Number.isInteger(Number(form.order)) && Number(form.order) >= 0
  return baseOk && originOk && orderOk
})

const submit = () => {
  Object.keys(form).forEach(k => { touched.value[k] = true })
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
      onSuccess: () => {
        form.reset()
        emit('saved')
        emit('close')
      },
      onFinish: () => {
        form.transform((d) => d)
      }
    })
}
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
