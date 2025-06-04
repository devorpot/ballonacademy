<template>
  <div>
   <div class="container">
      <h1>Bienvenido a Academia de globos</h1>
      <p>{{ $page.props.auth.user.name }}</p>
   <form @submit.prevent="logout">
      <button type="submit" class="text-red-600 hover:underline">
        Cerrar sesión
      </button>
    </form>
    <p>Lorem ipsuasdasdsaddd asdsadsada sm asdddasd dolor sit amet consectetur adipisicing elit. Labore in, quibusdam amet deleniti, assumenda aliquid error sunt, qui totamasdasdsad saepe mollitia dignissimos aspernatur sint fugiat quis rem neque facere eaque.</p>

   </div>

  </div>
</template>



<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
  router.post('/logout')
}
</script>
