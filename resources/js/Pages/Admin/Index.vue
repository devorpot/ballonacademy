<template>
  <nav class="flex justify-between p-4 bg-gray-100">
    <div>Bienvenido, <strong>{{ $page.props.auth.user.name }}</strong></div>

    <form @submit.prevent="logout">
      <button type="submit" class="text-red-600 hover:underline">
        Cerrar sesión
      </button>
    </form>
  </nav>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'

import { router } from '@inertiajs/vue3'

defineOptions({
  layout: AdminLayout
})

function logout() {
  router.post('/logout')
}
</script>
