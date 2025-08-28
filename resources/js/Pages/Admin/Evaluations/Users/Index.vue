<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref, computed, onMounted } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  // Puede ser un array simple o el objeto de paginate() { data, links, meta, ... }
  evaluations: { type: [Array, Object], required: true },
  // Cuando navegas por /admin/evaluation-users/course/{course}/index
  course: { type: Object, default: null },
  // Filtros actuales
  filters: {
    type: Object,
    default: () => ({
      course: null,
      user: '',
      status: '',
      q: '',
      per_page: 20
    })
  }
})

const page = usePage()

// UI state
const toast = ref(null)
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const showDeleteModal = ref(false)
const deletingId = ref(null)
const isDeleting = ref(false)

// Ordenamiento
const sortKey = ref('id')
const sortOrder = ref('asc')

// Mapas de labels
const TYPE_MAP = { 1: 'Curso', 2: 'Video' }
const EVA_TYPE_MAP = { 1: 'Cuestionario', 2: 'Video' }

// Utils
const fmtDate = (d) => {
  if (!d) return '—'
  const date = new Date(d)
  return isNaN(date) ? '—' : date.toLocaleDateString()
}

const statusBadge = (statusLabelRaw) => {
  const txt = String(statusLabelRaw ?? '—')
  if (/aprob/i.test(txt)) return { class: 'badge bg-success-subtle text-success border', text: txt }
  if (/revi/i.test(txt))  return { class: 'badge bg-warning-subtle text-warning border', text: txt }
  if (/no\s?apro/i.test(txt)) return { class: 'badge bg-danger-subtle text-danger border', text: txt }
  return { class: 'badge bg-secondary-subtle text-secondary border', text: txt }
}

onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

// Normaliza evaluations: acepta array o paginate()
const baseList = computed(() => {
  if (Array.isArray(props.evaluations)) return props.evaluations
  return props.evaluations?.data ?? []
})

// Filas listas para render
const rows = computed(() => {
  return baseList.value.map(ev => {
    // Cuando viene de evaluation-users suele venir así: { id, user, course, evaluation, ... }
    // Aseguramos accesos defensivos
    const evaluation = ev.evaluation ?? ev
    const typeLabel =
      evaluation.type_label ??
      TYPE_MAP[Number(evaluation.type)] ??
      evaluation.type ?? '—'
    const evaTypeLabel =
      evaluation.eva_type_label ??
      EVA_TYPE_MAP[Number(evaluation.eva_type)] ??
      evaluation.eva_type ?? '—'

    return {
      id: ev.id,
      userName: ev.user?.name ?? '—',
      courseTitle: ev.course?.title ?? evaluation.course?.title ?? '—',
      evaluationTitle: evaluation.title ?? '—',
      typeLabel,
      evaTypeLabel,
      pointsMin: evaluation.points_min ?? '—',
      sendDate: fmtDate(ev.eva_send_date ?? evaluation.eva_send_date),
      statusLabel: ev.status_label ?? evaluation.status_label ?? '—',
      approvedBy: ev.approved_user?.name ?? '—'
    }
  })
})

// Búsqueda
const filtered = computed(() => {
  if (!searchQuery.value) return rows.value
  const q = searchQuery.value.toLowerCase()
  return rows.value.filter(r =>
    r.userName.toLowerCase().includes(q) ||
    r.courseTitle.toLowerCase().includes(q) ||
    r.evaluationTitle.toLowerCase().includes(q) ||
    String(r.typeLabel).toLowerCase().includes(q) ||
    String(r.evaTypeLabel).toLowerCase().includes(q) ||
    String(r.statusLabel).toLowerCase().includes(q)
  )
})

// Ordenamiento
const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const sorted = computed(() => {
  const data = [...filtered.value]
  data.sort((a, b) => {
    let aVal = a[sortKey.value]
    let bVal = b[sortKey.value]
    // Normaliza a string para comparación estable cuando son textos
    aVal = aVal ?? ''
    bVal = bVal ?? ''
    if (typeof aVal === 'string') aVal = aVal.toLowerCase()
    if (typeof bVal === 'string') bVal = bVal.toLowerCase()

    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
    return 0
  })
  return data
})

