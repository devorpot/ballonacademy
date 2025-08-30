<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import Title from '@/Components/Admin/Ui/Title.vue'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  // Paginador de Laravel: { data, links, meta }
  submissions: { type: Object, required: true },
  // Alumno
  student: { type: Object, required: true },
  // Filtros actuales enviados por el backend
  filters: {
    type: Object,
    default: () => ({
      q: '',
      status: '',
      course: '',
      per_page: 20,
      sort: 'created_at',
      dir: 'desc',
    })
  },
  // Opciones para filtro por curso
  courseOptions: { type: Array, default: () => [] },
  // Opciones para filtro por estatus
  statusOptions: { type: Array, default: () => [] },
})

const page = usePage()
const toast = ref(null)

// Estado local (refleja props.filters)
const searchQuery  = ref(props.filters.q || '')
const statusFilter = ref(props.filters.status || '')
const courseFilter = ref(props.filters.course || '')
const perPage      = ref(props.filters.per_page || 20)

// Ordenamiento local para la tabla (solo visual en la página actual)
const sortKey   = ref(props.filters.sort || 'created_at')
const sortOrder = ref(props.filters.dir  || 'desc')

// Dataset actual (solo la página enviada por el backend)
const rows = computed(() => props.submissions?.data || [])

// Meta de paginación del backend
const currentPage = computed(() => props.submissions?.meta?.current_page || 1)
const totalPages  = computed(() => props.submissions?.meta?.last_page    || 1)
const totalCount  = computed(() => props.submissions?.meta?.total        || rows.value.length)

// Notificaciones flash
onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

// --- Aplicar filtros al servidor ---
let searchTimer = null
const applyFilters = (extra = {}) => {
  router.get(
    route('admin.evaluation-users.byUser', { user: props.student.id }),
    {
      q:        searchQuery.value || undefined,
      status:   statusFilter.value || undefined,
      course:   courseFilter.value || undefined,
      per_page: perPage.value,
      sort:     sortKey.value,
      dir:      sortOrder.value,
      ...extra, // e.g. { page: 3 }
    },
    { preserveScroll: true, preserveState: true, replace: true }
  )
}

// Debounce para búsqueda
watch(searchQuery, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => applyFilters(), 400)
})

// Cambios inmediatos en selects
watch([statusFilter, courseFilter, perPage], () => applyFilters())

// Paginación vía backend
const changePage = (pageNum) => {
  if (pageNum >= 1 && pageNum <= totalPages.value) {
    applyFilters({ page: pageNum })
  }
}

// Ordenamiento local (y lo enviamos también al backend para consistencia)
const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = (sortOrder.value === 'asc') ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
  applyFilters()
}

const sortIcon = (key) => {
  if (sortKey.value !== key) return 'bi bi-arrow-down-up'
  return sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down'
}

// Ordenamiento visual local sobre la página actual (opcional)
const sortedRows = computed(() => {
  const data = [...rows.value]
  const key = sortKey.value
  const dir = sortOrder.value === 'asc' ? 1 : -1

  const read = (row, k) => {
    switch (k) {
      case 'created_at': return row.created_at || ''
      case 'status':     return row.status ?? ''
      case 'id':         return row.id ?? ''
      case 'course':
        return (row.course?.title || '').toString().toLowerCase()
      case 'evaluation':
        return (row.evaluation?.title || '').toString().toLowerCase()
      default:
        return (row[k] ?? '').toString().toLowerCase()
    }
  }

  data.sort((a, b) => {
    const av = read(a, key)
    const bv = read(b, key)
    if (av < bv) return -1 * dir
    if (av > bv) return  1 * dir
    return 0
  })

  return data
})

// Reset de filtros
const clearFilters = () => {
  searchQuery.value  = ''
  statusFilter.value = ''
  courseFilter.value = ''
  perPage.value      = 20
  sortKey.value      = 'created_at'
  sortOrder.value    = 'desc'
  applyFilters({ page: 1 })
}
</script>

