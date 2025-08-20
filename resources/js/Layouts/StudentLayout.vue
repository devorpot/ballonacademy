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
        <aside class="mobile-sidebar" @click.stop aria-label="Menú lateral">
          <button class="close-button" @click="toggleSidebar" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
          </button>
          <Sidebar />
        </aside>
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
:root {
  --topnav-h: 56px;      /* Altura aproximada del TopNav */
  --mobilebar-h: 50px;   /* Altura de .mobile-header en móviles */
}
@media (min-width: 1200px) {
  :root { --mobilebar-h: 0px; } /* En escritorio, no hay barra móvil extra */
}

/* Contenedor principal: usa viewport modernos para móvil */
.layout-wrapper {
  height: 100dvh;               /* corrige issues de barras de navegador en móvil */
  min-height: 100svh;
  display: flex;
  flex-direction: column;
  overflow: hidden;             /* evita scroll global */
}

/* Zona debajo del header(s). Sin scroll propio, reparte hijos. */
.layout-body {
  display: flex;
  flex: 1;
  min-height: 0;                /* clave para permitir overflow en hijos */
  overflow: hidden;             /* sin scroll aquí */
  background: #f8f9fa;
}

/* ===== Sidebar escritorio (sticky) ===== */
.sidebar {
  /* ancho fijo acorde a tu diseño */
  width: 280px;
  flex: 0 0 280px;
  /* sticky respecto al viewport, compensando encabezados */
  position: sticky;
  top: calc(var(--topnav-h) + var(--mobilebar-h));
  align-self: flex-start;
  /* alto acoplado al viewport descontando encabezados */
  height: calc(100dvh - var(--topnav-h) - var(--mobilebar-h));
  overflow: hidden;  /* evita scroll en sidebar; quita si quieres scroll en él */
  padding: .5rem .75rem;
  background: #fff;
  border-right: 1px solid rgba(0,0,0,.05);
  border-top: 1px solid rgba(0,0,0,.05);
}

/* ===== Contenido scrolleable ===== */
.layout-content {
  flex: 1 1 auto;
  min-width: 0;
  min-height: 0;
  padding: 1.25rem 1.5rem;
  overflow: auto;                             /* el scroll vive aquí */
  -webkit-overflow-scrolling: touch;          /* inercia iOS */
  scroll-behavior: smooth;                    /* UX suave */
  background: #f8f9fa;
  /* compensa encabezados para que el primer contenido no quede oculto en anclas */
  scroll-padding-top: 1rem;
}

/* ===== Mobile header ===== */
.mobile-header {
  height: var(--mobilebar-h);
  display: flex;
  align-items: center;
}
.menu-toggle-btn {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  padding: .25rem .5rem;
  color: #0d6efd;
  cursor: pointer;
}
.menu-toggle-btn:hover { color: #0a58ca; }

/* ===== Sidebar móvil (overlay tipo offcanvas) ===== */
.mobile-sidebar-overlay {
  position: fixed;
  inset: 0;
  z-index: 1050;
  background-color: rgba(0, 0, 0, .4);
  display: flex;
  justify-content: flex-start;
  /* evita scroll del fondo (refuerzo, también se maneja por JS) */
  backdrop-filter: blur(1px);
}
.mobile-sidebar {
  width: min(86vw, 320px);
  height: 100dvh;
  background: #fff;
  overflow: auto;
  position: relative;
  padding: 1rem;
  box-shadow: 0 0 24px rgba(0,0,0,.15);
  /* respeta zonas seguras en iPhone */
  padding-top: max(1rem, env(safe-area-inset-top));
  padding-bottom: max(1rem, env(safe-area-inset-bottom));
}

/* Botón cerrar */
.close-button {
  position: absolute;
  top: .75rem;
  right: .75rem;
  background: #000;
  border: none;
  font-size: 1.1rem;
  color: #fff;
  border-radius: .375rem;
  padding: .25rem .45rem;
  z-index: 10;
  cursor: pointer;
}
.close-button:hover { opacity: .9; }

/* ===== Transiciones ===== */
.slide-enter-active, .slide-leave-active { transition: transform .3s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }

.fade-enter-active, .fade-leave-active { transition: opacity .25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ===== Utilidades para truncado dentro de flex/grid ===== */
.min-w-0 { min-width: 0 !important; }

</style>
