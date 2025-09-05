<!-- resources/js/Pages/Profile/ArtistPortfolio.vue -->
<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

// Props mínimas esperadas desde el servidor
const props = defineProps({
  profile: { type: Object, required: true },
  // opcional: lista de piezas ya normalizadas desde backend
  pieces: { type: Array, default: () => [] }, // [{id, title, src, width, height, tags:[], likes, created_at}]
  projects: { type: Array, default: () => [] }, // casos de estudio
})

// Derivados básicos
const fullName = computed(() => `${props.profile.firstname ?? ''} ${props.profile.lastname ?? ''}`.trim() || 'Artista del Globo')
const nickname = computed(() => props.profile.nickname ? `@${props.profile.nickname}` : '')
const coverUrl = computed(() => props.profile.cover_image ? `/storage/${props.profile.cover_image}` : null)
// Solo placehold.co para avatar por defecto
const avatarUrl = computed(() => props.profile.profile_image ? `/storage/${props.profile.profile_image}` : 'https://placehold.co/160x160')

// Fuente de datos: si no llegan piezas desde backend, simular algunas
// Solo placehold.co, mismas medidas, sin textos
const fallbackPieces = [
  { id: 1, title: 'Arco orgánico pastel', src: 'https://placehold.co/900x1200', tags: ['orgánico','pastel'], likes: 34 },
  { id: 2, title: 'Backdrop corporativo', src: 'https://placehold.co/1200x900', tags: ['corporativo'], likes: 18 },
  { id: 3, title: 'Muro chroma', src: 'https://placehold.co/900x900', tags: ['muro','evento'], likes: 22 },
  { id: 4, title: 'Túnel con globos', src: 'https://placehold.co/1000x1400', tags: ['túnel','evento'], likes: 41 },
  { id: 5, title: 'Centro de mesa', src: 'https://placehold.co/1400x1000', tags: ['centro','boda'], likes: 13 },
  { id: 6, title: 'Arco metálico', src: 'https://placehold.co/1100x1400', tags: ['estructura'], likes: 29 },
  { id: 7, title: 'Entrada temática', src: 'https://placehold.co/1200x1200', tags: ['temática'], likes: 25 },
  { id: 8, title: 'Cielo de globos', src: 'https://placehold.co/900x1100', tags: ['techo','orgánico'], likes: 37 },
  { id: 9, title: 'Photobooth', src: 'https://placehold.co/1300x1000', tags: ['photo','evento'], likes: 19 },
]
const allPieces = computed(() => props.pieces.length ? props.pieces : fallbackPieces)

// Tags dinámicos
const allTags = computed(() => {
  const set = new Set()
  allPieces.value.forEach(p => (p.tags || []).forEach(t => set.add(t)))
  return Array.from(set).sort()
})

const selectedTag = ref('todos')
const searchText = ref('')

// Filtro principal
const filteredPieces = computed(() => {
  const txt = searchText.value.trim().toLowerCase()
  const tag = selectedTag.value
  return allPieces.value.filter(p => {
    const tagOk = tag === 'todos' || (p.tags || []).includes(tag)
    const textOk = !txt || (p.title?.toLowerCase().includes(txt) || (p.tags || []).some(t => t.toLowerCase().includes(txt)))
    return tagOk && textOk
  })
})

// Lightbox
const lightbox = ref({ visible: false, index: 0 })
function openLightbox(idx) {
  lightbox.value = { visible: true, index: idx }
}
function closeLightbox() {
  lightbox.value.visible = false
}
function nextItem() {
  lightbox.value.index = (lightbox.value.index + 1) % filteredPieces.value.length
}
function prevItem() {
  lightbox.value.index = (lightbox.value.index - 1 + filteredPieces.value.length) % filteredPieces.value.length
}

// Likes locales
function likePiece(p) {
  p.likes = (p.likes || 0) + 1
}

// Sección Proyectos tipo case study
// Solo placehold.co, mismas medidas, sin textos
const caseStudies = computed(() => {
  if (props.projects.length) return props.projects
  return [
    {
      id: 'c1',
      title: 'Lanzamiento corporativo: arco orgánico XL',
      cover: 'https://placehold.co/1200x640',
      role: 'Diseño, montaje, logística',
      tools: ['Qualatex', 'Estructuras metálicas', 'Globos 5" y 12"'],
      summary: 'Concepto, pruebas de paleta, montaje en sitio, y cronograma de desmontaje. Entrega en 6 horas.',
      link: '#',
    },
    {
      id: 'c2',
      title: 'Boda jardín: cielo de globos y photobooth',
      cover: 'https://placehold.co/1200x640',
      role: 'Dirección de arte, montaje',
      tools: ['Globos mate', 'Hilo nylon', 'Luz cálida'],
      summary: 'Instalación aérea de 12 metros y cabina para fotos con marca de los novios.',
      link: '#',
    },
  ]
})

