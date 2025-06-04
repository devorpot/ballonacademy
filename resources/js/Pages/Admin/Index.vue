<template>
  <nav class="flex justify-between p-4 bg-gray-100">
    <div>Bienvenido, {{ $page.props.auth.user.name }}</div>

    <form @submit.prevent="logout">
      <button type="submit" class="text-red-600 hover:underline">
        Cerrar sesión
      </button>
    </form>
  </nav>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
  router.post('/logout')
}
</script>