<template>
  <Head :title="`Gestión de Envíos · ${student.name}`" />

  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Estudiantes', route: 'admin.students.index' },
        { label: `Envíos de ${student.name}`, route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="`Envíos de ${student.name}`" />
            <div class="d-flex gap-2">
              <Link :href="route('admin.students.index')" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver a estudiantes
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Barra de búsqueda con tu componente -->
    <CrudFilters
      v-model:searchQuery="searchQuery"
      :count="totalCount"
      placeholder="Buscar por comentario, curso, evaluación, lección o video"
      item-label="envío(s)"
    />

    <!-- Filtros avanzados -->
    <section class="my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <div class="row g-2">
              <div class="col-12 col-md-4">
                <label class="form-label small text-muted">Curso</label>
                <select class="form-select" v-model="courseFilter">
                  <option value="">Todos</option>
                  <option v-for="opt in courseOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>

              <div class="col-6 col-md-3">
                <label class="form-label small text-muted">Estatus</label>
                <select class="form-select" v-model="statusFilter">
                  <option value="">Todos</option>
                  <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>

              <div class="col-6 col-md-2">
                <label class="form-label small text-muted">Por página</label>
                <select class="form-select" v-model.number="perPage">
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
              </div>

              <div class="col-12 col-md-3 d-flex align-items-end justify-content-md-end">
                <div class="btn-group">
                  <button class="btn btn-primary" @click="applyFilters">
                    <i class="bi bi-funnel me-1"></i> Aplicar
                  </button>
                  <button class="btn btn-outline-secondary" @click="clearFilters">
                    Limpiar
                  </button>
                </div>
              </div>
            </div><!-- /.row -->
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla -->
    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th role="button" @click="sortBy('id')">
                      ID <i :class="sortIcon('id')"></i>
                    </th>
                    <th>Curso</th>
                    <th>Evaluación</th>
                    <th>Lección</th>
                    <th>Video</th>
                    <th role="button" @click="sortBy('status')">
                      Estatus <i :class="sortIcon('status')"></i>
                    </th>
                    <th role="button" @click="sortBy('created_at')">
                      Enviado <i :class="sortIcon('created_at')"></i>
                    </th>
                    <th>Revisó</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="item in sortedRows" :key="item.id">
                    <td class="text-muted">{{ item.id }}</td>
                    <td>
                      <div class="text-truncate" :title="item.course?.title || ''">
                        <strong>{{ item.course?.title || '-' }}</strong>
                      </div>
                    </td>
                    <td>
                      <div class="text-truncate" :title="item.evaluation?.title || ''">
                        {{ item.evaluation?.title || '-' }}
                      </div>
                    </td>
                    <td>
                      <div class="text-truncate" :title="item.lesson?.title || ''">
                        {{ item.lesson?.title || '-' }}
                      </div>
                    </td>
                    <td>
                      <div class="text-truncate" :title="item.video?.title || ''">
                        {{ item.video?.title || '-' }}
                      </div>
                    </td>
                    <td>
                      <span class="badge rounded-pill"
                            :class="{
                              'bg-secondary': item.status === 111,
                              'bg-warning':  item.status === 222,
                              'bg-success':  item.status === 333,
                              'bg-danger':   item.status === 999
                            }">
                        {{ item.status_label }}
                      </span>
                    </td>
                    <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                    <td>{{ item.approved_user?.name || '-' }}</td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link
                          :href="route('admin.evaluation-users.show', item.id)"
                          class="btn btn-outline-success"
                          title="Ver detalle"
                        >
                          <i class="bi bi-eye-fill"></i>
                        </Link>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="rows.length === 0">
                    <td colspan="9" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No hay envíos para este usuario
                    </td>
                  </tr>
                </tbody>

              </table>
            </div>
            <!-- Paginación del backend -->
            <CrudPagination
              :current-page="currentPage"
              :total-pages="totalPages"
              @change-page="changePage"
            />
          </div>
        </div>
      </div>
    </section>

    <ToastNotification
      v-if="toast"
      :message="toast.message"
      :type="toast.type"
      @hidden="toast = null"
    />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td, .table th {
  vertical-align: middle;
}
</style>
