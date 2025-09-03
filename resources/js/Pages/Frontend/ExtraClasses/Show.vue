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

    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row g-4">
          <!-- Columna principal -->
          <div class="col-12 col-lg-8" id="content" ref="contentCol" :class="{ 'lg-sticky': stickContent }" :style="stickyStyle">

            <!-- Reproductor -->
            <div class="card shadow-sm border-0 mb-3">
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

            <!-- Detalles -->
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

          <!-- Sidebar: Otras clases -->
          <div class="col-12 col-lg-4" id="sidebar" ref="sidebarCol" :class="{ 'lg-sticky': stickSidebar }" :style="stickyStyle">
            <div class="card shadow-sm border-0 h-100">
              <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0">Otras clases</h6>
                <Link :href="route('dashboard.extras.index')" class="small">Ver todas</Link>
              </div>
              <div class="card-body pt-3 pb-0 px-3" id="extra-list">
                <div v-for="e in extras" :key="e.id" class="mb-3">
                  <a class="text-decoration-none" :href="route('dashboard.extras.show', { extra: e.id })">
                    <div class="d-flex align-items-center gap-3">
                      <div class="flex-shrink-0">
                        <div class="ratio ratio-16x9 rounded overflow-hidden" style="width: 120px;">
                          <img
                            :src="e.cover_url || e.image_url"
                            class="w-100 h-100 object-fit-cover"
                            :alt="e.title"
                            v-if="e.cover_url || e.image_url"
                          />
                          <div v-else class="bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-image text-muted"></i>
                          </div>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <div class="fw-semibold text-dark text-truncate">{{ e.title }}</div>
                        <div class="small text-muted text-truncate-2">{{ e.extract }}</div>
                      </div>
                    </div>
                  </a>
                </div>

                <div v-if="!extras || !extras.length" class="text-muted">
                  No hay más clases listadas.
                </div>
              </div>
            </div>
          </div>
          <!-- /Sidebar -->
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const props = defineProps({
  extra: { type: Object, required: true },      // {id,title,extract,description,category,tags,is_youtube,youtube_url,image_url,cover_url,video_url,subt_url,created_at,...}
  extras: { type: Array, default: () => [] },   // otras clases (idealmente excluye la actual)
  resources: { type: Array, default: () => [] } // opcional
})

const extra = computed(() => props.extra)
const extras = computed(() => props.extras)
const resources = computed(() => props.resources)
const hasResources = computed(() => Array.isArray(resources.value) && resources.value.length > 0)

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

/* ===== Sticky layout (misma lógica que usas en videos) ===== */
const contentCol = ref(null)
const sidebarCol = ref(null)
const stickContent = ref(false)
const stickSidebar = ref(false)
const topOffset = ref(16) // px; se recalcula

const stickyStyle = computed(() => ({ '--stick-top': `${topOffset.value}px` }))

let resizeObs
let rafId = null

function computeTopOffset() {
  let offset = 16
  const candidates = [
    document.querySelector('.navbar'),
    document.querySelector('.topnav'),
    document.querySelector('header'),
    document.querySelector('.bg-light.border-bottom')
  ]
  for (const el of candidates) {
    if (el) offset += el.getBoundingClientRect().height
  }
  topOffset.value = Math.max(16, Math.round(offset))
}

function updateStickiness() {
  if (window.innerWidth < 992) {
    stickContent.value = false
    stickSidebar.value = false
    return
  }
  const c = contentCol.value
  const s = sidebarCol.value
  if (!c || !s) return

  const ch = c.scrollHeight
  const sh = s.scrollHeight
  const tol = 24

  if (sh > ch + tol) {
    stickContent.value = true
    stickSidebar.value = false
  } else if (ch > sh + tol) {
    stickContent.value = false
    stickSidebar.value = true
  } else {
    stickContent.value = false
    stickSidebar.value = false
  }
}

function scheduleMeasure() {
  cancelAnimationFrame(rafId)
  rafId = requestAnimationFrame(() => {
    computeTopOffset()
    updateStickiness()
  })
}

onMounted(() => {
  scheduleMeasure()
  if ('ResizeObserver' in window) {
    resizeObs = new ResizeObserver(scheduleMeasure)
    if (contentCol.value) resizeObs.observe(contentCol.value)
    if (sidebarCol.value) resizeObs.observe(sidebarCol.value)
  }
  window.addEventListener('resize', scheduleMeasure, { passive: true })
  window.addEventListener('orientationchange', scheduleMeasure, { passive: true })
})

onBeforeUnmount(() => {
  cancelAnimationFrame(rafId)
  window.removeEventListener('resize', scheduleMeasure)
  window.removeEventListener('orientationchange', scheduleMeasure)
  if (resizeObs) {
    try {
      if (contentCol.value) resizeObs.unobserve(contentCol.value)
      if (sidebarCol.value) resizeObs.unobserve(sidebarCol.value)
      resizeObs.disconnect()
    } catch {}
  }
})
</script>

<style scoped>
#extra-list {
  max-height: 70vh;
  padding-right: 6px;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (min-width: 992px) {
  .lg-sticky {
    position: sticky;
    top: var(--stick-top, 16px);
  }
}

/* Contenido enriquecido */
.content.description :deep(p) { margin-bottom: 0.8rem; }
.content.description :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: .5rem;
}
</style>