// Sidebar data
const services = [
  { icon: 'bi-brush', name: 'Diseño de arcos orgánicos' },
  { icon: 'bi-bank', name: 'Decoración corporativa' },
  { icon: 'bi-heart', name: 'Bodas y XV años' },
  { icon: 'bi-camera', name: 'Photobooth temático' },
]

const socialLinks = computed(() => ({
  instagram: props.profile.instagram || '#',
  facebook: props.profile.facebook || '#',
  tiktok: props.profile.tiktok || '#',
  website: props.profile.website || '#',
}))

// Modal de contacto (simulado)
const contact = ref({ name: '', email: '', message: '' })
function sendContact() {
  const el = document.getElementById('modalContact')
  const modal = window.bootstrap?.Modal.getOrCreateInstance(el)
  modal?.hide()
  contact.value = { name: '', email: '', message: '' }
}
</script>

<template>
  <GuestLayout>
    <Head :title="`${fullName} | Portafolio`" />

    <section class="portfolio-hero position-relative">
      <div
        class="hero-cover rounded"
        :style="coverUrl ? `background-image:url(${coverUrl})` : ''"
      >
        <div class="overlay"></div>
      </div>

      <div class="container-fluid hero-bar text-center">
        <div class="d-inline-flex flex-column align-items-center">
          <img
            :src="avatarUrl"
            class="avatar-xxl border border-3 border-white shadow mb-2"
            alt="avatar"
          >
          <h1 class="h4 mb-0">{{ fullName }}</h1>
          <div class="small">{{ nickname }}</div>
        </div>

        <div class="position-absolute end-0 bottom-0 mb-3 me-3 d-none d-md-flex gap-2">
          <a :href="socialLinks.instagram" class="btn btn-light btn-sm"><i class="bi bi-instagram"></i></a>
          <a :href="socialLinks.facebook" class="btn btn-light btn-sm"><i class="bi bi-facebook"></i></a>
          <a :href="socialLinks.tiktok" class="btn btn-light btn-sm"><i class="bi bi-tiktok"></i></a>
          <a :href="socialLinks.website" class="btn btn-light btn-sm"><i class="bi bi-globe"></i></a>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalContact">
            <i class="bi bi-envelope me-1"></i>Contactar
          </button>
        </div>
      </div>
    </section>

    <!-- CONTENIDO -->
    <section class="py-4">
      <div class="container-fluid">
        <div class="row g-4">
          <!-- MAIN -->
          <div class="col-12 col-lg-9">
            <!-- Toolbar de filtros -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body d-flex flex-wrap gap-2 align-items-center">
                <div class="me-auto d-flex gap-2 flex-wrap">
                  <button
                    class="btn btn-sm"
                    :class="selectedTag === 'todos' ? 'btn-primary' : 'btn-light'"
                    @click="selectedTag = 'todos'"
                  >
                    Todos
                  </button>
                  <button
                    v-for="t in allTags" :key="t"
                    class="btn btn-sm"
                    :class="selectedTag === t ? 'btn-primary' : 'btn-light'"
                    @click="selectedTag = t"
                  >
                    {{ t }}
                  </button>
                </div>

                <div class="ms-auto" style="min-width:220px">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" v-model="searchText" class="form-control" placeholder="Buscar piezas">
                  </div>
                </div>
              </div>
            </div>

            <!-- MASONRY GRID -->
            <div class="masonry">
              <figure
                v-for="(p, idx) in filteredPieces"
                :key="p.id"
                class="masonry-item card border-0 shadow-sm"
                role="button"
                @click="openLightbox(idx)"
                :title="p.title"
              >
                <img
                  :src="p.src"
                  class="card-img-top"
                  :alt="p.title"
                  loading="lazy"
                />
                <figcaption class="card-body py-2">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="text-truncate pe-2">{{ p.title }}</div>
                    <button class="btn btn-sm btn-light" @click.stop="likePiece(p)">
                      <i class="bi bi-heart me-1"></i>{{ p.likes || 0 }}
                    </button>
                  </div>
                  <div class="small text-muted mt-1">
                    <i class="bi bi-tag me-1"></i>{{ (p.tags || []).join(', ') }}
                  </div>
                </figcaption>
              </figure>

              <div v-if="!filteredPieces.length" class="text-center text-muted py-5">
                No hay piezas con ese filtro
              </div>
            </div>

            <!-- PROYECTOS / CASE STUDIES -->
            <div class="mt-4">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="h5 mb-0">Proyectos</h3>
                <a href="#" class="btn btn-sm btn-light">Ver todos</a>
              </div>

              <div class="row g-3">
                <div v-for="c in caseStudies" :key="c.id" class="col-12 col-md-6">
                  <div class="card border-0 shadow-sm h-100">
                    <div class="ratio ratio-16x9">
                      <img :src="c.cover" alt="" class="w-100 h-100 object-fit-cover rounded-top">
                    </div>
                    <div class="card-body">
                      <h5 class="card-title mb-1">{{ c.title }}</h5>
                      <div class="text-muted small mb-2">
                        <i class="bi bi-person-gear me-1"></i>{{ c.role }}
                      </div>
                      <p class="mb-2">{{ c.summary }}</p>
                      <div class="small text-muted mb-3">
                        <i class="bi bi-tools me-1"></i>{{ c.tools.join(' · ') }}
                      </div>
                      <a :href="c.link" class="btn btn-sm btn-outline-primary">Ver caso</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- SIDEBAR -->
          <div class="col-12 col-lg-3">
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Acerca de</h6>
              </div>
              <div class="card-body">
                <p class="mb-2">{{ props.profile.description || 'Artista especializado en diseño y montaje de estructuras con globos.' }}</p>
                <div class="text-muted small">
                  <i class="bi bi-geo-alt me-1"></i>{{ props.profile.country || 'Ubicación no especificada' }}
                </div>
              </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>Servicios</h6>
              </div>
              <ul class="list-group list-group-flush">
                <li v-for="s in services" :key="s.name" class="list-group-item d-flex align-items-center gap-2">
                  <i :class="`bi ${s.icon}`"></i> <span>{{ s.name }}</span>
                </li>
              </ul>
            </div>

            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-share me-2"></i>Redes</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalContact">
                  Contactar
                </button>
              </div>
              <div class="card-body d-flex gap-2">
                <a :href="socialLinks.instagram" class="btn btn-light flex-fill"><i class="bi bi-instagram"></i></a>
                <a :href="socialLinks.facebook" class="btn btn-light flex-fill"><i class="bi bi-facebook"></i></a>
                <a :href="socialLinks.tiktok" class="btn btn-light flex-fill"><i class="bi bi-tiktok"></i></a>
                <a :href="socialLinks.website" class="btn btn-light flex-fill"><i class="bi bi-globe"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MODAL: Lightbox -->
    <div class="modal fade" id="modalLightbox" tabindex="-1" :class="{ show: lightbox.visible }" style="display: none;" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-black">
          <div class="modal-body p-2 position-relative">
            <button type="button" class="btn btn-light position-absolute" style="top:8px; right:8px" @click="closeLightbox">
              Cerrar
            </button>
            <button type="button" class="btn btn-light position-absolute" style="top:50%; left:8px; transform:translateY(-50%)" @click="prevItem">
              <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="btn btn-light position-absolute" style="top:50%; right:8px; transform:translateY(-50%)" @click="nextItem">
              <i class="bi bi-chevron-right"></i>
            </button>
            <img
              v-if="filteredPieces[lightbox.index]"
              :src="filteredPieces[lightbox.index].src"
              class="img-fluid d-block mx-auto rounded"
              :alt="filteredPieces[lightbox.index].title"
            />
            <div class="text-white-50 small mt-2 text-center">
              {{ filteredPieces[lightbox.index]?.title }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Contacto -->
    <div class="modal fade" id="modalContact" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" @submit.prevent="sendContact">
          <div class="modal-header">
            <h5 class="modal-title">Contacto profesional</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input v-model="contact.name" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="contact.email" type="email" class="form-control" required>
            </div>
            <div class="mb-0">
              <label class="form-label">Mensaje</label>
              <textarea v-model="contact.message" rows="4" class="form-control" placeholder="Cuéntame sobre tu evento o requerimiento" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" type="submit">
              Enviar
            </button>
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>

<style scoped>
/* HERO */
.portfolio-hero { --hero-h: 180px; }
@media (min-width: 768px) { .portfolio-hero { --hero-h: 240px; } }
@media (min-width: 1200px) { .portfolio-hero { --hero-h: 300px; } }

/* Cover con altura fija (sin ratio) */
.portfolio-hero .hero-cover {
  height: var(--hero-h);
  background: #0d6efd center/cover no-repeat;
  position: relative;
}
.portfolio-hero .overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,.25), rgba(0,0,0,.65));
  border-radius: inherit;
}

/* Barra inferior que flota sobre el cover */
.portfolio-hero .hero-bar {
  position: relative;
  margin-top: calc(-1 * (var(--hero-h) / 3));
  z-index: 2;
  display: flex;
  justify-content: center;
  padding-bottom: 1rem;
}
.avatar-xxl {
  width: 128px;
  height: 128px;
  border-radius: 50%;
  object-fit: cover;
  z-index: 3;
}
@media (min-width: 992px) {
  .avatar-xxl { width: 140px; height: 140px; }
}
.portfolio-hero .hero-content {
  margin-top: -56px;
  padding: 0 1rem;
}

/* MASONRY sin librerías: columns */
.masonry {
  column-count: 1;
  column-gap: 1rem;
}
@media (min-width: 576px) { .masonry { column-count: 2; } }
@media (min-width: 992px) { .masonry { column-count: 3; } }
.masonry-item {
  break-inside: avoid;
  margin-bottom: 1rem;
  transition: transform .2s ease, box-shadow .2s ease;
  overflow: hidden;
}
.masonry-item img {
  width: 100%;
  height: auto;
  display: block;
}
.masonry-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}

/* Utilidades */
.object-fit-cover { object-fit: cover; }
</style>
