<script setup>
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';
import ShowStudentModal from '@/Pages/Admin/Students/ShowStudentModal.vue';
import CreateStudentModal from '@/Pages/Admin/Students/CreateStudentModal.vue';

const props = defineProps({
  students: Array
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const sortKey = ref('id');
const sortOrder = ref('asc');

const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const showCreateModal = ref(false);
const showViewModal = ref(false);
const selectedStudent = ref(null);
const toast = ref(null);

const page = usePage();

onMounted(() => {
  if (page.props.flash.success) {
    toast.value = { message: page.props.flash.success, type: 'success' };
  }
  if (page.props.flash.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' };
  }
});

const openViewModal = (student) => {
  selectedStudent.value = student;
  showViewModal.value = true;
};

const onCreated = () => {
  toast.value = { message: 'Estudiante creado exitosamente', type: 'success' };
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

const sortIcon = (key) => {
  if (sortKey.value !== key) return 'bi bi-arrow-down-up';
  return sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down';
};

const filteredStudents = computed(() => {
  if (!searchQuery.value) return props.students;
  const query = searchQuery.value.toLowerCase();
  return props.students.filter(s =>
    s.firstname.toLowerCase().includes(query) ||
    s.lastname.toLowerCase().includes(query) ||
    s.user.email.toLowerCase().includes(query) ||
    s.student_id.toLowerCase().includes(query) ||
    s.phone.toLowerCase().includes(query) ||
    s.country.toLowerCase().includes(query)
  );
});

const sortedStudents = computed(() => {
  let data = [...filteredStudents.value];
  data.sort((a, b) => {
    let aVal, bVal;
    switch (sortKey.value) {
      case 'name':
        aVal = `${a.firstname} ${a.lastname}`.toLowerCase();
        bVal = `${b.firstname} ${b.lastname}`.toLowerCase();
        break;
      case 'email':
        aVal = a.user.email.toLowerCase();
        bVal = b.user.email.toLowerCase();
        break;
      default:
        aVal = (a[sortKey.value] || '').toString().toLowerCase();
        bVal = (b[sortKey.value] || '').toString().toLowerCase();
    }
    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
  return data;
});

const paginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedStudents.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(sortedStudents.value.length / itemsPerPage.value));

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

const deleteStudent = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  Inertia.delete(route('admin.students.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete();
      toast.value = { message: 'Estudiante eliminado exitosamente', type: 'success' };
    },
    onError: () => {
      isDeleting.value = false;
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' };
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};
</script>

<template>
  <Head title="Gestión de Estudiantes" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Estudiantes', route: '' }
    ]" />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Gestionar Estudiantes'" />
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Estudiante
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="section-filters my-2">
      <div class="container-fluid">
        <CrudFilters v-model:searchQuery="searchQuery" :count="sortedStudents.length" placeholder="Buscar estudiantes..." item-label="estudiante(s)" />
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
                    <th @click="sortBy('id')" style="cursor: pointer;">ID <i :class="sortIcon('id')"></i></th>
                    <th @click="sortBy('name')" style="cursor: pointer;">Nombre <i :class="sortIcon('name')"></i></th>
                    <th @click="sortBy('email')" style="cursor: pointer;">Email <i :class="sortIcon('email')"></i></th>
                    <th @click="sortBy('student_id')" style="cursor: pointer;">Matrícula <i :class="sortIcon('student_id')"></i></th>
                    <th @click="sortBy('phone')" style="cursor: pointer;">Teléfono <i :class="sortIcon('phone')"></i></th>
                    <th @click="sortBy('country')" style="cursor: pointer;">País <i :class="sortIcon('country')"></i></th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in paginatedStudents" :key="student.id">
                    <td>{{ student.id }}</td>
                    <td>{{ student.firstname }} {{ student.lastname }}</td>
                    <td>{{ student.user.email }}</td>
                    <td>{{ student.student_id }}</td>
                    <td>{{ student.phone }}</td>
                    <td>{{ student.country }}</td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" @click="openViewModal(student)" title="Ver">
                          <i class="bi bi-eye-fill"></i>
                        </button>
                        <Link :href="route('admin.students.edit', student.id)" class="btn btn-outline-warning" title="Editar">
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button class="btn btn-outline-danger" @click="prepareDelete(student.id)" :disabled="isDeleting" title="Eliminar">
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="sortedStudents.length === 0">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron estudiantes
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
      @confirm="deleteStudent"
      title="¿Deseas eliminar este estudiante?"
      confirm-message="Por favor confirma la eliminación de este estudiante"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />

    <CreateStudentModal v-if="showCreateModal"
      :show="showCreateModal"
      @saved="onCreated"
      @close="showCreateModal = false"
    />

    <ShowStudentModal v-if="showViewModal"
      :show="showViewModal"
      :student="selectedStudent"
      @close="showViewModal = false"
    />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td, .table th {
  vertical-align: middle;
}
</style>
