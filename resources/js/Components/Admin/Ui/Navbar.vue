<script setup>
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { onMounted, onBeforeUnmount } from 'vue'
import * as bootstrap from 'bootstrap'

// Logout
function logout() {
  router.post(route('logout'))
}

// Helpers de activo
const isActive    = (name) => route().current(name)
const isActiveAny = (names = []) => names.some(n => route().current(n))

let _dropdownRefs = []
let _offcanvas = null
let _collapses = [] // para submenús del sidebar

function closeSidebar() {
  try { _offcanvas?.hide() } catch {}
}

onMounted(() => {
  // 1) Inicializar dropdowns del nav inferior (tu código)
  const toggles = document.querySelectorAll('.navbar [data-bs-toggle="dropdown"]')
  const mqLg = window.matchMedia('(min-width: 992px)')

  toggles.forEach((el) => {
    const dd = new bootstrap.Dropdown(el, { popperConfig: { strategy: 'fixed' } })
    const parent = el.closest('.dropdown')

    const showOnHover = () => { if (mqLg.matches) dd.show() }
    const hideOnLeave = () => { if (mqLg.matches) dd.hide() }

    parent?.addEventListener('mouseenter', showOnHover)
    parent?.addEventListener('mouseleave', hideOnLeave)

    _dropdownRefs.push({ dd, parent, showOnHover, hideOnLeave })
  })

  // 2) Inicializar Offcanvas del Sidebar móvil
const sbEl = document.getElementById('adminSidebar')
  if (sbEl) {
    _offcanvas = bootstrap.Offcanvas.getOrCreateInstance(sbEl, {
      scroll: true,
      backdrop: true
    })

    // Limpieza defensiva cuando termina de cerrar
    sbEl.addEventListener('hidden.bs.offcanvas', () => {
      // Si por algún motivo quedara un backdrop, quitarlo
      document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove())
      // Reestablecer estilos de body
      document.body.classList.remove('offcanvas-backdrop', 'offcanvas-open')
      document.body.style.removeProperty('overflow')
    })
  }

  // 3) Inicializar colapsables internos del sidebar (submenús)
  const collapseIds = ['sbStudents', 'sbCourses', 'sbEvaluations']
  collapseIds.forEach(id => {
    const el = document.getElementById(id)
    if (el) {
      const c = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false })
      _collapses.push(c)
    }
  })

  // 4) Siempre cerrar el sidebar al empezar una navegación Inertia
  router.on('start', () => closeSidebar())
})

onBeforeUnmount(() => {
  _dropdownRefs.forEach(({ dd, parent, showOnHover, hideOnLeave }) => {
    try { dd.hide() } catch {}
    parent?.removeEventListener('mouseenter', showOnHover)
    parent?.removeEventListener('mouseleave', hideOnLeave)
  })
  _dropdownRefs = []
  _collapses = []
  _offcanvas = null
})
</script>


