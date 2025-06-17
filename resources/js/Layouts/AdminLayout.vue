<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js'
import Navbar from '@/Partials/Admin/Navbar.vue'
import Footer from '@/Partials/Admin/Footer.vue'
 
</script>

<template>
    <div>
        <Head>
            <slot name="head" />
        </Head>

        <Navbar />
 
        <Transition name="fade" mode="out-in">
            <main class="main">
                <slot />
            </main>
          </Transition>

       <Footer />
    </div>
</template>

<style scoped>
/* Ejemplo de animación */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
