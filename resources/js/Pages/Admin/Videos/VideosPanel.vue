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


 
    <section class="section-data">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Cover</th>
                    <th>Título</th>
                    <th>Descripción corta</th>
                    <th>URL</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="video in course.videos" :key="video.id">
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
                    <td class="text-truncate" style="max-width: 250px;">{{ video.video_url }}</td>
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
                  <tr v-if="!course.videos.length">
                    <td colspan="5" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No hay videos asignados a este curso
                    </td>
                  </tr>
                </tbody>
              </table>
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
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';

const props = defineProps({
  course: Object
});

const showDeleteModal = ref(false);
const deletingId = ref(null);
const isDeleting = ref(false);
const toast = ref(null);

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
