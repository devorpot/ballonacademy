<script setup>
import { Inertia } from '@inertiajs/inertia'
import { Head, usePage, useForm } from '@inertiajs/vue3'
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { route } from 'ziggy-js'
import axios from 'axios'
// Layout & UI
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import Title from '@/Components/Admin/Ui/Title.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  // Paginado desde backend: { data, links, meta }
  activations: { type: Object, required: true },
  courses: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({
      q: '',
      course_id: null,
      active: null,
      sortBy: 'activations.id',
      sortDir: 'desc',
      perPage: 15,
    })
  }
})

const page = usePage()

// ===== Estado UI =====
const toast = ref(null)
const isDeleting = ref(false)
const showDeleteModal = ref(false)
const deletingId = ref(null)

// Modales
const showCreateActivationModal = ref(false)
const showDetailsModal = ref(false)

// ===== Estado de filtros/paginación =====
const state = reactive({
  q: props.filters.q ?? '',
  course_id: props.filters.course_id ?? null,
  active: props.filters.active ?? null,
  sortBy: props.filters.sortBy ?? 'activations.id',
  sortDir: props.filters.sortDir ?? 'desc',
  perPage: Number(props.filters.perPage ?? 15),
  page: Number(props.activations?.meta?.current_page ?? 1),
})

const currentPage = computed(() => Number(props.activations?.meta?.current_page ?? state.page))

// ===== Helpers =====
let cancelRefresh = null

function refreshIndex(partials = ['activations', 'filters']) {
  // Cancela la visita previa si existe
  if (typeof cancelRefresh === 'function') cancelRefresh()

  Inertia.get(
    route('admin.activations.index'),
    {
      q: state.q || undefined,
      course_id: state.course_id || undefined,
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
      only: partials,
      onCancelToken: (cancelToken) => { cancelRefresh = cancelToken }, // <-- clave
    }
  )
}


let searchTimer = null
function debouncedRefresh() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    state.page = 1
    refreshIndex()
  }, 300)
}


// ===== Watchers =====
watch(
  () => ({
    q: state.q,
    course_id: state.course_id,
    active: state.active,
    sortBy: state.sortBy,
    sortDir: state.sortDir,
    perPage: state.perPage
  }),
  debouncedRefresh,
  { deep: true }
)

// ===== Flashes =====
onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

// ===== Acciones fila =====
async function copyLink(activation) {
  const link = getPublicLink(activation)
  if (!link) {
    toast.value = { message: 'No hay enlace para copiar', type: 'danger' }
    return
  }

  try {
    // Clipboard API (requiere contexto seguro: https/localhost)
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(link)
    } else {
      // Fallback con textarea temporal
      legacyCopy(link)
    }
    toast.value = { message: 'Enlace copiado al portapapeles', type: 'success' }
  } catch (err) {
    console.error('Clipboard error:', err)
    try {
      legacyCopy(link)
      toast.value = { message: 'Enlace copiado al portapapeles', type: 'success' }
    } catch (err2) {
      console.error('Legacy copy error:', err2)
      toast.value = { message: 'No se pudo copiar el enlace', type: 'danger' }
    }
  }
}

// Crea un textarea oculto, selecciona y copia (compatibilidad amplia)
function legacyCopy(text) {
  const ta = document.createElement('textarea')
  ta.value = text
  ta.setAttribute('readonly', '')
  ta.style.position = 'fixed'
  ta.style.top = '-9999px'
  document.body.appendChild(ta)
  ta.select()
  const ok = document.execCommand('copy')
  document.body.removeChild(ta)
  if (!ok) throw new Error('execCommand copy failed')
}

// Obtiene el enlace público de forma segura
function getPublicLink(activation) {
  const fromProp = activation?.public_link && String(activation.public_link).trim()
  if (fromProp) return fromProp
  return buildPublicLink(activation?.hash)
}

function buildPublicLink(hash) {
  if (!hash) return null
  const base = window.location?.origin || ''
  if (!base) return null
  return `${base}/register/student/${hash}`
}

async function resendActivation(activationId) {
  try {
    await axios.post(route('admin.activations.resend', { activation: activationId }))
    toast.value = { message: 'Invitación reenviada correctamente', type: 'success' }
    // No hace falta refrescar toda la tabla, pero si quieres:
    // refreshIndex(['activations'])
  } catch (e) {
    console.error(e)
    toast.value = { message: 'Error al reenviar la invitación', type: 'danger' }
  }
}


async function toggleActivation(activationId) {
  try {
    const { data } = await axios.patch(route('admin.activations.toggle', { activation: activationId }))
    toast.value = { message: 'Estado actualizado', type: 'success' }
    // Si quieres evitar un GET, podrías mutar la fila en memoria con data.active.
    // Para mantenerlo simple y consistente, recarga parcial:
    //refreshIndex(['activations'])
  } catch (e) {
    console.error(e)
    toast.value = { message: 'No se pudo actualizar el estado', type: 'danger' }
  }
}

