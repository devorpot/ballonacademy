<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="auth-page">
        <Head>
            <slot name="head" />
        </Head>

        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">
                            <i class="bi bi-shield-lock me-2"></i>
                            Nombre de tu App
                        </h2>
                    </div>
                    
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.auth-page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 20px;
    background-color: #f8f9fa;
}
</style>