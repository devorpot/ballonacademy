<!-- resources/js/Pages/Admin/VideoComments/Index.vue -->
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

const props = defineProps({
  comments: { type: Object, required: true }, // paginación: { data, links, meta }
  courses:  { type: Array,  default: () => [] },
  videos:   { type: Array,  default: () => [] },
  users:    { type: Array,  default: () => [] },
  filters: {
    type: Object,
    default: () => ({
      q: '',
      course_id: null,
      video_id: null,
      user_id: null,
      active: null,
      sortBy: 'video_comments.created_at',
      sortDir: 'desc',
      perPage: 30,
    })
  }
})

const page = usePage()

// ===== Estado UI =====
const toast = ref(null)
const isDeleting = ref(false)
const showDeleteModal = ref(false)
const deletingId = ref(null)

const showDetailsModal = ref(false)
const selectedComment = ref(null)

// ===== Filtros / paginación =====
const state = reactive({
  q: props.filters.q ?? '',
  course_id: props.filters.course_id ?? null,
  video_id: props.filters.video_id ?? null,
  user_id: props.filters.user_id ?? null,
  active: props.filters.active ?? null,
  sortBy: props.filters.sortBy ?? 'video_comments.created_at',
  sortDir: props.filters.sortDir ?? 'desc',
  perPage: Number(props.filters.perPage ?? 30),
  page: Number(props.comments?.meta?.current_page ?? 1),
})

const pager = computed(() => {
  // Soporta shape con meta (JSON:API) y shape plano (Laravel por defecto)
  const c = props.comments || {}
  const meta = c.meta || {}
  return {
    current_page: Number(meta.current_page ?? c.current_page ?? 1),
    last_page:    Number(meta.last_page    ?? c.last_page    ?? 1),
  }
})

const currentPage = computed(() => pager.value.current_page)

function refreshIndex(partials = ['comments', 'filters']) {
  Inertia.get(
    route('admin.video-comments.index'),
    {
      q: state.q || undefined,
      course_id: state.course_id || undefined,
      video_id: state.video_id || undefined,
      user_id: state.user_id || undefined,
      active: state.active ?? undefined,
      sortBy: state.sortBy,
      sortDir: state.sortDir,
      perPage: state.perPage,
      page: state.page,
    },
    { preserveState: true, preserveScroll: true, replace: true, only: partials }
  )
}

let searchTimer = null
function debouncedRefresh() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    state.page = 1
    refreshIndex()
  }, 350)
}

// ===== Watchers =====
watch(() => state.q, debouncedRefresh)
watch(() => state.course_id, () => { state.page = 1; refreshIndex() })
watch(() => state.video_id,  () => { state.page = 1; refreshIndex() })
watch(() => state.user_id,   () => { state.page = 1; refreshIndex() })
watch(() => state.active,    () => { state.page = 1; refreshIndex() })
watch(() => state.perPage,   () => { state.page = 1; refreshIndex() })

onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

// ===== Acciones fila =====
function toggleComment(commentId) {
  Inertia.patch(route('admin.video-comments.toggle', { comment: commentId }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.value = { message: 'Estado actualizado', type: 'success' }
      refreshIndex(['comments'])
    },
    onError: () => {
      toast.value = { message: 'No se pudo actualizar el estado', type: 'danger' }
    }
  })
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