function prepareDelete(id) {
  deletingId.value = id
  showDeleteModal.value = true
}
function cancelDelete() {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}
function deleteActivation() {
  if (!deletingId.value) return
  isDeleting.value = true
  Inertia.delete(route('admin.activations.destroy', { activation: deletingId.value }), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete()
      toast.value = { message: 'Activación eliminada', type: 'success' }
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
function badgeClass(val) { return val ? 'badge bg-success' : 'badge bg-secondary' }
function formatDate(dt) {
  if (!dt) return '-'
  try { const d = new Date(dt); return d.toLocaleString() } catch { return dt }
}

// ====== Crear Activación (useForm para evitar recarga y manejar errores 422) ======
const form = useForm({
  name: '',
  email: '',
  phone: '',
  course_id: null,
})

function openCreateModal() {
  resetCreateForm()
  showCreateActivationModal.value = true
}

function resetCreateForm() {
  form.reset()
  form.course_id = props.courses?.[0]?.id ?? null
  form.clearErrors()
}

function submitCreateActivation() {
  form.post(route('admin.activations.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateActivationModal.value = false
      toast.value = { message: 'Activación creada y enviada por correo', type: 'success' }
      refreshIndex()
    },
    onError: () => {
      // Los errores llegan en form.errors.<campo>, p.ej. form.errors.email
      toast.value = { message: 'Revisa los campos del formulario', type: 'danger' }
    },
  })
}

// ====== Detalles (modal) ======
const selectedActivation = ref(null)
function openDetails(activation) {
  selectedActivation.value = activation
  showDetailsModal.value = true
}
</script>

<template>
  <Head title="Gestión de Activaciones" />

  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Activaciones', route: '' }
      ]"
    />

    <section class="section-heading my-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center gap-2">
            <Title :title="'Gestionar Activaciones'" />
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" @click="openCreateModal">
                <i class="bi bi-lightning-charge me-1"></i> Crear Activación
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-data py-3">
      <div class="container-fluid">
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-md-4">
                <label class="form-label">Buscar (nombre/email/código)</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input v-model="state.q" type="text" class="form-control" placeholder="Escribe para buscar..." />
                </div>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Curso</label>
                <select v-model="state.course_id" class="form-select">
                  <option :value="null">Todos</option>
                  <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>

              <div class="col-12 col-md-3">
                <label class="form-label">Estado</label>
                <select v-model="state.active" class="form-select">
                  <option :value="null">Todos</option>
                  <option :value="1">Activos</option>
                  <option :value="0">Inactivos</option>
                </select>
              </div>

              <div class="col-12 col-md-2">
                <label class="form-label">Por página</label>
                <select v-model.number="state.perPage" class="form-select">
                  <option :value="10">10</option>
                  <option :value="15">15</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla -->
    <section class="section-panel">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
             <thead class="table-light">
                <tr>
                  <th style="width:72px">
                    <button class="btn btn-link p-0" @click="onSort({ sortBy: 'activations.id', sortDir: state.sortDir === 'asc' ? 'desc':'asc' })">
                      # <i class="bi" :class="state.sortBy==='activations.id' && state.sortDir==='asc' ? 'bi-sort-down' : 'bi-sort-up'"></i>
                    </button>
                  </th>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>Curso</th>
                  <th>Código</th>
                  <th>Estado</th>
                  <th>Creado</th>
                  <th class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!activations?.data?.length">
                  <td colspan="8" class="text-center py-4 text-muted">No hay activaciones</td>
                </tr>
                <tr v-for="a in activations.data" :key="a.id">
                  <td>{{ a.id }}</td>
                  <td>
                    <div class="fw-semibold">{{ a.name }}</div>
                    <small class="text-muted">{{ a.phone || '—' }}</small>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width:220px">{{ a.email }}</div>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width:220px">{{ a.course?.title || a.course_title || '—' }}</div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="fw-semibold">{{ a.code }}</span>
                      <button class="btn btn-sm btn-outline-secondary" @click="copyLink(a)" title="Copiar enlace">
                        <i class="bi bi-clipboard"></i>
                      </button>
                    </div>
                  </td>
                  <td>
                    <span :class="badgeClass(a.active)">{{ a.active ? 'Activo' : 'Inactivo' }}</span>
                  </td>
                  <td>
                    <div>{{ formatDate(a.created) }}</div>
                  </td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-secondary" @click="openDetails(a)">
                        <i class="bi bi-eye"></i> Ver
                      </button>
                      <button class="btn btn-outline-primary"  @click.prevent="resendActivation(a.id)">
                        <i class="bi bi-send"></i> Reenviar
                      </button>
                      <button class="btn btn-outline-secondary" @click.prevent="toggleActivation(a.id)">
                        <i class="bi" :class="a.active ? 'bi-toggle-on' : 'bi-toggle-off'"></i>
                        {{ a.active ? 'Desactivar' : 'Activar' }}
                      </button>
                      <button class="btn btn-outline-danger" @click="prepareDelete(a.id)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="card-body border-top">
            <nav v-if="activations?.meta">
              <ul class="pagination mb-0 justify-content-end">
                <li class="page-item" :class="{ disabled: currentPage <= 1 }">
                  <button class="page-link" @click="onPageChange(currentPage - 1)" :disabled="currentPage <= 1">Anterior</button>
                </li>
                <li class="page-item disabled">
                  <span class="page-link">Página {{ activations.meta.current_page }} de {{ activations.meta.last_page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: currentPage >= activations.meta.last_page }">
                  <button class="page-link" @click="onPageChange(currentPage + 1)" :disabled="currentPage >= activations.meta.last_page">Siguiente</button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </section>

   <!-- Modal: Crear Activación -->
<div v-if="showCreateActivationModal" class="modal fade show d-block" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crear Activación</h5>
        <button type="button" class="btn-close" @click="showCreateActivationModal=false"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Nombre completo</label>
            <input v-model="form.name" type="text" class="form-control" placeholder="Nombre y apellidos" />
            <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" class="form-control" placeholder="correo@dominio.com" />
            <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Teléfono (opcional)</label>
            <input v-model="form.phone" type="text" class="form-control" placeholder="+52 ..." />
            <div v-if="form.errors.phone" class="text-danger small mt-1">{{ form.errors.phone }}</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Curso</label>
            <select v-model="form.course_id" class="form-select">
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
            <div v-if="form.errors.course_id" class="text-danger small mt-1">{{ form.errors.course_id }}</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" @click="showCreateActivationModal=false">Cancelar</button>
        <button type="button" class="btn btn-primary" :disabled="form.processing" @click="submitCreateActivation">
          <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
          Crear y enviar
        </button>
      </div>
    </div>
  </div>
</div>
<div v-if="showCreateActivationModal" class="modal-backdrop fade show"></div>


    <!-- Modal: Detalles -->
    <div v-if="showDetailsModal && selectedActivation" class="modal fade show d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalles de la Activación #{{ selectedActivation.id }}</h5>
            <button type="button" class="btn-close" @click="showDetailsModal=false"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="text-muted small">Nombre</div>
                <div class="fw-semibold">{{ selectedActivation.name }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-muted small">Email</div>
                <div class="fw-semibold">{{ selectedActivation.email }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-muted small">Teléfono</div>
                <div class="fw-semibold">{{ selectedActivation.phone || '—' }}</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="text-muted small">Curso</div>
                <div class="fw-semibold">{{ selectedActivation.course?.title || selectedActivation.course_title || '—' }}</div>
              </div>
              <div class="col-12 col-md-4">
                <div class="text-muted small">Código</div>
                <div class="fw-semibold">{{ selectedActivation.code }}</div>
              </div>
              <div class="col-12 col-md-8">
               <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted small">Enlace público</div>
                    <div style="max-width: 420px; overflow: hidden;">
                      {{ getPublicLink(selectedActivation) || '—' }}
                    </div>
                  </div>
                  <button
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="!getPublicLink(selectedActivation)"
                    @click="copyLink(selectedActivation)"
                    title="Copiar enlace"
                  >
                    <i class="bi bi-clipboard"></i> Copiar
                  </button>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="text-muted small">Estado</div>
                <span :class="badgeClass(selectedActivation.active)">{{ selectedActivation.active ? 'Activo' : 'Inactivo' }}</span>
              </div>
              <div class="col-12 col-md-4">
                <div class="text-muted small">Creado</div>
                <div>{{ formatDate(selectedActivation.created) }}</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="showDetailsModal=false">Cerrar</button>
            <button type="button" class="btn btn-outline-primary" @click="resendActivation(selectedActivation.id)">
              <i class="bi bi-send"></i> Reenviar
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="toggleActivation(selectedActivation.id)">
              <i class="bi" :class="selectedActivation.active ? 'bi-toggle-on' : 'bi-toggle-off'"></i>
              {{ selectedActivation.active ? 'Desactivar' : 'Activar' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showDetailsModal" class="modal-backdrop fade show"></div>

    <!-- Modales utilitarios -->
    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteActivation"
      title="¿Deseas eliminar esta activación?"
      confirm-message="Por favor confirma la eliminación de esta activación"
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
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
.modal-backdrop { backdrop-filter: blur(2px); }
</style>
