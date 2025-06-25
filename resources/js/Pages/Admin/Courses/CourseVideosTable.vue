<template>
  <section class="section-videos my-4">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Videos del Curso</h5>
          <button class="btn btn-sm btn-success" :disabled="loading" @click="addVideo">
            <i class="bi bi-plus-lg me-1"></i> Agregar Video
          </button>
        </div>

        <div class="card-body p-0">
          <table class="table table-sm table-striped mb-0">
            <thead>
              <tr>
                <th>Cover</th>
                <th>Título</th>
                <th>Descripción corta</th>
                <th>URL</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <draggable
              tag="tbody"
              v-model="videos"
              item-key="id"
              @end="updateOrder"
              ghost-class="drag-ghost"
              chosen-class="drag-chosen"
            >
              <template #item="{ element }">
                <tr>
                  <td>
                    <img
                      v-if="element.image_cover"
                      :src="`/storage/${element.image_cover}`"
                      class="img-thumbnail"
                      style="max-width: 80px;"
                    />
                    <span v-else class="text-muted">N/A</span>
                  </td>
                  <td>{{ element.title }}</td>
                  <td>{{ element.description_short }}</td>
                  <td>{{ element.video_url }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button
                        class="btn btn-outline-primary"
                        @click="editVideo(element)"
                        :disabled="loading"
                      >
                        <i class="bi bi-pencil-fill"></i>
                      </button>
                      <button
                        class="btn btn-outline-danger"
                        @click="deleteVideo(element)"
                        :disabled="loading"
                      >
                        <span v-if="deletingId === element.id" class="spinner-border spinner-border-sm"></span>
                        <i v-else class="bi bi-trash-fill"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </draggable>
          </table>

          <p v-if="!loading && videos.length === 0" class="text-center text-muted my-3">
            No hay videos aún. Usa el botón "Agregar Video" para comenzar.
          </p>
        </div>
      </div>
    </div>
<pre>{{ teachers }}</pre>
    <CourseVideoAddModal
      :show="showVideoModal"
      :courseId="courseId"
      :editingId="editingId"
      :videoData="currentVideo"
      @close="showVideoModal = false"
        :teachers="teachers"
      @saved="loadVideos"
    />

    <SpinnerOverlay v-if="loading" />
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import draggable from 'vuedraggable';

import CourseVideoAddModal from '@/Pages/Admin/Courses/CourseVideoAddModal.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  courseId: Number
});

const showToast = useToast();

const videos = ref([]);
const loading = ref(false);
const deletingId = ref(null);

const showVideoModal = ref(false);
const editingId = ref(null);
const currentVideo = ref(null);

const teachers = ref([]);



const loadTeachers = async () => {
  const { data } = await axios.get(route('admin.teachers.list'));
  console.log('Maestros cargados:', data); 
  teachers.value = data;
};

const loadVideos = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get(route('admin.courses.videos.index', props.courseId));
    videos.value = data;
  } catch (err) {
    showToast('Error al cargar los videos', 'error');
  } finally {
    loading.value = false;
  }
};

const addVideo = () => {
  editingId.value = null;
  currentVideo.value = null;
  loadTeachers();   
  showVideoModal.value = true;
};

const editVideo = (video) => {
  editingId.value = video.id;
  currentVideo.value = video;
  loadTeachers();  
  showVideoModal.value = true;
};

const deleteVideo = async (video) => {
  if (!confirm(`¿Eliminar video: ${video.title}?`)) return;
  deletingId.value = video.id;
  try {
    await axios.delete(route('admin.courses.videos.destroy', [props.courseId, video.id]));
    await loadVideos();
    showToast('Video eliminado', 'success');
  } catch (err) {
    showToast('Error al eliminar el video', 'error');
  } finally {
    deletingId.value = null;
  }
};

const updateOrder = async () => {
  try {
    await axios.post(route('admin.courses.videos.reorder', props.courseId), {
      order: videos.value.map(v => v.id)
    });
    showToast('Orden actualizado', 'success');
  } catch (err) {
    showToast('Error al actualizar el orden', 'error');
  }
};

onMounted(() => {
  loadVideos();
});
</script>

<style scoped>
.drag-ghost {
  opacity: 0.5;
}
.drag-chosen {
  background-color: #f0f0f0;
}
</style>
