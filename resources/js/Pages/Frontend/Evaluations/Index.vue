<script setup>
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import StudentLayout from '@/Layouts/StudentLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';

const props = defineProps({
  evaluations: Array,
  filters: Object
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const sortKey = ref('id');
const sortOrder = ref('asc');

const deletingEvaluation = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
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
    (e.eva_comments || '').toLowerCase().includes(query)
  );
});

const sortedEvaluations = computed(() => {
  let data = [...filteredEvaluations.value];
  data.sort((a, b) => {
    let aVal = a[sortKey.value], bVal = b[sortKey.value];
    return sortOrder.value === 'asc'
      ? String(aVal).localeCompare(String(bVal))
      : String(bVal).localeCompare(String(aVal));
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

const prepareDelete = (evaluation) => {
  deletingEvaluation.value = evaluation;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingEvaluation.value = null;
  isDeleting.value = false;
};

const deleteEvaluation = () => {
  if (!deletingEvaluation.value) return;

  console.log(deletingEvaluation)

  const url = route('dashboard.courses.evaluations.destroy', {
    course: deletingEvaluation.value.course_id,
    evaluation: deletingEvaluation.value.id
  });

  console.log('URL generada para eliminar:', url); // Verifica si incluye los IDs correctamente

  isDeleting.value = true;

  Inertia.delete(url, {
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
  <Head title="Mis Evaluaciones" />
  <StudentLayout>


    {{ page.props.user }}
    <Breadcrumbs username="estudiante" :breadcrumbs="[
      { label: 'Dashboard', route: 'dashboard.index' },
      { label: 'Mis Evaluaciones', route: '' }
    ]" />

 

    <div class="text-end mb-3" v-if="page.props.canSubmitEvaluation">
  <Link :href="route('dashboard.courses.evaluations.create', { course: page.props.course.id })" class="btn btn-primary">
    <i class="bi bi-plus-circle me-2"></i>Agregar Evaluación
  </Link>
</div>


    <CrudFilters v-model:searchQuery="searchQuery" :count="sortedEvaluations.length" placeholder="Buscar por comentario..." item-label="evaluación(es)" />

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
                    <th>Curso</th>
                    <th>Enviado</th>
                     
                    <th>Archivo</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="eva in paginatedEvaluations" :key="eva.id">
                    <td>{{ eva.id }}</td>
                    <td>{{ eva.course.title }}</td>
                    <td>{{ eva.eva_send_date}} </td>
                     
                    <td>
                      <a :href="route('dashboard.courses.evaluations.download', [eva.course_id, eva.id])" target="_blank" class="btn btn-sm btn-outline-primary">
                        Descargar video
                      </a>
                    </td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-danger" @click="prepareDelete(eva)" :disabled="isDeleting" title="Eliminar">
                          <i class="bi bi-trash-fill"></i>
                        </button>
                        <Link
                            class="btn btn-outline-secondary"
                            :href="route('dashboard.courses.evaluations.edit', { course: eva.course_id, evaluation: eva.id })"
                            title="Editar"
                          >
                            <i class="bi bi-pencil-square"></i>
                          </Link>

                      </div>
                    </td>
                  </tr>
                  <tr v-if="sortedEvaluations.length === 0">
                    <td colspan="4" class="text-center py-4 text-muted">
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
      confirm-message="Esta acción eliminará el archivo subido por el estudiante"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </StudentLayout>
</template>
