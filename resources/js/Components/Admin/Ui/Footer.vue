<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js'
 
function logout() {
  router.post('/logout')
}
</script>

<template>
        <footer class="bg-light mt-5 py-3">
            <div class="container-fluid">
                <div class="text-center text-muted">
                    <small>Academia Internacional de Globos &copy; {{ new Date().getFullYear() }}</small>
                </div>
            </div>
        </footer>

    </template>