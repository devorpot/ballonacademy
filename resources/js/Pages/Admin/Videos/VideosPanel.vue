<template>
  <Head :title="`Videos del Curso: ${course.title}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Cursos', route: 'admin.courses.index' },
        { label: `Videos - ${course.title}`, route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid d-flex justify-content-between align-items-center mb-3">
        <h4 class="admin-title">
          <i class="bi bi-play-circle me-2"></i> Videos del Curso
        </h4>
        <Link
          :href="route('admin.videos.create', { course_id: course.id })"
          class="btn btn-primary"
        >
          <i class="bi bi-plus-circle me-1"></i> Agregar Video
        </Link>
      </div>
    </section>

    <!-- CAMPO DE BÚSQUEDA Y BADGE -->
    <section class="section-filters my-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input
                type="text"
                class="form-control"
                placeholder="Buscar videos..."
                v-model="searchQuery"
              />
            </div>
          </div>
          <div class="col-12 col-md-6 text-end mt-2 mt-md-0">
            <span class="badge bg-light text-dark">
              <i class="bi bi-film me-1"></i>
              {{ filteredVideos.length }} video(s)
            </span>
          </div>
        </div>
      </div>
    </section>

    <section class="section-data">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 40px;"></th>
                    <th>Order</th>
                    <th>Cover</th>
                    <th>Título</th>
                    <th>Descripción corta</th>
                     
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <!-- DRAGGABLE SOLO SI NO HAY BÚSQUEDA -->
                <draggable
                  v-if="!searchQuery"
                  v-model="videoList"
                  tag="tbody"
                  :animation="200"
                  handle=".drag-handle"
                  item-key="id"
                  @end="onDragEnd"
                >
                  <template #item="{ element: video }">
                    <tr>
                      <td>
                        <i class="bi bi-arrows-move drag-handle" style="cursor: grab;" title="Arrastrar para reordenar"></i>
                      </td>
                      <td>
                        {{ video.order }}
                      </td>
                      <td>
                        <img
                          :src="video.image_cover ? `/storage/${video.image_cover}` : 'https://placehold.co/120x70?text=Sin+imagen'"
                          class="img-thumbnail"
                          style="max-height: 60px; max-width: 100px;"
                          :alt="video.title"
                        />
                      </td>
                      <td>{{ video.title }}</td>
                      <td>{{ video.description_short }}</td>
                      
                      <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                          <Link
                            :href="route('admin.videos.show', video.id)"
                            class="btn btn-outline-primary"
                            title="Ver"
                          >
                            <i class="bi bi-eye-fill"></i>
                          </Link>
                          <Link
                            :href="route('admin.videos.edit', video.id)"
                            class="btn btn-outline-warning"
                            title="Editar"
                          >
                            <i class="bi bi-pencil-fill"></i>
                          </Link>
                          <button
                            class="btn btn-outline-danger"
                            @click="prepareDelete(video.id)"
                            title="Eliminar"
                          >
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </draggable>
                <!-- TABLA FILTRADA SIN DRAG SI HAY BÚSQUEDA -->
                <tbody v-else>
                  <tr v-for="video in filteredVideos" :key="video.id">
                    <td></td>
                    <td>{{ video.order }}</td>
                    <td>
                      <img
                        :src="video.image_cover ? `/storage/${video.image_cover}` : 'https://placehold.co/120x70?text=Sin+imagen'"
                        class="img-thumbnail"
                        style="max-height: 60px; max-width: 100px;"
                        :alt="video.title"
                      />
                    </td>

                    <td>{{ video.title }}</td>
                    <td>{{ video.description_short }}</td>
                    
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link
                          :href="route('admin.videos.show', video.id)"
                          class="btn btn-outline-primary"
                          title="Ver"
                        >
                          <i class="bi bi-eye-fill"></i>
                        </Link>
                        <Link
                          :href="route('admin.videos.edit', video.id)"
                          class="btn btn-outline-warning"
                          title="Editar"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button
                          class="btn btn-outline-danger"
                          @click="prepareDelete(video.id)"
                          title="Eliminar"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!filteredVideos.length">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No hay videos que coincidan con la búsqueda
                    </td>
                  </tr>
                </tbody>
              </table>
              <!-- Aviso para el usuario -->
              <div v-if="searchQuery" class="alert alert-info mt-2 mb-0 py-2 small">
                Para reordenar, limpia la búsqueda.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteVideo"
      title="¿Eliminar este video?"
      confirm-message="Esta acción no se puede deshacer"
      cancel-text="Cancelar"
      confirm-text="Eliminar"
    />

    <ToastNotification
      v-if="toast"
      :message="toast.message"
      :type="toast.type"
      @hidden="toast = null"
    />
  </AdminLayout>
</template>


<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { route } from 'ziggy-js';
import draggable from 'vuedraggable';
import axios from 'axios';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';

const props = defineProps({
  course: Object
});

// Buscador
const searchQuery = ref('');

// Lista reactiva base (SIEMPRE ordenada por 'order' al inicio)
const videoList = ref([...props.course.videos].sort((a, b) => a.order - b.order));

// Filtro reactivo
const filteredVideos = computed(() => {
  if (!searchQuery.value) return videoList.value;
  const query = searchQuery.value.toLowerCase();
  return videoList.value.filter(v =>
    (v.title || '').toLowerCase().includes(query) ||
    (v.description_short || '').toLowerCase().includes(query) ||
    (v.video_url || '').toLowerCase().includes(query)
  );
});

// Modal y estado de eliminación
const showDeleteModal = ref(false);
const deletingId = ref(null);
const isDeleting = ref(false);
const toast = ref(null);

// Drag & Drop: actualiza el valor de 'order' en la UI y backend
const onDragEnd = async () => {
  // Refleja visualmente el nuevo orden (1, 2, 3...)
  videoList.value.forEach((video, idx) => {
    video.order = idx + 1;
  });

  // Envía el nuevo orden al backend
  const order = videoList.value.map(v => v.id);
  try {
    await axios.post(route('admin.courses.videos.reorder', { course: props.course.id }), { order });
    toast.value = { message: 'Orden de videos actualizado', type: 'success' };
  } catch (e) {
    console.error('Error al actualizar orden:', e, e?.response?.data);
    toast.value = {
      message: `No se pudo actualizar el orden. ${e?.response?.data?.message || ''}`,
      type: 'danger'
    };
  }
};

// Eliminar video
const prepareDelete = (id) => {
  deletingId.value = id;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingId.value = null;
};

const deleteVideo = () => {
  if (!deletingId.value) return;

  isDeleting.value = true;

  Inertia.delete(route('admin.videos.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      // Elimina el video de la lista reactiva (para no recargar la página)
      videoList.value = videoList.value.filter(v => v.id !== deletingId.value);
      toast.value = { message: 'Video eliminado correctamente', type: 'success' };
      cancelDelete();
    },
    onError: () => {
      toast.value = { message: 'Error al eliminar el video', type: 'danger' };
      isDeleting.value = false;
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};
</script>
