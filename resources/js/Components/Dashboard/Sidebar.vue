<template>
  <aside class="sidebar shadow-sm bg-white d-flex flex-column align-items-center p-4">
    <!-- Avatar -->
    <div class="avatar mb-3">
      <img
         :src="user.profile.profile_image ? `/storage/${user.profile.profile_image}` : 'https://placehold.co/300x180?text=Sin+imagen'"
        alt="Avatar"
        class="rounded-circle shadow"
        width="100"
        height="100"
      />
    </div>


 
 
    <!-- Datos del usuario -->
    <div class="text-center w-100 mb-4">
      <h6 class="text-uppercase fw-semibold mb-1">{{ user.profile.firstname }}  </h6>
      <small class="text-muted">@{{ user.profile.nickname }}</small>
    </div>

    <!-- Menú de navegación -->
    <nav class="w-100">
      <ul class="nav flex-column gap-2">
        <li class="nav-item">
          <Link :href="route('dashboard.profile.edit')" class="btn btn-nav">
            <i class="bi bi-person-circle me-2"></i> Mi perfil
          </Link>
        </li>
        <li class="nav-item">
          <Link :href="route('dashboard.courses.index')" class="btn btn-nav">
            <i class="bi bi-journal-bookmark-fill me-2"></i> Cursos
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
    <div class="current-course w-100 mt-auto pt-4 border-top text-center">
     
    </div>
  </aside>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
const { props } = usePage()
const user = props.auth.user
</script>

<style scoped>
.sidebar {
  height: 100vh;
  width: 100%;
 
  border-right: 1px solid #dee2e6;
 
}

.avatar img {
  border: 2px solid #dee2e6;
  object-fit: cover;
}

/* Botón de navegación */
.btn-nav {
  display: flex;
  align-items: center;
  width: 100%;
  background-color: #EEE;
  color: #212529;
  padding: 0.5rem 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  text-align: center;
  transition: all 0.2s ease;
  font-size: 1rem;
  text-decoration: none;
  text-transform: uppercase;
  font-weight: bold;
 
}

.btn-nav:hover {
  background-color: #0d6efd;
  color: #fff;
  border-color: #0d6efd;
}

.btn-nav i {
  font-size: 1.1rem;
}

/* Curso actual */
.current-course {
  font-size: 0.85rem;
}

@media (min-width:1200px) {
   .sidebar{
      min-width:320px;

   }
}
@media (max-width:1200px) {
   .sidebar{
      width:320px;
     
   }
}

</style>
