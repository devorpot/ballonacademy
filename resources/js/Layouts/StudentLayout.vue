<script setup>
import { ref, watch, onBeforeUnmount } from 'vue' 
import { Head } from '@inertiajs/vue3'

import TopNav from '@/Components/Dashboard/Ui/TopNav.vue'
import Sidebar from '@/Components/Dashboard/Ui/Sidebar.vue'

import Footer from '@/Components/Dashboard/Footer.vue'

const showMobileSidebar = ref(false)

function toggleSidebar() {
  showMobileSidebar.value = !showMobileSidebar.value
}

// Bloquea el scroll del body cuando el sidebar móvil está abierto
watch(showMobileSidebar, (open) => {
  if (open) {
    document.documentElement.style.overflow = 'hidden'
    document.body.style.overflow = 'hidden'
  } else {
    document.documentElement.style.overflow = ''
    document.body.style.overflow = ''
  }
})
onBeforeUnmount(() => {
  document.documentElement.style.overflow = ''
  document.body.style.overflow = ''
})
</script>


<template>
  <div class="layout-wrapper">
    <Head><slot name="head" /></Head>

    <!-- Barra superior -->
    <TopNav />

    <!-- Botón menú móvil -->
    <div class="mobile-header px-3 py-2 bg-light border-bottom d-xl-none">
      <div class="d-flex justify-content-between align-items-center">
        <strong>Menú</strong>
        <button class="menu-toggle-btn" @click="toggleSidebar" aria-label="Abrir menú">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </div>

    <!-- Sidebar móvil -->
    <Transition name="slide">
      <div v-if="showMobileSidebar" class="mobile-sidebar-overlay" @click="toggleSidebar" role="presentation">
        <div class="mobile-sidebar" @click.stop aria-label="Menú lateral">
          <button class="close-button" @click="toggleSidebar" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
          </button>
          <Sidebar />
        </div>
      </div>
    </Transition>

    <!-- Cuerpo (sidebar sticky + contenido scrolleable) -->
    <Transition name="fade" mode="out-in">
      <div :key="$page.component" class="layout-body">
        <!-- Sidebar escritorio (sticky) -->
        <aside class="sidebar d-none d-xl-block" aria-label="Navegación lateral">
          <Sidebar />
        </aside>

        <!-- Contenido dinámico (scrolleable) -->
        <main class="layout-content dashboard-user ">
          
          <slot />
          
        </main>
      </div>
    </Transition>

    <!-- Mueve el Footer aquí si lo quieres fuera del scroll -->
    <Footer />
  </div>
</template>


<style scoped>
/* Vars: ajusta estas alturas a tu TopNav y a la barra móvil si cambian */

</style>
