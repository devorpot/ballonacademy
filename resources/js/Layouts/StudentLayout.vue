<!-- resources/js/Layouts/StudentLayout.vue -->
<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue'
import { Head, usePage, router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import axios from 'axios'
 

/* ===== Estado global de la página ===== */
const page = usePage()
const transientWelcome = ref(null)

/* ===== Precarga basada en AdminLayout (sin nprogress) ===== */
const isLoading = ref(false)

/** Cierra y limpia cualquier overlay/backdrop de Bootstrap que haya quedado abierto */
function closeAllBootstrapOverlays() {
  try {
    // Cerrar instancias activas
    document.querySelectorAll('.modal.show').forEach(el => {
      bootstrap.Modal.getInstance(el)?.hide()
    })
    document.querySelectorAll('.offcanvas.show').forEach(el => {
      bootstrap.Offcanvas.getInstance(el)?.hide()
    })
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
      bootstrap.Dropdown.getInstance(el)?.hide()
    })
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      bootstrap.Tooltip.getInstance(el)?.dispose()
    })
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
      bootstrap.Popover.getInstance(el)?.dispose()
    })

    // Remover backdrops huérfanos
    document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(el => el.remove())

    // Restaurar clases/estilos que bloquean el scroll
    document.body.classList.remove('modal-open')
    document.body.style.overflow = ''
    document.body.style.paddingRight = ''
    document.documentElement.style.overflow = ''
  } catch {}
}

function start() {
  // Antes de cambiar de ruta, cierra todo lo abierto
  closeAllBootstrapOverlays()
  isLoading.value = true
  document.body.classList.add('route-changing')
}
function finish() {
  isLoading.value = false
  document.body.classList.remove('route-changing')
  // Limpieza por si quedó algo pendiente
  closeAllBootstrapOverlays()
}

onMounted(() => {
  if (page.props?.welcome) {
    transientWelcome.value = page.props.welcome
    setTimeout(() => { transientWelcome.value = null }, 3000)
  }

  router.on('start', start)
  router.on('finish', finish)
  router.on('invalid', finish)
  router.on('error', finish)

  // Si el usuario navega con el historial, limpiar overlays
  window.addEventListener('popstate', closeAllBootstrapOverlays)
})

onBeforeUnmount(() => {
  closeAllBootstrapOverlays()
  window.removeEventListener('popstate', closeAllBootstrapOverlays)
  document.documentElement.style.overflow = ''
  document.body.style.overflow = ''
})

/* ===== Sidebar móvil ===== */
const showMobileSidebar = ref(false)
function toggleSidebar() {
  showMobileSidebar.value = !showMobileSidebar.value
}
watch(showMobileSidebar, (open) => {
  if (open) {
    document.documentElement.style.overflow = 'hidden'
    document.body.style.overflow = 'hidden'
  } else {
    document.documentElement.style.overflow = ''
    document.body.style.overflow = ''
  }
})

/* ===== Datos de usuario para Topbar/Sidebar ===== */
const user = computed(() => page.props?.auth?.user ?? null)
const isAuthenticated = computed(() => !!user.value)
const profile = computed(() => user.value?.profile ?? null)

const avatarSrc = computed(() => {
  const img = profile.value?.profile_image
  return img ? `/storage/${img}` : 'https://placehold.co/300x180?text=Sin+imagen'
})
const firstName = computed(() => profile.value?.firstname || user.value?.name || 'Invitado')
const nickname = computed(() => {
  if (profile.value?.nickname) return profile.value.nickname
  const email = user.value?.email
  return email ? email.split('@')[0] : 'usuario'
})

/* ===== Topbar: idioma y avatar pequeño ===== */
const userName = computed(() => user.value?.profile?.nickname ?? user.value?.name ?? 'Invitado')
const userLocale = computed(() => user.value?.locale ?? 'es')
const profileImage = computed(() => {
  const img = user.value?.profile?.profile_image
  return img ? `/storage/${img}` : 'https://placehold.co/300x180?text=Sin+imagen'
})

/* Traducciones (si tu template usa L) */
const L = computed(() => page.props?.L ?? {})

/* Idioma */
const showLangMenu = ref(false)
const changingLocale = ref(false)
const languages = { es: 'Español', en: 'English' }

async function changeLocale(locale) {
  try {
    changingLocale.value = true
    await axios.post(route('dashboard.user.update-locale'), { locale })
    window.location.reload()
  } catch (error) {
    console.error('Error al cambiar idioma', error)
  } finally {
    changingLocale.value = false
  }
}
</script>

