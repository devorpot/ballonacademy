<!-- resources/js/Pages/Frontend/Distributors/Index.vue -->
<template>
  <Head title="Distribuidores" />
  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Distribuidores', route: '' }
      ]"
    />
 

    <section class="section-panel py-3">
      <div class="container-fluid">
        <!-- Filters -->
        <div class="card shadow-sm mb-3">
          <div class="card-header">
            <h5 class="card-title">Busqueda</h5>
          </div>
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-md-4">
                <label class="form-label">Buscar por nombre</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="bi bi-search"></i>
                  </span>
                  <input
                    v-model="form.q"
                    type="text"
                    class="form-control"
                    placeholder="Nombre del distribuidor"
                  />
                </div>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">País</label>
                <select v-model="form.country" class="form-select">
                  <option value="">Todos</option>
                  <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>

    
              <div class="col-12 d-flex gap-2 mt-2">
                <button class="btn btn-primary " @click="applyFilters">
                  <i class="bi bi-funnel me-1"></i> Aplicar
                </button>
                <button class="btn btn-outline-secondary " @click="resetFilters">
                  <i class="bi bi-x-circle me-1"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="distributors.data.length === 0" class="alert alert-info">
          No se encontraron distribuidores con los filtros actuales.
        </div>

        <!-- Grid -->
        <div v-else class="row">
         <!-- Lista en filas modernas -->
          <div class="card">
             <div class="card-header">
                <h5 class="card-title">Resultados</h5>
              </div>
            <div class="card-body">
                          <div
              v-for="d in distributors.data"
              :key="d.id"
              class="col-12 mb-3"
            >
              <div class="card list-row shadow-sm hover-lift">
                <div class="row g-0 align-items-center">
                  <!-- Logo / Placeholder -->
                  <div class="col-auto">
                    <img
                      v-if="d.logo_url"
                      :src="d.logo_url"
                      :alt="d.name"
                      class="logo-thumb rounded-start"
                    />
                    <div v-else class="logo-thumb rounded-start d-flex align-items-center justify-content-center bg-light text-muted">
                      <i class="bi bi-building fs-3"></i>
                    </div>
                  </div>

                  <!-- Contenido -->
                  <div class="col">
                    <div class="card-body py-3">
                      <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h5 class="card-title mb-0 me-2">
                          <i class="bi bi-building me-1"></i>{{ d.name }}
                        </h5>

                        <span v-if="d.region" class="badge text-bg-light border">
                          <i class="bi bi-diagram-3 me-1"></i>{{ d.region }}
                        </span>
                        <span v-if="d.zone" class="badge text-bg-light border">
                          <i class="bi bi-grid-3x3-gap me-1"></i>{{ d.zone }}
                        </span>
                      </div>

                      <p class="text-muted mb-2 d-flex flex-wrap align-items-center gap-2">
                        <span>
                          <i class="bi bi-geo-alt me-1"></i>
                          {{ d.country }}<span v-if="d.state">, {{ d.state }}</span>
                        </span>
                      </p>

                      <p v-if="d.description" class="mb-0 text-truncate-2">
                        {{ truncate(d.description, 200) }}
                      </p>
                    </div>
                  </div>

                  <!-- Acciones -->
                  <div class="col-12 col-md-auto">
                    <div class="card-body py-3 pt-md-3 d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
                      <a
                        v-if="d.map_url"
                        :href="d.map_url"
                        class="btn btn-outline-success btn-sm"
                        target="_blank"
                        rel="noopener noreferrer nofollow"
                      >
                        <i class="bi bi-map me-1"></i> Ver mapa
                      </a>

                      <a
                        v-if="d.phone"
                        :href="`tel:${d.phone}`"
                        class="btn btn-outline-secondary btn-sm "
                      >
                        <i class="bi bi-telephone me-1"></i> Llamar
                      </a>

                      <a
                        v-if="d.whatsapp"
                        :href="whatsUrl(d.whatsapp, `Hola ${d.name}, me interesa su distribución.`)"
                        class="btn btn-outline-success btn-sm "
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                      </a>

                      <button class="btn btn-primary  btn-sm" @click="openModal(d)">
                        <i class="bi bi-card-text me-1"></i> Ver ficha
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>
          </div>

        </div>

        <!-- Pagination -->
        <nav v-if="distributors.links && distributors.links.length > 3" class="mt-3">
          <ul class="pagination">
            <li
              v-for="link in distributors.links"
              :key="link.url + link.label"
              :class="['page-item', { active: link.active, disabled: !link.url }]"
            >
              <a
                v-if="link.url"
                class="page-link"
                href="#"
                @click.prevent="goTo(link.url)"
                v-html="link.label"
              />
              <span v-else class="page-link" v-html="link.label"></span>
            </li>
          </ul>
        </nav>
      </div>
    </section>

    <!-- Modal Ficha -->
    <div
      class="modal fade show"
      tabindex="-1"
      style="display: block; background: rgba(0,0,0,.5);"
      v-if="showModal"
      @click.self="closeModal"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-card-text me-2"></i>Ficha del distribuidor
            </h5>
            <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
          </div>

          <div class="modal-body" v-if="selected">
           <div class="card border-0 shadow-sm">
  <div class="card-body p-3 p-md-4">
    <div class="row g-3 align-items-start">
      <!-- Logo -->
      <div class="col-12 col-md-4 order-1">
        <div class="ratio ratio-4x3 bg-light rounded d-flex align-items-center justify-content-center">
          <img
            v-if="selected.logo_url"
            :src="selected.logo_url"
            :alt="selected.name"
            class="w-100 h-100 logo-img"
            loading="lazy"
            @error="onImgError"
          />
          <i v-else class="bi bi-building text-muted fs-1"></i>
        </div>
      </div>

      <!-- Info -->
      <div class="col-12 col-md-8 order-2">
        <h3 class="fw-semibold mb-2 text-break">{{ selected.name }}</h3>

        <!-- Ubicación + tags -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3 text-muted small">
          <span class="me-2">
            <i class="bi bi-geo-alt me-1"></i>
            {{ selected.country }}<span v-if="selected.state">, {{ selected.state }}</span>
          </span>
          <span v-if="selected.region" class="badge text-bg-light border">
            <i class="bi bi-diagram-3 me-1"></i>{{ selected.region }}
          </span>
          <span v-if="selected.zone" class="badge text-bg-light border">
            <i class="bi bi-grid-3x3-gap me-1"></i>{{ selected.zone }}
          </span>
        </div>

        <!-- Contacto (list-group para mejor lectura en móvil) -->
        <ul class="list-group list-group-flush small mb-3">
          <li v-if="selected.address" class="list-group-item px-0">
            <i class="bi bi-signpost text-primary me-2"></i>
            <span class="text-break">{{ selected.address }}</span>
          </li>
          <li v-if="selected.email" class="list-group-item px-0">
            <i class="bi bi-envelope text-primary me-2"></i>
            <a :href="`mailto:${selected.email}`" class="text-decoration-none text-break">
              {{ selected.email }}
            </a>
          </li>
          <li v-if="selected.phone" class="list-group-item px-0">
            <i class="bi bi-telephone text-primary me-2"></i>
            <a :href="`tel:${selected.phone}`" class="text-decoration-none">{{ selected.phone }}</a>
          </li>
          <li v-if="selected.whatsapp" class="list-group-item px-0">
            <i class="bi bi-whatsapp text-success me-2"></i>
            <a
              :href="whatsUrl(selected.whatsapp, `Hola ${selected.name}, me interesa su distribución.`)"
              target="_blank" rel="noopener"
              class="text-decoration-none"
            >
              {{ prettyPhone(selected.whatsapp) }}
            </a>
          </li>
          <li v-if="selected.website" class="list-group-item px-0">
            <i class="bi bi-globe text-primary me-2"></i>
            <a :href="ensureProtocol(selected.website)" target="_blank" rel="noopener" class="text-decoration-none text-break">
              {{ selected.website }}
            </a>
          </li>
          <li v-if="selected.facebook" class="list-group-item px-0">
            <i class="bi bi-facebook text-primary me-2"></i>
            <a :href="ensureProtocol(selected.facebook)" target="_blank" rel="noopener" class="text-decoration-none">Facebook</a>
          </li>
          <li v-if="selected.instagram" class="list-group-item px-0">
            <i class="bi bi-instagram text-danger me-2"></i>
            <a :href="ensureProtocol(selected.instagram)" target="_blank" rel="noopener" class="text-decoration-none">Instagram</a>
          </li>
          <li v-if="selected.tiktok" class="list-group-item px-0">
            <i class="bi bi-tiktok text-dark me-2"></i>
            <a :href="ensureProtocol(selected.tiktok)" target="_blank" rel="noopener" class="text-decoration-none">TikTok</a>
          </li>
        </ul>

        <!-- Acciones rápidas: full width en móvil, inline en desktop -->
        <div class="d-grid gap-2 d-md-flex">
          <a v-if="selected.phone" :href="`tel:${selected.phone}`" class="btn btn-primary btn-sm">
            <i class="bi bi-telephone me-1"></i> Llamar
          </a>
          <a
            v-if="selected.whatsapp"
            :href="whatsUrl(selected.whatsapp, `Hola ${selected.name}, me interesa su distribución.`)"
            target="_blank" rel="noopener"
            class="btn btn-success btn-sm"
          >
            <i class="bi bi-whatsapp me-1"></i> WhatsApp
          </a>
          <a
            v-if="mapUrl(selected)"
            :href="mapUrl(selected)"
            target="_blank" rel="noopener"
            class="btn btn-outline-secondary btn-sm"
          >
            <i class="bi bi-geo-alt me-1"></i> Mapa
          </a>
        </div>
      </div>

      <!-- Descripción -->
      <div class="col-12 order-3">
        <div v-if="selected.description" class="bg-light rounded p-3 mt-2">
          <p class="mb-0 clamp-4">{{ selected.description }}</p>
        </div>
      </div>
    </div>
  </div>
