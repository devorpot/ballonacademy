<template>
  <Head :title="`Videos del Curso: ${course.title}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Cursos', route: 'admin.courses.index' },
        { label: `Videos - ${course.title}`, route: '' }
      ]"
    />

    <!-- Encabezado -->
    <section class="section-heading">
      <div class="container-fluid d-flex justify-content-between align-items-center mb-3">
        <h4 class="admin-title mb-0">
          <i class="bi bi-play-circle me-2"></i> Videos del Curso
        </h4>
        <Link
          :href="route('admin.videos.create', { course_id: course.id })"
          class="btn btn-primary btn-sm"
        >
          <i class="bi bi-plus-circle me-1"></i> Agregar Video
        </Link>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-filters my-2">
      <div class="container-fluid">
        <div class="card mb-3">
          <div class="card-body">
            <div class="row g-2 align-items-center">
              <div class="col-12 col-md-8">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Buscar por título, descripción, ruta..."
                    v-model="searchQuery"
                  />
                </div>
              </div>
              <div class="col-12 col-md-4 text-md-end">
                <span class="badge bg-light text-dark">
                  <i class="bi bi-collection-play me-1"></i>
                  {{ filteredVideos.length }} video(s)
                </span>
              </div>
            </div>
            <div v-if="searchQuery" class="alert alert-info mt-3 mb-0 py-2 small">
              Para reordenar, limpia la búsqueda.
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla -->
    <section class="section-data">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 40px;"></th>
                    <th class="text-nowrap">Orden</th>
                    <th>Portada</th>
                    <th style="min-width: 280px;">Título</th>
                    <th class="d-none d-lg-table-cell">Comentarios</th>
                    <th class="text-nowrap">Video</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>

                <!-- Draggable si NO hay búsqueda -->
                <draggable
                  v-if="!searchQuery"
                  v-model="videoList"
                  tag="tbody"
                  :animation="200"
                  handle=".drag-handle"
                  item-key="id"
                  @end="onDragEnd"
                >
                  <template #item="{ element: video }">
                    <tr>
                      <!-- Handle -->
                      <td>
                        <i class="bi bi-arrows-move drag-handle text-muted" style="cursor: grab;" title="Arrastrar para reordenar"></i>
                      </td>

                      <!-- Orden -->
                      <td class="text-muted">
                        {{ video.order ?? '—' }}
                      </td>

                      <!-- Cover -->
                      <td style="width: 120px;">
                        <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary shadow-sm thumb">
                          <template v-if="video.image_cover">
                            <img :src="safeUrl(video.image_cover)" class="w-100 h-100 object-fit-cover" :alt="video.title" />
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
                            :href="route('admin.videos.show', video.id)"
                            class="btn btn-outline-primary"
                            title="Ver"
                            data-bs-toggle="tooltip"
                          >
                            <i class="bi bi-eye-fill"></i>
                          </Link>
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
                            title="Eliminar"
                            data-bs-toggle="tooltip"
                          >
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </draggable>

                <!-- Tabla sin drag si hay búsqueda -->
                <tbody v-else>
                  <tr v-for="video in filteredVideos" :key="video.id">
                    <td></td>

                    <td class="text-muted">{{ video.order ?? '—' }}</td>

                    <td style="width: 120px;">
                      <div class="ratio ratio-16x9 rounded overflow-hidden bg-body-secondary shadow-sm thumb">
                        <template v-if="video.image_cover">
                          <img :src="safeUrl(video.image_cover)" class="w-100 h-100 object-fit-cover" :alt="video.title" />
                        </template>
                        <template v-else>
                          <div class="d-flex align-items-center justify-content-center w-100 h-100 text-muted">
                            <i class="bi bi-image"></i>
                          </div>
                        </template>
                      </div>
                    </td>

                    <td>
                      <div class="fw-semibold">{{ video.title }}</div>
                      <div class="small text-muted text-truncate-2">{{ truncate(video.description_short, 140) }}</div>

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

                    <td class="d-none d-lg-table-cell">
                      <span
                        :class="video.comments ? 'badge bg-success-subtle text-success-emphasis' : 'badge bg-secondary-subtle text-secondary-emphasis'"
                      >
                        <i class="bi" :class="video.comments ? 'bi-chat-dots' : 'bi-chat'"></i>
                        <span class="ms-1">{{ video.comments ? 'Habilitados' : 'Deshabilitados' }}</span>
                      </span>
                    </td>

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

                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link
                          :href="route('admin.videos.show', video.id)"
                          class="btn btn-outline-primary"
                          title="Ver"
                          data-bs-toggle="tooltip"
                        >
                          <i class="bi bi-eye-fill"></i>
                        </Link>
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
                          title="Eliminar"
                          data-bs-toggle="tooltip"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="!filteredVideos.length">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No hay videos que coincidan con la búsqueda
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modales y notificaciones -->
    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteVideo"
      title="¿Eliminar este video?"
      confirm-message="Esta acción no se puede deshacer"
      cancel-text="Cancelar"
      confirm-text="Eliminar"
    />

    <ToastNotification
      v-if="toast"
      :message="toast.message"
      :type="toast.type"
      @hidden="toast = null"
    />
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { route } from 'ziggy-js'
import draggable from 'vuedraggable'
import axios from 'axios'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  course: Object,
  videos: Array
})

