<template>
  <div>
   <div class="container">
      <h1>Bienvenido al Dashboard </h1>
    <form @submit.prevent="logout">
      <button type="submit">Cerrar sesión</button>
    </form>

    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Labore in, quibusdam amet deleniti, assumenda aliquid error sunt, qui totam saepe mollitia dignissimos aspernatur sint fugiat quis rem neque facere eaque.</p>

   </div>

  </div>
</template>



<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
  router.post('/logout')
}
</script>
