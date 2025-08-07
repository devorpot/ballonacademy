<script setup>
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';
import draggable from 'vuedraggable';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';

const props = defineProps({
  evaluation: Object,
  questions: Array,
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const toast = ref(null);

const sortKey = ref('id');
const sortOrder = ref('asc');
const page = usePage();

const questions = ref([...props.questions]); // Para drag&drop
const isOrdering = ref(false);

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

const optionIndexes = (question) => {
  return [1,2,3,4,5].filter(i => question['option_' + i] && question['option_' + i].trim());
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

const deleteQuestion = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  useForm().delete(route('admin.evaluation-questions.destroy', [props.evaluation.id, deletingId.value]), {
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

// Guarda el orden automáticamente cuando termina el drag
const onDragEnd = () => {
  saveOrder();
};

const saveOrder = () => {
  isOrdering.value = true;
  const order = questions.value.map((q, idx) => ({
    id: q.id,
    order: idx + 1,
  }));
  useForm({ order }).post(route('admin.evaluation-questions.reorder', props.evaluation.id), {
    preserveScroll: true,
    forceFormData: false,
    onSuccess: () => {
      // Actualizar localmente el campo order de cada pregunta
      questions.value.forEach((q, idx) => {
        q.order = idx + 1;
      });
      toast.value = { message: 'Orden actualizado correctamente', type: 'success' };
      isOrdering.value = false;
    },
    onError: () => {
      toast.value = { message: 'Error al guardar el orden', type: 'danger' };
      isOrdering.value = false;
    }
  });
};

</script>

<template>
  <Head :title="`Preguntas de Evaluación #${evaluation.id}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Evaluaciones', route: 'admin.evaluations.index' },
        { label: `Preguntas de Evaluación #${evaluation.id}`, route: 'admin.evaluations.index' }
      ]"
    />

    <section class="section-heading mb-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title">
              <i class="bi bi-question-circle me-2"></i> Preguntas de Evaluación #{{ evaluation.id }}
            </h4>

            <Link class="btn btn-primary" :href="route('admin.evaluation-questions.create', evaluation.id)">
              <i class="bi bi-plus-lg me-1"></i> Nueva Pregunta
            </Link>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters v-model:searchQuery="searchQuery" :count="questions.length" placeholder="Buscar preguntas..." item-label="pregunta(s)" />

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header text-end">
            <Link class="btn btn-primary btn-sm " :href="route('admin.evaluation-questions.preview', evaluation.id)">
              <i class="bi bi-plus-lg me-1"></i> Previsualizar
            </Link>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:40px;"></th>
                    <th @click="sortBy('id')" style="cursor: pointer;">
                      Orden
                      <i :class="sortKey === 'id' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('question')" style="cursor: pointer;">
                      Pregunta
                      <i :class="sortKey === 'question' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('type')" style="cursor: pointer;">
                      Tipo
                      <i :class="sortKey === 'type' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <draggable
                  tag="tbody"
                  v-model="questions"
                  item-key="id"
                  handle=".drag-handle"
                  :animation="180"
                  @end="onDragEnd"
                >
                  <template #item="{ element: question, index }">
                    <tr>
                      <td class="drag-handle" style="cursor:grab;"><i class="bi bi-list"></i></td>
                      <td>{{ question.order }}</td>
                      <td>{{ question.question }}</td>
                      <td>
                        <span v-if="question.type">Abierta</span>
                        <span v-else>Opción múltiple</span>
                      </td>
                       
                      
                      <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                          <Link :href="route('admin.evaluation-questions.edit', [evaluation.id, question.id])" class="btn btn-warning" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                          </Link>
                          <button class="btn btn-danger" @click="prepareDelete(question.id)" :disabled="isDeleting" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </draggable>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <ConfirmDeleteModal :show="showDeleteModal" :loading="isDeleting" @close="cancelDelete" @confirm="deleteQuestion"
      title="¿Deseas eliminar esta pregunta?"
      confirm-message="Por favor confirma la eliminación de esta pregunta"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
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
