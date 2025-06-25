<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-camera-video-fill me-2"></i> 
            {{ editingId ? 'Editar Video' : 'Agregar Video' }}
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': processing }">
              <FieldText id="video_title" label="Título" v-model="form.title" placeholder="Título del video" />
              <FieldText id="video_description" label="Descripción" v-model="form.description" placeholder="Descripción del video" />
              <FieldText id="video_description_short" label="Descripción corta" v-model="form.description_short" placeholder="Descripción corta" />
              <FieldText id="video_comments" label="Comentarios" v-model="form.comments" placeholder="Comentarios" />
              <FieldText id="video_url" label="URL del video" v-model="form.video_url" placeholder="https://..." />
              <FieldImage id="video_image_cover" label="Cover" v-model="form.image_cover" ref="imageCoverRef" />

              <div v-if="currentCover" class="mt-2">
                <label class="form-label text-muted">Cover actual:</label>
                <img :src="currentCover" alt="Cover actual" class="img-thumbnail" style="max-width: 100px;" />
              </div>
            </div>

            <SpinnerOverlay v-if="processing" />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">
              <i class="bi bi-x-lg me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success" :disabled="processing">
              <span v-if="processing" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-save me-1"></i> {{ editingId ? 'Actualizar Video' : 'Guardar Video' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

const props = defineProps({
  show: Boolean,
  courseId: Number,
  editingId: Number,
  videoData: Object
});

const emit = defineEmits(['close', 'saved']);

const form = ref({
  title: '',
  description: '',
  description_short: '',
  comments: '',
  video_url: '',
  image_cover: null
});

const currentCover = ref(null);
const imageCoverRef = ref(null);
const processing = ref(false);

const resetForm = () => {
  form.value = {
    title: '',
    description: '',
    description_short: '',
    comments: '',
    video_url: '',
    image_cover: null
  };
  currentCover.value = null;
};

const initForm = () => {
  resetForm();
  if (props.videoData) {
    form.value.title = props.videoData.title;
    form.value.description = props.videoData.description;
    form.value.description_short = props.videoData.description_short;
    form.value.comments = props.videoData.comments;
    form.value.video_url = props.videoData.video_url;
    currentCover.value = props.videoData.image_cover ? `/storage/${props.videoData.image_cover}` : null;
  }
};

const submit = async () => {
  processing.value = true;
  const formData = new FormData();
  formData.append('title', form.value.title);
  formData.append('description', form.value.description || '');
  formData.append('description_short', form.value.description_short || '');
  formData.append('comments', form.value.comments || '');
  formData.append('video_url', form.value.video_url);
  if (form.value.image_cover) {
    formData.append('image_cover', form.value.image_cover);
  }

  try {
    if (props.editingId) {
      await axios.post(
        route('admin.courses.videos.update', [props.courseId, props.editingId]),
        formData,
        { headers: { 'X-HTTP-Method-Override': 'PUT' } }
      );
    } else {
      await axios.post(route('admin.courses.videos.store', props.courseId), formData);
    }
    emit('saved');
    emit('close');
  } catch (err) {
    console.error(err);
  } finally {
    processing.value = false;
  }
};

// Inicializar el formulario al abrir el modal
watch(() => props.show, (newVal) => {
  if (newVal) initForm();
});
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