<template>
  <!-- NAV SUPERIOR -->
  <div class="w-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary ">
      <div class="container-fluid" style="max-width:1600px;">
        <Link class="navbar-brand" :href="route('admin.dashboard')">
          <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-internacional.png" alt="Academia Internacional de globos" style="width:90px; height:auto;" />
        </Link>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#topbarNav"
          aria-controls="topbarNav"
          aria-expanded="false"
          aria-label="Alternar navegación"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <Link
                class="nav-link"
                :class="{ active: isActive('admin.dashboard') }"
                :href="route('admin.dashboard')"
              >
                <i class="bi bi-speedometer2 me-1"></i> Ir al Dashboard
              </Link>
            </li>
            <li class="nav-item">
              <Link class="nav-link" :href="route('logout')" method="post" as="button">
                <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>

  <!-- NAV INFERIOR -->
  <div class="w-100">
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg">
      <div class="container-fluid" style="max-width:1600px;">

        <!-- Botón SOLO móviles: abre el sidebar -->
        <button
          class="btn btn-outline-light d-lg-none"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#adminSidebar"
          aria-controls="adminSidebar"
        >
          <i class="bi bi-list "></i>
        </button>

        <!-- Menú horizontal en escritorio -->
        <div class="collapse navbar-collapse d-none d-lg-block">
          <ul class="navbar-nav d-flex flex-row w-100">
             <li class="nav-item flex-fill text-center">
              <Link
                class="nav-link"
                :class="{ active: isActive('admin.activities.index') }"
                :href="route('admin.activities.index')"
              >
                <i class="bi bi-people me-1"></i> Actividad
              </Link>
            </li>
            <!-- Usuarios -->
            <li class="nav-item flex-fill text-center">
              <Link
                class="nav-link"
                :class="{ active: isActive('admin.users.index') }"
                :href="route('admin.users.index')"
              >
                <i class="bi bi-people me-1"></i> Usuarios
              </Link>
            </li>

            <!-- Estudiantes -->
            <li class="nav-item flex-fill text-center dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                id="navStudents"
                role="button"
                data-bs-toggle="dropdown"
                data-bs-display="static"
                aria-expanded="false"
                :class="{ active: isActiveAny(['admin.students.*','admin.activations.*']) }"
                @click.prevent
              >
                <i class="bi bi-mortarboard me-1"></i> Estudiantes
              </a>
              <ul class="dropdown-menu" aria-labelledby="navStudents">
                <li>
                  <Link class="dropdown-item" :href="route('admin.students.index')">
                    <i class="bi bi-list-ul me-2"></i> Todos los estudiantes
                  </Link>
                </li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.students.create')">
                    <i class="bi bi-plus-circle me-2"></i> Crear estudiante
                  </Link>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.activations.index')">
                    <i class="bi bi-key me-2"></i> Activaciones
                  </Link>
                </li>
              </ul>
            </li>

            <!-- Cursos -->
            <li class="nav-item flex-fill text-center dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                id="navCourses"
                role="button"
                data-bs-toggle="dropdown"
                data-bs-display="static"
                aria-expanded="false"
                :class="{ active: isActiveAny(['admin.courses.*','admin.extras.*','admin.videos.*','admin.video-comments.*']) }"
                @click.prevent
              >
                <i class="bi bi-journal-text me-1"></i> Cursos
              </a>
              <ul class="dropdown-menu" aria-labelledby="navCourses">
                <li>
                  <Link class="dropdown-item" :href="route('admin.courses.index')">
                    <i class="bi bi-list-ul me-2"></i> Todos los cursos
                  </Link>
                </li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.courses.create')">
                    <i class="bi bi-plus-circle me-2"></i> Crear curso
                  </Link>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.extras.index')">
                    <i class="bi bi-stars me-2"></i> Clases Extras
                  </Link>
                </li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.videos.index')">
                    <i class="bi bi-collection-play me-2"></i> Todos los Videos
                  </Link>
                </li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.video-comments.index')">
                    <i class="bi bi-chat-dots me-2"></i> Comentarios
                  </Link>
                </li>
              </ul>
            </li>

            <!-- Maestros -->
            <li class="nav-item flex-fill text-center">
              <Link
                class="nav-link"
                :class="{ active: isActive('admin.teachers.index') }"
                :href="route('admin.teachers.index')"
              >
                <i class="bi bi-person-video3 me-1"></i> Maestros
              </Link>
            </li>

            <!-- Evaluaciones -->
            <li class="nav-item flex-fill text-center dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                id="navEvaluations"
                role="button"
                data-bs-toggle="dropdown"
                data-bs-display="static"
                aria-expanded="false"
                :class="{ active: isActiveAny(['admin.evaluation-users.*','admin.evaluations.*']) }"
                @click.prevent
              >
                <i class="bi bi-clipboard-check me-1"></i> Evaluaciones
              </a>
              <ul class="dropdown-menu" aria-labelledby="navEvaluations">
                <li>
                  <Link class="dropdown-item" :href="route('admin.evaluation-users.all')">
                    <i class="bi bi-inbox-arrow-down me-2"></i> Evaluaciones enviadas
                  </Link>
                </li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.evaluations.index')">
                    <i class="bi bi-diagram-3 me-2"></i> Evaluaciones por Curso
                  </Link>
                </li>
                <li>
                  <Link class="dropdown-item" :href="route('admin.evaluations.create')">
                    <i class="bi bi-plus-circle me-2"></i> Crear Evaluación
                  </Link>
                </li>
              </ul>
            </li>

            <!-- Distribuidores -->
            <li class="nav-item flex-fill text-center">
              <Link
                class="nav-link"
                :class="{ active: isActive('admin.distributors.index') }"
                :href="route('admin.distributors.index')"
              >
                <i class="bi bi-shop me-1"></i> Distribuidores
              </Link>
            </li>

            <!-- Certificados -->
            <li class="nav-item flex-fill text-center">
              <Link
                class="nav-link"
                :class="{ active: isActive('admin.certificates.index') }"
                :href="route('admin.certificates.index')"
              >
                <i class="bi bi-award me-1"></i> Certificados
              </Link>
            </li>

            <!-- Suscripciones -->
            <li class="nav-item flex-fill text-center">
              <Link
                class="nav-link"
                :class="{ active: isActive('admin.subscriptions.index') }"
                :href="route('admin.subscriptions.index')"
              >
                <i class="bi bi-credit-card me-1"></i> Suscripciones
              </Link>
            </li>

          </ul>
        </div>
      </div>
    </nav>
  </div>

  <!-- SIDEBAR / OFFCANVAS SOLO MÓVILES -->
  <div
    class="offcanvas offcanvas-start"
    tabindex="-1"
    id="adminSidebar"
    aria-labelledby="adminSidebarLabel"
    data-bs-scroll="true"
    data-bs-backdrop="true"
  >
    <div class="offcanvas-header bg-dark text-white">
      <h5 class="offcanvas-title" id="adminSidebarLabel">
        <i class="bi bi-menu-button-wide me-2"></i> Menú
      </h5>
     <button
  type="button"
  class="btn-close btn-close-white"
  data-bs-dismiss="offcanvas"
  aria-label="Cerrar">
