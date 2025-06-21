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
import CreateSubscriptionModal from '@/Pages/Admin/Subscriptions/CreateSubscriptionModal.vue';
import ShowSubscriptionModal from '@/Pages/Admin/Subscriptions/ShowSubscriptionModal.vue';

const props = defineProps({
  subscriptions: Array,
  users: Array,
  courses: Array
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
const selectedSubscription = ref(null);
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

const sortIcon = (key) => {
  if (sortKey.value !== key) return 'bi bi-arrow-down-up';
  return sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down';
};

const filteredSubscriptions = computed(() => {
  if (!searchQuery.value) return props.subscriptions;
  const query = searchQuery.value.toLowerCase();
  return props.subscriptions.filter(s =>
    s.user.name.toLowerCase().includes(query) ||
    s.course.title.toLowerCase().includes(query) ||
    (s.payment_currency || '').toLowerCase().includes(query) ||
    (s.payment_status || '').toLowerCase().includes(query)
  );
});

const sortedSubscriptions = computed(() => {
  let data = [...filteredSubscriptions.value];
  data.sort((a, b) => {
    let aVal, bVal;
    switch (sortKey.value) {
      case 'user':
        aVal = a.user.name.toLowerCase();
        bVal = b.user.name.toLowerCase();
        break;
      case 'course':
        aVal = a.course.title.toLowerCase();
        bVal = b.course.title.toLowerCase();
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

const paginatedSubscriptions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedSubscriptions.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(sortedSubscriptions.value.length / itemsPerPage.value));

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

const deleteSubscription = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  Inertia.delete(route('admin.subscriptions.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete();
      toast.value = { message: 'Suscripción eliminada exitosamente', type: 'success' };
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

const openViewModal = (subscription) => {
  selectedSubscription.value = subscription;
  showViewModal.value = true;
};

const onCreated = () => {
  toast.value = { message: 'Suscripción creada exitosamente', type: 'success' };
  showCreateModal.value = false;
};
</script>

<template>
  <Head title="Gestión de Suscripciones" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Suscripciones', route: '' }
    ]" />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Gestionar Suscripciones'" />
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus-lg me-1"></i> Nueva Suscripción
            </button>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters v-model:searchQuery="searchQuery" :count="sortedSubscriptions.length" placeholder="Buscar suscripciones..." item-label="suscripción(es)" />

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('id')" style="cursor: pointer;">ID <i :class="sortIcon('id')"></i></th>
                    <th @click="sortBy('user')" style="cursor: pointer;">Usuario <i :class="sortIcon('user')"></i></th>
                    <th @click="sortBy('course')" style="cursor: pointer;">Curso <i :class="sortIcon('course')"></i></th>
                    <th @click="sortBy('payment_amount')" style="cursor: pointer;">Monto <i :class="sortIcon('payment_amount')"></i></th>
                    <th @click="sortBy('payment_currency')" style="cursor: pointer;">Moneda <i :class="sortIcon('payment_currency')"></i></th>
                    <th @click="sortBy('payment_status')" style="cursor: pointer;">Estado <i :class="sortIcon('payment_status')"></i></th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="subscription in paginatedSubscriptions" :key="subscription.id">
                    <td>{{ subscription.id }}</td>
                    <td>{{ subscription.user.name }}</td>
                    <td>{{ subscription.course.title }}</td>
                    <td>{{ subscription.payment_amount }}</td>
                    <td>{{ subscription.payment_currency }}</td>
                    <td>{{ subscription.payment_status }}</td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" @click="openViewModal(subscription)" title="Ver">
                          <i class="bi bi-eye-fill"></i>
                        </button>
                        <Link :href="route('admin.subscriptions.edit', subscription.id)" class="btn btn-outline-warning" title="Editar">
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button class="btn btn-outline-danger" @click="prepareDelete(subscription.id)" :disabled="isDeleting" title="Eliminar">
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="sortedSubscriptions.length === 0">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron suscripciones
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
      @confirm="deleteSubscription"
      title="¿Deseas eliminar esta suscripción?"
      confirm-message="Por favor confirma la eliminación de esta suscripción"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />

    <CreateSubscriptionModal v-if="showCreateModal"
      :show="showCreateModal"
      :users="props.users"
      :courses="props.courses"
      @saved="onCreated"
      @close="showCreateModal = false"
    />

    <ShowSubscriptionModal v-if="showViewModal"
      :show="showViewModal"
      :subscription="selectedSubscription"
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
