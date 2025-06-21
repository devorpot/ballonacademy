<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-person-vcard me-2"></i> Detalles de Usuario
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <h6 class="fw-bold text-muted">Información Básica</h6>
              <hr>
              <dl class="row mb-0">
                <dt class="col-sm-5">Nombre:</dt>
                <dd class="col-sm-7">{{ user.name }}</dd>

                <dt class="col-sm-5">Email:</dt>
                <dd class="col-sm-7">{{ user.email }}</dd>

                <dt class="col-sm-5">Teléfono:</dt>
                <dd class="col-sm-7">{{ user.phone || 'No especificado' }}</dd>

                <dt class="col-sm-5">Fecha de creación:</dt>
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

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="emit('close')">
            <i class="bi bi-x-lg me-1"></i> Cerrar
          </button>
          <Link :href="route('admin.users.edit', user.id)" class="btn btn-warning">
            <i class="bi bi-pencil-fill me-1"></i> Editar
          </Link>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  show: Boolean,
  user: Object
});

const emit = defineEmits(['close']);
</script>

<style scoped>
.badge {
  font-size: 0.85rem;
}
</style>
