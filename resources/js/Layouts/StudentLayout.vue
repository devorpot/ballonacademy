<script setup>
import { Head } from '@inertiajs/vue3';

import TopNav from '@/Components/Dashboard/TopNav.vue';
import Sidebar from '@/Components/Dashboard/Sidebar.vue';
import Footer from '@/Components/Dashboard/Footer.vue';
</script>

<template>
  <div class="layout-wrapper">
    <Head>
      <slot name="head" />
    </Head>

    <TopNav />

    <!-- Barra visible solo en móvil -->
    <div class="mobile-header d-md-none px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
      <strong>Menú</strong>
      <button class="btn btn-sm btn-outline-primary d-print-none" type="button">
        <i class="bi bi-list"></i>
      </button>
    </div>

    <Transition name="fade" mode="out-in">
      <div :key="$page.component" class="layout-body">
        <aside class="sidebar d-none d-md-block">
          <Sidebar />
        </aside>

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
  height: 100vh; /* Cambiar min-height por height */
  display: flex;
  flex-direction: column;
}

.layout-body {
  display: flex;
  flex: 1;
  min-height: 0; /* Necesario para que los hijos puedan tener overflow */
}

.sidebar {
  width: 320px;
  flex-shrink: 0;
  background-color: #fff;
  border-right: 1px solid #dee2e6;
}

.layout-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background-color: #f8f9fa;
  min-height: 0; /* permite que el scroll funcione correctamente */
}

/* Transiciones */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Menú móvil */
.mobile-header {
  height: 50px;
}
</style>
