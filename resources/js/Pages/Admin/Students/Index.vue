<script setup>
import { Inertia } from '@inertiajs/inertia'
import { Head, usePage } from '@inertiajs/vue3'
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import Title from '@/Components/Admin/Ui/Title.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

import ShowStudentModal from '@/Components/Admin/Students/ShowStudentModal.vue'
import CreateStudentModal from '@/Components/Admin/Students/CreateStudentModal.vue'
import StudentsTable from '@/Components/Admin/Students/StudentsTable.vue'

// Modal de activación existente (por usuario)
import ActivationUserModal from '@/Components/Admin/User/ActivationUserModal.vue'
// Nuevo: crear activación manual
import CreateActivationModal from '@/Components/Admin/User/CreateActivationModal.vue'

const props = defineProps({
  // students paginado desde el backend: { data, links, meta }
  students: { type: Object, required: true },
  courses: { type: Array, default: () => [] },
  // Filtros enviados por el backend para rehidratar el estado de la tabla
  filters: {
    type: Object,
    default: () => ({
      q: '',
      country: null,
      active: null,
      sortBy: 'users.id',
      sortDir: 'desc',
      perPage: 15,
    })
  },
  countries: { type: Array, default: () => [] }
})

const page = usePage()

// Estado de UI
const deletingId = ref(null)
const showDeleteModal = ref(false)
const isDeleting = ref(false)
const showCreateModal = ref(false)
const showViewModal = ref(false)
const selectedStudent = ref(null)
const toast = ref(null)

// Activaciones
const showActivateModal = ref(false)
const userToActivate = ref(null)
const showCreateActivationModal = ref(false)

// Estado de filtros controlado por la vista
const state = reactive({
  q: props.filters.q ?? '',
  country: props.filters.country ?? null,
  active: props.filters.active ?? null,
  sortBy: props.filters.sortBy ?? 'users.id',
  sortDir: props.filters.sortDir ?? 'desc',
  perPage: Number(props.filters.perPage ?? 15),
  page: Number(props.students?.meta?.current_page ?? 1),
})

const currentPage = computed(() => Number(props.students?.meta?.current_page ?? state.page))

// Debounce para búsqueda
let searchTimer = null
function debouncedRefresh() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    state.page = 1
    refreshIndex()
  }, 350)
}

// Sincroniza con el backend usando Inertia, manteniendo scroll y estado visual
function refreshIndex(partials = ['students', 'filters']) {
  Inertia.get(
    route('admin.students.index'),
    {
      q: state.q || undefined,
      country: state.country || undefined,
      active: state.active ?? undefined,
      sortBy: state.sortBy,
      sortDir: state.sortDir,
      perPage: state.perPage,
      page: state.page,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: partials
    }
  )
}

// Watchers de filtros
watch(() => state.q, debouncedRefresh)
watch(() => state.country, () => { state.page = 1; refreshIndex() })
watch(() => state.active, () => { state.page = 1; refreshIndex() })
watch(() => state.perPage, () => { state.page = 1; refreshIndex() })

// Eventos de la tabla
function onSort({ sortBy, sortDir }) {
  state.sortBy = sortBy
  state.sortDir = sortDir
  state.page = 1
  refreshIndex()
}

function onPageChange(pageNumber) {
  state.page = pageNumber
  refreshIndex()
}

// Flashes
onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

// Detalle
function openViewModal(user) {
  selectedStudent.value = user
  showViewModal.value = true
}

// Crear
function onCreated() {
  toast.value = { message: 'Estudiante creado exitosamente', type: 'success' }
  showCreateModal.value = false
  // Refrescar solo la lista
  refreshIndex()
}

// Eliminar
function prepareDelete(userId) {
  deletingId.value = userId
  showDeleteModal.value = true
}

function cancelDelete() {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}

function deleteStudent() {
  if (!deletingId.value) return
  isDeleting.value = true
  Inertia.delete(route('admin.students.destroy', { user: deletingId.value }), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete()
      toast.value = { message: 'Estudiante eliminado exitosamente', type: 'success' }
      // Mantiene filtros y página actual si la paginación aún es válida
      refreshIndex()
    },
    onError: () => {
      isDeleting.value = false
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' }
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}

// Activación
function openActivateModal(user) {
  userToActivate.value = user
  showActivateModal.value = true
}
function onActivationSent() {
  toast.value = { message: 'Se envió la activación al correo indicado', type: 'success' }
}
</script>

<template>
  <Head title="Gestión de Estudiantes" />

  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Estudiantes', route: '' }
      ]"
    /> 

    <section class="section-heading  pt-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center gap-2">
            <Title :title="'Gestionar Estudiantes'" />
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" @click="showCreateActivationModal = true">
                <i class="bi bi-lightning-charge me-1"></i> Crear Activación
              </button>
              <button class="btn btn-primary" @click="showCreateModal = true">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Estudiante
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla con filtros y paginación controlados -->
    <StudentsTable
      :students="students"
      :filters="{
        q: state.q,
        country: state.country,
        countries,
        active: state.active,
        sortBy: state.sortBy,
        sortDir: state.sortDir,
        perPage: state.perPage,
        page: currentPage
      }"
      :isDeleting="isDeleting"
      @view="openViewModal"
      @delete="prepareDelete"
      @activate="openActivateModal"
      @update:search="val => state.q = val"
      @update:country="val => state.country = val"
      @update:active="val => state.active = val"
      @update:perPage="val => state.perPage = Number(val)"
      @sort="onSort"
      @page="onPageChange"
    />

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteStudent"
      title="¿Deseas eliminar este estudiante?"
      confirm-message="Por favor confirma la eliminación de este estudiante"
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

    <CreateStudentModal
      v-if="showCreateModal"
      :courses="courses"
      :show="showCreateModal"
      @saved="onCreated"
      @close="showCreateModal = false"
    />

 

    <!-- Activación existente por usuario -->
    <ActivationUserModal
      v-if="showActivateModal && userToActivate"
      :show="showActivateModal"
      :user="userToActivate"
      @close="showActivateModal = false"
      @sent="onActivationSent"
    />

    <!-- Crear Activación manual -->
    <CreateActivationModal
      v-if="showCreateActivationModal"
      :show="showCreateActivationModal"
      :courses="courses"
      @close="showCreateActivationModal = false"
    />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
</style>
