<template>
  <section class="section-videos my-4">
    <div class="container-fluid">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 rounded-top-4">
          <h5 class="mb-0 fw-bold">
            <i class="bi bi-play-circle me-2"></i> Videos del Curso
          </h5>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover table-sm align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="text-center" style="width:32px"></th>
                <th>Orden</th>
                <th>Cover</th>
                <th>Título</th>
                <th>Descripción corta</th>
                <th class="text-end" style="width:120px;">Acciones</th>
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
                <tr class="reorder-row">
                  <td class="text-center reorder-handle" style="cursor: grab;">
                    <i class="bi bi-grip-vertical fs-4 text-muted"></i>
                  </td>
                  <td>{{ element.order }}</td>
                  <td>
                    <img
                      v-if="element.image_cover"
                      :src="`/storage/${element.image_cover}`"
                      class="img-thumbnail border-0 shadow-sm"
                      style="max-width: 80px; max-height: 60px; object-fit: cover;"
                    />
                    <span v-else class="text-muted small">N/A</span>
                  </td>
                  <td>{{ element.title }}</td>
                  <td>{{ element.description_short }}</td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <Link :href="route('admin.videos.edit', element.id)" class="btn btn-outline-warning" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                      </Link>
                      <button
                        class="btn btn-outline-danger"
                        @click="deleteVideo(element)"
                        :disabled="loading"
                        :title="deletingId === element.id ? 'Eliminando...' : 'Eliminar'"
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
            No hay videos aún.
          </p>
        </div>
      </div>
    </div>
    <SpinnerOverlay v-if="loading" />
  </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import draggable from 'vuedraggable';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  courseId: { type: [String, Number], required: true },
  videos: { type: Array, default: () => [] }
});

const showToast = useToast();
const videos = ref([]);
const loading = ref(false);
const deletingId = ref(null);

// Carga videos al montar el componente
const loadVideos = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get(route('admin.courses.videos.list', props.courseId));
    videos.value = data;
  } catch (err) {
    showToast('Error al cargar los videos', 'error');
  } finally {
    loading.value = false;
  }
};

// Elimina video
const deleteVideo = async (video) => {
  if (!confirm(`¿Eliminar video: ${video.title}?`)) return;
  deletingId.value = video.id;
  try {
    await axios.delete(route('admin.courses.videos.delete', [props.courseId, video.id]));
    await loadVideos();
    showToast('Video eliminado', 'success');
  } catch (err) {
    showToast('Error al eliminar el video', 'error');
  } finally {
    deletingId.value = null;
  }
};

// Actualiza el orden de los videos
const updateOrder = async () => {
  try {
    await axios.post(route('admin.courses.videos.reorder', props.courseId), {
      order: videos.value.map(v => v.id)
    });
    showToast('Orden actualizado', 'success');
    await loadVideos();
  } catch (err) {
    showToast('Error al actualizar el orden', 'error');
  }
};

onMounted(loadVideos);
</script>

<style scoped>
.drag-ghost {
  opacity: 0.6;
  background: #e2e6ea !important;
}
.drag-chosen {
  background: #e9f7ef !important;
  box-shadow: 0 2px 12px 0 rgba(34, 197, 94, .08);
}
.reorder-row td {
  transition: box-shadow .2s;
}
.reorder-handle:hover i {
  color: #198754 !important;
  transition: color .2s;
}
.table thead th {
  font-size: .93rem;
  letter-spacing: .02em;
}
.table tr {
  vertical-align: middle;
}
</style>
