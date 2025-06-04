<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js'
import Navbar from '@/Partials/Admin/Navbar.vue'
import Footer from '@/Partials/Admin/Footer.vue'
</script>

<template>
    <div>
        <Head>
            <slot name="head" />
        </Head>

        <Navbar />
        <!-- Contenido principal -->
        <main class="main">
            <slot />
        </main>

       <Footer />
    </div>
</template>