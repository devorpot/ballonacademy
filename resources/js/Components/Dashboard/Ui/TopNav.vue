<template>
  <nav class="top-nav navbar-card d-flex justify-content-between align-items-center px-3 py-2">
    <!-- Logo -->
    <div class="d-flex align-items-center gap-2">
      <div class="logo-circle shadow-sm">
        <img src="https://placehold.co/100x100" alt="Logo" height="38" />
      </div>
      <span class="fs-5 fw-semibold app-title d-none d-md-inline ms-2">Academia de Globos</span>
    </div>

    <!-- Menú derecho -->
    <div class="d-flex align-items-center gap-3">
      <!-- Selector de idioma -->
      <div class="position-relative">
        <button class="btn-icon" @click="showLangMenu = !showLangMenu" aria-haspopup="true" aria-expanded="showLangMenu">
          <i class="bi bi-globe2 fs-5"></i>
        </button>
        <div
          v-if="showLangMenu"
          class="lang-dropdown shadow-sm border rounded-2 bg-white position-absolute mt-2 end-0 z-10"
        >
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
          <img
            :src="profileImage"
            alt="Avatar"
            class="rounded-circle shadow"
            width="40"
            height="40"
          />
        </div>
        <span class="fw-medium d-none d-md-inline">{{ userName }}</span>
        <Link :href="route('dashboard.profile.edit')" class="btn btn-profile" title="Editar perfil">
          <i class="bi bi-gear fs-6"></i>
        </Link>
      </div>

      <!-- Si no hay sesión, mostramos un placeholder simple -->
      <div class="d-flex align-items-center gap-2 user-menu" v-else>
        <div class="user-avatar">?</div>
        <span class="fw-medium d-none d-md-inline">Invitado</span>
      </div>

      <!-- Logout -->
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        class="btn btn-logout ms-2"
        title="Cerrar sesión"
        v-if="isAuthenticated"
      >
        <i class="bi bi-box-arrow-right me-1"></i>
        <span class="d-none d-sm-inline">Salir</span>
      </Link>
    </div>
  </nav>

  <!-- Spinner global en body -->
  <teleport to="body">
    <SpinnerOverlay v-if="loading" :fullscreen="true" />
  </teleport>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'
import axios from 'axios'
import SpinnerOverlay from '@/Components/Dashboard/Ui/SpinnerOverlay.vue'

const page = usePage()

// user puede ser undefined durante el render inicial, hacemos fallback seguro
const user = computed(() => page.props?.auth?.user ?? null)
const isAuthenticated = computed(() => !!user.value)

const userName = computed(() => user.value?.name ?? 'Invitado')
const userLocale = computed(() => user.value?.locale ?? 'es')

const profileImage = computed(() => {
  const img = user.value?.profile?.profile_image
  return img ? `/storage/${img}` : 'https://placehold.co/300x180?text=Sin+imagen'
})

const showLangMenu = ref(false)
const loading = ref(false)

const languages = {
  es: 'Español',
  en: 'English'
}

async function changeLocale(locale) {
  try {
    loading.value = true
    await axios.post(route('dashboard.user.update-locale'), { locale })
    window.location.reload()
  } catch (error) {
    console.error('Error al cambiar idioma', error)
  } finally {
    loading.value = false
  }
}
</script>
<style scoped>
.top-nav.navbar-card {
  background: #fff;
  border-radius: 1.3rem;
  box-shadow: 0 2px 16px 0 #00205718;
  margin: 15px 16px 0 16px;
  height: 66px;
  min-height: 60px;
  position: relative;
  z-index: 100;
}
.logo-circle {
  width: 42px;
  height: 42px;
  background: #e9ecef;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 2px solid #007bff22;
}
.app-title {
  color: #2563eb;
  font-family: 'Montserrat', 'Roboto', Arial, sans-serif;
  letter-spacing: 0.02em;
}
.btn-icon {
  background: transparent;
  border: none;
  color: #2261a9;
  transition: color 0.18s;
  padding: 0.5rem 0.7rem;
  border-radius: 50%;
}
.btn-icon:hover,
.btn-profile:hover {
  background: #f1f5f9;
  color: #1c478c;
}
.user-menu {
  background: #f8fafc;
  border-radius: 2rem;
  padding: 0.35rem 0.95rem 0.35rem 0.6rem;
  gap: 0.45rem;
  box-shadow: 0 1px 3px #dde8f433;
}
.user-avatar {
  color: #98a8c7;
  background: #fff;
  border-radius: 50%;
  border: 1.5px solid #e2e8f0;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.btn-profile {
  background: none;
  border: none;
  color: #2261a9;
  font-size: 1.05rem;
  transition: background 0.16s;
  padding: 0.3rem 0.7rem;
  border-radius: 50%;
}
.btn-logout {
  background: #e9ecef;
  color: #d92929;
  border: none;
  border-radius: 1.4rem;
  padding: 0.46rem 1.2rem;
  font-weight: 500;
  font-size: 1rem;
  transition: background 0.15s, color 0.14s;
  margin-left: 0.7rem;
}
.btn-logout:hover {
  background: #ffe0e3;
  color: #b30000;
}

.lang-dropdown {
  min-width: 150px;
  right: 0;
  top: 100%;
  padding: 0.5rem;
}
.lang-dropdown .dropdown-item {
  border-radius: 0.375rem;
  padding: 0.3rem 0.75rem;
}
.lang-dropdown .dropdown-item:hover {
  background-color: #f1f5f9;
}



@media (max-width: 600px) {
  .top-nav.navbar-card {
    padding: 0.2rem 0.5rem;
    margin: 0;
    border-radius: 0 0 1.2rem 1.2rem;
  }
  .app-title {
    display: none;
  }
}
</style>
