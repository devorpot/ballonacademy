<template>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Cursos inscritos</h5>

        <div class="d-flex gap-2">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input
              v-model="query"
              type="search"
              class="form-control"
              placeholder="Buscar curso..."
              aria-label="Buscar curso"
            />
          </div>

          <div class="input-group">
            <span class="input-group-text">Ordenar por</span>
            <select v-model="sortBy" class="form-select" aria-label="Ordenar por">
              <option value="created_at">Fecha de inscripción</option>
              <option value="title">Título</option>
            </select>
            <button
              class="btn btn-outline-secondary"
              :aria-label="`Cambiar orden (${sortDir === 'desc' ? 'Desc' : 'Asc'})`"
              @click="toggleSortDir"
              type="button"
            >
              <i class="bi" :class="sortDir === 'desc' ? 'bi-sort-down' : 'bi-sort-up'"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:64px;">Logo</th>
              <th>Curso</th>
              <th>Descripción</th>
              <th>Fecha de inscripción</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>

          <tbody v-if="paged.length">
            <tr v-for="item in paged" :key="item.id">
              <td>
                <img
                  v-if="thumbSrc(item)"
                  :src="thumbSrc(item)"
                  :alt="`Logo ${item.title}`"
                  class="rounded object-fit-cover"
                  style="width:48px;height:48px"
                />
                <div v-else class="bg-secondary text-white d-inline-flex rounded justify-content-center align-items-center"
                     style="width:48px;height:48px;font-weight:600;">
                  {{ initials(item.title) }}
                </div>
              </td>

              <td class="fw-medium">
                <a
                  v-if="courseHref(item)"
                  :href="courseHref(item)"
                  class="text-decoration-none"
                >
                  {{ item.title }}
                </a>
                <span v-else>{{ item.title }}</span>
              </td>

              <td class="text-muted">
                <span class="d-inline-block text-truncate" style="max-width: 40ch;">
                  {{ item.description || '—' }}
                </span>
              </td>

              <td>
                <span class="small text-muted d-block">ID: {{ item.subscription?.id ?? '—' }}</span>
                <span>{{ formatDate(item.subscription?.created_at) }}</span>
              </td>

              <td class="text-end">
                <div class="btn-group">
                  <a
                    v-if="courseHref(item)"
                    :href="courseHref(item)"
                    class="btn btn-outline-primary btn-sm"
                    title="Ver curso"
                  >
                    <i class="bi bi-box-arrow-up-right me-1"></i>Ver
                  </a>
                  <button
                    v-else
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    disabled
                    title="Ruta no configurada"
                  >
                    <i class="bi bi-slash-circle me-1"></i>Sin ruta
                  </button>
                </div>
              </td>
            </tr>
          </tbody>

          <tbody v-else>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                No hay cursos que coincidan con la búsqueda.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
          Mostrando {{ startIndex + 1 }}–{{ endIndex }} de {{ filtered.length }}
        </small>

        <nav aria-label="Paginación cursos">
          <ul class="pagination mb-0">
            <li class="page-item" :class="{ disabled: page === 1 }">
              <button class="page-link" @click="goTo(page - 1)" :disabled="page === 1">Anterior</button>
            </li>
            <li
              v-for="n in totalPages"
              :key="n"
              class="page-item"
              :class="{ active: n === page }"
            >
              <button class="page-link" @click="goTo(n)">{{ n }}</button>
            </li>
            <li class="page-item" :class="{ disabled: page === totalPages }">
              <button class="page-link" @click="goTo(page + 1)" :disabled="page === totalPages">Siguiente</button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  enrollments: { type: Array, required: true },

  // Nombre de ruta Ziggy para ver curso. Cambia según tu app:
  // ejemplos: 'admin.courses.show' o 'dashboard.courses.show'
  courseRouteName: { type: String, default: 'admin.courses.show' },

  // Items por página
  perPage: { type: Number, default: 10 },

  // Locale para formatear fecha
  dateLocale: { type: String, default: 'es-MX' },
})

const query   = ref('')
const sortBy  = ref('created_at') // 'created_at' | 'title'
const sortDir = ref('desc')       // 'asc' | 'desc'
const page    = ref(1)

const normalized = computed(() =>
  (props.enrollments || []).map(e => ({
    id: e.id,
    title: e.title,
    image_cover: e.image_cover || null,
    logo: e.logo || null,
    description: e.description || '',
    subscription: e.subscription || null
  }))
)

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return normalized.value
  return normalized.value.filter(e =>
    (e.title || '').toLowerCase().includes(q) ||
    (e.description || '').toLowerCase().includes(q)
  )
})

const sorted = computed(() => {
  const list = [...filtered.value]
  list.sort((a, b) => {
    if (sortBy.value === 'title') {
      const at = (a.title || '').toLowerCase()
      const bt = (b.title || '').toLowerCase()
      return sortDir.value === 'asc' ? at.localeCompare(bt) : bt.localeCompare(at)
    }
    // Por fecha (subscription.created_at)
    const ad = a.subscription?.created_at ? new Date(a.subscription.created_at).getTime() : 0
    const bd = b.subscription?.created_at ? new Date(b.subscription.created_at).getTime() : 0
    return sortDir.value === 'asc' ? ad - bd : bd - ad
  })
  return list
})

const totalPages = computed(() => Math.max(1, Math.ceil(sorted.value.length / props.perPage)))
const startIndex = computed(() => (page.value - 1) * props.perPage)
const endIndex   = computed(() => Math.min(sorted.value.length, startIndex.value + props.perPage))
const paged      = computed(() => sorted.value.slice(startIndex.value, endIndex.value))

function goTo(n) {
  if (n < 1 || n > totalPages.value) return
  page.value = n
}

function toggleSortDir() {
  sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
}

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(props.dateLocale, {
      year: 'numeric', month: 'short', day: '2-digit',
      hour: '2-digit', minute: '2-digit'
    }).format(new Date(value))
  } catch {
    return value
  }
}

function thumbSrc(item) {
  // Preferimos logo si existe, si no la portada
  const path = item.logo || item.image_cover
  return path ? `/storage/${path}` : null
}

function initials(text) {
  const s = (text || '').trim()
  return s ? s.split(/\s+/).map(p => p[0]).slice(0,2).join('').toUpperCase() : 'C'
}

function courseHref(item) {
  try {
    return route(props.courseRouteName, { course: item.id })
  } catch {
    return null
  }
}
</script>

<style scoped>
.object-fit-cover { object-fit: cover; }
.table td, .table th { vertical-align: middle; }
.text-truncate { max-width: 100%; }
</style>
