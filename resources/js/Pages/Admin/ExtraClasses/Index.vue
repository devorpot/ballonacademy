<!-- resources/js/Pages/Admin/ExtraClasses/Index.vue -->
<script setup>
import { Inertia } from '@inertiajs/inertia'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
import { route } from 'ziggy-js'
import axios from 'axios'
import draggable from 'vuedraggable'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'
import CreateExtraClassModal from '@/Pages/Admin/ExtraClasses/CreateExtraClassModal.vue'
import ShowExtraClassModal from '@/Pages/Admin/ExtraClasses/ShowExtraClassModal.vue'

const props = defineProps({
  extras:  { type: Object, required: true }, // paginator: {data, meta, links}
  filters: {
    type: Object,
    default: () => ({ q: '', active: '', category: '', per_page: 20, sortBy: 'order', sortDir: 'asc' })
  }
})

const page = usePage()

/* ===== Filtros ===== */
const searchQuery      = ref(props.filters.q || '')
const selectedActive   = ref(props.filters.active ?? '')
const selectedCategory = ref(props.filters.category ?? '')
const perPage          = ref(props.filters.per_page || 20)

/* ===== Orden visual (tabla) ===== */
const sortKey   = ref(props.filters.sortBy || 'order')
const sortOrder = ref(props.filters.sortDir || 'asc')

/* ===== Modales / estado ===== */
const showCreateModal = ref(false)
const showViewModal   = ref(false)
const selectedItem    = ref(null)

const showDeleteModal = ref(false)
const deletingId      = ref(null)
const isDeleting      = ref(false)

const toast = ref(null)

/* ===== Flash ===== */
onMounted(() => {
  const flash = page.props?.value?.flash || {}
  if (flash.success) toast.value = { message: flash.success, type: 'success' }
  if (flash.error)   toast.value = { message: flash.error,   type: 'danger'  }
})

/* ===== Helpers ===== */
const sortIcon = (key) => {
  return sortKey.value === key
    ? (sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down')
    : 'bi bi-arrow-down-up'
}

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const currentPage = computed(() => props.extras?.meta?.current_page || 1)
const totalPages  = computed(() => props.extras?.meta?.last_page   || 1)

/* ===== Navegación / búsqueda ===== */
const goToPage = (pageNumber) => {
  Inertia.get(route('admin.extras.index'), {
    q: searchQuery.value || undefined,
    active: selectedActive.value || undefined,
    category: selectedCategory.value || undefined,
    per_page: perPage.value || undefined,
    page: pageNumber,
    sortBy: sortKey.value,
    sortDir: sortOrder.value
  }, { preserveScroll: true, preserveState: true, replace: true })
}

let debounceTimer = null
const triggerSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    Inertia.get(route('admin.extras.index'), {
      q: searchQuery.value || undefined,
      active: selectedActive.value || undefined,
      category: selectedCategory.value || undefined,
      per_page: perPage.value || undefined,
      sortBy: sortKey.value,
      sortDir: sortOrder.value
    }, { preserveScroll: true, preserveState: true, replace: true })
  }, 350)
}
watch([searchQuery, selectedActive, selectedCategory, perPage, sortKey, sortOrder], triggerSearch)

/* ===== Tabla: datos de la página actual (ordenados para vista) ===== */
const sortedPageData = computed(() => {
  const data = [...(props.extras?.data || [])]
  const key  = sortKey.value
  const ord  = sortOrder.value
  const norm = (val) => {
    if (val === null || val === undefined) return ''
    if (typeof val === 'number') return val
    if (typeof val === 'string') return val.toLowerCase()
    return String(val).toLowerCase()
  }
  data.sort((a, b) => {
    const av = norm(a?.[key])
    const bv = norm(b?.[key])
    if (av < bv) return ord === 'asc' ? -1 : 1
    if (av > bv) return ord === 'asc' ?  1 : -1
    return 0
  })
  return data
})

/* ===== Reordenar como en VideosPanel (drag sobre <tbody>) ===== */
const tableRows = ref(
  [...(props.extras?.data || [])]
    .sort((a, b) => (a.order ?? 9999) - (b.order ?? 9999))
)

// Rehidratar tableRows cada que cambie la página (por paginación o filtros)
watch(() => props.extras?.data, (rows) => {
  tableRows.value = [...(rows || [])].sort((a, b) => (a.order ?? 9999) - (b.order ?? 9999))
})

