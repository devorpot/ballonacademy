<!-- resources/js/Components/Dashboard/Video/VideoResourcesCompact.vue -->
<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  videoResources: { type: Array, required: true, default: () => [] },
  videoId: { type: Number, required: true }
})

function mapTypeInfo(type) {
  const t = Number(type)
  if (t === 1) return { label: 'Archivo', icon: 'bi-file-earmark' }
  if (t === 2) return { label: 'Video', icon: 'bi-camera-reels' }
  if (t === 3) return { label: 'Imagen', icon: 'bi-file-earmark-image' }
  return { label: 'Recurso', icon: 'bi-file-earmark' }
}

function normalizeSrc(r) {
  const src = r.file_src || r.video_src || r.img_src || ''
  if (!src) return null
  if (/^https?:\/\//i.test(src)) return src
  if (src.startsWith('/')) return src
  return `/storage/${src}`
}

const items = computed(() => {
  const arr = Array.isArray(props.videoResources) ? props.videoResources : []
  return arr.map(r => {
    const t = mapTypeInfo(r.type)
    const href = normalizeSrc(r)
    return {
      id: r.id,
      title: r.title || 'Recurso',
      typeLabel: t.label,
      icon: t.icon,
      href,
      canDownload: !!href
    }
  })
})

const hasItems = computed(() => items.value.length > 0)

/* Toggle del cuerpo de la card */
const isOpen = ref(false)
const bodyId = computed(() => `card-res-compact-body-${props.videoId}`)
</script>

<template>
  <div class="card border-0 shadow-sm">
    <!-- Header con título, contador y botón toggle -->
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-folder2-open"></i>
        <h6 class="fw-bold mb-0">Archivos del Video</h6>
        <small class="text-muted ms-2">
          Video #{{ videoId }} · {{ hasItems ? items.length : 0 }} recurso(s)
        </small>
      </div>

      <button
        type="button"
        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center"
        @click="isOpen = !isOpen"
        :aria-expanded="isOpen"
        :aria-controls="bodyId"
        :title="isOpen ? 'Ocultar' : 'Mostrar'"
      >
        <i v-if="isOpen" class="bi bi-chevron-down"></i>
        <i v-else class="bi bi-dash-lg"></i>
      </button>
    </div>

    <!-- Cuerpo colapsable -->
    <Transition name="collapse-fade">
      <div v-show="isOpen" :id="bodyId" class="card-body p-0">
        <ul v-if="hasItems" class="list-group list-group-flush">
          <li
            v-for="res in items"
            :key="res.id"
            class="list-group-item d-flex align-items-center gap-2 py-2"
          >
            <!-- Icono compacto -->
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded border bg-light"
                 style="width:40px;height:40px;">
              <i class="bi" :class="res.icon"></i>
            </div>

            <!-- Título y tipo -->
            <div class="flex-grow-1 d-flex justify-content-between align-items-center" style="min-width:0;">
              <span class="text-truncate">{{ res.title }}</span>
              <span class="badge text-bg-secondary ms-2">{{ res.typeLabel }}</span>
            </div>

            <!-- Acción -->
            <div class="ms-2">
              <a
                v-if="res.canDownload"
                :href="res.href"
                download
                target="_blank"
                rel="noopener"
                class="btn btn-sm btn-outline-secondary"
                title="Descargar recurso"
              >
                <i class="bi bi-download"></i>
              </a>
              <span
                v-else
                class="btn btn-sm btn-outline-secondary disabled"
                title="Sin archivo disponible"
              >
                <i class="bi bi-download"></i>
              </span>
            </div>
          </li>
        </ul>

        <div v-else class="p-3 small text-muted">
          No hay recursos disponibles para este video.
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.list-group-item {
  font-size: 0.9rem;
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

/* Animación del colapso */
.collapse-fade-enter-active,
.collapse-fade-leave-active {
  transition: opacity 0.2s ease;
}
.collapse-fade-enter-from,
.collapse-fade-leave-to {
  opacity: 0;
}
</style>
