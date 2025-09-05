<!-- resources/js/Pages/dashboard/ExtraClasses/Show.vue -->
<template>
  <Head :title="extra.title" />

  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Clases Extra', route: 'dashboard.extras.index' },
        { label: extra.title }
      ]"
    />

    <!-- Sección 1: Video a todo el ancho -->
    <section class="section-video w-100 mb-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm border-0">
              <div class="card-body p-0">
                <!-- YouTube -->
                <div v-if="Number(extra.is_youtube) === 1 && extra.youtube_url" class="ratio ratio-16x9">
                  <iframe
                    :src="embedYoutube(extra.youtube_url)"
                    title="YouTube video"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                  ></iframe>
                </div>
                <!-- Archivo local -->
                <div v-else class="ratio ratio-16x9 bg-dark d-flex align-items-center justify-content-center">
                  <video
                    v-if="extra.video_url"
                    class="w-100 h-100"
                    controls
                    playsinline
                    preload="metadata"
                    :poster="extra.cover_url || extra.image_url || null"
                  >
                    <source :src="extra.video_url" type="video/mp4" />
                    <track
                      v-if="extra.subt_url"
                      kind="subtitles"
                      :src="extra.subt_url"
                      srclang="es"
                      label="Español"
                      default
                    />
                    Tu navegador no soporta la reproducción de video.
                  </video>
                  <div v-else class="text-white-50 p-4 text-center">
                    No hay video disponible.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 2: Detalles debajo del video -->
    <section class="section-details mb-4">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
                  <h1 class="h5 fw-bold mb-0">{{ extra.title }}</h1>
                  <div class="d-flex align-items-center gap-2">
                    <span v-if="extra.category" class="badge bg-primary">{{ extra.category }}</span>
                    <span v-if="extra.created_at" class="badge bg-light text-dark">
                      <i class="bi bi-calendar-event me-1"></i>{{ formatDate(extra.created_at) }}
                    </span>
                  </div>
                </div>

                <p v-if="extra.extract" class="lead mb-3">{{ extra.extract }}</p>

                <div v-if="extra.description" class="content description mb-3" v-html="extra.description"></div>

                <div v-if="extra.tags" class="mb-3">
                  <span v-for="tag in extra.tags.split(',')" :key="tag" class="badge bg-secondary me-1">
                    #{{ tag.trim() }}
                  </span>
                </div>

                <ul class="list-inline small text-muted mb-0">
                  <li class="list-inline-item me-3" v-if="extra.views">
                    <i class="bi bi-eye me-1"></i>{{ extra.views }} vistas
                  </li>
                  <li class="list-inline-item me-3" v-if="extra.duration">
                    <i class="bi bi-clock me-1"></i>{{ extra.duration }}
                  </li>
                </ul>

                <!-- Recursos opcionales -->
                <div v-if="hasResources" class="mt-4">
                  <h2 class="h6 fw-bold mb-2"><i class="bi bi-paperclip me-1"></i> Recursos</h2>
                  <ul class="list-group list-group-flush">
                    <li v-for="(r, idx) in resources" :key="idx" class="list-group-item d-flex align-items-center justify-content-between">
                      <div class="me-3">
                        <strong>{{ r.title || ('Recurso ' + (idx + 1)) }}</strong>
                        <div v-if="r.description" class="small text-muted">{{ r.description }}</div>
                      </div>
                      <a v-if="r.url" class="btn btn-sm btn-outline-primary" :href="r.url" target="_blank" rel="noopener">
                        Descargar
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 3: Otras clases (cards + paginación 9 por página) -->
    <section class="section-others py-3">
      <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h2 class="h5 fw-bold mb-0">Más clases</h2>
          <Link :href="route('dashboard.extras.index')" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list me-1"></i>Ver todas
          </Link>
        </div>

        <div v-if="totalExtras > 0" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
          <div v-for="e in pagedExtras" :key="e.id" class="col">
           <!-- Card Extra mejorada -->
                  <div class="card card-extra h-100 shadow-sm border-0 position-relative">
                    <!-- Media -->
                    <div class="ratio ratio-16x9 bg-light rounded-top overflow-hidden position-relative">
                      <img
                        v-if="e.cover_url || e.image_url"
                        :src="e.cover_url || e.image_url"
                        :alt="e.title"
                        class="card-img w-100 h-100"
                        loading="lazy"
                        decoding="async"
                      />
                      <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="bi bi-image text-muted fs-3"></i>
                      </div>

                      <!-- Gradiente y play -->
                      <div class="media-overlay"></div>
                      <div class="play-indicator">
                        <i class="bi" :class="Number(e.is_youtube) === 1 ? 'bi-youtube' : 'bi-play-btn-fill'"></i>
                      </div>

                      <!-- Badges superiores -->
                      <div class="position-absolute top-0 start-0 p-2 d-flex gap-1">
                        <span v-if="Number(e.is_youtube) === 1" class="badge bg-danger text-white">YouTube</span>
                        <span v-else class="badge bg-secondary text-white">Archivo</span>
                        <span v-if="e.subt_url || e.subt_str" class="badge bg-dark">Sub</span>
                      </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                      <h3 class="h6 fw-semibold text-truncate mb-1" :title="e.title">{{ e.title }}</h3>
                      <div v-if="e.extract" class="small text-muted text-truncate-2">{{ e.extract }}</div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-transparent border-0 pt-0 d-flex align-items-center justify-content-between">
                      <div class="d-flex flex-wrap align-items-center gap-1">
                        <span v-if="e.category" class="badge bg-light text-dark border">{{ e.category }}</span>
                        <span
                          v-for="t in (e.tags ? e.tags.split(',').slice(0, 2) : [])"
                          :key="t"
                          class="badge bg-light text-dark border small"
                        >
                          #{{ t.trim() }}
                        </span>
                      </div>
                      <Link :href="route('dashboard.extras.show', { extra: e.id })" class="btn btn-sm btn-primary">
                        Ver
                      </Link>
                    </div>

                    <!-- Hace toda la card clickeable -->
                    <Link :href="route('dashboard.extras.show', { extra: e.id })" class="stretched-link" aria-label="Ver clase"></Link>
                  </div>

          </div>
        </div>

        <div v-else class="alert alert-light border text-muted">No hay más clases listadas.</div>

        <!-- Paginación -->
        <nav v-if="totalPages > 1" class="mt-3" aria-label="Paginación de otras clases">
          <ul class="pagination justify-content-center mb-0 flex-wrap">
            <li class="page-item" :class="{ disabled: page === 1 }">
              <button class="page-link" @click="prevPage" :disabled="page === 1">Anterior</button>
            </li>
            <li v-for="p in pagesArray" :key="`p-${p}`" class="page-item" :class="{ active: p === page }">
              <button class="page-link" @click="goToPage(p)">{{ p }}</button>
            </li>
            <li class="page-item" :class="{ disabled: page === totalPages }">
              <button class="page-link" @click="nextPage" :disabled="page === totalPages">Siguiente</button>
            </li>
          </ul>
        </nav>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const props = defineProps({
  extra: { type: Object, required: true },      // {id,title,extract,description,category,tags,is_youtube,youtube_url,image_url,cover_url,video_url,subt_url,created_at,...}
  extras: { type: Array, default: () => [] },   // otras clases (excluye la actual)
  resources: { type: Array, default: () => [] } // opcional
})

