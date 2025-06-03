<template>
  <div>
    <h1>Bienvenido al Dashboard</h1>
    <form @submit.prevent="logout">
      <button type="submit">Cerrar sesión</button>
    </form>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
  router.post('/logout')
}
</script>
