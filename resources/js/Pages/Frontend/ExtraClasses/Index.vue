<!-- resources/js/Pages/dashboard/ExtraClasses/Index.vue -->
<template>
  <Head title="Clases Extra" />
  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Clases Extra', route: '' }
      ]"
    />

    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="card shadow-sm border-0">
          <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">Clases Extra</h5>

            <!-- Resumen paginación (si viene paginado) -->
            <div v-if="meta" class="small text-muted">
              Mostrando {{ meta.from }}–{{ meta.to }} de {{ meta.total }}
            </div>
          </div>

          <div class="card-body">
            <!-- Empty -->
            <div v-if="!items.length" class="alert alert-info mb-0">
              No hay clases extra disponibles por el momento.
            </div>

            <!-- Grid de cards -->
            <div v-else class="row row-cols-1 row-cols-md-2 row-cols-xl-2 g-4">
              <div v-for="extra in items" :key="extra.id" class="col">
                <!-- Card hero Extra -->
                <div class="card shadow-sm border-0 overflow-hidden position-relative h-100">
                  <div class="row g-0 flex-lg-row">
                    <!-- Media -->
                    <div class="col-12 col-lg-5">
                      <div class="position-relative ratio ratio-16x9 bg-light">
                        <img
                          :src="coverUrl(extra.cover_url || extra.image_url)"
                          :alt="extra.title"
                          class="w-100 h-100 object-fit-cover"
                          loading="lazy"
                          @error="onImgError"
                        />
                        <!-- Badges -->
                       
                      </div>
                    </div>

                    <!-- Content -->
                    <div class="col-12 col-lg-7">
                      <div class="card-body d-flex flex-column h-100">
                        <!-- Title -->
                        <h3 class="h6 fw-bold mb-2 text-body text-wrap">
                          {{ extra.title }}
                        </h3>

                        <!-- Extract -->
                        <p v-if="extra.extract" class="text-secondary mb-2 clamp-2">
                          {{ extra.extract }}
                        </p>

                        <!-- Tags -->
                        <div v-if="extra.tags" class="mb-3">
                          <span
                            v-for="tag in extra.tags.split(',')"
                            :key="tag"
                            class="badge bg-secondary me-1"
                          >
                            #{{ tag.trim() }}
                          </span>
                        </div>

                        <!-- Stats -->
                        <ul class="list-inline small text-muted mb-3">
                          <li class="list-inline-item me-3" v-if="extra.views">
                            <i class="bi bi-eye me-1"></i>{{ extra.views }} vistas
                          </li>
                          <li class="list-inline-item me-3" v-if="extra.duration">
                            <i class="bi bi-clock me-1"></i>{{ extra.duration }}
                          </li>
                        </ul>

                        <!-- Actions -->
                        <div class="mt-auto d-flex gap-2">
                          <Link
                            :href="showUrl(extra.id)"
                            class="btn btn-primary btn-sm"
                            :title="`Ver clase: ${extra.title}`"
                          >
                            <i class="bi bi-play-circle me-1"></i> Ver Clase
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Stretched link para hacer clic en toda la tarjeta -->
                  <Link :href="showUrl(extra.id)" class="stretched-link" aria-label="Ver clase"></Link>
                </div>
              </div>
            </div>

            <!-- Paginación (LengthAwarePaginator) -->
            <nav v-if="hasLinks" class="mt-4" aria-label="Paginación de clases extra">
              <ul class="pagination justify-content-center mb-0 flex-wrap">
                <li
                  v-for="(l, i) in (isPaginated ? extras.links : [])"
                  :key="i"
                  class="page-item"
                  :class="{ active: l.active, disabled: !l.url }"
                >
                  <Link
                    v-if="l.url"
                    class="page-link"
                    :href="l.url"
                    preserve-scroll
                    preserve-state
                  >
                    <span v-html="labelES(l.label)"></span>
                  </Link>
                  <span v-else class="page-link" v-html="labelES(l.label)"></span>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const props = defineProps({
  // Puede llegar como paginador { data, links, meta } o como arreglo simple
  extras: { type: [Object, Array], required: true }
})

const extras = computed(() => props.extras)
const isPaginated = computed(() => !!extras.value && Array.isArray(extras.value.data))
const items = computed(() =>
  isPaginated.value ? (extras.value.data || []) : (Array.isArray(extras.value) ? extras.value : [])
)
const hasLinks = computed(() => isPaginated.value && Array.isArray(extras.value.links) && extras.value.links.length > 0)
const meta = computed(() => (isPaginated.value ? extras.value.meta : null))

function showUrl(id) {
  return route('dashboard.extras.show', { extra: id })
}

function formatDate(iso) {
  if (!iso) return ''
  try {
    const d = new Date(iso)
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return iso
  }
}

function onImgError(e) {
  e.target.src = 'https://placehold.co/600x400?text=Extra+Class'
}

function coverUrl(path) {
  if (!path) return 'https://placehold.co/600x400?text=Extra+Class'
  const p = String(path)
  if (/^https?:\/\//i.test(p)) return p
  if (p.startsWith('/storage/')) return p
  return `/storage/${p.replace(/^\/+/, '')}`
}

/**
 * Traduce y limpia las etiquetas de paginación que envía Laravel:
 * « Previous, Next » -> Anterior, Siguiente
 * Mantiene números y elimina HTML no deseado (se permite <i>).
 */
function labelES(label) {
  const raw = String(label)
    .replace(/&laquo;|«/g, '')
    .replace(/&raquo;|»/g, '')
    .trim()

  const text = raw
    .replace(/^Previous$/i, 'Anterior')
    .replace(/^Next$/i, 'Siguiente')

  // Evita HTML inesperado, permite etiquetas <i>
  return text.replace(/<(?!\/?i\\b)[^>]*>/g, '')
}
</script>

<style scoped>
/* Clamp utilitario para extracto */
.clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.object-fit-cover {
  object-fit: cover;
}
</style>
