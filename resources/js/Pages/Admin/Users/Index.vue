<template>
    <Head title="Gestión de Usuarios" />
    <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
                { label: 'Dashboard', route: 'admin.dashboard' },
                { label: 'Usuarios', route: '' },
 
              ]"
            />
    <!--sections-->
    <section class="section-heading">
        <div class="container-fluid">
         <div class="row ">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <Title :title="'Gestionar Usuarios'" />
                <ButtonAdd   label="Nuevo Usuario" icon="bi bi-plus-lg" @click="openCreateModal" />
            </div>
        </div>
    </div>
    </section>
    <!--/section-heading-->
    <section class="section-filters  my-2">
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
            </div>
        </div>
    </section>
    <!--/section-filters-->
    <section class="section-data  my-2">
                <div class="container-fluid ">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                       <th class="ps-4" @click="sortBy('id')" style="cursor: pointer;">
                                            ID
                                             <span v-if="sortKey === 'id'">
                                              <i :class="sortOrder === 'asc' ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                                            </span>
                                        </th>
                                        <th @click="sortBy('name')" style="cursor: pointer;">
                                            Nombre
                                           <span v-if="sortKey === 'name'">
                                              <i :class="sortOrder === 'asc' ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill'"></i>
                                            </span>
                                        </th>

                                        <th @click="sortBy('email')" style="cursor: pointer;">
                                            Email
                                            <span v-if="sortKey === 'email'">
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
                                                  class="badge bg-primary text-truncate"
                                                  style="max-width: 120px"
                                                  v-for="role in user.roles"
                                                  :key="role.id"
                                                >
                                                  {{ role.name }}
                                                </span>
                                                <span v-if="user.roles.length === 0" class="badge bg-secondary">
                                                    Sin roles
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group" role="group">
                                                <Link
                                                    :href="route('admin.users.show', user.id)"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="Ver detalles"
                                                    data-bs-toggle="tooltip"
                                                >
                                                    <i class="bi bi-eye-fill"></i>
                                                </Link>
                                                <Link
                                                    :href="route('admin.users.edit', user.id)"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Editar"
                                                    data-bs-toggle="tooltip"
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
    <!--/section-data-->
    <section class="section-pagination my-2">
         <div class="container-fluid">
             <div class="row">
                 <div class="col-12">
                    <div class="d-flex justify-content-center my-4">
                        <nav>
                            <ul class="pagination mb-0 align-items-center">
                                <li
                                    class="page-item"
                                    :class="{ disabled: currentPage === 1 }"
                                    @click="changePage(currentPage - 1)"
                                >
                                    <a class="page-link" href="#">Anterior</a>
                                </li>
                              <li
                                  v-for="page in totalPages"
                                  :key="page"
                                  class="page-item"
                                  :class="{ active: currentPage === page }"
                                  @click="changePage(page)"
                                  :aria-current="currentPage === page ? 'page' : null"
                                >
                                  <a class="page-link" href="#" :aria-label="`Página ${page}`">{{ page }}</a>
                                </li>
                                <li
                                    class="page-item"
                                    :class="{ disabled: currentPage === totalPages }"
                                    @click="changePage(currentPage + 1)"
                                >
                                    <a class="page-link" href="#">Siguiente</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                 </div>
             </div>
         </div> 
    </section>
     <!--/section-pagination-->
    <!--/sections-->

     <ConfirmDeleteModal
              :show="showDeleteModal"
              :user="deletingUser"
              :loading="isDeleting"
              @close="cancelDelete"
              @confirm="deleteUser"
              title="¿Deseas eliminar este usuario?"
              confirm-message="Por favor confirma la eliminación de"
              warning-text="Ten en cuenta que esta operación es irreversible."
              cancel-text="No, cancelar"
              confirm-text="Sí, eliminar"
            />
            <UserModal :show="showCreateModal" @close="closeCreateModal" :roles="props.roles" />
    </AdminLayout>
