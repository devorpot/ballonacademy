<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Modal from 'bootstrap/js/dist/modal'

const props = defineProps({
  videoResources: { type: Array, default: () => [] },
  routeShow: { type: String, default: 'dashboard.videos.resources.show' },
  videoId: { type: Number, required: true }
})

const emit = defineEmits(['show-resource']) 

function typeBadge(type) {
  switch (type) {
    case 1: return { label: 'Archivo', icon: 'bi-file-earmark-text', class: 'bg-secondary' }
    case 2: return { label: 'Video', icon: 'bi-camera-video', class: 'bg-primary' }
    case 3: return { label: 'Imagen', icon: 'bi-image', class: 'bg-success' }
    default: return { label: 'Otro', icon: 'bi-question-circle', class: 'bg-dark' }
  }
}

/* ================== Modal ================== */
const selected = ref(null)
const modalRef = ref(null)
let modalInstance = null

onMounted(() => {
  if (modalRef.value) {
    modalInstance = new Modal(modalRef.value, {
      backdrop: 'static',
      keyboard: true,
      focus: true
    })
  }
})

onBeforeUnmount(() => {
  if (modalInstance) {
    modalInstance.hide()
    modalInstance.dispose()
    modalInstance = null
  }
})

async function openResource(res) {
  selected.value = normalizeResource(res)
  emit('show-resource', res) 
  await nextTick()
  modalInstance?.show()
}

function closeModal() {
  modalInstance?.hide()
  selected.value = null
}

