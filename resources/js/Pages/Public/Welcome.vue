<script setup>
import { ref, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { route } from 'ziggy-js'

// Props esperadas desde el controlador Inertia
const props = defineProps({
  name: { type: String, default: '' },
  loginUrl: { type: String, default: '' }
})

const page = usePage()

// Fallback por si no te mandan loginUrl
const loginHref = computed(() => props.loginUrl || route('login'))

// Texto de bienvenida
const title = computed(() => `¡Bienvenido ${props.name || 'a la Academia'}!`)
</script>

<template>
  <GuestLayout>
    <Head title="Completar registro" />

    <section class="container ">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="hero py-3 text-center">
            <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-internacional.png" alt="">
          </div>

          <!-- Card principal -->
          <div class="card shadow-sm welcome-card overflow-hidden">
            <!-- Header con texto plano (antes era un logo) -->
            <div class="card-header text-center py-4 header-brand">
              <h2 class="mb-0 brand-title">International Balloons Academy</h2>
              <p class="mb-0 brand-subtitle">Plataforma de aprendizaje</p>
            </div>

            <!-- Contenido -->
            <div class="card-body p-4 p-md-5 text-center">
              <h1 class="h3 mb-3 text-brand-900">{{ title }}</h1>

              <p class="lead mb-2">
                Has completado tu registro en la <strong>Academia Internacional de Globos</strong>.
              </p>
              <p class="mb-4">
                Ya puedes acceder a tu cuenta y explorar todos los cursos disponibles.
              </p>

              <!-- Botón principal -->
              <div class="text-center my-4">
                <a :href="loginHref" class="btn btn-brand btn-lg px-4 fw-bold">
                  Ir al login
                </a>
              </div>

              <div class="small text-muted text-center">
                <p class="mb-1">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                <p class="mb-0 text-wrap link-line">
                  <a href="https://academiainternacionalglobos.com/login" class="link-brand">https://academiainternacionalglobos.com/login</a>
                </p>
              </div>
            </div>

            <!-- Sección decorativa en texto (antes imagen) -->
            <div class="hero-img-wrapper">
              <p class="hero-text m-0 py-3 text-center text-white">
                Aprende, practica y certifica tus habilidades en globoflexia profesional.
              </p>
            </div>

            <!-- Footer -->
            <div class="card-footer footer-brand text-center">
              <h3 class="h6 mb-1">CONTACTO</h3>
              <p class="mb-1 fw-semibold">contacto@academiainternacionalglobos.com</p>
              <p class="mb-2">
                <a href="https://wa.link/y73aps" class="fw-bold link-dark-brand">+52 332 834 3335</a>
              </p>

              <!-- Social -->
              <div class="d-flex justify-content-center gap-3 mb-3 social-links">
                <a href="https://www.facebook.com/AcademiaInternacionaldeGlobos" class="link-dark-brand">
                  <i class="bi bi-facebook me-1"></i> Facebook
                </a>
                <a href="https://www.instagram.com/academiainternacionalglobos/" class="link-dark-brand">
                  <i class="bi bi-instagram me-1"></i> Instagram
                </a>
                <a href="https://www.tiktok.com/@academiainterglobos" class="link-dark-brand">
                  <i class="bi bi-tiktok me-1"></i> TikTok
                </a>
                <a href="https://www.youtube.com/@AcademiaInternacionaldeGlobos" class="link-dark-brand">
                  <i class="bi bi-youtube me-1"></i> YouTube
                </a>
              </div>

              <!-- Logos reemplazados por texto plano -->
              <div class="d-flex justify-content-center align-items-center gap-3 logos-row flex-wrap">
                <span class="logo-text badge bg-light text-dark px-3 py-2">Fábricas</span>
                <span class="logo-text badge bg-light text-dark px-3 py-2">Globos Payaso</span>
                <span class="logo-text badge bg-light text-dark px-3 py-2">ANAFAG</span>
              </div>

              <p class="mt-3 mb-0 small">International Balloons Academy</p>
            </div>
          </div>

          <!-- Nota inferior -->
          <p class="text-center text-muted small mt-3">
            Si no solicitaste este correo, puedes ignorarlo.
          </p>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>

<style lang="less" scoped>
/* Paleta basada en el HTML original */
@brand-dark:  #0b2e57; // header/hero
@brand-deep:  #0B2B60; // header sólido
@brand-accent:#e4ae4e; // botón/footer
@text-muted:  #8a8a8a;


.hero{
  background:@brand-dark;
  border-radius:1rem;
  margin-bottom:1rem;
}

.welcome-card {
  border: 0;
  border-radius: 12px;
  overflow: hidden;
}

/* Header con texto */
.header-brand {
  background: @brand-deep;
  border: 0;
  color: #fff;

  .brand-title {
    font-weight: 700;
    letter-spacing: .3px;
  }
  .brand-subtitle {
    opacity: .9;
    font-size: .95rem;
  }
}

/* Tipografía */
.text-brand-900 {
  color: @brand-dark;
}

/* Botón */
.btn-brand {
  background: @brand-accent;
  border-color: @brand-accent;
  color: #fff;

  &:hover, &:focus {
    background: darken(@brand-accent, 7%);
    border-color: darken(@brand-accent, 7%);
    color: #fff;
  }
}

/* Enlaces */
.link-brand {
  color: @brand-dark;
  text-decoration: underline;

  &:hover {
    color: darken(@brand-dark, 8%);
  }
}

.link-dark-brand {
  color: @brand-dark;
  text-decoration: none;

  &:hover { text-decoration: underline; }
}

/* Sección hero en texto plano */
.hero-img-wrapper {
  background: @brand-dark;
}
.hero-text {
  font-size: 1rem;
  letter-spacing: .2px;
}

/* Footer */
.footer-brand {
  background: @brand-accent;
  color: @brand-dark;
  border: 0;
  padding: 28px;

  .logos-row .logo-text {
    border-radius: 999px;
    font-weight: 600;
  }
}

/* Utilidades */
.link-line {
  word-break: break-all;
}

@media (max-width: 575.98px) {
  .footer-brand {
    padding: 22px 18px;
  }
}
</style>