<template>
  <div class="layout-wrapper">
    <Head><slot name="head" /></Head>

    <!-- Overlay de precarga global -->
    <transition name="fade">
      <div v-if="isLoading" class="route-overlay d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" aria-label="Cargando..."></div>
      </div>
    </transition>

    <!-- Topbar integrado -->
    <nav class="top-nav navbar-card d-flex justify-content-between align-items-center px-3 py-2">
      <div class="d-flex align-items-center gap-2">
        <Link :href="route('dashboard.index')">
          <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-internacional.png"
               alt="Academia"
               style="max-width:110px;" />
        </Link>
      </div>

      <div class="d-flex align-items-center gap-3">
        <!-- Idioma -->
        <div class="position-relative">
          <button class="btn-icon" @click="showLangMenu = !showLangMenu" aria-haspopup="true" :aria-expanded="showLangMenu">
            <i class="bi bi-globe2 fs-5"></i>
          </button>
          <div v-if="showLangMenu"
               class="lang-dropdown shadow-sm border rounded-2 bg-white position-absolute mt-2 end-0 z-10">
            <button
              v-for="(label, code) in languages"
              :key="code"
              class="dropdown-item small text-start w-100"
              :class="{ 'fw-bold text-primary': userLocale === code }"
              @click="changeLocale(code)"
            >
              {{ label }}
            </button>
          </div>
        </div>

        <!-- Usuario -->
        <div class="d-flex align-items-center gap-2 user-menu" v-if="isAuthenticated">
          <div class="user-avatar">
            <img :src="profileImage" alt="Avatar" class="rounded-circle shadow" width="40" height="40" />
          </div>
          <span class="fw-medium d-none d-md-inline">{{ userName }}</span>
          <Link :href="route('dashboard.profile.edit')" class="btn btn-profile" title="Editar perfil">
            <i class="bi bi-gear fs-6"></i>
          </Link>
        </div>
        <div class="d-flex align-items-center gap-2 user-menu" v-else>
          <div class="user-avatar">?</div>
          <span class="fw-medium d-none d-md-inline">Invitado</span>
        </div>

        <!-- Logout -->
        <Link
          v-if="isAuthenticated"
          :href="route('logout')"
          method="post"
          as="button"
          class="btn btn-logout ms-2"
          title="Cerrar sesión"
        >
          <i class="bi bi-box-arrow-right me-1"></i>
          <span class="d-none d-sm-inline">Salir</span>
        </Link>

        <!-- Botón menú móvil -->
        <button class="menu-toggle-btn d-xl-none" @click="toggleSidebar" aria-label="Abrir menú">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </nav>

    <!-- Cuerpo (sidebar sticky + contenido scrolleable) -->
    <Transition name="fade" mode="out-in">
      <div :key="$page.component" class="layout-body">
        <!-- Sidebar escritorio integrado -->
        <aside class="sidebar d-none d-xl-block" aria-label="Navegación lateral">
          <aside class="sidebar-menu shadow-lg bg-white d-flex flex-column align-items-center px-0 py-5">
            <!-- Avatar -->
            <div class="avatar mb-3 position-relative">
              <span class="avatar-halo"></span>
              <img v-if="isAuthenticated" :src="avatarSrc" alt="Avatar" class="rounded-circle shadow" width="100" height="100" />
              <div v-else class="rounded-circle shadow d-flex align-items-center justify-content-center"
                   style="width: 100px; height: 100px; background: #e5e7eb; border: 3.5px solid #fff;"
                   aria-label="Invitado">?</div>
            </div>

            <!-- Datos -->
            <div class="text-center w-100 mb-4">
              <h5 class="user-name fw-bold mb-1 text-primary">{{ firstName }}</h5>
              <span class="user-nickname text-secondary">@{{ nickname }}</span>
            </div>

            <!-- Menú -->
            <nav class="w-100 px-3">
              <ul class="nav flex-column gap-2">
                <li class="nav-item">
                  <Link :href="route('dashboard.profile.edit')"
                        class="btn btn-nav"
                        :class="{ active: $page.url.startsWith('/dashboard/profile') }">
                    <i class="bi bi-person-circle me-2"></i>
                    {{ L.profile?.myProfile || 'Mi Perfil' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.courses.index')"
                        class="btn btn-nav"
                        :class="{ active: $page.url.startsWith('/dashboard/courses') }">
                    <i class="bi bi-journal-bookmark-fill me-2"></i>
                    {{ L.nav?.courses || 'Cursos' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.extras.index')"
                        class="btn btn-nav"
                        :class="{ active: $page.url.startsWith('/dashboard/extras') }">
                    <i class="bi bi-person-circle me-2"></i>
                    {{ L.profile?.extras || 'Clases Extra' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.distributors.index')"
                        class="btn btn-nav"
                        :class="{ active: $page.url.startsWith('/dashboard/distributors') }">
                    <i class="bi bi-person-circle me-2"></i>
                    {{ L.profile?.btnDistributors || 'Distribuidores' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.profile.edit')" class="btn btn-nav">
                    <i class="bi bi-pencil-square me-2"></i> Blog
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.security.edit')"
                        class="btn btn-nav"
                        :class="{ active: $page.url.startsWith('/dashboard/security') }">
                    <i class="bi bi-person-circle me-2"></i>
                    {{ L.profile?.security || 'Seguridad' }}
                  </Link>
                </li>
              </ul>
            </nav>
          </aside>
        </aside>

        <!-- Contenido -->
        <main class="layout-content dashboard-user">
          <slot />
        </main>
      </div>
    </Transition>

    <!-- Sidebar móvil integrado -->
    <Transition name="slide">
      <div v-if="showMobileSidebar" class="mobile-sidebar-overlay" @click="toggleSidebar" role="presentation">
        <div class="mobile-sidebar" @click.stop aria-label="Menú lateral">
          <button class="close-button" @click="toggleSidebar" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
          </button>

          <!-- Misma estructura del sidebar -->
          <aside class="sidebar-menu shadow-lg bg-white d-flex flex-column align-items-center px-0 py-5">
            <div class="avatar mb-3 position-relative">
              <span class="avatar-halo"></span>
              <img v-if="isAuthenticated" :src="avatarSrc" alt="Avatar" class="rounded-circle shadow" width="100" height="100" />
              <div v-else class="rounded-circle shadow d-flex align-items-center justify-content-center"
                   style="width: 100px; height: 100px; background: #e5e7eb; border: 3.5px solid #fff;" aria-label="Invitado">?</div>
            </div>
            <div class="text-center w-100 mb-4">
              <h5 class="user-name fw-bold mb-1 text-primary">{{ firstName }}</h5>
              <span class="user-nickname text-secondary">@{{ nickname }}</span>
            </div>
            <nav class="w-100 px-3">
              <ul class="nav flex-column gap-2">
                <li class="nav-item">
                  <Link :href="route('dashboard.profile.edit')" class="btn btn-nav" :class="{ active: $page.url.startsWith('/dashboard/profile') }" @click="toggleSidebar">
                    <i class="bi bi-person-circle me-2"></i>{{ L.profile?.myProfile || 'Mi Perfil' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.courses.index')" class="btn btn-nav" :class="{ active: $page.url.startsWith('/dashboard/courses') }" @click="toggleSidebar">
                    <i class="bi bi-journal-bookmark-fill me-2"></i>{{ L.nav?.courses || 'Cursos' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.extras.index')" class="btn btn-nav" :class="{ active: $page.url.startsWith('/dashboard/extras') }" @click="toggleSidebar">
                    <i class="bi bi-person-circle me-2"></i>{{ L.profile?.extras || 'Clases Extra' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.distributors.index')" class="btn btn-nav" :class="{ active: $page.url.startsWith('/dashboard/distributors') }" @click="toggleSidebar">
                    <i class="bi bi-person-circle me-2"></i>{{ L.profile?.btnDistributors || 'Distribuidores' }}
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.profile.edit')" class="btn btn-nav" @click="toggleSidebar">
                    <i class="bi bi-pencil-square me-2"></i> Blog
                  </Link>
                </li>
                <li class="nav-item">
                  <Link :href="route('dashboard.security.edit')" class="btn btn-nav" :class="{ active: $page.url.startsWith('/dashboard/security') }" @click="toggleSidebar">
                    <i class="bi bi-person-circle me-2"></i>{{ L.profile?.security || 'Seguridad' }}
                  </Link>
                </li>
              </ul>
            </nav>
          </aside>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style lang="less" scoped>
/* Variables y paleta */
@panel: #0063AF;
@primary: #3584E4;
@accent: #D4A744;
@text: #FFFFFF;

/* Precarga */
.fade-enter-active, .fade-leave-active { transition: opacity .35s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.route-overlay{
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, .35);
  z-index: 2000;
  backdrop-filter: blur(1px);
}

/* Topbar */
.top-nav.navbar-card {
  background: #042B60;
  box-shadow: 0 2px 16px 0 #00205718;
  height: 66px;
  min-height: 60px;
  position: relative;
  z-index: 100;
}
.btn-icon {
  background: @accent !important;
  border: none;
  color: #FFF;
  transition: color 0.18s, background 0.18s;
  padding: 0.5rem 0.7rem;
  border-radius: 50%;
  &:hover { background: #f1f5f9; color: #1c478c; }
}
.user-menu { color:#FFF; border-radius: 2rem; padding: .35rem .95rem; gap: .45rem; box-shadow: 0 1px 3px #dde8f433; }
.user-avatar { color:#FFF; border-radius: 50%; border: 1.5px solid #e2e8f0; width: 34px; height: 34px; display:flex; align-items:center; justify-content:center; font-size: 1.1rem; }
.btn-profile {
  background: @accent !important; border: none; color:#FFF; font-size:1.05rem; transition: background .16s;
  padding:.3rem .7rem; border-radius: 50%;
  &:hover { background: #f1f5f9; color:#1c478c; }
}
.btn-logout {
  background: #A51605; color:#FFF; border: none; border-radius: 1.4rem;
  padding: .46rem 1.2rem; font-weight: 500; font-size: 1rem; transition: background .15s, color .14s; margin-left: .7rem;
  &:hover { background: #ffe0e3; color: #b30000; }
}
.lang-dropdown {
  background: @panel !important; min-width: 150px; right: 0; top: 100%; padding: .5rem;
  .dropdown-item { border-radius: .375rem; padding: .3rem .75rem; color:#FFF !important;
    &:hover { background-color: #0D6EFD; }
  }
}
/* Botón menú móvil en topbar */
.menu-toggle-btn {
  background: transparent; border: none; font-size: 1.25rem; padding: .25rem .5rem; color: #fff; cursor: pointer;
}

/* Layout grid */
.layout-body{
  display: grid;
  grid-template-columns: 360px 1fr;
  min-height: calc(100dvh - 66px);
}
@media (max-width: 1200px) {
  .layout-body{ grid-template-columns: 1fr; }
}

/* Sidebar escritorio (contenedor sticky) */
.sidebar{
  position: sticky;
  top: 66px;
  align-self: start;
  height: calc(100dvh - 66px);
  overflow: hidden;
  border-right: 1px solid rgba(0,0,0,.06);
}

/* Sidebar UI */
.sidebar-menu {
  height: 100%;
  width: 100%;
  min-width: 300px;
  border-radius: 2rem 0 0 2rem;
  box-shadow: 0 8px 32px #00205716;
  background: @panel !important;
  position: relative;
  margin-top: -0.9rem;

  .avatar {
    width: 110px; height: 110px; margin-bottom: 1rem; position: relative; display: flex; align-items: center; justify-content: center;
    &-halo {
      position: absolute; z-index: 0; top: 4px; left: 4px; width: 100px; height: 100px; border-radius: 50%;
      background: linear-gradient(120deg, #e0e7ff 10%, #f1f5f9 80%); filter: blur(8px); opacity: .65; pointer-events: none;
    }
    img {
      border: 3.5px solid #fff; z-index: 2; object-fit: cover; background: #e5e7eb; box-shadow: 0 2px 12px #678ad522; position: relative; border-radius: 50%;
    }
  }
  .user-name { font-size: 1.25rem; color: #FFFFFF !important; letter-spacing: .01em; }
  .user-nickname { font-size: .98rem; color: #FFFFFF !important; font-weight: 500; }
  .nav { width: 100%; }

  .btn-nav {
    display: flex; align-items: center; gap: .3rem; width: 100%;
    background: @primary; color:#FFFFFF; padding: .68rem 1rem; border: none; border-radius: .85rem;
    transition: background .17s, color .14s, box-shadow .15s; font-size: 1.05rem; font-weight: 500; letter-spacing: .01em;
    box-shadow: 0 1.5px 7px #e3eaf333;
    i { font-size: 1.2rem; }
    &.active, &:focus, &:hover { background: @accent; color: #fff !important; box-shadow: 0 2px 10px #2563eb22; outline: none; }
  }
}

/* Contenido scrollable */
.layout-content{
  min-height: calc(100dvh - 66px);
  overflow: auto;
  -webkit-overflow-scrolling: touch;
  scroll-behavior: smooth;
  scroll-padding-top: 1rem;
  background: #f8f9fa;
}

/* Sidebar móvil (offcanvas) */
.slide-enter-active, .slide-leave-active { transition: transform .25s ease, opacity .25s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(100%); opacity: 0; }

.mobile-sidebar-overlay{
  position: fixed; inset: 0; background: rgba(15,15,15,.35); z-index: 2000; display: flex; justify-content: flex-end;
}
.mobile-sidebar{
  position: absolute; right: 0; top: 0; width: min(86vw, 320px); height: 100%;
  background: @panel !important; border-left: 1px solid rgba(0,0,0,.08);
  padding: 1rem; overflow-y: auto;
}
.close-button{
  position: absolute; top: .5rem; right: .75rem; border: none; background: #000; color: #fff; font-size: 1.1rem;
  border-radius: .375rem; padding: .25rem .45rem; z-index: 10; cursor: pointer;
  &:hover { opacity: .9; }
}

/* Mobile ajustes visuales */
@media (max-width: 600px) {
  .top-nav.navbar-card { padding: .2rem .5rem; margin: 0; border-radius: 0 0 1.2rem 1.2rem; }
}
</style>
