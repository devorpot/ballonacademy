<template>
  <div
    v-show="show"
    class="modal fade show d-block"
    tabindex="-1"
    aria-labelledby="showExtraClassModalLabel"
    aria-modal="true"
    role="dialog"
  >
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content" style="z-index:9999!important;">
        <div class="modal-header">
          <h5 class="modal-title" id="showExtraClassModalLabel">
            Detalles de la Clase Extra
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-4">
            <!-- Columna izquierda: media -->
            <div class="col-12 col-lg-5">
              <div class="ratio ratio-16x9 mb-3" v-if="isYouTube && embedUrl">
                <iframe
                  :src="embedUrl"
                  title="YouTube video player"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                />
              </div>

              <div v-else class="mb-3">
                <img
                  v-if="item.cover_url || item.image_url"
                  :src="item.cover_url || item.image_url"
                  :alt="item.title"
                  class="img-fluid rounded border w-100"
                  style="object-fit:cover; max-height:320px;"
                />
                <div v-else class="text-muted small">
                  Sin imagen
                </div>
              </div>

              <div class="d-grid gap-2">
                <a
                  v-if="isYouTube && item.youtube_url"
                  :href="item.youtube_url"
                  target="_blank"
                  rel="noopener"
                  class="btn btn-outline-danger btn-sm"
                >
                  Abrir en YouTube
                </a>

                <a
                  v-if="!isYouTube && item.video_url"
                  :href="item.video_url"
                  target="_blank"
                  rel="noopener"
                  class="btn btn-outline-primary btn-sm"
                >
                  Descargar video MP4
                </a>

                <a
                  v-if="item.subt_url"
                  :href="item.subt_url"
                  target="_blank"
                  rel="noopener"
                  class="btn btn-outline-secondary btn-sm"
                >
                  Descargar subtítulos (.vtt)
                </a>
              </div>
            </div>

            <!-- Columna derecha: texto y metadatos -->
            <div class="col-12 col-lg-7">
              <h4 class="mb-1 text-truncate">{{ item.title }}</h4>
              <p class="text-muted mb-2">{{ item.extract }}</p>

              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge text-bg-light border" v-if="item.category">Categoría: {{ item.category }}</span>
                <span class="badge" :class="item.active === 1 ? 'text-bg-success' : 'text-bg-secondary'">
                  {{ item.active === 1 ? 'Activo' : 'Inactivo' }}
                </span>
                <span class="badge" :class="isYouTube ? 'text-bg-danger' : 'text-bg-secondary'">
                  {{ isYouTube ? 'YouTube' : 'Archivo' }}
                </span>
                <span class="badge text-bg-light border">Orden: {{ item.order ?? 0 }}</span>
              </div>

              <div class="mb-3" v-if="item.tags">
                <small class="text-muted">Tags:</small>
                <div class="d-flex flex-wrap gap-2 mt-1">
                  <span
                    v-for="t in splitTags"
                    :key="t"
                    class="badge rounded-pill text-bg-light border"
                  >{{ t }}</span>
                </div>
              </div>

              <div class="mb-3">
                <small class="text-muted d-block mb-1">Descripción</small>
                <div class="content" v-html="safeDescription"></div>
              </div>

              <div class="text-muted small">
                <div>Creado: {{ formatDate(item.created_at) }}</div>
                <div>Actualizado: {{ formatDate(item.updated_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="emit('close')">
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Props: recibe el item completo (con *_url appends) y el flag show
const props = defineProps({
  show: { type: Boolean, default: false },
  item: {
    type: Object,
    required: true
  }
})
const emit = defineEmits(['close'])

const item = computed(() => props.item || {})

const isYouTube = computed(() => Number(item.value.is_youtube) === 1)

// Parseo básico para embeber YouTube (watch?v=, youtu.be/, shorts/)
const embedUrl = computed(() => {
  if (!isYouTube.value || !item.value.youtube_url) return ''
  try {
    const url = new URL(item.value.youtube_url)
    // watch?v=ID
    if (url.hostname.includes('youtube.com')) {
      if (url.pathname.startsWith('/watch')) {
        const id = url.searchParams.get('v')
        return id ? `https://www.youtube.com/embed/${id}` : ''
      }
      // shorts/ID o embed/ID
      const parts = url.pathname.split('/').filter(Boolean)
      const id = parts[0] === 'shorts' || parts[0] === 'embed' ? parts[1] : ''
      return id ? `https://www.youtube.com/embed/${id}` : ''
    }
    // youtu.be/ID
    if (url.hostname === 'youtu.be') {
      const id = url.pathname.replace('/', '')
      return id ? `https://www.youtube.com/embed/${id}` : ''
    }
  } catch (e) {
    return ''
  }
  return ''
})

const splitTags = computed(() => {
  if (!item.value.tags) return []
  return item.value.tags
    .split(',')
    .map(t => t.trim())
    .filter(Boolean)
})

// Si tu descripción viene con HTML permitido del backend, puedes confiar en ella.
// Si no, considera sanearla en servidor. Aquí solo devolvemos tal cual.
const safeDescription = computed(() => item.value.description || '')

const formatDate = (d) => {
  if (!d) return ''
  const dt = new Date(d)
  if (isNaN(dt.getTime())) return d
  return dt.toLocaleString()
}
</script>

<style scoped>
.content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: .5rem;
}
.content :deep(p) {
  margin-bottom: .5rem;
}
</style>
