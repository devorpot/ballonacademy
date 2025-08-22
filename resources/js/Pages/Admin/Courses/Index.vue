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
            <h4 class="admin-title">
              <i class="bi bi-journal-text me-2"></i> &nbsp; Gestionar Cursos
            </h4>
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Curso
            </button>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters v-model:searchQuery="searchQuery" :count="sortedCourses.length" placeholder="Buscar cursos..." item-label="curso(s)" />

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
           <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th></th>
                    <th @click="sortBy('id')" style="cursor: pointer;">
                      ID
                      <i :class="sortKey === 'id' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('title')" style="cursor: pointer;">
                      Título
                      <i :class="sortKey === 'title' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('level')" style="cursor: pointer;">
                      Nivel
                      <i :class="sortKey === 'level' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th>Descripción corta</th>
                    <th>Activo</th>
                    <th @click="sortBy('videos_count')" style="cursor: pointer;">
                      Videos
                      <i :class="sortKey === 'videos_count' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="course in paginatedCourses" :key="course.id">
                    <td>
                        
                      <ImageThumb :src="`/storage/${course.image_cover}`" size="thumb" />
                     
                  </td>
                    <td>{{ course.id }}</td>
                    <td>{{ course.title }}</td>
                    <td>{{ course.level }}</td>
                    <td>{{ course.description_short }}</td>
                    <td>
                      <span class="badge" :class="course.active ? 'bg-success' : 'bg-secondary'">
                        {{ course.active ? 'Activo' : 'Inactivo' }}
                      </span>
                    </td>
                    <td>{{ course.videos_count }}</td>
                    <td class="text-end pe-4">
                      <div class="d-flex justify-content-end flex-wrap gap-1">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Acciones del curso">
                          <button class="btn btn-outline-info" @click="openViewModal(course)" title="Ver curso">
                            <i class="bi bi-eye-fill"></i>
                          </button>
                          <Link :href="route('admin.courses.edit', course.id)" class="btn btn-outline-warning" title="Editar curso">
                            <i class="bi bi-pencil-fill"></i>
                          </Link>
                          <button class="btn btn-outline-danger" @click="prepareDelete(course.id)" :disabled="isDeleting" title="Eliminar curso">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </div>

                        <div class="btn-group btn-group-sm" role="group" aria-label="Gestión de estudiantes">
                          <Link
                            class="btn btn-outline-secondary"
                            :href="route('admin.courses.students', course.id)"
                            title="Ver estudiantes asignados"
                          >
                            <i class="bi bi-people-fill"></i>
                          </Link>
                        </div>

                        <Link
                          :href="route('admin.courses.videos.panel', course.id)"
                          class="btn btn-outline-primary btn-sm"
                          title="Gestionar videos del curso"
                        >
                          <i class="bi bi-play-circle me-1"></i> Videos
                        </Link>

                        <Link
                          :href="route('admin.courses.lessons.panel', course.id)"
                          class="btn btn-outline-primary btn-sm"
                          title="Gestionar Lecciones del curso"
                        >
                          <i class="bi bi-play-circle me-1"></i> Lecciones
                        </Link>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="sortedCourses.length === 0">
                    <td colspan="7" class="text-center py-4 text-muted">
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

    <CrudPagination :current-page="currentPage" :total-pages="totalPages" @change-page="changePage" />

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

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />

    <ShowCourseModal
      v-if="showViewModal"
      :show="showViewModal"
      :course="selectedCourse"
      @close="showViewModal = false"
    />

    <CreateCourseModal
      v-if="showCreateModal"
      :show="showCreateModal"
      @saved="onCreated"
      @close="showCreateModal = false"
    />
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue';
import ImageThumb from '@/Components/Admin/Ui/ImageThumb.vue';


import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';

import ShowCourseModal from '@/Components/Admin/Courses/ShowCourseModal.vue';
import CreateCourseModal from '@/Components/Admin/Courses/CreateCourseModal.vue';




const props = defineProps({
  courses: Array
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const showCreateModal = ref(false);
const showViewModal = ref(false);
const selectedCourse = ref(null);
const toast = ref(null);

const sortKey = ref('id');
const sortOrder = ref('asc');

const page = usePage();

onMounted(() => {
  if (page.props.flash.success) {
    toast.value = { message: page.props.flash.success, type: 'success' };
  }
  if (page.props.flash.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' };
  }
});

const onCreated = () => {
  toast.value = { message: 'Curso creado exitosamente', type: 'success' };
  showCreateModal.value = false;
};

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const filteredCourses = computed(() => {
  if (!searchQuery.value) return props.courses;
  const query = searchQuery.value.toLowerCase();
  return props.courses.filter(c =>
    c.title.toLowerCase().includes(query) ||
    (c.level || '').toLowerCase().includes(query) ||
    (c.description_short || '').toLowerCase().includes(query)
  );
});

const sortedCourses = computed(() => {
  let data = [...filteredCourses.value];
  data.sort((a, b) => {
    let aVal = a[sortKey.value] || '';
    let bVal = b[sortKey.value] || '';

    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
    if (typeof bVal === 'string') bVal = bVal.toLowerCase();

    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
  return data;
});

const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedCourses.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(sortedCourses.value.length / itemsPerPage.value));

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
    onSuccess: cancelDelete,
    onError: () => {
      isDeleting.value = false;
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' };
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};

const openViewModal = (course) => {
  selectedCourse.value = course;
  showViewModal.value = true;
};
</script>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td, .table th {
  vertical-align: middle;
}
</style>