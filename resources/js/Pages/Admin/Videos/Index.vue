<template>
  <Head title="Gestión de Videos" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Videos', route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title mb-0">
              <i class="bi bi-film me-2"></i> Gestionar Videos
            </h4>

            <Link :href="route('admin.videos.create')" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Nuevo Video
            </Link>
          </div>
        </div>
      </div>
    </section>

    <section class="section-filters my-2">
      <div class="container-fluid">
        <div class="card mb-3">
          <div class="card-body">
            <div class="row g-2 align-items-center">
              <div class="col-md-6">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Buscar por título, curso, maestro, ruta..."
                    v-model="searchQuery"
                  />
                </div>
              </div>

              <div class="col-md-3">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                  <select class="form-select" v-model.number="itemsPerPage">
                    <option :value="10">10 por página</option>
                    <option :value="20">20 por página</option>
                    <option :value="50">50 por página</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3 text-end">
                <span class="badge bg-light text-dark">
                  <i class="bi bi-collection-play me-1"></i>
                  {{ sortedVideos.length }} video(s)
                </span>
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
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th @click="sortBy('id')" class="text-nowrap" style="cursor:pointer;">
                      ID <i :class="sortIcon('id')" class="ms-1"></i>
                    </th>

                    <th>Portada</th>

                    <th @click="sortBy('title')" class="text-nowrap" style="cursor:pointer; min-width: 260px;">
                      Título <i :class="sortIcon('title')" class="ms-1"></i>
                    </th>

                    <th @click="sortBy('course')" class="text-nowrap" style="cursor:pointer;">
                      Curso <i :class="sortIcon('course')" class="ms-1"></i>
                    </th>

                    <th @click="sortBy('teacher')" class="text-nowrap d-none d-md-table-cell" style="cursor:pointer;">
                      Maestro <i :class="sortIcon('teacher')" class="ms-1"></i>
                    </th>

                    <th class="text-nowrap d-none d-lg-table-cell">Comentarios</th>

                    <th class="text-nowrap">Video</th>

                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="video in paginatedVideos" :key="video.id">
                    <!-- ID -->
                    <td class="text-muted">{{ video.id }}</td>

                    <!-- Cover -->
                    <td style="width: 96px;">
                      <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary shadow-sm thumb">
                        <template v-if="video.image_cover">
                          <img :src="safeUrl(video.image_cover)" class="w-100 h-100 object-fit-cover" alt="cover" />
                        </template>
                        <template v-else>
                          <div class="d-flex align-items-center justify-content-center w-100 h-100 text-muted">
                            <i class="bi bi-image"></i>
                          </div>
                        </template>
                      </div>
                    </td>

                    <!-- Título + meta -->
                    <td>
                      <div class="fw-semibold">{{ video.title }}</div>
                      <div class="small text-muted text-truncate-2">
                        {{ truncate(video.description_short, 140) }}
                      </div>

                      <div class="d-flex flex-wrap gap-2 mt-2">
                        <span v-if="video.duration" class="badge bg-primary-subtle text-primary-emphasis" data-bs-toggle="tooltip" title="Duración">
                          <i class="bi bi-clock me-1"></i>{{ formatDuration(video.duration) }}
                        </span>

                        <span v-if="video.size" class="badge bg-secondary-subtle text-secondary-emphasis" data-bs-toggle="tooltip" title="Tamaño de archivo">
                          <i class="bi bi-hdd me-1"></i>{{ formatBytes(video.size) }}
                        </span>

                        <span v-if="video.order !== null && video.order !== undefined" class="badge bg-info-subtle text-info-emphasis" data-bs-toggle="tooltip" title="Orden">
                          <i class="bi bi-list-ol me-1"></i>#{{ video.order }}
                        </span>

                        <span v-if="video.lesson_id" class="badge bg-light text-dark" data-bs-toggle="tooltip" title="Lección">
                          <i class="bi bi-journal-text me-1"></i>Lección {{ video.lesson_id }}
                        </span>
                      </div>
                    </td>

                    <!-- Curso -->
                    <td class="text-nowrap">
                      <span v-if="video.course?.title">{{ video.course.title }}</span>
                      <span v-else class="text-muted">Sin curso</span>
                    </td>

                    <!-- Maestro -->
                    <td class="text-nowrap d-none d-md-table-cell">
                      <span v-if="teacherName(video)">{{ teacherName(video) }}</span>
                      <span v-else class="text-muted">Sin maestro</span>
                    </td>

                    <!-- Comentarios -->
                    <td class="d-none d-lg-table-cell">
                      <span
                        :class="video.comments ? 'badge bg-success-subtle text-success-emphasis' : 'badge bg-secondary-subtle text-secondary-emphasis'"
                      >
                        <i class="bi" :class="video.comments ? 'bi-chat-dots' : 'bi-chat'"></i>
                        <span class="ms-1">{{ video.comments ? 'Habilitados' : 'Deshabilitados' }}</span>
                      </span>
                    </td>

                    <!-- Video: abrir / copiar -->
                    <td class="text-nowrap">
                      <div class="btn-group btn-group-sm" role="group">
                        <a
                          v-if="videoLink(video)"
                          :href="videoLink(video)"
                          class="btn btn-outline-secondary"
                          target="_blank" rel="noopener"
                          data-bs-toggle="tooltip"
                          title="Abrir video"
                        >
                          <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <button
                          v-if="videoLink(video)"
                          class="btn btn-outline-secondary"
                          @click="copyLink(videoLink(video))"
                          data-bs-toggle="tooltip"
                          title="Copiar enlace"
                        >
                          <i class="bi bi-clipboard"></i>
                        </button>
                        <span v-else class="text-muted small">N/A</span>
                      </div>
                    </td>

                    <!-- Acciones -->
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link
                          :href="route('admin.videos.edit', video.id)"
                          class="btn btn-outline-warning"
                          title="Editar"
                          data-bs-toggle="tooltip"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button
                          class="btn btn-outline-danger"
                          @click="prepareDelete(video.id)"
                          :disabled="isDeleting"
                          title="Eliminar"
                          data-bs-toggle="tooltip"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="sortedVideos.length === 0">
                    <td colspan="8" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron videos
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section-pagination my-2">
      <div class="container-fluid">
        <div class="d-flex justify-content-center my-4">
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }" @click="changePage(currentPage - 1)">
                <a class="page-link" href="#" title="Anterior">
                  <i class="bi bi-chevron-left"></i>
                </a>
              </li>

              <li
                v-for="page in totalPages"
                :key="page"
                class="page-item"
                :class="{ active: currentPage === page }"
                @click="changePage(page)"
              >
                <a class="page-link" href="#">{{ page }}</a>
              </li>

              <li class="page-item" :class="{ disabled: currentPage === totalPages }" @click="changePage(currentPage + 1)">
                <a class="page-link" href="#" title="Siguiente">
                  <i class="bi bi-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </section>

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteVideo"
      title="¿Deseas eliminar este video?"
      confirm-message="Por favor confirma la eliminación de este video"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref, computed, onMounted } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  videos: Array
})

