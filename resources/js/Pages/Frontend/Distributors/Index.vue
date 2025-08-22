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

              <div class="col-12 col-md-4">
                <label class="form-label">País</label>
                <select v-model="form.country" class="form-select">
                  <option value="">Todos</option>
                  <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>

    
              <div class="col-12 d-flex gap-2 mt-2">
                <button class="btn btn-primary" @click="applyFilters">
                  <i class="bi bi-funnel me-1"></i> Aplicar
                </button>
                <button class="btn btn-outline-secondary" @click="resetFilters">
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
          <div
            v-for="d in distributors.data"
            :key="d.id"
            class="col-12 col-md-6 col-lg-4 mb-4"
          >
            <div class="card h-100 shadow-sm">
              <img
                v-if="d.logo_url"
                :src="d.logo_url"
                :alt="d.name"
                class="card-img-top"
                style="object-fit: contain; height: 180px; background: #f8f9fa;"
              />
              <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-1">
                  <i class="bi bi-building me-1"></i>{{ d.name }}
                </h5>

                <p class="text-muted mb-2 d-flex flex-wrap align-items-center gap-2">
                  <span>
                    <i class="bi bi-geo-alt me-1"></i>
                    {{ d.country }}<span v-if="d.state">, {{ d.state }}</span>
                  </span>
                  <span v-if="d.region" class="badge text-bg-light border">
                    <i class="bi bi-diagram-3 me-1"></i>{{ d.region }}
                  </span>
                  <span v-if="d.zone" class="badge text-bg-light border">
                    <i class="bi bi-grid-3x3-gap me-1"></i>{{ d.zone }}
                  </span>
                </p>

                <p v-if="d.description" class="card-text small flex-grow-1">
                  {{ truncate(d.description, 160) }}
                </p>

                <div class="d-flex flex-wrap gap-2 mt-2">
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
                    class="btn btn-outline-secondary btn-sm"
                  >
                    <i class="bi bi-telephone me-1"></i> Llamar
                  </a>

                  <a
                    v-if="d.whatsapp"
                    :href="whatsUrl(d.whatsapp, `Hola ${d.name}, me interesa su distribución.`)"
                    class="btn btn-outline-success btn-sm"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                  </a>

                  <button class="btn btn-primary btn-sm" @click="openModal(d)">
                    <i class="bi bi-card-text me-1"></i> Ver ficha
                  </button>
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
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <img
                  v-if="selected.logo_url"
                  :src="selected.logo_url"
                  :alt="selected.name"
                  class="img-fluid rounded border"
                />
              </div>
              <div class="col-12 col-md-8">
                <h4 class="mb-1">{{ selected.name }}</h4>
                <p class="text-muted mb-2 d-flex flex-wrap align-items-center gap-2">
                  <span>
                    <i class="bi bi-geo-alt me-1"></i>
                    {{ selected.country }}<span v-if="selected.state">, {{ selected.state }}</span>
                  </span>
                  <span v-if="selected.region" class="badge text-bg-light border">
                    <i class="bi bi-diagram-3 me-1"></i>{{ selected.region }}
                  </span>
                  <span v-if="selected.zone" class="badge text-bg-light border">
                    <i class="bi bi-grid-3x3-gap me-1"></i>{{ selected.zone }}
                  </span>
                </p>

                <div class="list-group small">
                  <div v-if="selected.address" class="list-group-item">
                    <i class="bi bi-signpost me-1"></i>{{ selected.address }}
                  </div>
                  <div v-if="selected.email" class="list-group-item">
                    <i class="bi bi-envelope me-1"></i>
                    <a :href="`mailto:${selected.email}`">{{ selected.email }}</a>
                  </div>
                  <div v-if="selected.phone" class="list-group-item">
                    <i class="bi bi-telephone me-1"></i>
                    <a :href="`tel:${selected.phone}`">{{ selected.phone }}</a>
                  </div>
                  <div v-if="selected.whatsapp" class="list-group-item">
                    <i class="bi bi-whatsapp me-1"></i>
                    <a
                      :href="whatsUrl(selected.whatsapp, `Hola ${selected.name}, me interesa su distribución.`)"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      {{ prettyPhone(selected.whatsapp) }}
                    </a>
                  </div>
                  <div v-if="selected.website" class="list-group-item">
                    <i class="bi bi-globe me-1"></i>
                    <a :href="ensureProtocol(selected.website)" target="_blank" rel="noopener noreferrer">
                      {{ selected.website }}
                    </a>
                  </div>
                  <div v-if="selected.facebook" class="list-group-item">
                    <i class="bi bi-facebook me-1"></i>
                    <a :href="ensureProtocol(selected.facebook)" target="_blank" rel="noopener noreferrer">
                      Facebook
                    </a>
                  </div>
                  <div v-if="selected.instagram" class="list-group-item">
                    <i class="bi bi-instagram me-1"></i>
                    <a :href="ensureProtocol(selected.instagram)" target="_blank" rel="noopener noreferrer">
                      Instagram
                    </a>
                  </div>
                  <div v-if="selected.tiktok" class="list-group-item">
                    <i class="bi bi-tiktok me-1"></i>
                    <a :href="ensureProtocol(selected.tiktok)" target="_blank" rel="noopener noreferrer">
                      TikTok
                    </a>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <p v-if="selected.description" class="mb-0">
                  {{ selected.description }}
                </p>
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
</style>
