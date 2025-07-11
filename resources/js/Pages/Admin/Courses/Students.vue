<template>
  <Head :title="`Alumnos de ${course.title}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Cursos', route: 'admin.courses.index' },
        { label: `Alumnos - ${course.title}`, route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title">
              <i class="bi bi-journal-text me-2"></i> &nbsp; Gestionar Alumnos
            </h4>
            
            <!--<button class="btn btn-sm btn-primary" @click="showAddModal = true">
              <i class="bi bi-person-plus-fill me-1"></i> Agregar alumno
            </button>    -->
          </div> 

        </div>
      </div>
    </section>

    <CrudFilters
      v-model:searchQuery="search"
      :count="visibleStudents.length"
      placeholder="Buscar por nombre o email..."
      item-label="alumno(s)"
    />

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('name')" style="cursor:pointer">
                      Nombre
                      <i :class="sortIcon('name')"></i>
                    </th>
                    <th @click="sortBy('email')" style="cursor:pointer">
                      Email
                      <i :class="sortIcon('email')"></i>
                    </th>
                    <th>Teléfono</th>
                    <th>Talla</th>
                    <th>País</th>
                    <th class="text-end"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in visibleStudents" :key="student.id">
                    <td>{{ student.name }}</td>
                    <td>{{ student.email }}</td>
                    <td>{{ student.phone }}</td>
                    <td>{{ student.shirt_size }}</td>
                    <td>{{ student.country }}</td>
                    <td class="text-end">
                        <button class="btn btn-outline-primary" @click="viewStudent(student)">
                          <i class="bi bi-eye-fill"></i>
                        </button>
                       
                    </td>
                  </tr>
                  <tr v-if="visibleStudents.length === 0">
                    <td colspan="6" class="text-center text-muted py-4">No se encontraron alumnos.</td>
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
      @confirm="deleteStudent"
      title="¿Deseas quitar este alumno del curso?"
      confirm-message="Por favor confirma que deseas quitar este alumno"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, quitar"
    />

    <!-- Modal para agregar alumnos -->
    <div v-if="showAddModal" class="modal fade show d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="z-index: 9999 !important;">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-person-plus me-2"></i> Agregar alumno a {{ course.title }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input v-model="searchModal" class="form-control mb-3" placeholder="Buscar alumno por nombre o email..." />
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in filteredCandidates" :key="student.id">
                    <td>{{ student.name }}</td>
                    <td>{{ student.email }}</td>
                    <td>
                      <button class="btn btn-sm btn-success" @click="assignStudent(student.id)">
                        <i class="bi bi-plus-circle me-1"></i> Agregar
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredCandidates.length === 0">
                    <td colspan="3" class="text-center text-muted py-3">No se encontraron coincidencias.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">
              <i class="bi bi-x-lg me-1"></i> Cerrar
            </button>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show" @click="closeModal" />
    </div>

    <!-- Modal de vista de detalles del alumno -->
<div v-if="showViewModal" class="modal fade show d-block" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="z-index: 9999 !important;">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-person-circle me-2"></i> Detalles del Alumno
        </h5>
        <button type="button" class="btn-close" @click="closeViewModal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div v-if="selectedStudent">
          <p><strong>Nombre:</strong> {{ selectedStudent.name }}</p>
          <p><strong>Email:</strong> {{ selectedStudent.email }}</p>
          <p><strong>Teléfono:</strong> {{ selectedStudent.phone }}</p>
          <p><strong>Talla de camiseta:</strong> {{ selectedStudent.shirt_size }}</p>
          <p><strong>País:</strong> {{ selectedStudent.country }}</p>
          <p><strong>Dirección:</strong> {{ selectedStudent.address }}</p>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="closeViewModal">
          <i class="bi bi-x-lg me-1"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade show" @click="closeViewModal" />
</div>

  </AdminLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';

const props = defineProps({
  course: Object,
  students: Array
});

const search = ref('');
const searchModal = ref('');
const showAddModal = ref(false);
const allStudents = ref([]);
const sortKey = ref('name');
const sortOrder = ref('asc');

const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

const closeModal = () => {
  showAddModal.value = false;
  searchModal.value = '';
};

const showViewModal = ref(false);
const selectedStudent = ref(null);

const viewStudent = (student) => {
  selectedStudent.value = student;
  showViewModal.value = true;
};

const closeViewModal = () => {
  showViewModal.value = false;
  selectedStudent.value = null;
};


onMounted(async () => {
  try {
    const { data } = await axios.get(route('admin.students.list'));
    allStudents.value = data;
  } catch (error) {
    console.error('Error cargando lista de alumnos:', error);
  }
});

const visibleStudents = computed(() => {
  const q = search.value.toLowerCase();
  let filtered = props.students.filter(s =>
    s.name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q)
  );
  filtered.sort((a, b) => {
    let aVal = a[sortKey.value]?.toLowerCase() || '';
    let bVal = b[sortKey.value]?.toLowerCase() || '';
    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
  return filtered;
});

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

const filteredCandidates = computed(() => {
  const query = searchModal.value.toLowerCase();
  const assignedIds = props.students.map(s => s.id);
  return allStudents.value
    .filter(s => !assignedIds.includes(s.id))
    .filter(s => s.name.toLowerCase().includes(query) || s.email.toLowerCase().includes(query));
});

const assignStudent = async (studentId) => {
  try {
    await axios.post(route('admin.courses.assign-students', props.course.id), {
      students: [...props.students.map(s => s.id), studentId]
    });
    router.reload({ only: ['students'] });
  } catch (error) {
    console.error('Error asignando alumno:', error);
  }
};

const confirmDelete = (studentId) => {
  deletingId.value = studentId;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingId.value = null;
};

const deleteStudent = async () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  try {
    const remaining = props.students.map(s => s.id).filter(id => id !== deletingId.value);
    await axios.post(route('admin.courses.assign-students', props.course.id), {
      students: remaining
    });
    router.reload({ only: ['students'] });
  } catch (error) {
    console.error('Error quitando alumno:', error);
  } finally {
    cancelDelete();
    isDeleting.value = false;
  }
};
</script>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td,
.table th {
  vertical-align: middle;
}
</style>
