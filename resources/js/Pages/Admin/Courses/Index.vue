<template>
  <Head title="Gestión de Cursos" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Cursos', route: '' }
    ]" />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Gestionar Cursos'" />
            <Link :href="route('admin.courses.create')" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Curso
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
                        placeholder="Buscar cursos..."
                        v-model="searchQuery"
                      />
                    </div>
                  </div>
                  <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark">
                      <i class="bi bi-journal-text me-1"></i>
                      {{ filteredCourses.length }} curso(s)
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
                    <th>Nivel</th>
                    <th>Descripción corta</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="course in paginatedCourses" :key="course.id">
                    <td>{{ course.id }}</td>
                    <td>{{ course.title }}</td>
                    <td>{{ course.level }}</td>
                    <td>{{ course.description_short }}</td>
                    <td class="text-end pe-4">
                      <div class="btn-group">
                        <Link
                          :href="route('admin.courses.videos.index', course.id)"
                          class="btn btn-sm btn-outline-info"
                          title="Ver Videos"
                        >
                          <i class="bi bi-collection-play-fill"></i>
                        </Link>
                        <Link
                          :href="route('admin.courses.edit', course.id)"
                          class="btn btn-sm btn-outline-warning"
                          title="Editar"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button
                          class="btn btn-sm btn-outline-danger"
                          @click="prepareDelete(course.id)"
                          :disabled="isDeleting"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>

                  </tr>
                  <tr v-if="filteredCourses.length === 0">
                    <td colspan="5" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron cursos
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
      @confirm="deleteCourse"
      title="¿Deseas eliminar este curso?"
      confirm-message="Por favor confirma la eliminación de este curso"
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
  courses: Array
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

const filteredCourses = computed(() => {
  if (!searchQuery.value) return props.courses;
  const query = searchQuery.value.toLowerCase();
  return props.courses.filter(c =>
    c.title.toLowerCase().includes(query) ||
    (c.level && c.level.toLowerCase().includes(query)) ||
    (c.description_short && c.description_short.toLowerCase().includes(query))
  );
});

const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredCourses.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredCourses.value.length / itemsPerPage.value));

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

const deleteCourse = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;

  Inertia.delete(route('admin.courses.destroy', deletingId.value), {
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
