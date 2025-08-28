<template>
  <aside class="sidebar-menu shadow-lg bg-white d-flex flex-column align-items-center px-0 py-4">
    <!-- Avatar -->
    <div class="avatar mb-3 position-relative">
      <span class="avatar-halo"></span>

      <!-- Imagen cuando hay sesión -->
      <img
        v-if="isAuthenticated"
        :src="avatarSrc"
        alt="Avatar"
        class="rounded-circle shadow"
        width="100"
        height="100"
      />

      <!-- Placeholder cuando NO hay sesión -->
      <div
        v-else
        class="rounded-circle shadow d-flex align-items-center justify-content-center"
        style="width: 100px; height: 100px; background: #e5e7eb; border: 3.5px solid #fff;"
        aria-label="Invitado"
      >
        ?
      </div>
    </div>

    <!-- Datos del usuario -->
    <div class="text-center w-100 mb-4">
      <h5 class="user-name fw-bold mb-1 text-primary">{{ firstName }}</h5>
      <span class="user-nickname text-secondary">@{{ nickname }}</span>
    </div>

    <!-- Menú de navegación -->
    <nav class="w-100 px-3">
      <ul class="nav flex-column gap-2">
          <li class="nav-item">
          <Link
            :href="route('dashboard.security.edit')"
            class="btn btn-nav"
            :class="{ active: $page.url.startsWith('/dashboard/security') }"
          >
            <i class="bi bi-person-circle me-2"></i>
            {{ L.profile?.security || 'Seguridad' }}
          </Link>
        </li>
        <li class="nav-item">
          <Link
            :href="route('dashboard.profile.edit')"
            class="btn btn-nav"
            :class="{ active: $page.url.startsWith('/dashboard/profile') }"
          >
            <i class="bi bi-person-circle me-2"></i>
            {{ L.profile?.myProfile || 'Mi Perfil' }}
          </Link>
        </li>
        <li class="nav-item">
          <Link
            :href="route('dashboard.distributors.index')"
            class="btn btn-nav"
            :class="{ active: $page.url.startsWith('/dashboard/distributors') }"
          >
            <i class="bi bi-person-circle me-2"></i>
            {{ L.profile?.btnDistributors || 'Distribuidores' }}
          </Link>
        </li>
        <li class="nav-item">
          <Link
            :href="route('dashboard.courses.index')"
            class="btn btn-nav"
            :class="{ active: $page.url.startsWith('/dashboard/courses') }"
          >
            <i class="bi bi-journal-bookmark-fill me-2"></i>
            {{ L.nav?.courses || 'Cursos' }}
          </Link>
        </li>
        <li class="nav-item">
          <Link :href="route('dashboard.profile.edit')" class="btn btn-nav">
            <i class="bi bi-pencil-square me-2"></i> Blog
          </Link>
        </li>
      </ul>
    </nav>

    <!-- Curso actual -->
    <div class="current-course w-100 mt-auto pt-4 border-top text-center px-3">
      <div class="fw-semibold text-secondary mb-1" style="font-size: 0.95rem;">Curso actual</div>
      <div class="text-primary fw-bold" style="font-size: 1.07rem;">
        No seleccionado
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const page = usePage()

// User seguro y banderas
const user = computed(() => page.props?.auth?.user ?? null)
const isAuthenticated = computed(() => !!user.value)

// Perfil y fallbacks
const profile = computed(() => user.value?.profile ?? null)

const avatarSrc = computed(() => {
  const img = profile.value?.profile_image
  return img ? `/storage/${img}` : 'https://placehold.co/300x180?text=Sin+imagen'
})

const firstName = computed(() => {
  return profile.value?.firstname || user.value?.name || 'Invitado'
})

const nickname = computed(() => {
  if (profile.value?.nickname) return profile.value.nickname
  const email = user.value?.email
  return email ? email.split('@')[0] : 'usuario'
})

// Traducciones seguras
const L = computed(() => page.props?.L ?? {})
</script>

<style scoped>
.sidebar-menu {
  height: 100vh;
  width: 100%;
  min-width: 300px;
  border-radius: 2rem 0 0 2rem;
  box-shadow: 0 8px 32px #00205716;
  background: #0063AF!important;
  position: relative;
  margin-top: 1rem;

}


 
.avatar {
  width: 110px;
  height: 110px;
  margin-bottom: 1rem;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.avatar-halo {
  position: absolute;
  z-index: 0;
  top: 4px;
  left: 4px;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(120deg, #e0e7ff 10%, #f1f5f9 80%);
  filter: blur(8px);
  opacity: 0.65;
  pointer-events: none;
}
.avatar img {
  border: 3.5px solid #fff;
  z-index: 2;
  object-fit: cover;
  background: #e5e7eb;
  box-shadow: 0 2px 12px #678ad522;
  position: relative;
}

.user-name {
  font-size: 1.25rem;
  color: #FFFFFF!important;
  letter-spacing: 0.01em;
}
.user-nickname {
  font-size: 0.98rem;
  color: #FFFFFF!important;
  font-weight: 500;
}

.nav { width: 100%; }
.btn-nav {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  width: 100%;
  background: #3584E4;
  color: #FFFFFF;
  padding: 0.68rem 1rem;
  border: none;
  border-radius: 0.85rem;
  transition: background 0.17s, color 0.14s, box-shadow 0.15s;
  font-size: 1.05rem;
  font-weight: 500;
  letter-spacing: 0.01em;
  box-shadow: 0 1.5px 7px #e3eaf333;
}
.btn-nav i { font-size: 1.2rem; }
.btn-nav.active,
.btn-nav:focus,
.btn-nav:hover {
  background: #D4A744;
  color: #fff !important;
  box-shadow: 0 2px 10px #2563eb22;
  outline: none;
}

.current-course {
  font-size: 0.92rem;
  border-top: 1.5px solid #e3eaf3;
  padding-top: 1.15rem;
  margin-top: 2.2rem;
  background: transparent;
}

@media (max-width: 1200px) {
  .sidebar {
    border-radius: 0;
    box-shadow: 0 2px 16px #00205711;
  }
  .avatar, .avatar-halo { width: 88px; height: 88px; }
  .avatar-halo { top: 2px; left: 2px; width: 84px; height: 84px; }
  .avatar img { width: 84px !important; height: 84px !important; }
}
</style>
