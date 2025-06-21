<template>
  <Head :title="`Detalles del Maestro: ${teacher.firstname} ${teacher.lastname}`" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Maestros', route: 'admin.teachers.index' },
          { label: 'Detalle', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="admin-title">
            <i class="bi bi-person-vcard me-2"></i>
            Detalles del Maestro: {{ teacher.firstname }} {{ teacher.lastname }}
          </h4>
          <div class="btn-group">
            <Link :href="route('admin.teachers.edit', teacher.id)" class="btn btn-warning">
              <i class="bi bi-pencil-fill me-1"></i> Editar
            </Link>
            <Link :href="route('admin.teachers.index')" class="btn btn-secondary">
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
                <dd class="col-sm-7">{{ teacher.firstname }} {{ teacher.lastname }}</dd>

                <dt class="col-sm-5">Email:</dt>
                <dd class="col-sm-7">{{ teacher.user.email }}</dd>

                <dt class="col-sm-5">Clave Maestro:</dt>
                <dd class="col-sm-7">{{ teacher.teacher_id }}</dd>

                <dt class="col-sm-5">Teléfono:</dt>
                <dd class="col-sm-7">{{ teacher.phone }}</dd>

                <dt class="col-sm-5">Especialidad:</dt>
                <dd class="col-sm-7">{{ teacher.specialty }}</dd>

                <dt class="col-sm-5">País:</dt>
                <dd class="col-sm-7">{{ teacher.country }}</dd>

                <dt class="col-sm-5">Fecha de Creación:</dt>
                <dd class="col-sm-7">{{ new Date(teacher.created_at).toLocaleString() }}</dd>
              </dl>
            </div>

            <div class="col-md-6 mb-3">
              <h6 class="fw-bold text-muted">Roles Asignados</h6>
              <hr>
              <div v-if="teacher.user.roles.length > 0">
                <span v-for="role in teacher.user.roles" :key="role.id" class="badge bg-primary me-1 mb-1">
                  {{ role.name }}
                </span>
              </div>
              <div v-else class="alert alert-warning mb-0">
                Este maestro no tiene roles asignados
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';

const props = defineProps({
  teacher: Object
});
</script>
