<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  }
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_ids: []
});

const allRolesSelected = computed({
  get: () => form.role_ids.length === props.roles.length,
  set: (value) => {
    form.role_ids = value ? props.roles.map(role => role.id) : [];
  }
});

const submit = () => {
  form.post(route('admin.users.store'), {
    preserveScroll: true
  });
};
</script>

<template>
  <Head title="Crear Nuevo Usuario" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h1 class="h3 mb-0">
            <i class="bi bi-person-plus me-2"></i>Crear Nuevo Usuario
          </h1>
          <Link :href="route('admin.users.index')" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Volver
          </Link>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form @submit.prevent="submit" novalidate>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  v-model="form.name"
                  required
                  :class="{'is-invalid': form.errors.name}"
                >
                <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  v-model="form.email"
                  required
                  :class="{'is-invalid': form.errors.email}"
                >
                <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  id="password"
                  v-model="form.password"
                  required
                  :class="{'is-invalid': form.errors.password}"
                >
                <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <input
                  type="password"
                  class="form-control"
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  required
                >
              </div>

              <div class="col-12 mb-3">
                <label class="form-label">Roles</label>

                <div class="form-check mb-2">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="select_all_roles"
                    v-model="allRolesSelected"
                  >
                  <label class="form-check-label fw-bold" for="select_all_roles">Seleccionar todos los roles</label>
                </div>

                <div class="row">
                  <div class="col-md-3 mb-2" v-for="role in roles" :key="role.id">
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        :id="'role_' + role.id"
                        :value="role.id"
                        v-model="form.role_ids"
                      >
                      <label class="form-check-label" :for="'role_' + role.id">{{ role.name }}</label>
                    </div>
                  </div>
                </div>

                <div v-if="form.errors.role_ids || form.errors.role" class="text-danger mt-2">
                  {{ form.errors.role_ids || form.errors.role }}
                </div>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i>Guardar
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
