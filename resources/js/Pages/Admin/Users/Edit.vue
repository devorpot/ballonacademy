<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
 import { route } from 'ziggy-js'
const props = defineProps({
    user: Object,
    roles: Array,
    userRoles: Array
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.userRoles
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head :title="`Editar Usuario: ${user.name}`" />

    <AdminLayout>
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">
                            <i class="bi bi-person-gear me-2"></i>Editar Usuario: {{ user.name }}
                        </h1>
                        <Link :href="route('admin.users.index')" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </Link>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="name" 
                                       v-model="form.name" required>
                                <div v-if="form.errors.name" class="text-danger">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       v-model="form.email" required>
                                <div v-if="form.errors.email" class="text-danger">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password" 
                                       v-model="form.password">
                                <small class="text-muted">Dejar en blanco para no cambiar</small>
                                <div v-if="form.errors.password" class="text-danger">
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password_confirmation" 
                                       v-model="form.password_confirmation">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Roles</label>
                                <div class="row">
                                    <div v-for="role in roles" :key="role.id" class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   :id="'role_' + role.id" 
                                                   :value="role.name"
                                                   v-model="form.roles">
                                            <label class="form-check-label" :for="'role_' + role.id">
                                                {{ role.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" 
                                        :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    <i class="bi bi-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>