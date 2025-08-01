<template>
  <nav class="top-nav d-flex justify-content-between align-items-center px-4 py-2 bg-primary text-white">
    <img src="https://placehold.co/100x100" alt="Logo" height="32" />

    <div class="d-flex align-items-center gap-3">
      <i class="bi bi-globe"></i>

      <!-- Mostrar nombre del usuario -->
      <span>{{ user.name }}</span>
     

       <Link :href="route('dashboard.profile.edit')"   class="btn btn-sm btn-light text-dark">
        <i class="bi bi-person-circle"></i>
      </Link>

      <!-- Cerrar sesión -->
      <Link :href="route('logout')" method="post" as="button" class="btn btn-sm btn-light text-dark">
        <i class="bi bi-box-arrow-right me-1"></i> Salir
      </Link>
    </div>
  </nav>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

// Acceder al usuario actual desde Inertia
const { props } = usePage()
const user = props.auth.user
</script>

<style scoped>
.top-nav {
  height: 60px;
}
</style>
