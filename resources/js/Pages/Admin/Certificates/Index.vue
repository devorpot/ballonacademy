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
import CreateCertificateModal from '@/Pages/Admin/Certificates/CreateCertificateModal.vue';
import ShowCertificateModal from '@/Pages/Admin/Certificates/ShowCertificateModal.vue';


const showViewModal = ref(false);
const selectedCertificate = ref(null);

const openViewModal = (certificate) => {
  selectedCertificate.value = certificate;
  showViewModal.value = true;
};


const props = defineProps({
  certificates: Array,
  filters: Object,
  users: Array,
  teachers: Array
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
  toast.value = { message: 'Certificado creado exitosamente', type: 'success' };
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

const filteredCertificates = computed(() => {
  if (!searchQuery.value) return props.certificates;
  const query = searchQuery.value.toLowerCase();
  return props.certificates.filter(c =>
    c.user.name.toLowerCase().includes(query) ||
    `${c.master.firstname} ${c.master.lastname}`.toLowerCase().includes(query) ||
    (c.authorized_by || '').toLowerCase().includes(query)
  );
});

const sortedCertificates = computed(() => {
  let data = [...filteredCertificates.value];
  data.sort((a, b) => {
    let aVal, bVal;

    if (sortKey.value === 'user') {
      aVal = a.user.name.toLowerCase();
      bVal = b.user.name.toLowerCase();
    } else if (sortKey.value === 'master') {
      aVal = `${a.master.firstname} ${a.master.lastname}`.toLowerCase();
      bVal = `${b.master.firstname} ${b.master.lastname}`.toLowerCase();
    } else if (sortKey.value === 'authorized_by') {
      aVal = (a.authorized_by || '').toLowerCase();
      bVal = (b.authorized_by || '').toLowerCase();
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

const paginatedCertificates = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedCertificates.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(sortedCertificates.value.length / itemsPerPage.value));

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

const deleteCertificate = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  Inertia.delete(route('admin.certificates.destroy', deletingId.value), {
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
  <Head title="Gestión de Certificados" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Certificados', route: '' }
    ]" />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title"><i class="bi bi-people-fill me-2"></i> &nbsp; Gestionar Certificados</h4>
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Certificado
            </button>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters v-model:searchQuery="searchQuery" :count="sortedCertificates.length" placeholder="Buscar certificados..." item-label="certificado(s)" />

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
                    <th @click="sortBy('user')" style="cursor: pointer;">
                      Usuario
                      <i :class="sortKey === 'user' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('master')" style="cursor: pointer;">
                      Maestro
                      <i :class="sortKey === 'master' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('authorized_by')" style="cursor: pointer;">
                      Autorizado por
                      <i :class="sortKey === 'authorized_by' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('date_expedition')" style="cursor: pointer;">
                      Fecha Expedición
                      <i :class="sortKey === 'date_expedition' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="certificate in paginatedCertificates" :key="certificate.id">
                    <td>{{ certificate.id }}</td>
                    <td>{{ certificate.user.name }}</td>
                    <td>{{ certificate.master.firstname }} {{ certificate.master.lastname }}</td>
                    <td>{{ certificate.authorized_by }}</td>
                    <td>{{ certificate.date_expedition }}</td>
                    <td class="text-end pe-4">
  <div class="btn-group btn-group-sm">
    <Link :href="route('admin.certificates.edit', certificate.id)" class="btn btn-outline-warning" title="Editar">
      <i class="bi bi-pencil-fill"></i>
    </Link>
    <button class="btn btn-outline-info" @click="openViewModal(certificate)" title="Ver">
      <i class="bi bi-eye-fill"></i>
    </button>
    <button class="btn btn-outline-danger" @click="prepareDelete(certificate.id)" :disabled="isDeleting" title="Eliminar">
      <i class="bi bi-trash-fill"></i>
    </button>
  </div>
</td>

                  </tr>
                  <tr v-if="sortedCertificates.length === 0">
                    <td colspan="6" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron certificados
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

    <ConfirmDeleteModal :show="showDeleteModal" :loading="isDeleting" @close="cancelDelete" @confirm="deleteCertificate"
      title="¿Deseas eliminar este certificado?"
      confirm-message="Por favor confirma la eliminación de este certificado"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />

    <CreateCertificateModal v-if="showCreateModal"
      :show="showCreateModal"
      :users="props.users"
      :teachers="props.teachers"
      @saved="onCreated"
      @close="showCreateModal = false"
    />


    <ShowCertificateModal
      v-if="showViewModal"
      :show="showViewModal"
      :certificate="selectedCertificate"
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