</div>


          </div>

          <div class="modal-footer d-flex flex-wrap gap-2">
            <a
              v-if="selected?.map_url"
              :href="selected.map_url"
              target="_blank"
              rel="noopener noreferrer nofollow"
              class="btn btn-outline-success"
            >
              <i class="bi bi-map me-1"></i> Abrir en Google Maps
            </a>
            <button type="button" class="btn btn-secondary ms-auto" @click="closeModal">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- /Modal Ficha -->
  </StudentLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { reactive, watch, ref } from 'vue'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

// Props desde Inertia
const props = defineProps({
  distributors: Object, // { data, links, meta }
  filters: Object,      // { q, country, state }
  countries: Array,
  states: Array
})

const distributors = props.distributors
const countries = props.countries
const states = props.states

// Filtros
const form = reactive({
  q: props.filters.q || '',
  country: props.filters.country || '',
  state: props.filters.state || ''
})

const mapUrl = (s) => {
  if (s?.gmap_link) return ensureProtocol(s.gmap_link)
  if (s?.lat && s?.lng) return `https://maps.google.com/?q=${s.lat},${s.lng}`
  return null
}

watch(() => form.country, () => {
  form.state = ''
  debouncedApply()
})

let timer = null
function debouncedApply() {
  clearTimeout(timer)
  timer = setTimeout(applyFilters, 350)
}