/* ================== Helpers ================== */
function ensureUrl(p) {
  if (!p) return null
  if (/^https?:\/\//i.test(p) || p.startsWith('/')) return p
  return `/storage/${p}`
}

function normalizeResource(res) {
  // Campos del modelo: file_src, video_src, img_src, type
  let url = null
  if (res.type === 2) url = ensureUrl(res.video_src || res.file_src)
  else if (res.type === 3) url = ensureUrl(res.img_src || res.file_src)
  else url = ensureUrl(res.file_src)

  const ext = getExt(url)
  const mime = guessMime(ext)
  const provider = detectProvider(url)

  return {
    ...res,
    url,
    ext,
    mime,
    provider
  }
}

function getExt(url) {
  if (!url) return ''
  const clean = url.split('?')[0].split('#')[0]
  const dot = clean.lastIndexOf('.')
  return dot >= 0 ? clean.substring(dot + 1).toLowerCase() : ''
}

function guessMime(ext) {
  if (['mp4', 'webm', 'ogg'].includes(ext)) return `video/${ext}`
  if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'].includes(ext)) return `image/${ext === 'jpg' ? 'jpeg' : ext}`
  if (ext === 'pdf') return 'application/pdf'
  if (['doc', 'docx'].includes(ext)) return 'application/msword'
  if (['xls', 'xlsx'].includes(ext)) return 'application/vnd.ms-excel'
  if (['ppt', 'pptx'].includes(ext)) return 'application/vnd.ms-powerpoint'
  return ''
}

function detectProvider(url) {
  if (!url) return null
  if (/youtube\.com|youtu\.be/i.test(url)) return 'youtube'
  if (/vimeo\.com/i.test(url)) return 'vimeo'
  return null
}

const isVideo = computed(() => {
  if (!selected.value) return false
  if (selected.value.type === 2) return true
  return selected.value.mime?.startsWith('video/') || selected.value.provider === 'youtube'
})

const isImage = computed(() => {
  if (!selected.value) return false
  if (selected.value.type === 3) return true
  return selected.value.mime?.startsWith('image/')
})

const isDownloadable = computed(() => {
  if (!selected.value) return false
  const m = selected.value.mime || ''
  if (m.startsWith('video/') || m.startsWith('image/')) return false
  return true
})

function youtubeEmbed(url) {
  // Convierte enlaces a formato embebido
  try {
    const u = new URL(url)
    if (u.hostname.includes('youtu.be')) {
      const id = u.pathname.slice(1)
      return `https://www.youtube.com/embed/${id}`
    }
    if (u.hostname.includes('youtube.com')) {
      const id = u.searchParams.get('v')
      if (id) return `https://www.youtube.com/embed/${id}`
    }
    return url
  } catch {
    return url
  }
}

function humanFileType(res) {
  if (!res) return ''
  const e = (res.ext || '').toUpperCase()
  if (e) return e
  if (res.mime) return res.mime
  return 'Archivo'
}

function formatUploaded(value) {
  if (!value) return ''
  try {
    const d = new Date(value)
    return new Intl.DateTimeFormat('es-MX', { year: 'numeric', month: 'short', day: '2-digit' }).format(d)
  } catch {
    return value
  }
}
</script>

<template>
  <div class="d-block">
      <h5 class="my-3">Archivos del Video</h5>
      
  </div>
  <div class="d-flex flex-column gap-3">
    <div
      v-for="res in videoResources"
      :key="res.id"
      class="card shadow-sm border-0"
    >
      <div class="row g-0 align-items-stretch flex-md-row flex-column">
        <!-- Columna lateral decorativa -->
        <div class="col-auto d-flex align-items-center">
          <div class="h-100 d-none d-md-block bg-light"
               style="width:6px; border-top-left-radius:.5rem; border-bottom-left-radius:.5rem;"></div>
          <div class="p-3 d-md-none d-flex align-items-center">
            <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center"
                 style="width:48px; height:48px;">
              <i :class="['bi', typeBadge(res.type).icon, 'fs-5']"></i>
            </div>
          </div>
        </div>

        <!-- Contenido principal -->
        <div class="col">
          <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
              <!-- Título y badges -->
              <div class="d-flex flex-column">
                <h5 class="card-title mb-1 d-flex align-items-center gap-2">
                  <i :class="['bi', typeBadge(res.type).icon]"></i>
                  <span>{{ res.title }}</span>
                </h5>

                <div class="d-flex flex-wrap align-items-center gap-2">
                  <span class="badge" :class="typeBadge(res.type).class">
                    {{ typeBadge(res.type).label }}
                  </span>
                  <span v-if="res.uploaded" class="badge text-bg-light border">
                    {{ formatUploaded(res.uploaded) }}
                  </span>
                </div>
              </div>

              <!-- Acción: abrir modal -->
              <button
                type="button"
                class="btn btn-outline-primary btn-sm fw-semibold d-inline-flex align-items-center gap-2"
                @click="openResource(res)"
                title="Ver recurso"
              >
                <i class="bi bi-box-arrow-up-right"></i>
                Ver recurso
              </button>
            </div>

            <!-- Descripción -->
            <div v-if="res.description" class="mt-2 small text-muted">
              {{ res.description }}
            </div>

            <!-- Link alterno para abrir en página (opcional) -->
            <div class="mt-2" v-if="routeShow">
              <Link
                class="link-secondary small"
                :href="route(routeShow, { video: videoId, resource: res.id })"
                title="Abrir en página dedicada"
              >
                Abrir en página
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- /card -->

      <Teleport to="body">
    <div
      class="modal fade"
      tabindex="-1"
      ref="modalRef"
      aria-labelledby="resourceModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5 d-flex align-items-center gap-2" id="resourceModalLabel">
              <i v-if="selected" :class="['bi', typeBadge(selected.type).icon]"></i>
              {{ selected?.title || 'Recurso' }}
            </h1>
            <button type="button" class="btn-close" aria-label="Cerrar" @click="closeModal"></button>
          </div>

          <div class="modal-body">
            <!-- Video -->
            <template v-if="selected && isVideo">
              <!-- YouTube -->
              <div v-if="selected.provider === 'youtube'" class="ratio ratio-16x9">
                <iframe
                  :src="youtubeEmbed(selected.url)"
                  title="YouTube"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowfullscreen
                ></iframe>
              </div>

              <!-- HTML5 -->
              <div v-else class="ratio ratio-16x9">
                <video
                  :key="selected.url"
                  class="w-100 h-100 rounded"
                  controls
                  playsinline
                  controlsList="nodownload"
                >
                  <source :src="selected.url" :type="selected.mime || 'video/mp4'">
                  Tu navegador no soporta el video.
                </video>
              </div>

              <p v-if="selected.description" class="text-muted mt-3">{{ selected.description }}</p>
            </template>

            <!-- Imagen -->
            <template v-else-if="selected && isImage">
              <div class="text-center">
                <img
                  :src="selected.url"
                  :alt="selected.title || 'Imagen'"
                  class="img-fluid rounded shadow-sm"
                  style="max-height: 70vh; object-fit: contain;"
                />
              </div>
              <p v-if="selected.description" class="text-muted mt-3">{{ selected.description }}</p>
            </template>

            <!-- Descargable (PDF/DOC/otros) -->
            <template v-else-if="selected && isDownloadable">
              <div class="d-flex flex-column align-items-center justify-content-center py-4">
                <i class="bi bi-file-earmark-arrow-down display-4 mb-2 text-secondary"></i>
                <div class="mb-3">
                  Tipo de archivo: <strong>{{ humanFileType(selected) }}</strong>
                </div>
                <a
                  class="btn btn-primary"
                  :href="selected.url"
                  download
                  target="_blank"
                  rel="noopener"
                >
                  Descargar
                </a>
              </div>
              <p v-if="selected.description" class="text-muted mt-3 text-center">{{ selected.description }}</p>
            </template>

            <!-- Fallback -->
            <template v-else>
              <div class="text-center text-muted py-5">
                No se pudo cargar el recurso.
              </div>
            </template>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeModal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
  </div>

  <!-- Modal teletransportado al body -->

</template>