function deleteComment() {
  if (!deletingId.value) return
  isDeleting.value = true
  Inertia.delete(route('admin.video-comments.destroy', { comment: deletingId.value }), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete()
      toast.value = { message: 'Comentario eliminado', type: 'success' }
      refreshIndex()
    },
    onError: () => {
      isDeleting.value = false
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' }
    },
    onFinish: () => { isDeleting.value = false }
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
function truncate(text, max = 120) {
  if (!text) return ''
  const t = String(text)
  return t.length > max ? t.slice(0, max) + '…' : t
}
function openDetails(comment) {
  selectedComment.value = comment
  showDetailsModal.value = true
}
</script>

<template>
  <Head title="Comentarios de Video" />

  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Comentarios', route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center gap-2">
            <Title :title="'Gestionar Comentarios de Video'" />
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-lg-4">
                <label class="form-label">Buscar en comentario</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input v-model="state.q" type="text" class="form-control" placeholder="Texto del comentario..." />
                </div>
              </div>

              <div class="col-12 col-sm-4 col-lg-2">
                <label class="form-label">Curso</label>
                <select v-model="state.course_id" class="form-select">
                  <option :value="null">Todos</option>
                  <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>

              <div class="col-12 col-sm-4 col-lg-2">
                <label class="form-label">Video</label>
                <select v-model="state.video_id" class="form-select">
                  <option :value="null">Todos</option>
                  <option v-for="v in videos" :key="v.id" :value="v.id">
                    {{ v.title }}
                  </option>
                </select>
              </div>

              <div class="col-12 col-sm-4 col-lg-2">
                <label class="form-label">Usuario</label>
                <select v-model="state.user_id" class="form-select">
                  <option :value="null">Todos</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">
                    {{ u.name }}
                  </option>
                </select>
              </div>

              <div class="col-12 col-sm-4 col-lg-2">
                <label class="form-label">Activo</label>
                <select v-model="state.active" class="form-select">
                  <option :value="null">Todos</option>
                  <option :value="1">Activos</option>
                  <option :value="0">Inactivos</option>
                </select>
              </div>

              <div class="col-12 col-sm-4 col-lg-2">
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
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="table-responsive">
           <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width:72px">
                    <button class="btn btn-link p-0"
                            @click="onSort({ sortBy: 'video_comments.id', sortDir: state.sortDir === 'asc' ? 'desc' : 'asc' })">
                      # <i class="bi" :class="state.sortBy==='video_comments.id' && state.sortDir==='asc' ? 'bi-sort-down' : 'bi-sort-up'"></i>
                    </button>
                  </th>
                  <th>Usuario</th>
                  <th style="min-width:320px;">Comentario</th>
                  <th>Curso</th>
                  <th>Video</th>
                  <th class="text-center">Likes</th>
                  <th class="text-center">Dislikes</th>
                  <th>
                    <button class="btn btn-link p-0"
                            @click="onSort({ sortBy: 'video_comments.created_at', sortDir: state.sortDir === 'asc' ? 'desc' : 'asc' })">
                      Fecha <i class="bi" :class="state.sortBy==='video_comments.created_at' && state.sortDir==='asc' ? 'bi-sort-down' : 'bi-sort-up'"></i>
                    </button>
                  </th>
                  <th>Estado</th>
                  <th class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!comments?.data?.length">
                  <td colspan="10" class="text-center py-4 text-muted">No hay comentarios</td>
                </tr>

                <tr v-for="c in comments.data" :key="c.id">
                  <td>{{ c.id }}</td>
                  <td>
                    <div class="fw-semibold">{{ c.user?.name || '—' }}</div>
                    <small class="text-muted">ID: {{ c.user_id }}</small>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width: 520px">{{ truncate(c.comment, 140) }}</div>
                    <button class="btn btn-sm btn-outline-secondary mt-1" @click="openDetails(c)">
                      Ver completo
                    </button>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width: 220px">
                      {{ c.course?.title || '—' }}
                    </div>
                  </td>
                  <td>
                    <div class="text-truncate" style="max-width: 220px">
                      {{ c.video?.title || '—' }}
                    </div>
                  </td>
                  <td class="text-center">{{ c.likes ?? 0 }}</td>
                  <td class="text-center">{{ c.dislikes ?? 0 }}</td>
                  <td>{{ formatDate(c.created_at) }}</td>
                  <td><span :class="badgeClass(c.active)">{{ c.active ? 'Activo' : 'Inactivo' }}</span></td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-secondary" @click="toggleComment(c.id)">
                        <i class="bi" :class="c.active ? 'bi-toggle-on' : 'bi-toggle-off'"></i>
                        {{ c.active ? 'Desactivar' : 'Activar' }}
                      </button>
                      <button class="btn btn-outline-danger" @click="prepareDelete(c.id)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
         <!-- Paginación -->
<div class="card-body border-top">
  <nav v-if="comments && (comments.meta || typeof comments.current_page !== 'undefined')">
    <ul class="pagination mb-0 justify-content-end">
      <li class="page-item" :class="{ disabled: currentPage <= 1 }">
        <button class="page-link" @click="onPageChange(currentPage - 1)" :disabled="currentPage <= 1">Anterior</button>
      </li>
      <li class="page-item disabled">
        <span class="page-link">
          Página {{ pager.current_page }} de {{ pager.last_page }}
        </span>
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

    <!-- Modal: Detalles del comentario -->
    <div v-if="showDetailsModal && selectedComment" class="modal fade show d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              Comentario #{{ selectedComment.id }}
            </h5>
            <button type="button" class="btn-close" @click="showDetailsModal=false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2">
              <div class="text-muted small">Usuario</div>
              <div class="fw-semibold">{{ selectedComment.user?.name || '—' }} <small class="text-muted">(#{{ selectedComment.user_id }})</small></div>
            </div>
            <div class="mb-2">
              <div class="text-muted small">Curso</div>
              <div class="fw-semibold">{{ selectedComment.course?.title || '—' }}</div>
            </div>
            <div class="mb-2">
              <div class="text-muted small">Video</div>
              <div class="fw-semibold">{{ selectedComment.video?.title || '—' }}</div>
            </div>
            <div class="mb-2">
              <div class="text-muted small">Fecha</div>
              <div class="fw-semibold">{{ formatDate(selectedComment.created_at) }}</div>
            </div>
            <div class="mb-3">
              <div class="text-muted small">Comentario</div>
              <div class="fw-semibold" style="white-space: pre-wrap">{{ selectedComment.comment }}</div>
            </div>
            <div class="d-flex gap-3">
              <div><span class="text-muted small">Likes</span> <div class="fw-semibold">{{ selectedComment.likes ?? 0 }}</div></div>
              <div><span class="text-muted small">Dislikes</span> <div class="fw-semibold">{{ selectedComment.dislikes ?? 0 }}</div></div>
              <div><span class="text-muted small">Estado</span> <div><span :class="badgeClass(selectedComment.active)">{{ selectedComment.active ? 'Activo' : 'Inactivo' }}</span></div></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="showDetailsModal=false">Cerrar</button>
            <button class="btn btn-outline-secondary" @click="toggleComment(selectedComment.id)">
              <i class="bi" :class="selectedComment.active ? 'bi-toggle-on' : 'bi-toggle-off'"></i>
              {{ selectedComment.active ? 'Desactivar' : 'Activar' }}
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
      @confirm="deleteComment"
      title="¿Deseas eliminar este comentario?"
      confirm-message="Por favor confirma la eliminación de este comentario."
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
