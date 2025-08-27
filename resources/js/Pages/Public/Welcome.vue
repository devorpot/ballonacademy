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

// Assets (podrías moverlos a tu config si lo prefieres)
const LOGO_URL = 'https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-internacional.png'
const HERO_IMG = 'https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/card-pba_hover.png'
const LOGO_FABRICAS = 'https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-fabricas.png'
const LOGO_PAYASO = 'https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-globos-payaso.png'
const LOGO_ANAFAG = 'https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-anafag.png'
</script>

<template>
  <GuestLayout>
    <Head title="Completar registro" />

    <section class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-8">

          <!-- Card principal -->
          <div class="card shadow-sm welcome-card overflow-hidden">
            <!-- Header con logo -->
            <div class="card-header text-center py-4 header-brand">
              <img :src="LOGO_URL" alt="Academia Internacional de Globos" class="brand-logo img-fluid" />
            </div>

            <!-- Contenido -->
            <div class="card-body p-4 p-md-5">
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

              <div class="small text-muted">
                <p class="mb-1">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                <p class="mb-0 text-wrap link-line">
                  <a :href="loginHref" class="link-brand">{{ loginHref }}</a>
                </p>
              </div>
            </div>

            <!-- Imagen decorativa -->
            <div class="hero-img-wrapper">
              <img :src="HERO_IMG" alt="Academia Internacional de Globos" class="w-100 d-block hero-img" />
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

              <!-- Logos -->
              <div class="d-flex justify-content-center align-items-center gap-3 logos-row">
                <img :src="LOGO_FABRICAS" alt="Fábricas" class="logo-mini" />
                <img :src="LOGO_PAYASO" alt="Globos Payaso" class="logo-mini" />
                <img :src="LOGO_ANAFAG" alt="ANAFAG" class="logo-mini" />
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

.welcome-card {
  border: 0;
  border-radius: 12px;
  overflow: hidden;
}

/* Header con logo */
.header-brand {
  background: @brand-deep;
  border: 0;

  .brand-logo {
    max-width: 220px;
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

/* Imagen hero */
.hero-img-wrapper {
  background: @brand-dark;
}
.hero-img {
  display: block;
  width: 100%;
  height: auto;
}

/* Footer */
.footer-brand {
  background: @brand-accent;
  color: @brand-dark;
  border: 0;
  padding: 28px;

  .logos-row .logo-mini {
    height: 36px;
    display: block;
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
