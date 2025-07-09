<template>
  <aside class="sidebar bg-white p-4 text-center shadow-sm">
    <img
      src="https://placehold.co/260x260"
      alt="Avatar"
      class="rounded-circle mb-3"
      width="100"
    />

    <!-- Usuario logueado -->
    <h6 class="mb-0 text-uppercase">{{ user.name }}</h6>
    <small class="text-muted">@{{ user.username }}</small>

    <div class="mt-4 d-grid gap-2">
      <button class="btn btn-outline-secondary">MI PERFIL</button>
      <button class="btn btn-outline-secondary">CURSOS</button>
      <button class="btn btn-outline-secondary">BLOG</button>
    </div>

    <div class="mt-4">
      <small class="text-muted">CURSANDO:</small>
      <h6 class="text-uppercase fw-bold">PBA</h6>
      <small>VIDEO 2</small>
    </div>
  </aside>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { props } = usePage()
const user = props.auth.user
</script>