// Paginación cliente
const paginated = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return sorted.value.slice(start, start + itemsPerPage.value)
})
const totalPages = computed(() => Math.ceil(sorted.value.length / itemsPerPage.value))
const changePage = (pageNum) => {
  if (pageNum >= 1 && pageNum <= totalPages.value) {
    currentPage.value = pageNum
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

// Eliminar
const askDelete = (row) => {
  deletingId.value = row.id
  showDeleteModal.value = true
}
const cancelDelete = () => {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}
const confirmDelete = () => {
  if (!deletingId.value) return
  isDeleting.value = true
  Inertia.delete(route('admin.evaluation-users.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      toast.value = { message: 'Evaluación eliminada correctamente.', type: 'success' }
      cancelDelete()
    },
    onError: () => {
      toast.value = { message: 'No se pudo eliminar la evaluación.', type: 'danger' }
      isDeleting.value = false
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}
</script>

<template>
  <Head title="Evaluaciones Enviadas por Usuarios" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Evaluaciones', route: 'admin.evaluations.index' },
        props.course
          ? {
              label: props.course.title,
              route: 'admin.evaluation-users.course.index',
              params: { course: props.course.id }
            }
          : { label: 'Envíos', route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title">
              <i class="bi bi-person-check me-2"></i> Evaluaciones Enviadas
            </h4>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters
      v-model:searchQuery="searchQuery"
      :count="sorted.length"
      placeholder="Buscar por usuario, curso, evaluación, tipo o estatus..."
      item-label="envío(s)"
    />

    <section class="section-data my-3">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('id')" style="cursor:pointer;">
                      ID
                      <i :class="sortKey === 'id' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('userName')" style="cursor:pointer;">
                      Usuario
                      <i :class="sortKey === 'userName' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('courseTitle')" style="cursor:pointer;">
                      Curso
                      <i :class="sortKey === 'courseTitle' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('evaluationTitle')" style="cursor:pointer;">
                      Evaluación
                      <i :class="sortKey === 'evaluationTitle' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('typeLabel')" style="cursor:pointer;">
                      Ámbito
                      <i :class="sortKey === 'typeLabel' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('evaTypeLabel')" style="cursor:pointer;">
                      Tipo
                      <i :class="sortKey === 'evaTypeLabel' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('pointsMin')" style="cursor:pointer;">
                      Pts. mín.
                      <i :class="sortKey === 'pointsMin' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('statusLabel')" style="cursor:pointer;">
                      Estatus
                      <i :class="sortKey === 'statusLabel' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th>
                      Aprobado por
                    </th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="ev in paginated" :key="ev.id">
                    <td>{{ ev.id }}</td>
                    <td>{{ ev.userName }}</td>
                    <td>{{ ev.courseTitle }}</td>
                    <td>{{ ev.evaluationTitle }}</td>
                    <td>{{ ev.typeLabel }}</td>
                    <td>{{ ev.evaTypeLabel }}</td>
                    <td>{{ ev.pointsMin }}</td>
                    <td>
                      <span :class="statusBadge(ev.statusLabel).class">{{ statusBadge(ev.statusLabel).text }}</span>
                    </td>
                    <td>{{ ev.approvedBy }}</td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm" role="group">
                        <Link
                          class="btn btn-outline-primary"
                          :href="route('admin.evaluation-users.show', ev.id)"
                          title="Ver envío"
                        >
                          <i class="bi bi-eye-fill"></i>
                        </Link>
                        <button
                          type="button"
                          class="btn btn-outline-danger"
                          :disabled="isDeleting && deletingId === ev.id"
                          @click="askDelete(ev)"
                          title="Eliminar envío"
                        >
                          <span v-if="isDeleting && deletingId === ev.id" class="spinner-border spinner-border-sm me-1"></span>
                          <i v-else class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="sorted.length === 0">
                    <td colspan="11" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No hay evaluaciones enviadas
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
      @confirm="confirmDelete"
      title="¿Deseas eliminar este envío de evaluación?"
      confirm-message="Por favor confirma la eliminación. Esta acción no se puede deshacer."
      warning-text="Se eliminará el registro seleccionado."
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
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
.badge.border { border: 1px solid rgba(0,0,0,.1); }
</style>
