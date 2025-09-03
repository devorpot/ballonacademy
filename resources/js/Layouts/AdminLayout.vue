<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import Navbar from '@/Components/Admin/Ui/Navbar.vue'
import Footer from '@/Components/Admin/Ui/Footer.vue'

const isLoading = ref(false)

function start() {
  isLoading.value = true
  document.body.classList.add('route-changing')
}
function finish() {
  isLoading.value = false
  document.body.classList.remove('route-changing')
}

onMounted(() => {
  router.on('start', start)
  router.on('finish', finish)
  router.on('invalid', finish)
  router.on('error', finish)
})
</script>

<template>
  <div class="admin">
    <Head>
      <slot name="head" />
    </Head>

    <Navbar />

    <!-- Overlay de precarga -->
    <transition name="fade">
      <div v-if="isLoading" class="route-overlay d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" aria-label="Cargando..."></div>
      </div>
    </transition>

    <Transition name="fade" mode="out-in" appear>
      <div :key="$page.url">
        <main class="main">
          <slot />
        </main>
      </div>
    </Transition>

    <Footer />
  </div>
</template>


<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Public+Sans:ital,wght@0,100..900;1,100..900&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap');

body{
  font-family: 'Public Sans',sans-serif!important;
}

.navbar a{
  text-transform: uppercase!important;
  font-weight:600!important;
}

table thead th{
  text-transform:uppercase;
  font-weight:400!important;
  font-size: 0.9rem!important;
}
table td{
  font-size: 0.9rem!important;
}

/* Estado durante cambio de ruta */
body.route-changing { overflow-y: auto; }

/* Transición global */
.fade-enter-active, .fade-leave-active { transition: opacity .35s ease, transform .35s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Overlay de carga */
.route-overlay{
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, .35); /* semi-transparente */
  z-index: 2000; /* por encima del contenido y navbar */
  backdrop-filter: blur(1px);
}

/* NProgress (ya con tu color) */
#nprogress .bar { background: #28a745 !important; }
#nprogress .peg { box-shadow: 0 0 10px #28a745, 0 0 5px #28a745 !important; }
#nprogress .spinner-icon {
  border-top-color: #28a745 !important;
  border-left-color: #28a745 !important;
}
</style>
