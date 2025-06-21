<template>
  <section class="section-videos my-4">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Videos del Curso</h5>
          <button class="btn btn-sm btn-success" :disabled="loading" @click="toggleForm">
            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else-if="editingId" class="bi bi-save me-1"></i>
            <i v-else class="bi bi-plus-lg me-1"></i>
            {{ editingId ? 'Actualizar Video' : 'Agregar Video' }}
          </button>
        </div>

        <div class="card-body">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>Cover</th>
                <th>Título</th>
                <th>Descripción corta</th>
                <th>URL</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="video in videos" :key="video.id">
                <td>
                  <img v-if="video.image_cover" :src="`/storage/${video.image_cover}`" alt="Cover"
                    class="img-thumbnail" style="max-width: 80px;" />
                  <span v-else class="text-muted">N/A</span>
                </td>
                <td>{{ video.title }}</td>
                <td>{{ video.description_short }}</td>
                <td>{{ video.video_url }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="editVideo(video)">
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-outline-danger" :disabled="loading" @click="deleteVideo(video)">
                      <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                      <i v-else class="bi bi-trash-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Formulario de agregar/editar video con animación de aparición -->
          <div v-if="showForm" class="mt-3" v-show="showForm" @transitionend="onTransitionEnd">
            <FieldText id="video_title" label="Título" v-model="videoForm.title" placeholder="Título del video" />
            <FieldText id="video_description" label="Descripción" v-model="videoForm.description" placeholder="Descripción del video" />
            <FieldText id="video_description_short" label="Descripción corta" v-model="videoForm.description_short" placeholder="Descripción corta" />
            <FieldText id="video_comments" label="Comentarios" v-model="videoForm.comments" placeholder="Comentarios" />
            <FieldText id="video_url" label="URL del video" v-model="videoForm.video_url" placeholder="https://..." />
            <FieldImage 
              id="video_image_cover" 
              label="Cover" 
              v-model="videoForm.image_cover" 
              ref="imageCoverRef"
            />
            <div v-if="currentCover" class="mt-2">
              <label class="form-label text-muted">Cover actual:</label>
              <img :src="currentCover" alt="Cover actual" class="img-thumbnail" style="max-width: 100px;" />
            </div>
          </div>

          <div v-if="showForm" class="mt-4 text-end">
            <button class="btn btn-success" :disabled="loading" @click="saveVideo">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-save me-1"></i>
              {{ editingId ? 'Actualizar Video' : 'Guardar Video' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';

const props = defineProps({
  courseId: Number
});

const imageCoverRef = ref(null);

const videos = ref([]);
const loading = ref(false);
const editingId = ref(null);
const currentCover = ref(null);
const showForm = ref(false); // Para controlar la visibilidad del formulario

const videoForm = {
  title: '',
  description: '',
  description_short: '',
  comments: '',
  video_url: '',
  image_cover: null
};

const loadVideos = async () => {
  try {
    const { data } = await axios.get(route('admin.courses.videos.index', props.courseId));
    videos.value = data;
  } catch (err) {
    console.error('Error cargando videos', err);
  }
};

const resetForm = () => {
  videoForm.title = '';
  videoForm.description = '';
  videoForm.description_short = '';
  videoForm.comments = '';
  videoForm.video_url = '';
  videoForm.image_cover = null;
  editingId.value = null;
  currentCover.value = null;
};

const saveVideo = async () => {
  loading.value = true;
  const formData = new FormData();
  formData.append('title', videoForm.title);
  formData.append('description', videoForm.description || '');
  formData.append('description_short', videoForm.description_short || '');
  formData.append('comments', videoForm.comments || '');
  formData.append('video_url', videoForm.video_url);
  if (videoForm.image_cover) {
    formData.append('image_cover', videoForm.image_cover);
  }

  try {
    if (editingId.value) {
      await axios.post(route('admin.courses.videos.update', [props.courseId, editingId.value]), formData, {
        headers: { 'X-HTTP-Method-Override': 'PUT' }
      });
    } else {
      await axios.post(route('admin.courses.videos.store', props.courseId), formData);
    }
    await loadVideos();
    resetForm();
    showForm.value = false; // Ocultar el formulario después de guardar
  } catch (err) {
    console.error('Error guardando video', err);
  } finally {
    loading.value = false;
  }
};

const editVideo = (video) => {
  editingId.value = video.id;
  videoForm.title = video.title;
  videoForm.description = video.description;
  videoForm.description_short = video.description_short;
  videoForm.comments = video.comments;
  videoForm.video_url = video.video_url;
  currentCover.value = video.image_cover ? `/storage/${video.image_cover}` : null;
  videoForm.image_cover = null;
  showForm.value = true; // Mostrar el formulario cuando se edita un video
};

const deleteVideo = async (video) => {
  if (!confirm(`¿Eliminar video: ${video.title}?`)) return;

  loading.value = true;
  try {
    await axios.delete(route('admin.courses.videos.destroy', [props.courseId, video.id]));
    await loadVideos();
  } catch (err) {
    console.error('Error eliminando video', err);
  } finally {
    loading.value = false;
  }
};

// Función para alternar la visibilidad del formulario
const toggleForm = () => {
  showForm.value = !showForm.value;
};

onMounted(() => loadVideos());
</script>

<style scoped>
/* Animaciones para el formulario */
.transition-enter-active, .transition-leave-active {
  transition: opacity 0.3s ease;
}
.transition-enter, .transition-leave-to /* .transition-leave-active en <2.1.8 */ {
  opacity: 0;
}
</style>
