<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    users: Array,
    roles: Array
});

const deleteUser = (id) => {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
        Inertia.delete(route('admin.users.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AdminLayout>
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">
                            <i class="bi bi-people-fill me-2"></i>Gestión de Usuarios
                        </h1>
                        <Link :href="route('admin.users.create')" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Nuevo Usuario
                        </Link>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id">
                                    <td>{{ user.id }}</td>
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>
                                        <span v-for="role in user.roles" :key="role.id" 
                                              class="badge bg-primary me-1">
                                            {{ role.name }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <Link :href="route('admin.users.show', user.id)" 
                                              class="btn btn-sm btn-outline-info me-1"
                                              title="Ver">
                                            <i class="bi bi-eye-fill"></i>
                                        </Link>
                                        <Link :href="route('admin.users.edit', user.id)" 
                                              class="btn btn-sm btn-outline-warning me-1"
                                              title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </Link>
                                        <button @click="deleteUser(user.id)" 
                                                class="btn btn-sm btn-outline-danger"
                                                title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>