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
            <Title :title="'Gestionar Certificados'" />
            <Link :href="route('admin.certificates.create')" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Certificado
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
                        placeholder="Buscar certificados..."
                        v-model="searchQuery"
                      />
                    </div>
                  </div>
                  <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark">
                      <i class="bi bi-card-text me-1"></i>
                      {{ filteredCertificates.length }} certificado(s)
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
                    <th>Usuario</th>
                    <th>Maestro</th>
                    <th>Autorizado por</th>
                    <th>Fecha Expedición</th>
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
                      <div class="btn-group">
                        <Link
                          :href="route('admin.certificates.edit', certificate.id)"
                          class="btn btn-sm btn-outline-warning"
                          title="Editar"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <Link
                          :href="route('admin.certificates.show', certificate.id)"
                          class="btn btn-sm btn-outline-info"
                          title="Ver"
                        >
                          <i class="bi bi-eye-fill"></i>
                        </Link>
                        <button
                          class="btn btn-sm btn-outline-danger"
                          @click="prepareDelete(certificate.id)"
                          :disabled="isDeleting"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredCertificates.length === 0">
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
      @confirm="deleteCertificate"
      title="¿Deseas eliminar este certificado?"
      confirm-message="Por favor confirma la eliminación de este certificado"
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
  certificates: Array
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

const filteredCertificates = computed(() => {
  if (!searchQuery.value) return props.certificates;
  const query = searchQuery.value.toLowerCase();
  return props.certificates.filter(c =>
    c.user.name.toLowerCase().includes(query) ||
    `${c.master.firstname} ${c.master.lastname}`.toLowerCase().includes(query) ||
    (c.authorized_by || '').toLowerCase().includes(query)
  );
});

const paginatedCertificates = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredCertificates.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredCertificates.value.length / itemsPerPage.value));

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
