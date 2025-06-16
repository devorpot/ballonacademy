<script setup>
import { Head, Link , usePage  } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Inertia } from '@inertiajs/inertia';
import { route } from 'ziggy-js';
 
import { useToast } from '@/composables/useToast';
import { ref, computed, onMounted } from 'vue';


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

 showToast('Probando', 'Probando el toast', 'success');

 const flash = page.props.flash;

  if (flash?.success) {
    showToast(flash.success, 'success');
  }

  if (flash?.error) {
    showToast(flash.error, 'danger');
  }

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

});
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AdminLayout>
        <div class="container-fluid py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h3><i class="bi bi-people-fill me-2"></i>Gestión de Usuarios</h3>
                    <Link :href="route('admin.users.create')" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-2"></i>Nuevo Usuario
                    </Link>
                </div>
            </div>

            <!-- Búsqueda -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Buscar usuarios..."
                                    v-model="searchQuery"
                                >
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

            <!-- Tabla -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                   <th class="ps-4" @click="sortBy('id')" style="cursor: pointer;">
                                        ID
                                        <span v-if="sortKey === 'id'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th @click="sortBy('name')" style="cursor: pointer;">
                                        Nombre
                                        <span v-if="sortKey === 'name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                                    </th>

                                    <th @click="sortBy('email')" style="cursor: pointer;">
                                        Email
                                        <span v-if="sortKey === 'email'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
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
                                                class="badge bg-primary"
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
                                                title="Eliminar"
                                                data-bs-toggle="tooltip"
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

            <!-- Paginación -->
            <div class="d-flex justify-content-center my-4">
                <nav>
                    <ul class="pagination mb-0">
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
                        >
                            <a class="page-link" href="#">{{ page }}</a>
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

        <!-- Modal eliminar -->
        <div class="modal fade" id="modal-alert" :class="{ 'show d-block': showDeleteModal }" tabindex="-1" v-if="showDeleteModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar eliminación
                        </h5>
                        <button type="button" class="btn-close" @click="cancelDelete"></button>
                    </div>
                    <div class="modal-body">
                        <p>
                            ¿Estás seguro de que deseas eliminar al usuario 
                            <strong>{{ deletingUser?.name }}</strong>?
                        </p>
                        <p class="small text-muted">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="cancelDelete">Cancelar</button>
                        <button class="btn btn-danger" @click="deleteUser" :disabled="isDeleting">
                            <span v-if="isDeleting" class="spinner-border spinner-border-sm me-2"></span>
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show" @click="cancelDelete"></div>
        </div>
    </AdminLayout>
</template>

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

.modal {
    background-color: rgba(0, 0, 0, 0.5);
}

#modal-alert{
    
    .modal-content{
        z-index:9999!important;
    }
}
</style>
