<script setup>
 import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
 import { route } from 'ziggy-js'
function logout() {
  router.post('/logout')
}
</script>

<template>
            <!-- Navbar -->
       <div class="w-100">
           <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <Link class="navbar-brand" :href="route('admin.dashboard')">
                    Panel de Administración
                </Link>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Usuarios
                            </Link>
                        </li>
                         
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('dashboard')">
                                <i class="bi bi-house-door me-1"></i> Ir al Dashboard
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('logout')" method="post">
                                <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

       </div>
      <div class="w-100">
           <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
   
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav w-100 d-flex justify-content-between"> 
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Usuarios
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Alumnos
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Maestros
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Certificados
                            </Link>
                        </li>
                         <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Cursos
                            </Link>
                        </li>
                         <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Suscripciones
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('admin.users.index')">
                                <i class="bi bi-people me-1"></i> Pagos
                            </Link>
                        </li>
                         
                    </ul>
               
                </div>
            </div>
        </nav>
      </div>

</template>