</button>
    </div>

    <div class="offcanvas-body p-0">
        <div class="sidebar-body-wrap">
      <nav class="nav flex-column sidebar-nav">
        <!-- Usuarios -->
        <Link
          class="nav-link px-3 py-2"
          :class="{ active: isActive('admin.users.index') }"
          :href="route('admin.users.index')"
          @click="closeSidebar"
        >
          <i class="bi bi-people me-2"></i> Usuarios
        </Link>

        <!-- Estudiantes (submenu colapsable) -->
        <button
          class="btn btn-toggle align-items-center rounded px-3 py-2 text-start"
          data-bs-toggle="collapse"
          data-bs-target="#sbStudents"
          aria-expanded="false"
        >
          <i class="bi bi-mortarboard me-2"></i> Estudiantes
        </button>
        <div class="collapse" id="sbStudents">
          <ul class="btn-toggle-nav list-unstyled fw-normal small">
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.students.index')" @click="closeSidebar">
                <i class="bi bi-list-ul me-2"></i> Todos los estudiantes
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.students.create')" @click="closeSidebar">
                <i class="bi bi-plus-circle me-2"></i> Crear estudiante
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.activations.index')" @click="closeSidebar">
                <i class="bi bi-key me-2"></i> Activaciones
              </Link>
            </li>
          </ul>
        </div>

        <!-- Cursos -->
        <button
          class="btn btn-toggle align-items-center rounded px-3 py-2 text-start"
          data-bs-toggle="collapse"
          data-bs-target="#sbCourses"
          aria-expanded="false"
        >
          <i class="bi bi-journal-text me-2"></i> Cursos
        </button>
        <div class="collapse" id="sbCourses">
          <ul class="btn-toggle-nav list-unstyled fw-normal small">
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.courses.index')" @click="closeSidebar">
                <i class="bi bi-list-ul me-2"></i> Todos los cursos
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.courses.create')" @click="closeSidebar">
                <i class="bi bi-plus-circle me-2"></i> Crear curso
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.extras.index')" @click="closeSidebar">
                <i class="bi bi-stars me-2"></i> Clases Extras
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.videos.index')" @click="closeSidebar">
                <i class="bi bi-collection-play me-2"></i> Todos los Videos
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.video-comments.index')" @click="closeSidebar">
                <i class="bi bi-chat-dots me-2"></i> Comentarios
              </Link>
            </li>
          </ul>
        </div>

        <!-- Maestros -->
        <Link
          class="nav-link px-3 py-2"
          :class="{ active: isActive('admin.teachers.index') }"
          :href="route('admin.teachers.index')"
          @click="closeSidebar"
        >
          <i class="bi bi-person-video3 me-2"></i> Maestros
        </Link>

        <!-- Evaluaciones -->
        <button
          class="btn btn-toggle align-items-center rounded px-3 py-2 text-start"
          data-bs-toggle="collapse"
          data-bs-target="#sbEvaluations"
          aria-expanded="false"
        >
          <i class="bi bi-clipboard-check me-2"></i> Evaluaciones
        </button>
        <div class="collapse" id="sbEvaluations">
          <ul class="btn-toggle-nav list-unstyled fw-normal small">
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.evaluation-users.all')" @click="closeSidebar">
                <i class="bi bi-inbox-arrow-down me-2"></i> Evaluaciones enviadas
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.evaluations.index')" @click="closeSidebar">
                <i class="bi bi-diagram-3 me-2"></i> Evaluaciones por Curso
              </Link>
            </li>
            <li>
              <Link class="link-body-emphasis d-inline-flex text-decoration-none rounded px-4 py-2"
                    :href="route('admin.evaluations.create')" @click="closeSidebar">
                <i class="bi bi-plus-circle me-2"></i> Crear Evaluación
              </Link>
            </li>
          </ul>
        </div>

        <!-- Distribuidores -->
        <Link
          class="nav-link px-3 py-2"
          :class="{ active: isActive('admin.distributors.index') }"
          :href="route('admin.distributors.index')"
          @click="closeSidebar"
        >
          <i class="bi bi-shop me-2"></i> Distribuidores
        </Link>

        <!-- Certificados -->
        <Link
          class="nav-link px-3 py-2"
          :class="{ active: isActive('admin.certificates.index') }"
          :href="route('admin.certificates.index')"
          @click="closeSidebar"
        >
          <i class="bi bi-award me-2"></i> Certificados
        </Link>

        <!-- Suscripciones -->
        <Link
          class="nav-link px-3 py-2"
          :class="{ active: isActive('admin.subscriptions.index') }"
          :href="route('admin.subscriptions.index')"
          @click="closeSidebar"
        >
          <i class="bi bi-credit-card me-2"></i> Suscripciones
        </Link>
      </nav>

      <hr class="my-2" />

      <div class="px-3 pb-3">
        <Link class="btn btn-outline-secondary w-100" :href="route('logout')" method="post" as="button" @click="closeSidebar">
          <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
        </Link>
      </div>
    </div>
    </div>
  </div>