// Arrastre finalizado: renumera y envía al backend
const onDragEnd = async () => {
  // Reflejo visual (1..n) en la página actual
  tableRows.value.forEach((row, idx) => { row.order = idx + 1 })

  // Enviar ids en orden
  const ids = tableRows.value.map(r => r.id)
  try {
    await axios.post(route('admin.extras.reorder'), { ids })
    toast.value = { message: 'Orden actualizado', type: 'success' }
    // refrescar solo el recurso
     Inertia.reload({ only: ['extras'] })
  } catch (e) {
    toast.value = { message: 'No se pudo actualizar el orden', type: 'danger' }
    // revertir vista si falla: recargar lista
    Inertia.reload({ only: ['extras'] })
  }
}

/* ===== Ver / Eliminar ===== */
const openViewModal = (item) => { selectedItem.value = item; showViewModal.value = true }

const prepareDelete = (id) => { deletingId.value = id; showDeleteModal.value = true }

const cancelDelete = () => {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}

const deleteItem = () => {
  if (!deletingId.value) return
  isDeleting.value = true
  Inertia.delete(route('admin.extras.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete()
      toast.value = { message: 'Clase extra eliminada', type: 'success' }
      Inertia.reload({ only: ['extras'] })
    },
    onError: () => {
      isDeleting.value = false
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' }
    },
    onFinish: () => { isDeleting.value = false }
  })
}

/* ===== Después de crear ===== */
const onCreated = () => {
  toast.value = { message: 'Clase extra creada exitosamente', type: 'success' }
  showCreateModal.value = false
  Inertia.reload({ only: ['extras'] })
}
</script>

