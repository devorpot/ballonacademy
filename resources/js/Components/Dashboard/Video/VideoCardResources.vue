<!-- resources/js/Components/Dashboard/Video/VideoCardResources.vue -->
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

const items = computed(() => {
  const arr = Array.isArray(props.videoResources) ? props.videoResources : []
  return arr.map(r => {
    const typeInfo = mapTypeInfo(r.type)
    const source = r.file_src || r.video_src || r.img_src || null
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

/* Toggle del cuerpo de la card */
const isOpen = ref(true)
const bodyId = computed(() => `card-res-body-${props.videoId}`)
</script>

<template>
  <div class="card border-0 shadow ">
    <!-- Header con título, contador y botón toggle -->
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-folder2-open"></i>
         <h6 class="fw-bold mb-0"> Archivos del Video</h6>
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

              <div class="flex-grow-1" style="min-width:0">
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold text-truncate">{{ res.title }}</span>
                  <span class="badge text-bg-secondary">{{ res.typeLabel }}</span>
                </div>
                <div v-if="res.description" class="text-muted small mt-1 text-truncate">
                  {{ res.description }}
                </div>
              </div>

              <div class="ms-auto">
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
    </Transition>
  </div>
</template>

<style scoped>
.list-group-item { padding-top: .9rem; padding-bottom: .9rem; }
.collapse-fade-enter-active,
.collapse-fade-leave-active {   }
.collapse-fade-enter-from,
.collapse-fade-leave-to { opacity: 0; }
</style>
