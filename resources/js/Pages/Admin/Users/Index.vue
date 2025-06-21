<template>
  <Head title="Gestión de Usuarios" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Usuarios', route: '' }
    ]" />

    <!-- Encabezado -->
    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Gestionar Usuarios'" />
            <ButtonAdd label="Nuevo Usuario" icon="bi bi-plus-lg" @click="openCreateModal" />
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-filters my-2">
      <div class="container-fluid">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row justify-content-between align-items-center">
              <div class="col-md-6 mb-3 mb-md-0">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': searchQuery && filteredUsers.length === 0 }"
                    placeholder="Buscar usuarios..."
                    v-model="searchQuery"
                  />
                </div>
              </div>
              <div class="col-md-4 text-end">
                <span class="badge bg-light text-dark">
                  <i class="bi bi-people-fill me-1"></i>
                  {{ filteredUsers.length }} usuario(s)
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla -->
    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4" @click="sortBy('id')" style="cursor: pointer;">
                      ID <span v-if="sortKey === 'id'">
                        <i :class="sortOrder === 'asc' ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                      </span>
                    </th>
                    <th @click="sortBy('name')" style="cursor: pointer;">
                      Nombre <span v-if="sortKey === 'name'">
                        <i :class="sortOrder === 'asc' ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                      </span>
                    </th>
                    <th @click="sortBy('email')" style="cursor: pointer;">
                      Email <span v-if="sortKey === 'email'">
                        <i :class="sortOrder === 'asc' ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                      </span>
                    </th>
                    <th>Roles</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in paginatedUsers" :key="user.id">
                    <td class="ps-4">{{ user.id }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                          <span class="avatar-title bg-light text-primary rounded-circle">
                            {{ user.name.charAt(0).toUpperCase() }}
                          </span>
                        </div>
                        {{ user.name }}
                      </div>
                    </td>
                    <td>{{ user.email }}</td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <span
                          v-for="role in user.roles"
                          :key="role.id"
                          class="badge bg-primary text-truncate"
                          style="max-width: 120px"
                        >
                          {{ role.name }}
                        </span>
                        <span v-if="user.roles.length === 0" class="badge bg-secondary">Sin roles</span>
                      </div>
                    </td>
                    <td class="text-end pe-4">
                      <div class="btn-group" role="group">
                        <button
                          class="btn btn-sm btn-outline-info"
                          @click="openViewModal(user)"
                          title="Ver detalles"
                        >
                          <i class="bi bi-eye-fill"></i>
                        </button>
                        <Link
                          :href="route('admin.users.edit', user.id)"
                          class="btn btn-sm btn-outline-warning"
                          title="Editar"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button
                          @click="prepareDelete(user.id)"
                          class="btn btn-sm btn-outline-danger"
                          :disabled="isDeleting"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredUsers.length === 0">
                    <td colspan="5" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron usuarios
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Paginación -->
    <section class="section-pagination my-2">
      <div class="container-fluid">
        <div class="d-flex justify-content-center my-4">
          <nav>
            <ul class="pagination mb-0 align-items-center">
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

    <!-- Modales -->
    <ConfirmDeleteModal
      :show="showDeleteModal"
      :user="deletingUser"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteUser"
      title="¿Deseas eliminar este usuario?"
      confirm-message="Por favor confirma la eliminación de este usuario"
      warning-text="Ten en cuenta que esta operación es irreversible."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <UserModal :show="showCreateModal" @close="closeCreateModal" :roles="props.roles" />

    <ShowUserModal v-if="showViewModal" :show="showViewModal" :user="selectedUser" @close="showViewModal = false" />
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import ButtonAdd from '@/Components/Admin/Ui/ButtonAdd.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import UserModal from '@/Pages/Admin/Users/CreateUserModal.vue';
import ShowUserModal from '@/Pages/Admin/Users/ShowUserModal.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  users: Array,
  roles: Array
});

const showCreateModal = ref(false);
const showViewModal = ref(false);
const selectedUser = ref(null);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const sortKey = ref('id');
const sortOrder = ref('asc');

const openCreateModal = () => { showCreateModal.value = true; };
const closeCreateModal = () => { showCreateModal.value = false; };
const openViewModal = (user) => {
  selectedUser.value = user;
  showViewModal.value = true;
};

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const filteredUsers = computed(() => {
  if (!searchQuery.value) return props.users;
  const query = searchQuery.value.toLowerCase();
  return props.users.filter(user =>
    user.name.toLowerCase().includes(query) ||
    user.email.toLowerCase().includes(query) ||
    user.roles.some(role => role.name.toLowerCase().includes(query))
  );
});

const sortedUsers = computed(() => {
  return [...filteredUsers.value].sort((a, b) => {
    let result = 0;
    if (sortKey.value === 'name') {
      result = a.name.localeCompare(b.name);
    } else if (sortKey.value === 'email') {
      result = a.email.localeCompare(b.email);
    } else if (sortKey.value === 'id') {
      result = a.id - b.id;
    }
    return sortOrder.value === 'asc' ? result : -result;
  });
});

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedUsers.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value));

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const deletingUser = computed(() => props.users.find(u => u.id === deletingId.value));

const prepareDelete = (id) => {
  deletingId.value = id;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingId.value = null;
  isDeleting.value = false;
};

const showToast = useToast();
const page = usePage();

const deleteUser = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  Inertia.delete(route('admin.users.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete();
      showToast('Usuario eliminado correctamente', 'success');
    },
    onError: () => {
      showToast('Hubo un error al eliminar el usuario', 'danger');
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};

onMounted(() => {
  const flash = page.props.flash;
  if (flash?.success) showToast(flash.success, 'success');
  if (flash?.error) showToast(flash.error, 'danger');
});
</script>

<style scoped>
.avatar-sm {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.avatar-title {
  font-weight: 600;
  font-size: 1rem;
}

.table th {
  white-space: nowrap;
}

input.is-invalid {
  border-color: #dc3545;
}

.badge.text-truncate {
  max-width: 100px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
</style>
