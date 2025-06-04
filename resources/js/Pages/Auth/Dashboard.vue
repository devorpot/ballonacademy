<template>
  <div>
   <div class="container">
      <h1>Academia de globos</h1>
    <form @submit.prevent="logout">
      
 
    </form>

    <p>Lorem ipsusada sm asdasd dolor sit amet consectetur adipisicing elit. Labore in, quibusdam amet deleniti, assumenda aliquid error sunt, qui totamasdasdsad saepe mollitia dignissimos aspernatur sint fugiat quis rem neque facere eaque.</p>

   </div>

  </div>
</template>



<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
  router.post('/logout')
}
</script>
