<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';

const props = defineProps({
  user: Object
});
</script>

<template>
  <Head :title="`Detalles de Usuario: ${user.name}`" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Usuarios', route: 'admin.users.index' },
          { label: 'Detalle', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="admin-title">
            <i class="bi bi-person-vcard me-2"></i>
            Detalles de Usuario: {{ user.name }}
          </h4>
          <div class="btn-group">
            <Link :href="route('admin.users.edit', user.id)" class="btn btn-warning">
              <i class="bi bi-pencil-fill me-1"></i> Editar
            </Link>
            <Link :href="route('admin.users.index')" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-1"></i> Volver
            </Link>
          </div>
        </div>
      </section>

      <div class="card my-3">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <h6 class="fw-bold text-muted">Información Básica</h6>
              <hr>
              <dl class="row mb-0">
                <dt class="col-sm-5">Nombre:</dt>
                <dd class="col-sm-7">{{ user.name }}</dd>

                <dt class="col-sm-5">Email:</dt>
                <dd class="col-sm-7">{{ user.email }}</dd>

                <dt class="col-sm-5">Fecha de Creación:</dt>
                <dd class="col-sm-7">{{ new Date(user.created_at).toLocaleString() }}</dd>
              </dl>
            </div>

            <div class="col-md-6 mb-3">
              <h6 class="fw-bold text-muted">Roles Asignados</h6>
              <hr>
              <div v-if="user.roles.length > 0">
                <span v-for="role in user.roles" :key="role.id" class="badge bg-primary me-1 mb-1">
                  {{ role.name }}
                </span>
              </div>
              <div v-else class="alert alert-warning mb-0">
                Este usuario no tiene roles asignados
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