const extra = computed(() => props.extra)
const resources = computed(() => props.resources)
const hasResources = computed(() => Array.isArray(resources.value) && resources.value.length > 0)

// Paginación client-side para "extras"
const perPage = 9
const page = ref(1)
const totalExtras = computed(() => props.extras?.length || 0)
const totalPages = computed(() => Math.max(1, Math.ceil(totalExtras.value / perPage)))

const pagedExtras = computed(() => {
  const start = (page.value - 1) * perPage
  return props.extras.slice(start, start + perPage)
})

const pagesArray = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1))
function goToPage(p) {
  if (p >= 1 && p <= totalPages.value) page.value = p
}
function prevPage() { if (page.value > 1) page.value-- }
function nextPage() { if (page.value < totalPages.value) page.value++ }

watch(() => totalPages.value, () => { if (page.value > totalPages.value) page.value = 1 })
watch(() => props.extras, () => { page.value = 1 })

// Embeds
function embedYoutube(url) {
  try {
    const u = new URL(url)
    let id = ''
    if (u.hostname.includes('youtu.be')) {
      id = u.pathname.replace('/', '')
    } else {
      id = u.searchParams.get('v') || ''
    }
    return id ? `https://www.youtube.com/embed/${id}?rel=0&modestbranding=1` : url
  } catch {
    return url
  }
}

function formatDate(iso) {
  if (!iso) return ''
  try {
    const d = new Date(iso)
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
  } catch {
    return iso
  }
}
</script>

<style scoped>
.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Contenido enriquecido */
.content.description :deep(p) { margin-bottom: 0.8rem; }
.content.description :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: .5rem;
}

/* Estilos de la card mejorada */
.card-extra .card-img {
  object-fit: cover;
  transition: transform .25s ease;
}
.card-extra:hover .card-img {
  transform: scale(1.03);
}

.card-extra .media-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,.45), rgba(0,0,0,0) 60%);
  opacity: 0;
  transition: opacity .25s ease;
}
.card-extra:hover .media-overlay {
  opacity: 1;
}

.card-extra .play-indicator {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  color: #fff;
  opacity: .85;
  transform: scale(.95);
  transition: transform .2s ease, opacity .2s ease;
  pointer-events: none;
}
.card-extra:hover .play-indicator {
  transform: scale(1.05);
}

/* Truncado de 2 líneas */
.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