</template>
<script setup>
        import { Head, Link , usePage  } from '@inertiajs/vue3';
        import AdminLayout from '@/Layouts/AdminLayout.vue';
        import { Inertia } from '@inertiajs/inertia';
        import { route } from 'ziggy-js';
        import { Tooltip, Collapse, Dropdown } from 'bootstrap';
         
        import { useToast } from '@/composables/useToast';
        import { ref, computed, onMounted } from 'vue';

        //Componentes
        import UserModal from '@/Components/Admin/User/UserCreateModal.vue';
        import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';

        //Admin UI
        import Title  from '@/Components/Admin/Ui/Title.vue';
        import Breadcrumbs  from '@/Components/Admin/Ui/Breadcrumbs.vue';
        import ButtonAdd from '@/Components/Admin/Ui/ButtonAdd.vue';


        const showCreateModal = ref(false);
        const openCreateModal = () => { showCreateModal.value = true; };
        const closeCreateModal = () => { showCreateModal.value = false; };


         
        const showToast = useToast();
        const page = usePage();
         

        const props = defineProps({
            users: Array,
            roles: Array
        });

        const searchQuery = ref('');
        const deletingId = ref(null);
        const showDeleteModal = ref(false);
        const isDeleting = ref(false);

        // Paginación
        const currentPage = ref(1);
        const itemsPerPage = ref(10);

        // Ordenamiento
        const sortKey = ref('id'); // clave por la que ordenar
        const sortOrder = ref('asc'); // 'asc' o 'desc'

        // Función para cambiar el orden
        const sortBy = (key) => {
            if (sortKey.value === key) {
                sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortKey.value = key;
                sortOrder.value = 'asc';
            }
        };

        // Computed: filtro por búsqueda
        const filteredUsers = computed(() => {
            if (!searchQuery.value) return props.users;
            const query = searchQuery.value.toLowerCase();
            return props.users.filter(user =>
                user.name.toLowerCase().includes(query) ||
                user.email.toLowerCase().includes(query) ||
                user.roles.some(role => role.name.toLowerCase().includes(query))
            );
        });

        // Computed: usuarios ordenados
        const sortedUsers = computed(() => {
            return [...filteredUsers.value].sort((a, b) => {
                let result = 0;
                if (sortKey.value === 'name') {
                    result = a.name.localeCompare(b.name);
                } else if (sortKey.value === 'id') {
                    result = a.id - b.id;
                }
                return sortOrder.value === 'asc' ? result : -result;
            });
        });

        // Computed: usuarios paginados
        const paginatedUsers = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage.value;
            return sortedUsers.value.slice(start, start + itemsPerPage.value);
        });

        const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value));

        const changePage = (page) => {
            if (page >= 1 && page <= totalPages.value) {
                currentPage.value = page;
                scrollToTop();
            }
        };

        const scrollToTop = () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // Eliminación de usuario
        const deletingUser = computed(() => {
            return props.users.find(u => u.id === deletingId.value);
        });

        const prepareDelete = (id) => {
            deletingId.value = id;
            showDeleteModal.value = true;
        };

        const cancelDelete = () => {
            showDeleteModal.value = false;
            deletingId.value = null;
            isDeleting.value = false;
        };

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

        // Activar tooltips de Bootstrap
        onMounted(() => {
         
         const flash = page.props.flash;

          if (flash?.success) {
            showToast(flash.success, 'success');
          }

          if (flash?.error) {
            showToast(flash.error, 'danger');
          }

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
          

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

.btn-group {
    box-shadow: none;
}

.btn-sm {
    padding: 0.35rem 0.5rem;
}

/* Animaciones suaves con fade-in */
 
/* Input de búsqueda con estilo de error si no hay resultados */
input.is-invalid {
  border-color: #dc3545;
  padding-right: 2.25rem;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23dc3545' viewBox='0 0 16 16'%3e%3cpath d='M16 1.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0h13A1.5 1.5 0 0 1 16 1.5zM8 3.993a.905.905 0 1 0-1.81 0 .905.905 0 0 0 1.81 0zM7.1 6h1.8v6H7.1V6z'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(0.375em + 0.1875rem) center;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Botones de tabla con espaciado uniforme */
.btn-group .btn {
  min-width: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Tooltip más visible */
[data-bs-toggle="tooltip"] {
  cursor: pointer;
}

/* Roles con truncado en badges para evitar overflow */
.badge.text-truncate {
  max-width: 100px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}



</style>
