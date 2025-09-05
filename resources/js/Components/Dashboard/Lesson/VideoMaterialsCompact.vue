<!-- resources/js/Components/Dashboard/Video/VideoMaterialsCompact.vue -->
<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  videoMaterials: { type: Array, required: true, default: () => [] },
  videoId: { type: Number, required: true }
})

function safeUrl(u) {
  if (!u) return null
  if (/^https?:\/\//i.test(u)) return u
  if (u.startsWith('/')) return u
  return `/storage/${u}`
}

const items = computed(() => {
  const arr = Array.isArray(props.videoMaterials) ? props.videoMaterials : []
  return arr.map(m => ({
    id: m.id,
    name: m.name || 'Material',
    quantity: Number.isFinite(+m.quantity) ? +m.quantity : null,
    imageUrl: safeUrl(m.image_url || m.image)
  }))
})

const hasItems = computed(() => items.value.length > 0)

/* Toggle */
const isOpen = ref(false)
const bodyId = computed(() => `card-mat-compact-${props.videoId}`)
</script>

<template>
  <div class="card border-0 shadow-sm">
    <!-- Header con título y toggle -->
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-box-seam"></i>
        <h6 class="fw-bold mb-0">Materiales del Video</h6>
        <small class="text-muted ms-2">{{ items.length }} total</small>
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

    <!-- Body colapsable -->
    <Transition name="collapse-fade">
      <div v-show="isOpen" :id="bodyId" class="card-body p-0">
        <ul v-if="hasItems" class="list-group list-group-flush">
          <li
            v-for="mat in items"
            :key="mat.id"
            class="list-group-item d-flex align-items-center gap-2 py-2"
          >
            <!-- Miniatura 40x40 -->
            <div class="flex-shrink-0">
              <img
                v-if="mat.imageUrl"
                :src="mat.imageUrl"
                alt=""
                class="rounded border bg-light"
                style="width:40px;height:40px;object-fit:cover;"
              />
              <div
                v-else
                class="rounded border bg-light d-flex align-items-center justify-content-center text-muted small"
                style="width:40px;height:40px;"
              >
                40×40
              </div>
            </div>

            <!-- Título y cantidad -->
            <div class="flex-grow-1 d-flex justify-content-between align-items-center">
              <span class="text-truncate">{{ mat.name }}</span>
              <span v-if="mat.quantity !== null" class="badge text-bg-secondary">
                x{{ mat.quantity }}
              </span>
            </div>
          </li>
        </ul>

        <div v-else class="p-3 small text-muted">
          No hay materiales registrados.
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