</template>

<style scoped>
/* Asegura que el dropdown sea visible por encima del navbar */
.navbar { overflow: visible; }
.navbar-nav { align-items: stretch; }
.navbar .dropdown { position: relative; } /* ancla */
.navbar .dropdown-menu {
  position: absolute;
  z-index: 1080;
  background-color: #000; /* submenu negro */
  border: none;
  border-radius: 0.5rem;
  min-width: 200px;
  padding: 0.25rem 0;
   inset: auto auto auto 0; /* deja a Popper posicionar; evitar "estirar" contenedores */
  max-width: min(90vw, 360px);     /* no más ancho que la pantalla en móviles */
  overflow-wrap: anywhere;          /* cortar contenido largo */
}

.navbar .dropdown-item {
  color: #fff; /* links blancos */
}

.navbar .dropdown-item:hover,
.navbar .dropdown-item:focus {
  background-color: var(--bs-primary); /* hover azul */
  color: #fff; /* mantiene texto blanco */
}

.navbar .nav-link.active {
  font-weight: 600;
}

/* Sidebar look & feel */
.sidebar-nav .nav-link { color: var(--bs-body-color); }
.sidebar-nav .nav-link.active { font-weight: 600; }
.btn-toggle {
  color: var(--bs-body-color);
  background-color: transparent;
  border: 0;
  width: 100%;
}
.btn-toggle:focus { box-shadow: none; }
.btn-toggle-nav a { color: var(--bs-body-color); }
.offcanvas { max-width: 320px; }

/* ===== Offcanvas negro y tipografía ===== */
#adminSidebar.offcanvas {
  max-width: 340px;                 /* un poco más ancho */
  background: #0b0b0b;              /* negro */
  color: #f1f1f1;
  border-right: 1px solid rgba(255,255,255,.06);
}

/* Header del offcanvas */
#adminSidebar .offcanvas-header {
  background: #0f0f0f;
  border-bottom: 1px solid rgba(255,255,255,.06);
}

