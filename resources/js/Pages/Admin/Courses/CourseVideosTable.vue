<template>
  <section id="course-videos" class="section-videos my-4">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Videos del Curso</h5>
          <button class="btn btn-sm btn-success" @click="openCreateModal">
            <i class="bi bi-plus-lg me-1"></i> Agregar Video
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
                  <img v-if="video.image_cover" :src="`/storage/${video.image_cover}`" alt="Cover" class="img-thumbnail" style="max-width: 80px;" />
                  <span v-else class="text-muted">N/A</span>
                </td>
                <td>{{ video.title }}</td>
                <td>{{ video.description_short }}</td>
                <td>{{ video.video_url }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="openEditModal(video)">
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-outline-danger" @click="deleteVideo(video)">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal para Crear o Editar Video -->
    <div v-if="showModal" class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Editar Video' : 'Agregar Video' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <FieldText id="video_title" label="Título" v-model="videoForm.title" placeholder="Título del video" />
            <FieldText id="video_description" label="Descripción" v-model="videoForm.description" placeholder="Descripción del video" />
            <FieldText id="video_description_short" label="Descripción corta" v-model="videoForm.description_short" placeholder="Descripción corta" />
            <FieldText id="video_comments" label="Comentarios" v-model="videoForm.comments" placeholder="Comentarios" />
            <FieldText id="video_url" label="URL del video" v-model="videoForm.video_url" placeholder="https://..." />
            <FieldImage id="video_image_cover" label="Cover" v-model="videoForm.image_cover" ref="imageCoverRef" />

            <div v-if="currentCover" class="mt-2">
              <label class="form-label text-muted">Cover actual:</label>
              <img :src="currentCover" alt="Cover actual" class="img-thumbnail" style="max-width: 100px;" />
              <button class="btn btn-sm btn-outline-danger mt-1" @click="deleteCurrentCover">
                <i class="bi bi-trash"></i> Eliminar cover actual
              </button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cerrar</button>
            <button class="btn btn-success" @click="saveVideo">
              <i v-if="videoForm.processing" class="bi bi-spinner"></i>
              <span v-else>{{ editingId ? 'Actualizar Video' : 'Guardar Video' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { route } from 'ziggy-js';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

const props = defineProps({
  courseId: Number,
  videos: Array
});

const showModal = ref(false);
const editingId = ref(null);
const currentCover = ref(null);
const imageCoverRef = ref(null);

const videoForm = useForm({
  title: '',
  description: '',
  description_short: '',
  comments: '',
  image_cover: null,
  video_url: ''
});

const openCreateModal = () => {
  resetForm();
  showModal.value = true;
};

const openEditModal = (video) => {
  editingId.value = video.id;
  videoForm.title = video.title;  // Asegúrate de que estos valores se estén asignando
  videoForm.description = video.description;
  videoForm.description_short = video.description_short;
  videoForm.comments = video.comments;
  videoForm.video_url = video.video_url;
  videoForm.image_cover = null;
  currentCover.value = video.image_cover ? `/storage/${video.image_cover}` : null;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const resetForm = () => {
  videoForm.reset();
  editingId.value = null;
  currentCover.value = null;
  if (imageCoverRef.value?.reset) {
    imageCoverRef.value.reset();
  }
};

const saveVideo = () => {
  const url = editingId.value
    ? route('admin.courses.videos.update', [props.courseId, editingId.value])
    : route('admin.courses.videos.store', props.courseId);

  const method = editingId.value ? 'put' : 'post';

  // Verifica los valores antes de enviar
  console.log(videoForm);

  videoForm[method](url, {
    forceFormData: true,  // Asegúrate de enviar datos correctamente, incluyendo archivos
    preserveScroll: true,
    onSuccess: () => {
      resetForm();
      Inertia.reload({ only: ['videos'] });
      closeModal();
    },
    onError: (errors) => {
      console.log(errors); // Verifica los errores en la consola
    }
  });
};


const deleteVideo = (video) => {
  if (confirm(`¿Deseas eliminar el video: ${video.title}?`)) {
    Inertia.delete(route('admin.courses.videos.destroy', [props.courseId, video.id]), {
      preserveScroll: true,
    });
  }
};

const deleteCurrentCover = () => {
  if (!editingId.value) return;
  if (confirm('¿Deseas eliminar el cover actual?')) {
    Inertia.delete(route('admin.courses.videos.delete-cover', [props.courseId, editingId.value]), {
      preserveScroll: true,
      onSuccess: () => {
        currentCover.value = null;
      }
    });
  }
};
</script>
