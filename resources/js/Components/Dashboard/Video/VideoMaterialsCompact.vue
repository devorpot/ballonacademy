<!-- resources/js/Components/Dashboard/Video/VideoMaterialsCompact.vue -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
  materials: { type: Array, required: true, default: () => [] },
  title: { type: String, default: 'Materiales' }
})

function safeUrl(u) {
  if (!u) return null
  if (/^https?:\/\//i.test(u)) return u
  if (u.startsWith('/')) return u
  return `/storage/${u}`
}

const items = computed(() => {
  const arr = Array.isArray(props.materials) ? props.materials : []
  return arr.map((m, idx) => ({
    id: m.id ?? idx,
    title: m.title ?? m.name ?? 'Material',
    image: safeUrl(m.image ?? m.image_url),
    quantity: Number.isFinite(+m.quantity) ? +m.quantity : null
  }))
})

const hasItems = computed(() => items.value.length > 0)
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-bag-check"></i>
        <h6 class="fw-bold mb-0">{{ title }}</h6>
        <small class="text-muted">· {{ items.length }} item(s)</small>
      </div>
    </div>

    <div class="card-body p-0">
      <ul v-if="hasItems" class="list-group list-group-flush">
        <li
          v-for="it in items"
          :key="it.id"
          class="list-group-item py-2"
        >
          <div class="d-flex align-items-center gap-2">
            <!-- Imagen 30x30 -->
            <div class="flex-shrink-0">
              <img
                v-if="it.image"
                :src="it.image"
                alt=""
                class="rounded border bg-light"
                style="width:30px;height:30px;object-fit:cover;"
              />
              <div
                v-else
                class="rounded border bg-light d-flex align-items-center justify-content-center text-muted"
                style="width:30px;height:30px;font-size:.65rem;"
              >
                —
              </div>
            </div>

            <!-- Título -->
            <div class="flex-grow-1 text-truncate">
              <span class="fw-semibold text-truncate d-inline-block" style="max-width: 100%;">
                {{ it.title }}
              </span>
            </div>

            <!-- Cantidad -->
            <span v-if="it.quantity !== null" class="badge text-bg-secondary">
              x{{ it.quantity }}
            </span>
          </div>
        </li>
      </ul>

      <div v-else class="p-3">
        <div class="alert alert-light border mb-0">
          No hay materiales registrados.
        </div>
      </div>
    </div>
  </div>
</template>
