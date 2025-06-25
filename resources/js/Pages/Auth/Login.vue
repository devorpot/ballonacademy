<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { route } from 'ziggy-js'; // 👈 Esta es la forma correcta

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <div class="card shadow-sm">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-3 fw-normal">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </h1>
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            v-model="form.email"
                            :class="{ 'is-invalid': form.errors.email }"
                            required
                            autofocus
                            autocomplete="username"
                        >
                        <div class="invalid-feedback" v-if="form.errors.email">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            v-model="form.password"
                            :class="{ 'is-invalid': form.errors.password }"
                            required
                            autocomplete="current-password"
                        >
                        <div class="invalid-feedback" v-if="form.errors.password">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="remember"
                            v-model="form.remember"
                        >
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button 
                            type="submit" 
                            class="btn btn-primary"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                        </button>
                    </div>

                    <div class="mt-3 text-center">
                        
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-muted">
                ¿No tienes una cuenta? 
                
            </p>
        </div>
    </GuestLayout>
</template>