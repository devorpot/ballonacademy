<script setup>
import { Inertia } from '@inertiajs/inertia'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'
import CreateExtraClassModal from '@/Pages/Admin/ExtraClasses/CreateExtraClassModal.vue'
import ShowExtraClassModal from '@/Pages/Admin/ExtraClasses/ShowExtraClassModal.vue'

const props = defineProps({
  // Ajustado: ahora esperamos 'extras' desde el controlador
  extras: { type: Object, required: true }, // Resultado de paginate(): data, meta, links
  filters: {
    type: Object,
    default: () => ({ q: '', active: '', category: '', per_page: 20, sortBy: 'created_at', sortDir: 'desc' })
  }
})

const page = usePage()

// Filtros controlados
const searchQuery = ref(props.filters.q || '')
const selectedActive = ref(props.filters.active ?? '')
const selectedCategory = ref(props.filters.category ?? '')
const perPage = ref(props.filters.per_page || 20)

// Ordenamiento local (por defecto reflejamos filtros iniciales)
const sortKey = ref(props.filters.sortBy || 'created_at')
const sortOrder = ref(props.filters.sortDir || 'desc')

// Modales
const showCreateModal = ref(false)
const showViewModal = ref(false)
const selectedItem = ref(null)

// Eliminación
const showDeleteModal = ref(false)
const deletingId = ref(null)
const isDeleting = ref(false)

// Toast
const toast = ref(null)

onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

// Ver modal
const openViewModal = (item) => {
  selectedItem.value = item
  showViewModal.value = true
}

// Icono de orden
const sortIcon = (key) => {
  return sortKey.value === key
    ? (sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down')
    : 'bi bi-arrow-down-up'
}

// Cambiar orden
const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

// Ordenamiento en el cliente sobre la página actual
const sortedPageData = computed(() => {
  const data = [...(props.extras?.data || [])]
  const key = sortKey.value
  const order = sortOrder.value

  // Comparación robusta para strings/números/fechas
  const norm = (val) => {
    if (val === null || val === undefined) return ''
    if (typeof val === 'number') return val
    if (typeof val === 'string') return val.toLowerCase()
    return String(val).toLowerCase()
  }

  data.sort((a, b) => {
    const aVal = norm(a?.[key])
    const bVal = norm(b?.[key])

    if (aVal < bVal) return order === 'asc' ? -1 : 1
    if (aVal > bVal) return order === 'asc' ? 1 : -1
    return 0
  })
  return data
})

// Paginación
const currentPage = computed(() => props.extras?.meta?.current_page || 1)
const totalPages  = computed(() => props.extras?.meta?.last_page || 1)

// Navegar a página
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

// Búsqueda con debounce
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

// Disparar búsqueda cuando cambien filtros o perPage o sort
watch([searchQuery, selectedActive, selectedCategory, perPage, sortKey, sortOrder], triggerSearch)

// Eliminar
const prepareDelete = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

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
    },
    onError: () => {
      isDeleting.value = false
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' }
    },
    onFinish: () => { isDeleting.value = false }
  })
}

// Después de crear desde el modal
const onCreated = () => {
  toast.value = { message: 'Clase extra creada exitosamente', type: 'success' }
  showCreateModal.value = false
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

    <section class="section-heading py-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title">
              <i class="bi bi-collection-play me-2"></i> Gestionar Clases Extra
            </h4>
            <Link class="btn btn-primary" :href="route('admin.extras.create')" >
              <i class="bi bi-plus-lg me-1"></i> Nueva Clase Extra
            </Link>
          </div>
        </div>
      </div>
    </section>

<section class="section-data py-2">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
         <div class="card border">
           <div class="card-body">
    <CrudFilters
       v-model:searchQuery="searchQuery"
  :count="props.extras?.meta?.total || 0"
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
           </div>
         </div>
        </div>
      </div>
    </div>
  </section>

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('id')" style="cursor: pointer;">
                      ID <i :class="sortIcon('id')"></i>
                    </th>
                    <th>Miniatura</th>
                    <th @click="sortBy('title')" style="cursor: pointer;">
                      Título <i :class="sortIcon('title')"></i>
                    </th>
                    <th @click="sortBy('category')" style="cursor: pointer;">
                      Categoría <i :class="sortIcon('category')"></i>
                    </th>
                    <th>Origen</th>
                    <th @click="sortBy('active')" style="cursor: pointer;">
                      Estado <i :class="sortIcon('active')"></i>
                    </th>
                    <th @click="sortBy('order')" style="cursor: pointer;">
                      Orden <i :class="sortIcon('order')"></i>
                    </th>
                    <th @click="sortBy('created_at')" style="cursor: pointer;">
                      Creada <i :class="sortIcon('created_at')"></i>
                    </th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="item in sortedPageData" :key="item.id">
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
                    <td>{{ item.order }}</td>
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

                  <tr v-if="(props.items?.data || []).length === 0">
                    <td colspan="9" class="text-center py-4 text-muted">
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
