<!-- resources/js/Components/Dashboard/Video/VideoCardResources.vue -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
  videoResources: {
    type: Array,
    required: true,
    default: () => []
  },
  videoId: {
    type: Number,
    required: true
  }
})

/**
 * Mapea el tipo numérico a etiqueta e ícono
 */
function mapTypeInfo(type) {
  const t = Number(type)
  if (t === 1) return { label: 'Archivo', icon: 'bi-file-earmark' }
  if (t === 2) return { label: 'Video', icon: 'bi-camera-reels' }
  if (t === 3) return { label: 'Imagen', icon: 'bi-file-earmark-image' }
  return { label: 'Recurso', icon: 'bi-file-earmark' }
}

/**
 * Normaliza items y asigna siempre un `downloadUrl` válido
 */
const items = computed(() => {
  const arr = Array.isArray(props.videoResources) ? props.videoResources : []
  return arr.map(r => {
    const typeInfo = mapTypeInfo(r.type)

    // Prioridad de la fuente según tipo
    const source =
      r.file_src ||
      r.video_src ||
      r.img_src ||
      null

    return {
      id: r.id,
      title: r.title || 'Recurso',
      description: r.description || '',
      uploaded: r.uploaded || null,
      typeLabel: typeInfo.label,
      icon: typeInfo.icon,
      downloadUrl: source,
      canDownload: !!source
    }
  })
})

const hasItems = computed(() => items.value.length > 0)
</script>

<template>
  <div class="card border-0 shadow h-100">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h6 class="mb-0">Archivos del Video</h6>
      <small class="text-muted">
        Video #{{ videoId }} · {{ hasItems ? items.length : 0 }} recurso(s)
      </small>
    </div>

    <div class="card-body p-0">
      <div v-if="hasItems">
        <ul class="list-group list-group-flush">
          <li
            v-for="res in items"
            :key="res.id"
            class="list-group-item d-flex align-items-start gap-3"
          >
            <div class="pt-1">
              <i class="bi" :class="res.icon" style="font-size:1.1rem;"></i>
            </div>

            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">{{ res.title }}</span>
                <span class="badge text-bg-secondary">{{ res.typeLabel }}</span>
              </div>

              <div v-if="res.description" class="text-muted small mt-1">
                {{ res.description }}
              </div>

      
            </div>

            <div class="ms-auto">
              <!-- Siempre intentamos habilitar descarga -->
              <a
                v-if="res.canDownload"
                :href="`/storage/${res.downloadUrl}`"
                download
                target="_blank"
                rel="noopener"
                class="btn btn-sm rounded-pill btn-outline-secondary"
                title="Descargar recurso"
              >
                <i class="bi bi-download"></i>
                <span class="ms-1 d-none d-sm-inline">Descargar</span>
              </a>

              <span
                v-else
                class="btn btn-sm btn-outline-secondary disabled"
                title="Sin archivo disponible"
              >
                <i class="bi bi-download"></i>
                <span class="ms-1 d-none d-sm-inline">Descargar</span>
              </span>
            </div>
          </li>
        </ul>
      </div>

      <div v-else class="p-3">
        <div class="alert alert-light border mb-0">
          No hay recursos disponibles para este video.
        </div>
      </div>
    </div>
  </div>
</template>
