<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js'
import Navbar from '@/Components/Admin/Navbar.vue'
import Footer from '@/Components/Admin/Footer.vue'
 
</script>

<template>
    <div>
        <Head>
            <slot name="head" />
        </Head>

        <Navbar />
 
          <Transition name="fade" mode="out-in">
            <div :key="$page.component">
              <main class="main">
                <slot />
              </main>
            </div>
          </Transition>

       <Footer />
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

</style>
