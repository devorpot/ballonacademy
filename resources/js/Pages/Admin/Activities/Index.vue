<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'
import axios from 'axios'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import Title from '@/Components/Admin/Ui/Title.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  activities: { type: Object, required: false, default: () => ({ data: [], meta: { current_page: 1, last_page: 1 } }) },
  filters: {
    type: Object,
    default: () => ({
      user_id: null,
      perPage: 30,
      sortBy: 'activities.created_at',
      sortDir: 'desc',
      q: ''
    })
  }
})

/** ===== Estado ===== */
const toast = ref(null)
const activities = ref(props.activities ?? { data: [], meta: { current_page: 1, last_page: 1 } })
const loading = ref(false)

const state = reactive({
  user_id: props.filters.user_id ?? null,
  perPage: Number(props.filters.perPage ?? 30),
  page: Number(props.activities?.meta?.current_page ?? props.activities?.current_page ?? 1),
  sortBy: props.filters.sortBy ?? 'activities.created_at',
  sortDir: props.filters.sortDir ?? 'desc',
  q: props.filters.q ?? ''
})

/** ===== Pager ===== */
const pager = computed(() => {
  const c = activities.value || {}
  const meta = c.meta || {}
  return {
    current_page: Number(meta.current_page ?? c.current_page ?? 1),
    last_page:    Number(meta.last_page    ?? c.last_page    ?? 1),
  }
})
const currentPage = computed(() => pager.value.current_page)

/** ===== Fetch (axios) ===== */
async function fetchActivities() {
  loading.value = true
  try {
    const { data } = await axios.get(route('admin.activities.list'), {
      params: {
        user_id: state.user_id || undefined,
        q: state.q || undefined,
        perPage: state.perPage,
        page: state.page,
        sortBy: state.sortBy,
        sortDir: state.sortDir,
      }
    })
    activities.value = data
  } catch (e) {
    toast.value = { message: 'No se pudo cargar la actividad', type: 'danger' }
  } finally {
    loading.value = false
  }
}

function onPageChange(pageNumber) {
  state.page = pageNumber
  fetchActivities()
}

/** ===== Watchers ===== */
let searchTimer = null
watch(() => state.perPage, () => { state.page = 1; fetchActivities() })
watch(() => state.sortBy,  () => { state.page = 1; fetchActivities() })
watch(() => state.sortDir, () => { state.page = 1; fetchActivities() })
watch(() => state.q, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { state.page = 1; fetchActivities() }, 300)
})
watch(() => state.user_id, () => { state.page = 1; fetchActivities() })

onMounted(() => { fetchActivities() })

/** ===== Autocomplete Usuario (remoto) ===== */
const userQuery = ref('')
const userResults = ref([])
const isSearching = ref(false)
const showUserDropdown = ref(false)
const selectedUserLabel = ref('')

let userSearchTimer = null
async function doUserSearch(q) {
  if (!q || String(q).trim() === '') {
    userResults.value = []
    return
  }
  isSearching.value = true
  try {
    const { data } = await axios.get(route('admin.activities.users_search'), { params: { q } })
    userResults.value = Array.isArray(data) ? data : []
  } catch {
    userResults.value = []
  } finally {
    isSearching.value = false
  }
}
function onUserInput() {
  clearTimeout(userSearchTimer)
  userSearchTimer = setTimeout(() => {
    showUserDropdown.value = true
    doUserSearch(userQuery.value)
  }, 250)
}
function selectUser(u) {
  state.user_id = u?.id ?? null
  selectedUserLabel.value = u ? `${u.name} — ${u.email}` : ''
  userQuery.value = ''
  userResults.value = []
  showUserDropdown.value = false
}
function clearUser() {
  state.user_id = null
  selectedUserLabel.value = ''
  userQuery.value = ''
  userResults.value = []
  showUserDropdown.value = false
}

/** Cerrar dropdown al hacer click afuera */
function onClickOutside(e) {
  const el = document.getElementById('user-autocomplete')
  if (el && !el.contains(e.target)) showUserDropdown.value = false
}
onMounted(() => document.addEventListener('click', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))