/* ===== Estado ===== */
const searchQuery = ref('')
const videoList = ref([...props.videos].sort((a, b) => (a.order ?? 9999) - (b.order ?? 9999)))

const showDeleteModal = ref(false)
const deletingId = ref(null)
const isDeleting = ref(false)
const toast = ref(null)

/* ===== Helpers UI ===== */
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
  return h > 0
    ? `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(r).padStart(2,'0')}`
    : `${String(m).padStart(2,'0')}:${String(r).padStart(2,'0')}`
}

const safeUrl = (u) => {
  if (!u) return null
  if (/^https?:\/\//i.test(u)) return u
  if (u.startsWith('/')) return u
  return `/storage/${u}`
}

 

const copyLink = async (text) => {
  const url = toAbsoluteUrl(text)
  if (!url) {
    toast.value = { message: 'No hay enlace disponible para copiar', type: 'danger' }
    return
  }

  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(url)
      toast.value = { message: 'Enlace copiado al portapapeles', type: 'success' }
    } else {
      // Fallback (HTTP, iOS viejos, navegadores sin permiso)
      const ok = fallbackCopyText(url)
      if (!ok) throw new Error('Fallback copy failed')
      toast.value = { message: 'Enlace copiado al portapapeles', type: 'success' }
    }
  } catch (e) {
    toast.value = { message: 'No se pudo copiar el enlace', type: 'danger' }
    console.error('copyLink error:', e)
  }
}

const toAbsoluteUrl = (u) => {
  if (!u) return null
  try {
    return new URL(u, window.location.origin).href
  } catch {
    return u
  }
}

const videoLink = (v) => {
  const link = v?.video_url ? v.video_url : safeUrl(v?.video_path)
  return link ? toAbsoluteUrl(link) : null
}


/* ===== Filtro ===== */
const filteredVideos = computed(() => {
  if (!searchQuery.value) return videoList.value
  const q = searchQuery.value.toLowerCase()
  return videoList.value.filter(v =>
    (v.title || '').toLowerCase().includes(q) ||
    (v.description_short || '').toLowerCase().includes(q) ||
    (v.video_url || '').toLowerCase().includes(q) ||
    (v.video_path || '').toLowerCase().includes(q)
  )
})

const fallbackCopyText = (text) => {
  const ta = document.createElement('textarea')
  ta.value = text
  ta.setAttribute('readonly', '')
  ta.style.position = 'fixed'
  ta.style.top = '-10000px'
  document.body.appendChild(ta)
  ta.select()
  let ok = false
  try {
    ok = document.execCommand('copy')
  } catch (e) {
    ok = false
  }
  document.body.removeChild(ta)
  return ok
}


/* ===== Drag & Drop ===== */
const onDragEnd = async () => {
  // Refleja visualmente el nuevo orden (1..n)
  videoList.value.forEach((video, idx) => {
    video.order = idx + 1
  })

  // Envía el nuevo orden al backend
  const order = videoList.value.map(v => v.id)
  try {
    await axios.post(route('admin.courses.videos.reorder', { course: props.course.id }), { order })
    toast.value = { message: 'Orden de videos actualizado', type: 'success' }
  } catch (e) {
    console.error('Error al actualizar orden:', e, e?.response?.data)
    toast.value = {
      message: `No se pudo actualizar el orden. ${e?.response?.data?.message || ''}`,
      type: 'danger'
    }
  }
}

/* ===== Eliminar ===== */
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
    onSuccess: () => {
      videoList.value = videoList.value.filter(v => v.id !== deletingId.value)
      toast.value = { message: 'Video eliminado correctamente', type: 'success' }
      cancelDelete()
    },
    onError: () => {
      toast.value = { message: 'Error al eliminar el video', type: 'danger' }
      isDeleting.value = false
    },
    onFinish: () => { isDeleting.value = false }
  })
}

/* ===== Tooltips ===== */
onMounted(() => {
  if (window.bootstrap?.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new window.bootstrap.Tooltip(el))
  }
})
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