watch(() => form.q, debouncedApply)
watch(() => form.state, debouncedApply)

function applyFilters() {
  router.get(
    route('dashboard.distributors.index'),
    { q: form.q, country: form.country, state: form.state },
    { preserveScroll: true, replace: true }
  )
}

function resetFilters() {
  form.q = ''
  form.country = ''
  form.state = ''
  applyFilters()
}

// Paginación
function goTo(url) {
  router.visit(url, { preserveScroll: true, replace: true })
}

// Modal
const showModal = ref(false)
const selected = ref(null)

function openModal(item) {
  selected.value = item
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selected.value = null
}

// Helpers
function truncate(text, max = 140) {
  if (!text) return ''
  return text.length > max ? text.slice(0, max) + '…' : text
}

function ensureProtocol(url) {
  if (!url) return '#'
  if (/^https?:\/\//i.test(url)) return url
  return `https://${url}`
}

function sanitizePhone(p) {
  return String(p || '').replace(/[^\d+]/g, '')
}

function whatsUrl(phone, text = '') {
  const number = sanitizePhone(phone)
  const q = text ? `?text=${encodeURIComponent(text)}` : ''
  return `https://wa.me/${number}${q}`
}

function prettyPhone(p) {
  return String(p || '')
}
</script>

<style scoped>
.modal {
  z-index: 2000;
}

.list-row {
  border: 1px solid var(--bs-border-color);
  border-radius: .75rem;
  transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
  border-color: rgba(0,0,0,.06);
}

.logo-thumb {
  width: 112px;
  height: 112px;
  object-fit: contain;
  background: #f8f9fa;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;      /* número de líneas */
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.logo-img { object-fit: contain; }
.clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}


</style>