const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const deletingId = ref(null)
const showDeleteModal = ref(false)
const isDeleting = ref(false)
const toast = ref(null)

const sortKey = ref('id')
const sortOrder = ref('asc')

const page = usePage()

onMounted(() => {
  // Tooltips
  if (window.bootstrap?.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new window.bootstrap.Tooltip(el))
  }
  if (page.props.flash?.success) toast.value = { message: page.props.flash.success, type: 'success' }
  if (page.props.flash?.error) toast.value = { message: page.props.flash.error, type: 'danger' }
})

/* ==== Helpers UI ==== */
const truncate = (str, n) => {
  if (!str) return ''
  return str.length > n ? str.slice(0, n - 1) + '…' : str
}

const formatBytes = (bytes) => {
  const b = Number(bytes)
  if (!b || b <= 0) return '—'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(b) / Math.log(1024))
  return `${(b / Math.pow(1024, i)).toFixed(i ? 1 : 0)} ${units[i]}`
}

const formatDuration = (sec) => {
  const s = Number(sec)
  if (!s || s <= 0) return '—'
  const h = Math.floor(s / 3600)
  const m = Math.floor((s % 3600) / 60)
  const r = s % 60
  return h > 0 ? `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(r).padStart(2,'0')}` : `${String(m).padStart(2,'0')}:${String(r).padStart(2,'0')}`
}

