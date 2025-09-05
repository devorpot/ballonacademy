<!-- resources/js/Components/Dashboard/Video/VideoCardMaterials.vue -->
<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  videoMaterials: { type: Array, required: true, default: () => [] },
  videoId: { type: Number, required: true },
  materialsSummary: { type: Object, default: () => null }
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
    notes: m.notes || '',
    quantity: Number.isFinite(+m.quantity) ? +m.quantity : null,
    imageUrl: safeUrl(m.image_url || m.image)
  }))
})

const hasItems = computed(() => items.value.length > 0)

const summary = computed(() => {
  if (props.materialsSummary) return props.materialsSummary
  return { count: items.value.length }
})

/* Toggle del cuerpo de la card */
const isOpen = ref(true)
const bodyId = computed(() => `card-mat-body-${props.videoId}`)
</script>

<template>
  <div class="card border-0 shadow ">
    <!-- Header con título y botón toggle -->
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-journal-bookmark"></i>
         <h6 class="fw-bold mb-0">  Materiales del Video</h6>
        <small class="text-muted ms-2">Video #{{ videoId }} · {{ summary.count || 0 }} material(es)</small>
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
              v-for="mat in items"
              :key="mat.id"
              class="list-group-item"
            >
              <!-- Miniatura | Texto -->
              <div class="d-flex align-items-start gap-3">
                <!-- Miniatura 70x70 -->
                <div class="flex-shrink-0">
                  <img
                    v-if="mat.imageUrl"
                    :src="mat.imageUrl"
                    alt=""
                    class="rounded border bg-light"
                    style="width:70px;height:70px;object-fit:cover;"
                  />
                  <div
                    v-else
                    class="rounded border bg-light d-flex align-items-center justify-content-center text-muted"
                    style="width:70px;height:70px;"
                  >
                    70×70
                  </div>
                </div>

                <!-- Título y descripción -->
                <div class="flex-grow-1" style="min-width:0">
                  <div class="d-flex align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 text-truncate">{{ mat.name }}</h6>
                    <span v-if="mat.quantity !== null" class="badge text-bg-secondary">Cantidad {{ mat.quantity }}</span>
                  </div>
                  <div v-if="mat.notes" class="small text-muted mt-1 text-truncate">
                    {{ mat.notes }}
                  </div>
                </div>
              </div>
            </li>
          </ul>

          <!-- Resumen simple -->
          <div class="p-3 border-top small text-muted">
            Materiales: {{ summary.count }}
          </div>
        </div>

        <div v-else class="p-3">
          <div class="alert alert-light border mb-0">
            No hay materiales registrados para este video.
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.list-group-item { padding-top: .9rem; padding-bottom: .9rem; }

/* Animación del colapso */
.collapse-fade-enter-active,
.collapse-fade-leave-active {   }
.collapse-fade-enter-from,
.collapse-fade-leave-to { opacity: 0; }
</style>
