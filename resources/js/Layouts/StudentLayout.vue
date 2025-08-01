<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'

import TopNav from '@/Components/Dashboard/TopNav.vue'
import Sidebar from '@/Components/Dashboard/Sidebar.vue'
import Footer from '@/Components/Dashboard/Footer.vue'

const showMobileSidebar = ref(false)

function toggleSidebar() {
  showMobileSidebar.value = !showMobileSidebar.value
}
</script>

<template>
  <div class="layout-wrapper">
    <Head>
      <slot name="head" />
    </Head>
 
    <!-- Barra superior -->
    <TopNav />

    <!-- Botón menú móvil -->
    <div class="mobile-header px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center d-xl-none">
      <strong>Menú</strong>
      <button class="menu-toggle-btn" @click="toggleSidebar" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>
    </div>
 
    <!-- Sidebar móvil -->
    <Transition name="slide">
      <div v-if="showMobileSidebar" class="mobile-sidebar-overlay" @click="toggleSidebar">
        <aside class="mobile-sidebar" @click.stop>
          <!-- Botón cerrar -->
          <button class="close-button" @click="toggleSidebar" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
          </button>

          <Sidebar />
        </aside>
      </div>
    </Transition>

    <!-- Contenido principal -->
    <Transition name="fade" mode="out-in">
      <div :key="$page.component" class="layout-body">
        <!-- Sidebar escritorio -->
        <aside class="sidebar d-none d-xl-block">
          <Sidebar />
        </aside>

        <!-- Contenido dinámico -->
        <main class="layout-content dashboard-user">
          <slot />
        </main>
      </div>
    </Transition>

    <Footer />
  </div>
</template>

<style scoped>
.layout-wrapper {
  height: 100vh;
  display: flex;
  flex-direction: column;

}

.layout-body {
  display: flex;
  flex: 1;
  min-height: 0;
}

 

.layout-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background-color: #f8f9fa;
  min-height: 0;
 
}

/* Mobile header (visible < 1200px) */
.mobile-header {
  height: 50px;
  display: flex;
}

.menu-toggle-btn {
  background-color: transparent;
  border: none;
  font-size: 1.25rem;
  padding: 0.25rem 0.5rem;
  color: #0d6efd;
  cursor: pointer;
}

.menu-toggle-btn:hover {
  color: #0a58ca;
}

/* Sidebar móvil */
.mobile-sidebar-overlay {
  position: fixed;
  inset: 0;
  z-index: 1050;
  background-color: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: flex-start;
}

.mobile-sidebar {
  width: 100%;
 
  height: 100vh;
 
  overflow-y: auto;
 
  position: relative;
}

/* Botón cerrar dentro del sidebar móvil */
.close-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: black;

  border: none;
  font-size: 1.25rem;
  color:#FFF;
  border-radius:;
  z-index: 10;
  cursor: pointer;
}

.close-button:hover {
  color: #EEE;
}

/* Transiciones */
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
