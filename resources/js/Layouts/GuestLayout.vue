<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="auth-page">
        <Head>
            <slot name="head" />
        </Head>

 
            
               
                    <slot />
              
   
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