<script setup>
import { Inertia } from '@inertiajs/inertia'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

// Si ya tienes estos componentes, ajusta las rutas; si no, puedes crearlos luego
 

const showViewModal = ref(false)
const selectedDistributor = ref(null)

const openViewModal = (distributor) => {
  selectedDistributor.value = distributor
  showViewModal.value = true
}

const props = defineProps({
  distributors: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  countries: { type: Array, default: () => [] } // opcional si lo usas en Create/Filters
})

const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const deletingId = ref(null)
const showDeleteModal = ref(false)
const isDeleting = ref(false)
const showCreateModal = ref(false)
const toast = ref(null)

const sortKey = ref('id')
const sortOrder = ref('asc')

const page = usePage()

onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

const onCreated = () => {
  toast.value = { message: 'Distribuidor creado exitosamente', type: 'success' }
  showCreateModal.value = false
}

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const filteredDistributors = computed(() => {
  if (!searchQuery.value) return props.distributors
  const q = searchQuery.value.toLowerCase()
  return props.distributors.filter(d =>
    (d.name || '').toLowerCase().includes(q) ||
    (d.country || '').toLowerCase().includes(q) ||
    (d.state || '').toLowerCase().includes(q) ||
    (d.email || '').toLowerCase().includes(q) ||
    (d.phone || '').toLowerCase().includes(q) ||
    (d.whatsapp || '').toLowerCase().includes(q)
  )
})

const sortedDistributors = computed(() => {
  const data = [...filteredDistributors.value]
  data.sort((a, b) => {
    let aVal, bVal
    switch (sortKey.value) {
      case 'name':
      case 'country':
      case 'state':
      case 'email':
        aVal = (a[sortKey.value] || '').toString().toLowerCase()
        bVal = (b[sortKey.value] || '').toString().toLowerCase()
        break
      default:
        aVal = a[sortKey.value]
        bVal = b[sortKey.value]
    }
    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
    return 0
  })
  return data
})

const paginatedDistributors = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return sortedDistributors.value.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() => Math.ceil(sortedDistributors.value.length / itemsPerPage.value))

const changePage = (p) => {
  if (p >= 1 && p <= totalPages.value) {
    currentPage.value = p
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const prepareDelete = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}

const deleteDistributor = () => {
  if (!deletingId.value) return
  isDeleting.value = true
  Inertia.delete(route('admin.distributors.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: cancelDelete,
    onError: () => {
      isDeleting.value = false
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' }
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}
</script>

<template>
  <Head title="Gestión de Distribuidores" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Distribuidores', route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title">
              <i class="bi bi-building me-2"></i>&nbsp; Gestionar Distribuidores
            </h4>
    
              <Link :href="route('admin.distributors.create')" class="btn btn-primary"title="Editar">
                          <i class="bi bi-plus-lg me-1"></i> Nuevo Distribuidor
                        </Link>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters
      v-model:searchQuery="searchQuery"
      :count="sortedDistributors.length"
      placeholder="Buscar distribuidores..."
      item-label="distribuidor(es)"
    />

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('id')" style="cursor: pointer;">
                      ID
                      <i :class="sortKey === 'id' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('name')" style="cursor: pointer;">
                      Nombre
                      <i :class="sortKey === 'name' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('country')" style="cursor: pointer;">
                      País
                      <i :class="sortKey === 'country' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('state')" style="cursor: pointer;">
                      Estado/País (state)
                      <i :class="sortKey === 'state' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('email')" style="cursor: pointer;">
                      Email
                      <i :class="sortKey === 'email' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="distributor in paginatedDistributors" :key="distributor.id">
                    <td>{{ distributor.id }}</td>
                    <td class="fw-semibold">
                      <div class="d-flex align-items-center gap-2">
                        <img
                          v-if="distributor.logo"
                          :src="`/storage/${distributor.logo}`"
                          alt="logo"
                          class="rounded border"
                          style="width: 28px; height: 28px; object-fit: cover;"
                        />
                        <span>{{ distributor.name }}</span>
                      </div>
                    </td>
                    <td>{{ distributor.country }}</td>
                    <td>{{ distributor.state }}</td>
                    <td>
                      <a v-if="distributor.email" :href="`mailto:${distributor.email}`">{{ distributor.email }}</a>
                      <span v-else class="text-muted">—</span>
                    </td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link :href="route('admin.distributors.edit', distributor.id)" class="btn btn-outline-warning" title="Editar">
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <Link class="btn btn-outline-info" :href="route('admin.distributors.show', distributor.id)" title="Ver">
                          <i class="bi bi-eye-fill"></i>
                        </Link>
                        <button class="btn btn-outline-danger" @click="prepareDelete(distributor.id)" :disabled="isDeleting" title="Eliminar">
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="sortedDistributors.length === 0">
                    <td colspan="6" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron distribuidores
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
      @change-page="changePage"
    />

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteDistributor"
      title="¿Deseas eliminar este distribuidor?"
      confirm-message="Por favor confirma la eliminación de este distribuidor"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

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