/** ===== Helpers UI ===== */
function formatDate(dt) {
  if (!dt) return '—'
  try { const d = new Date(dt); return d.toLocaleString() } catch { return dt }
}

/** ===== Modal Detalles ===== */
const showDetails = ref(false)
const selectedActivity = ref(null)

function openDetails(a) {
  selectedActivity.value = a
  showDetails.value = true
}
function closeDetails() {
  showDetails.value = false
  selectedActivity.value = null
}

/** ===== Ordenamiento por columnas (click en TH) ===== */
function toggleSort(col) {
  if (state.sortBy === col) {
    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc'
  } else {
    state.sortBy = col
    state.sortDir = 'asc'
  }
  state.page = 1
  fetchActivities()
}

function isSorted(col) {
  return state.sortBy === col
}
function sortDirIcon(col) {
  if (!isSorted(col)) return 'bi-arrow-down-up' // neutro
  return state.sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill'
}
function ariaSort(col) {
  if (!isSorted(col)) return 'none'
  return state.sortDir === 'asc' ? 'ascending' : 'descending'
}
</script>

<template>
  <Head title="Actividad del Sitio" />

  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Actividad', route: '' }
      ]"
    />

    <section class="section-heading pt-2">
      <div class="container-fluid">
           <div class="row">
             <div class="col-12">
                <Title :title="'Actividad del Sitio'" />
             </div>
           </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-panel py-2">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-lg-6" id="user-autocomplete">
                <label class="form-label">Usuario</label>

                <div class="input-group mb-2" v-if="state.user_id">
                  <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                  <input type="text" class="form-control" :value="selectedUserLabel" readonly />
                  <button class="btn btn-outline-secondary" type="button" @click="clearUser">
                    <i class="bi bi-x-circle"></i> Limpiar
                  </button>
                </div>

                <div class="position-relative">
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input
                      v-model="userQuery"
                      type="text"
                      class="form-control"
                      placeholder="Buscar por nombre o email…"
                      @input="onUserInput"
                      @focus="showUserDropdown = true"
                    />
                  </div>

                  <div
                    v-if="showUserDropdown"
                    class="list-group shadow position-absolute w-100 mt-1"
                    style="z-index: 1050; max-height: 320px; overflow: auto;"
                  >
                    <div v-if="isSearching" class="list-group-item text-muted small">
                      Buscando…
                    </div>
                    <button
                      v-for="u in userResults"
                      :key="u.id"
                      type="button"
                      class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                      @click="selectUser(u)"
                    >
                      <span>
                        <span class="fw-semibold">{{ u.name }}</span>
                        <small class="text-muted ms-2">{{ u.email }}</small>
                      </span>
                      <i class="bi bi-chevron-right"></i>
                    </button>
                    <div v-if="!isSearching && !userResults.length" class="list-group-item text-muted small">
                      Sin resultados
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-lg-2 ms-auto">
                <label class="form-label">Por página</label>
                <select v-model.number="state.perPage" class="form-select">
                  <option :value="10">10</option>
                  <option :value="30">30</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla -->
    <section class="section-panel py-2">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <!-- ID -->
                  <th
                    style="width:80px; cursor:pointer"
                    role="button"
                    :aria-sort="ariaSort('activities.id')"
                    @click="toggleSort('activities.id')"
                    class="user-select-none"
                  >
                    <div class="d-flex align-items-center gap-1">
                      <span>#</span>
                      <i class="bi" :class="sortDirIcon('activities.id')"></i>
                    </div>
                  </th>

                  <!-- Usuario -->
                  <th
                    style="min-width:220px; cursor:pointer"
                    role="button"
                    :aria-sort="ariaSort('users.name')"
                    @click="toggleSort('users.name')"
                    class="user-select-none"
                  >
                    <div class="d-flex align-items-center gap-1">
                      <span>Usuario</span>
                      <i class="bi" :class="sortDirIcon('users.name')"></i>
                    </div>
                  </th>

                  <!-- Curso -->
                  <th
                    style="cursor:pointer"
                    role="button"
                    :aria-sort="ariaSort('courses.title')"
                    @click="toggleSort('courses.title')"
                    class="user-select-none"
                  >
                    <div class="d-flex align-items-center gap-1">
                      <span>Curso</span>
                      <i class="bi" :class="sortDirIcon('courses.title')"></i>
                    </div>
                  </th>

                  <!-- Fecha -->
                  <th
                    style="min-width:160px; cursor:pointer"
                    role="button"
                    :aria-sort="ariaSort('activities.created_at')"
                    @click="toggleSort('activities.created_at')"
                    class="user-select-none"
                  >
                    <div class="d-flex align-items-center gap-1">
                      <span>Fecha</span>
                      <i class="bi" :class="sortDirIcon('activities.created_at')"></i>
                    </div>
                  </th>

                  <th style="min-width:120px">Acciones</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="!activities?.data?.length">
                  <td colspan="5" class="text-center py-4 text-muted">No hay actividad registrada.</td>
                </tr>

                <tr v-for="a in activities.data" :key="a.id">
                  <td>{{ a.id }}</td>
                  <td>
                    <div class="fw-semibold">{{ a.user?.name || '—' }}</div>
                    <small class="text-muted">
                      {{ a.user?.email || `ID: ${a.user_id ?? '—'}` }}
                    </small>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width: 240px;">
                      {{ a.course?.title || (a.course_id ? `#${a.course_id}` : '—') }}
                    </div>
                  </td>
                  <td>{{ formatDate(a.created_at) }}</td>
                  <td class="">

                    <button class="btn btn-sm btn-primary " @click="openDetails(a)">
                      <i class="bi bi-eye "></i> Detalles
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="card-body border-top">
            <nav v-if="activities && (activities.meta || typeof activities.current_page !== 'undefined')">
              <ul class="pagination mb-0 justify-content-end">
                <li class="page-item" :class="{ disabled: currentPage <= 1 }">
                  <button class="page-link" @click="onPageChange(currentPage - 1)" :disabled="currentPage <= 1">Anterior</button>
                </li>
                <li class="page-item disabled">
                  <span class="page-link">Página {{ pager.current_page }} de {{ pager.last_page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: currentPage >= pager.last_page }">
                  <button class="page-link" @click="onPageChange(currentPage + 1)" :disabled="currentPage >= pager.last_page">Siguiente</button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Detalles (sin mostrar 'type') -->
    <div class="modal fade show" tabindex="-1" style="display:block;" v-if="showDetails" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalles de la actividad #{{ selectedActivity?.id }}</h5>
            <button type="button" class="btn-close" @click="closeDetails"></button>
          </div>
          <div class="modal-body">
            <dl class="row mb-0">
              <dt class="col-sm-3">Fecha</dt>
              <dd class="col-sm-9">{{ formatDate(selectedActivity?.created_at) }}</dd>

              <dt class="col-sm-3">Usuario</dt>
              <dd class="col-sm-9">
                <div class="fw-semibold">{{ selectedActivity?.user?.name || '—' }}</div>
                <div class="text-muted small">{{ selectedActivity?.user?.email || (selectedActivity?.user_id ? `ID: ${selectedActivity.user_id}` : '—') }}</div>
              </dd>

              <dt class="col-sm-3">Curso</dt>
              <dd class="col-sm-9">
                {{ selectedActivity?.course?.title || (selectedActivity?.course_id ? `#${selectedActivity.course_id}` : '—') }}
              </dd>

              <dt class="col-sm-3">Video</dt>
              <dd class="col-sm-9">
                {{ selectedActivity?.video?.title || (selectedActivity?.video_id ? `#${selectedActivity.video_id}` : '—') }}
              </dd>

              <dt class="col-sm-3">Evaluación</dt>
              <dd class="col-sm-9">
                {{ selectedActivity?.evaluation?.title || (selectedActivity?.evaluation_id ? `#${selectedActivity.evaluation_id}` : '—') }}
              </dd>

              <!-- No se muestra 'type' ni 'description' -->
            </dl>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeDetails">Cerrar</button>
          </div>
        </div>
      </div>
      <!-- Backdrop manual -->
      <div class="modal-backdrop fade show"></div>
    </div>

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
/* Evita scroll de fondo cuando el modal está visible (modal simple sin JS de Bootstrap) */
:global(body) { overflow: auto; }
</style>
