<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object
});
</script>

<template>
    <Head :title="`Detalles de Usuario: ${user.name}`" />

    <AdminLayout>
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">
                            <i class="bi bi-person-vcard me-2"></i>Detalles de Usuario: {{ user.name }}
                        </h1>
                        <Link :href="route('admin.users.index')" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </Link>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h5 class="fw-bold">Información Básica</h5>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre:</label>
                                <p class="form-control-plaintext">{{ user.name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email:</label>
                                <p class="form-control-plaintext">{{ user.email }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Creación:</label>
                                <p class="form-control-plaintext">{{ new Date(user.created_at).toLocaleString() }}</p>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <h5 class="fw-bold">Roles Asignados</h5>
                            <hr>
                            <div v-if="user.roles.length > 0">
                                <div v-for="role in user.roles" :key="role.id" class="mb-2">
                                    <span class="badge bg-primary me-1">
                                        {{ role.name }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="alert alert-warning">
                                Este usuario no tiene roles asignados
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <Link :href="route('admin.users.edit', user.id)" class="btn btn-warning me-2">
                            <i class="bi bi-pencil-fill me-2"></i>Editar
                        </Link>
                        <Link :href="route('admin.users.index')" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Listado
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>