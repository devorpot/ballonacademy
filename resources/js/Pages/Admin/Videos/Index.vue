<template>
  <Head :title="`Gestión de Videos en ${course.title}`" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Cursos', route: 'admin.courses.index' },
      
      { label: 'Videos', route: '' }
    ]" />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="`Gestionar Videos de ${course.title}`" />
           <Link
            :href="route('admin.courses.videos.create', course.id)"
            class="btn btn-sm btn-outline-info"
            title="Agregar Video"
          >
            <i class="bi bi-plus-circle"></i>
          </Link>
          </div>
        </div>
      </div>
    </section>

    <section class="section-filters my-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card mb-4">
              <div class="card-body">
                <div class="row justify-content-between align-items-center">
                  <div class="col-md-6 mb-3 mb-md-0">
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
                  <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark">
                      <i class="bi bi-film me-1"></i>
                      {{ filteredVideos.length }} video(s)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción corta</th>
                    <th>URL del video</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="video in paginatedVideos" :key="video.id">
                    <td>{{ video.id }}</td>
                    <td>{{ video.title }}</td>
                    <td>{{ video.description_short }}</td>
                    <td>{{ video.video_url }}</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <Link
                          :href="route('admin.courses.videos.edit', [course.id, video.id])"
                          class="btn btn-sm btn-outline-warning"
                          title="Editar"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button
                          class="btn btn-sm btn-outline-danger"
                          @click="prepareDelete(video.id)"
                          :disabled="isDeleting"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredVideos.length === 0">
                    <td colspan="5" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron videos
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section-pagination my-2">
      <div class="container-fluid">
        <div class="d-flex justify-content-center my-4">
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }" @click="changePage(currentPage - 1)">
                <a class="page-link" href="#">Anterior</a>
              </li>
              <li
                v-for="page in totalPages"
                :key="page"
                class="page-item"
                :class="{ active: currentPage === page }"
                @click="changePage(page)"
              >
                <a class="page-link" href="#">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }" @click="changePage(currentPage + 1)">
                <a class="page-link" href="#">Siguiente</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </section>

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteVideo"
      title="¿Deseas eliminar este video?"
      confirm-message="Por favor confirma la eliminación de este video"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Inertia } from '@inertiajs/inertia';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';

const props = defineProps({
  course: Object,
  videos: Array
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

const filteredVideos = computed(() => {
  if (!searchQuery.value) return props.videos;
  const query = searchQuery.value.toLowerCase();
  return props.videos.filter(v =>
    v.title.toLowerCase().includes(query) ||
    (v.description_short && v.description_short.toLowerCase().includes(query)) ||
    (v.video_url && v.video_url.toLowerCase().includes(query))
  );
});

const paginatedVideos = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredVideos.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredVideos.value.length / itemsPerPage.value)));

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const prepareDelete = (id) => {
  deletingId.value = id;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingId.value = null;
  isDeleting.value = false;
};

const deleteVideo = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;

  Inertia.delete(route('admin.videos.destroy', [props.course.id, deletingId.value]), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete();
    },
    onError: () => {
      isDeleting.value = false;
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};
</script>
