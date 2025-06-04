
<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div>
        <Head>
            <slot name="head" />
        </Head>

        <!-- Navbar -->
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
                        <li class="nav-item">
                            <Link class="nav-link" :href="route('roles.index')">
                                <i class="bi bi-shield-lock me-1"></i> Roles
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

        <!-- Contenido principal -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-light mt-5 py-3">
            <div class="container-fluid">
                <div class="text-center text-muted">
                    <small>Sistema de Administración &copy; {{ new Date().getFullYear() }}</small>
                </div>
            </div>
        </footer>
    </div>
</template>