/* ===== Centro vertical del menú ===== */
.sidebar-body-wrap {
  display: flex;
  flex-direction: column;
  justify-content: center;          /* centra verticalmente el nav */
  min-height: calc(100vh - 70px - 86px); 
  /* 70px ~ header offcanvas; 86px ~ altura aprox. del bloque inferior + hr */
}

/* ===== Navegación lateral ===== */
.sidebar-nav {
  width: 100%;
  padding: .5rem 0;
}

/* Links simples */
.sidebar-nav .nav-link {
  color: #e6e6e6;
  padding: .875rem 1rem;            /* área táctil mayor */
  border-radius: .5rem;
  margin: .15rem .75rem;            /* separación entre botones */
  transition: background .2s ease, color .2s ease;
  display: flex;
  align-items: center;
  gap: .5rem;
  line-height: 1.25;
}
.sidebar-nav .nav-link:hover,
.sidebar-nav .nav-link:focus {
  background: rgba(255,255,255,.08);
  color: #fff;
}
.sidebar-nav .nav-link.active {
  background: rgba(13,110,253,.18); /* azul primario translúcido */
  color: #fff;
  font-weight: 600;
}

/* ===== Botones con submenú (collapse) ===== */
.btn-toggle {
  color: #e6e6e6;
  background: transparent;
  border: 0;
  width: 100%;
  padding: .875rem 1rem;
  border-radius: .5rem;
  margin: .15rem .75rem;
  display: flex;
  align-items: center;
  gap: .5rem;
  text-align: left;
  position: relative;
  line-height: 1.25;
}
.btn-toggle:hover,
.btn-toggle:focus {
  background: rgba(255,255,255,.08);
  color: #fff;
  outline: none;
  box-shadow: none;
}

/* Indicador chevron con CSS puro */
.btn-toggle::after {
  content: "";
  width: .6rem;
  height: .6rem;
  border-right: .15rem solid currentColor;
  border-bottom: .15rem solid currentColor;
  transform: rotate(-45deg);        /* apuntando a la derecha */
  opacity: .75;
  position: absolute;
  right: 1rem;
  top: 50%;
  translate: 0 -50%;
  transition: transform .2s ease, opacity .2s ease;
}
.btn-toggle[aria-expanded="true"]::after {
  transform: rotate(45deg);         /* apunta hacia abajo cuando está abierto */
  opacity: 1;
}

/* Contenedor de submenú */
.btn-toggle + .collapse {
  padding: .25rem 0 .5rem 0;
}

/* Items del submenú */
.btn-toggle-nav a {
  color: #d8d8d8;
  display: block;
  width: 100%;
  padding: .65rem 1rem .65rem 2.25rem; /* sangría para jerarquía */
  border-radius: .5rem;
  margin: .15rem .75rem;
  transition: background .2s ease, color .2s ease;
  background: rgba(255,255,255,.03);
}
.btn-toggle-nav a:hover,
.btn-toggle-nav a:focus {
  background: rgba(255,255,255,.10);
  color: #fff;
}

/* Separadores */
#adminSidebar hr {
  border-color: rgba(255,255,255,.08);
  margin: .75rem 1rem;
}

/* ===== Dropdowns del navbar (ya negro) ===== */
.navbar { overflow: visible; }
.navbar .dropdown-menu {
  position: absolute;
  z-index: 1080;
  background-color: #000;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: .5rem;
  min-width: 220px;
  padding: .25rem 0;
}
.navbar .dropdown-item { color: #fff; }
.navbar .dropdown-item:hover,
.navbar .dropdown-item:focus {
  background-color: var(--bs-primary);
  color: #fff;
}
.navbar .nav-link.active { font-weight: 600; }

/* ===== Responsive y touch targets ===== */
@media (max-width: 992px) {
  /* Más aire entre botones en móviles */
  .sidebar-nav .nav-link,
  .btn-toggle,
  .btn-toggle-nav a {
    margin: .25rem .75rem;
    padding-top: 1rem;
    padding-bottom: 1rem;
    color:#FFF!important;
    font-size:1rem;
  }

  /* Elevar contraste y mejorar legibilidad */
  .btn-toggle-nav a {
    background: rgba(255,255,255,.05);
  }
}

/* Ocultar el offcanvas en desktop si así lo deseas */
@media (min-width: 992px) {
  #adminSidebar { display: none !important; }
}
</style>
