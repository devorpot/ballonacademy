<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({
  evaluation: Object
})
</script>

<template>
  <Head title="¡Evaluación Enviada!" />
  <StudentLayout>
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card shadow-lg text-center">
            <div class="card-body py-5">
              <h1 class="fw-bold mb-4" style="color: #1B8FD5;">
                ¡Gracias por enviar tu evaluación!
              </h1>
              <p class="fs-5 mb-4">
                Hemos recibido tus respuestas. <br>
                Pronto recibirás noticias de tu instructor sobre tu resultado.<br>
                Puedes continuar explorando tus cursos.
              </p>
              <Link 
                :href="route('dashboard.courses.index')"
                class="btn btn-primary btn-lg"
              >
                Volver a Mis Cursos
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>

<style scoped>
.card {
  margin-top: 3rem;
}
</style>
