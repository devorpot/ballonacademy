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
            <Link :href="route('admin.students.create')" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Estudiante
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
                        placeholder="Buscar estudiantes..."
                        v-model="searchQuery"
                      />
                    </div>
                  </div>
                  <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark">
                      <i class="bi bi-people-fill me-1"></i>
                      {{ filteredStudents.length }} estudiante(s)
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
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Matrícula</th>
                    <th>Teléfono</th>
                    <th>País</th>
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
                      <div class="btn-group">
                        <Link
                          :href="route('admin.students.edit', student.id)"
                          class="btn btn-sm btn-outline-warning"
                          title="Editar"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button
                          class="btn btn-sm btn-outline-danger"
                          @click="prepareDelete(student.id)"
                          :disabled="isDeleting"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredStudents.length === 0">
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
      @confirm="deleteStudent"
      title="¿Deseas eliminar este estudiante?"
      confirm-message="Por favor confirma la eliminación de este estudiante"
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
  students: Array
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

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

const paginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredStudents.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredStudents.value.length / itemsPerPage.value));

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
