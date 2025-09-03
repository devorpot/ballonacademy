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
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">Clases Extra</h5>
              </div>

              <div class="card-body">
                <!-- Empty -->
                <div v-if="!items.length" class="alert alert-info">
                  No hay clases extra disponibles por el momento.
                </div>

                <!-- Grid -->
                <div v-else class="row g-4">
                  <div v-for="extra in items" :key="extra.id" class="col-12 col-lg-6">
                    <article class="extra-hero h-100 rounded-4 overflow-hidden d-flex flex-column flex-lg-row shadow-0 border-0">
                      <!-- Media -->
                      <div class="extra-hero__media position-relative">
                        <img
                          :src="coverUrl(extra.cover_url || extra.image_url)"
                          :alt="extra.title"
                          class="w-100 h-100 object-cover"
                          loading="lazy"
                          @error="onImgError"
                        />
                        <span
                          v-if="extra.category"
                          class="badge bg-primary position-absolute top-0 end-0 m-2"
                        >
                          {{ extra.category }}
                        </span>
                        <span
                          v-if="extra.created_at"
                          class="badge bg-light text-dark position-absolute bottom-0 start-0 m-2 small"
                        >
                          <i class="bi bi-calendar-event me-1"></i>
                          {{ formatDate(extra.created_at) }}
                        </span>
                      </div>

                      <!-- Content -->
                      <div class="extra-hero__content p-4 d-flex flex-column flex-grow-1">
                        <!-- Title -->
                        <h3 class="h5 fw-bold mb-2 text-body text-wrap">
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
                            <i class="bi bi-eye me-1"></i> {{ extra.views }} vistas
                          </li>
                          <li class="list-inline-item me-3" v-if="extra.duration">
                            <i class="bi bi-clock me-1"></i> {{ extra.duration }}
                          </li>
                          <li class="list-inline-item" v-if="Number(extra.active) === 1">
                            <i class="bi bi-check-circle-fill text-success me-1"></i> Activa
                          </li>
                          <li class="list-inline-item" v-else>
                            <i class="bi bi-x-circle-fill text-danger me-1"></i> Inactiva
                          </li>
                        </ul>

                        <!-- Actions -->
                        <div class="mt-auto d-flex gap-2">
                          <Link
                            :href="showUrl(extra.id)"
                            class="btn btn-primary btn-sm rounded-pill"
                            :title="`Ver clase: ${extra.title}`"
                          >
                            <i class="bi bi-play-circle me-1"></i> Ver Clase
                          </Link>
                          <button
                            type="button"
                            class="btn btn-outline-secondary rounded-pill btn-sm"
                            title="Agregar a favoritos"
                          >
                            <i class="bi bi-heart"></i>
                          </button>
                        </div>
                      </div>
                    </article>
                  </div>
                </div>

                <!-- Paginación (LengthAwarePaginator) -->
                <nav v-if="hasLinks" class="mt-4">
                  <ul class="pagination mb-0">
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
                        v-html="sanitizeLabel(l.label)"
                      />
                      <span v-else class="page-link" v-html="sanitizeLabel(l.label)"></span>
                    </li>
                  </ul>
                </nav>

              </div><!-- /card-body -->
            </div><!-- /card -->
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

function showUrl(id) {
  // Ruta del dashboard (no la de dashboard/admin)
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

// Los labels de paginación pueden venir con &laquo; &raquo; etc.
// Permitimos esas entidades pero evitamos HTML inesperado
function sanitizeLabel(label) {
  return String(label).replace(/<(?!\/?i\b)[^>]*>/g, '')
}
</script>

<style scoped>
.extra-hero {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: .75rem;
  box-shadow: 0 .25rem .5rem rgba(0,0,0,.05);
  transition: transform .15s ease, box-shadow .15s ease;
}
.extra-hero:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
}

/* Media */
.extra-hero__media {
  width: 100%;
  min-height: 200px;
  max-height: 260px;
  background: #f8f9fa;
}
@media (min-width: 992px) {
  .extra-hero__media {
    width: 40%;
    min-height: 100%;
  }
}
.object-cover {
  object-fit: cover;
  width: 100%;
  height: 100%;
  display: block;
}

/* Contenido */
.extra-hero__content {
  min-height: 200px;
  color: #212529;
}

/* Clamp utilitario */
.clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Paginación */
.pagination .page-item.disabled .page-link {
  pointer-events: none;
}
</style>
