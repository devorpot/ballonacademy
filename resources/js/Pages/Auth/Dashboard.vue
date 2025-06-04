<template>
  <div>
   <div class="container">
      <h1>Bienvenido al Dashboard </h1>
    <form @submit.prevent="logout">
      <button type="submit">Cerrar sesión</button>
      <p>asldjkhaskhdkasjhd</p>
    </form>

   </div>

  </div>
</template>



<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
  router.post('/logout')
}
</script>