const safeUrl = (u) => {
  if (!u) return null
  if (/^https?:\/\//i.test(u)) return u
  if (u.startsWith('/')) return u
  return `/storage/${u}`
}

const videoLink = (v) => v?.video_url ? v.video_url : safeUrl(v?.video_path)

const copyLink = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    toast.value = { message: 'Enlace copiado al portapapeles', type: 'success' }
  } catch {
    toast.value = { message: 'No se pudo copiar el enlace', type: 'danger' }
  }
}

const teacherName = (v) => {
  const t = v?.teacher
  if (!t) return ''
  const composed = [t.firstname, t.lastname].filter(Boolean).join(' ').trim()
  return composed || t.user?.name || ''
}

/* ==== Ordenación y búsqueda ==== */
const sortIcon = (key) => {
  if (sortKey.value !== key) return 'bi bi-arrow-down-up'
  return sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down'
}

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const filteredVideos = computed(() => {
  if (!searchQuery.value) return props.videos
  const q = searchQuery.value.toLowerCase()
  return props.videos.filter(v => {
    return (
      (v.title || '').toLowerCase().includes(q) ||
      (v.description_short || '').toLowerCase().includes(q) ||
      (v.video_url || '').toLowerCase().includes(q) ||
      (v.video_path || '').toLowerCase().includes(q) ||
      (v.course?.title || '').toLowerCase().includes(q) ||
      (teacherName(v) || '').toLowerCase().includes(q)
    )
  })
})

const sortedVideos = computed(() => {
  const data = [...filteredVideos.value]
  data.sort((a, b) => {
    let aVal, bVal
    switch (sortKey.value) {
      case 'course':
        aVal = (a.course?.title || '').toLowerCase()
        bVal = (b.course?.title || '').toLowerCase()
        break
      case 'teacher':
        aVal = (teacherName(a) || '').toLowerCase()
        bVal = (teacherName(b) || '').toLowerCase()
        break
      case 'id':
      case 'order':
      case 'duration':
      case 'size':
        aVal = Number(a[sortKey.value] ?? 0)
        bVal = Number(b[sortKey.value] ?? 0)
        return sortOrder.value === 'asc' ? aVal - bVal : bVal - aVal
      default:
        aVal = (a[sortKey.value] || '').toString().toLowerCase()
        bVal = (b[sortKey.value] || '').toString().toLowerCase()
    }
    return sortOrder.value === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal)
  })
  return data
})

/* ==== Paginación en cliente ==== */
const paginatedVideos = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return sortedVideos.value.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() => Math.max(1, Math.ceil(sortedVideos.value.length / itemsPerPage.value)))

const changePage = (p) => {
  if (p >= 1 && p <= totalPages.value) {
    currentPage.value = p
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

/* ==== Eliminar ==== */
const prepareDelete = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}

const deleteVideo = () => {
  if (!deletingId.value) return
  isDeleting.value = true
  Inertia.delete(route('admin.videos.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: cancelDelete,
    onError: () => {
      isDeleting.value = false
      toast.value = { message: 'Ocurrió un error al eliminar el video', type: 'danger' }
    },
    onFinish: () => { isDeleting.value = false }
  })
}
</script>

<style scoped>
.thumb img { display:block; }
.object-fit-cover { object-fit: cover; }
.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
