<script setup>
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';


import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';
// import CreateEvaluationModal from '@/Pages/Admin/Evaluations/CreateEvaluationModal.vue';
// import ShowEvaluationModal from '@/Pages/Admin/Evaluations/ShowEvaluationModal.vue';

const showViewModal = ref(false);
const selectedEvaluation = ref(null);

const openViewModal = (evaluation) => {
  selectedEvaluation.value = evaluation;
  showViewModal.value = true;
};

const props = defineProps({
  evaluations: Array,
  filters: Object,
  users: Array,
  courses: Array
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const showCreateModal = ref(false);
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
  toast.value = { message: 'Evaluación creada exitosamente', type: 'success' };
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

const filteredEvaluations = computed(() => {
  if (!searchQuery.value) return props.evaluations;
  const query = searchQuery.value.toLowerCase();
  return props.evaluations.filter(e =>
    e.user?.name?.toLowerCase().includes(query) ||
    e.course?.title?.toLowerCase().includes(query) ||
    (e.eva_comments || '').toLowerCase().includes(query)
  );
});

const sortedEvaluations = computed(() => {
  let data = [...filteredEvaluations.value];
  data.sort((a, b) => {
    let aVal, bVal;
    if (sortKey.value === 'user') {
      aVal = a.user?.name?.toLowerCase() || '';
      bVal = b.user?.name?.toLowerCase() || '';
    } else if (sortKey.value === 'course') {
      aVal = a.course?.title?.toLowerCase() || '';
      bVal = b.course?.title?.toLowerCase() || '';
    } else {
      aVal = a[sortKey.value];
      bVal = b[sortKey.value];
    }

    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
  return data;
});

const paginatedEvaluations = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedEvaluations.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(sortedEvaluations.value.length / itemsPerPage.value));

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

const deleteEvaluation = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  Inertia.delete(route('admin.evaluations.destroy', deletingId.value), {
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
</script>

<template>
  <Head title="Gestión de Evaluaciones" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Evaluaciones', route: '' }
    ]" />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title"><i class="bi bi-clipboard2-data me-2"></i> &nbsp; Gestionar Evaluaciones</h4>
            <Link class="btn btn-primary" :href="route('admin.evaluations.create')">
              <i class="bi bi-plus-lg me-1"></i> Nueva Evaluación
            </Link>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters v-model:searchQuery="searchQuery" :count="sortedEvaluations.length" placeholder="Buscar evaluaciones..." item-label="evaluación(es)" />

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('id')" style="cursor: pointer;">
                      ID
                      <i :class="sortKey === 'id' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    
                    <th @click="sortBy('course')" style="cursor: pointer;">
                      Curso
                      <i :class="sortKey === 'course' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                     <th >
                      Titulo
                     
                    </th>
                    <th @click="sortBy('eva_send_date')" style="cursor: pointer;">
                      Fecha Envío
                      <i :class="sortKey === 'eva_send_date' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th>
                      Tipo
                    </th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="evaluation in paginatedEvaluations" :key="evaluation.id">
                    <td>{{ evaluation.id }}</td>
                   
                    <td>{{ evaluation.course?.title }}</td>
                    <td>{{ evaluation.title }}</td>
                    <td>{{ evaluation.eva_send_date }}</td>
                    <td>
                      {{ evaluation.type_label || evaluation.type_name || evaluation.type }}


                    </td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link :href="route('admin.evaluations.edit', evaluation.id)" class="btn btn-outline-warning" title="Editar">
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <Link class="btn btn-outline-info" :href="route('admin.evaluations.show', evaluation.id)" title="Ver">
                          <i class="bi bi-eye-fill"></i>
                        </Link>

                         <Link class="btn btn-outline-info" 
                             :href="route('admin.evaluation-users.course.index', evaluation.course.id)"
                         title="Enviados">
                          <i class="bi bi-eye-fill"></i> Envios
                        </Link>
                          <Link
                              v-if="evaluation?.eva_type == 1"
                               :href="route('admin.evaluation-questions.index', evaluation.id)"
                              class="btn btn-outline-info btn-sm"
                              title="Preguntas de evaluación"
                            >
                              <i class="bi bi-question-circle"></i>
                            </Link>

                           
                        <button class="btn btn-outline-danger" @click="prepareDelete(evaluation.id)" :disabled="isDeleting" title="Eliminar">
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="sortedEvaluations.length === 0">
                    <td colspan="6" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron evaluaciones
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

    <ConfirmDeleteModal :show="showDeleteModal" :loading="isDeleting" @close="cancelDelete" @confirm="deleteEvaluation"
      title="¿Deseas eliminar esta evaluación?"
      confirm-message="Por favor confirma la eliminación de esta evaluación"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />

    <!--
    <CreateEvaluationModal v-if="showCreateModal"
      :show="showCreateModal"
      :users="props.users"
      :courses="props.courses"
      @saved="onCreated"
      @close="showCreateModal = false"
    />

    <ShowEvaluationModal
      v-if="showViewModal"
      :show="showViewModal"
      :evaluation="selectedEvaluation"
      @close="showViewModal = false"
    />
    -->
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