<template>
  <Head title="Clases Extra" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Clases Extra', route: '' }
      ]"
    />

    <!-- Encabezado -->
    <section class="section-heading py-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title">
              <i class="bi bi-collection-play me-2"></i> Gestionar Clases Extra
            </h4>
            <Link class="btn btn-primary" :href="route('admin.extras.create')">
              <i class="bi bi-plus-lg me-1"></i> Nueva Clase Extra
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-data py-2">
      <div class="container-fluid">
        <div class="card border">
          <div class="card-body">
            <CrudFilters
              v-model:searchQuery="searchQuery"
              :count="extras?.meta?.total || 0"
              placeholder="Buscar por título, extracto o tags..."
              item-label="resultado(s)"
            >
              <template #extra>
                <div class="row g-2">
                  <div class="col-12 col-sm-4">
                    <select class="form-select" v-model="selectedActive">
                      <option value="">Estado: todos</option>
                      <option value="1">Activo</option>
                      <option value="2">Inactivo</option>
                    </select>
                  </div>
                  <div class="col-12 col-sm-4">
                    <input class="form-control" v-model="selectedCategory" placeholder="Categoría" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <select class="form-select" v-model.number="perPage">
                      <option :value="10">10 por página</option>
                      <option :value="20">20 por página</option>
                      <option :value="50">50 por página</option>
                      <option :value="100">100 por página</option>
                    </select>
                  </div>
                </div>
              </template>
            </CrudFilters>
            <div v-if="searchQuery" class="alert alert-info mt-3 mb-0 py-2 small">
              Para reordenar, limpia la búsqueda.
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla con drag en <tbody>, igual que VideosPanel -->
    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:40px;"></th>
                    <th @click="sortBy('id')" style="cursor:pointer;">ID <i :class="sortIcon('id')"></i></th>
                    <th>Miniatura</th>
                    <th @click="sortBy('title')" style="cursor:pointer;">Título <i :class="sortIcon('title')"></i></th>
                    <th @click="sortBy('category')" style="cursor:pointer;">Categoría <i :class="sortIcon('category')"></i></th>
                    <th>Origen</th>
                    <th @click="sortBy('active')" style="cursor:pointer;">Estado <i :class="sortIcon('active')"></i></th>
                    <th @click="sortBy('order')" style="cursor:pointer;">Orden <i :class="sortIcon('order')"></i></th>
                    <th @click="sortBy('created_at')" style="cursor:pointer;">Creada <i :class="sortIcon('created_at')"></i></th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>

                <!-- Draggable si NO hay búsqueda -->
                <draggable
                  v-if="!searchQuery"
                  v-model="tableRows"
                  tag="tbody"
                  :animation="200"
                  handle=".drag-handle"
                  item-key="id"
                  @end="onDragEnd"
                >
                  <template #item="{ element: item }">
                    <tr>
                      <td>
                        <i class="bi bi-arrows-move drag-handle text-muted" style="cursor:grab;" title="Arrastrar para reordenar"></i>
                      </td>
                      <td>{{ item.id }}</td>
                      <td style="width:80px;">
                        <img
                          v-if="item.image_url || item.cover_url"
                          :src="item.image_url || item.cover_url"
                          class="rounded border"
                          style="width:60px;height:40px;object-fit:cover;"
                          :alt="item.title"
                        />
                        <div v-else class="text-muted small">Sin imagen</div>
                      </td>
                      <td class="fw-semibold">
                        <div class="text-truncate" style="max-width: 280px;">{{ item.title }}</div>
                        <div class="text-muted small text-truncate" style="max-width: 280px;">
                          {{ item.extract }}
                        </div>
                        <div class="mt-1">
                          <span v-if="item.order !== null && item.order !== undefined" class="badge bg-info-subtle text-info-emphasis">
                            <i class="bi bi-list-ol me-1"></i>#{{ item.order }}
                          </span>
                        </div>
                      </td>
                      <td>
                        <span v-if="item.category" class="badge text-bg-light border">{{ item.category }}</span>
                        <span v-else class="text-muted small">Sin categoría</span>
                      </td>
                      <td>
                        <span v-if="item.is_youtube === 1" class="badge text-bg-danger">YouTube</span>
                        <span v-else class="badge text-bg-secondary">Archivo</span>
                      </td>
                      <td>
                        <span v-if="item.active === 1" class="badge text-bg-success">Activo</span>
                        <span v-else class="badge text-bg-secondary">Inactivo</span>
                      </td>
                      <td>{{ item.order ?? '—' }}</td>
                      <td>{{ new Date(item.created_at).toLocaleDateString() }}</td>
                      <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                          <Link :href="route('admin.extras.edit', item.id)" class="btn btn-outline-warning" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                          </Link>
                          <button class="btn btn-outline-info" @click="openViewModal(item)" title="Ver">
                            <i class="bi bi-eye-fill"></i>
                          </button>
                          <button class="btn btn-outline-danger" @click="prepareDelete(item.id)" :disabled="isDeleting" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </draggable>

                <!-- Sin drag si hay búsqueda -->
                <tbody v-else>
                  <tr v-for="item in sortedPageData" :key="item.id">
                    <td></td>
                    <td>{{ item.id }}</td>
                    <td style="width:80px;">
                      <img
                        v-if="item.image_url || item.cover_url"
                        :src="item.image_url || item.cover_url"
                        class="rounded border"
                        style="width:60px;height:40px;object-fit:cover;"
                        :alt="item.title"
                      />
                      <div v-else class="text-muted small">Sin imagen</div>
                    </td>
                    <td class="fw-semibold">
                      <div class="text-truncate" style="max-width: 280px;">{{ item.title }}</div>
                      <div class="text-muted small text-truncate" style="max-width: 280px;">
                        {{ item.extract }}
                      </div>
                    </td>
                    <td>
                      <span v-if="item.category" class="badge text-bg-light border">{{ item.category }}</span>
                      <span v-else class="text-muted small">Sin categoría</span>
                    </td>
                    <td>
                      <span v-if="item.is_youtube === 1" class="badge text-bg-danger">YouTube</span>
                      <span v-else class="badge text-bg-secondary">Archivo</span>
                    </td>
                    <td>
                      <span v-if="item.active === 1" class="badge text-bg-success">Activo</span>
                      <span v-else class="badge text-bg-secondary">Inactivo</span>
                    </td>
                    <td>{{ item.order ?? '—' }}</td>
                    <td>{{ new Date(item.created_at).toLocaleDateString() }}</td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link :href="route('admin.extras.edit', item.id)" class="btn btn-outline-warning" title="Editar">
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button class="btn btn-outline-info" @click="openViewModal(item)" title="Ver">
                          <i class="bi bi-eye-fill"></i>
                        </button>
                        <button class="btn btn-outline-danger" @click="prepareDelete(item.id)" :disabled="isDeleting" title="Eliminar">
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="(extras?.data || []).length === 0">
                    <td colspan="10" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron clases extra
                    </td>
                  </tr>
                </tbody>

              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <CrudPagination
      :current-page="currentPage"
      :total-pages="totalPages"
      @change-page="goToPage"
    />

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteItem"
      title="¿Deseas eliminar esta clase extra?"
      confirm-message="Por favor confirma la eliminación de esta clase"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />

    <CreateExtraClassModal
      v-if="showCreateModal"
      :show="showCreateModal"
      @saved="onCreated"
      @close="showCreateModal = false"
    />

    <ShowExtraClassModal
      v-if="showViewModal"
      :show="showViewModal"
      :item="selectedItem"
      @close="showViewModal = false"
    />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
.admin-title { margin: 0; }
</style>
