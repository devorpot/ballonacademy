<template>
  <div class="container mt-4">
    <h1 class="mb-4">Editar Usuario</h1>

    <form @submit.prevent="submit" class="card card-body shadow-sm">
      <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input v-model="form.name" type="text" class="form-control" />
        <div v-if="form.errors.name" class="text-danger small">{{ form.errors.name }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input v-model="form.email" type="email" class="form-control" />
        <div v-if="form.errors.email" class="text-danger small">{{ form.errors.email }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Contraseña (opcional)</label>
        <input v-model="form.password" type="password" class="form-control" placeholder="Deja en blanco para no cambiar" />
        <div v-if="form.errors.password" class="text-danger small">{{ form.errors.password }}</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Rol</label>
        <select v-model="form.role" class="form-select">
          <option value="" disabled>Selecciona un rol</option>
          <option v-for="role in roles" :key="role.id" :value="role.name">
            {{ role.name }}
          </option>
        </select>
        <div v-if="form.errors.role" class="text-danger small">{{ form.errors.role }}</div>
      </div>

      <div class="d-flex justify-content-between">
        <Link href="/admin/users" class="btn btn-secondary">Cancelar</Link>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  roles: Array,
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  role: props.user.roles[0]?.name || '',
})

const submit = () => {
  form.put(`/admin/users/${props.user.id}`)
}
</script>
