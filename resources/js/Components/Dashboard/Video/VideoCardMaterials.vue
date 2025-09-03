<!-- resources/js/Components/Dashboard/Video/VideoCardMaterials.vue -->
<script setup>
import { computed } from 'vue'

const props = defineProps({
  videoMaterials: {
    type: Array,
    required: true,
    default: () => []
  },
  videoId: {
    type: Number,
    required: true
  },
  // Opcional: si ya lo calculas en el backend
  materialsSummary: {
    type: Object,
    default: () => null
  },
  // Opcional: formateo de moneda
  currency: {
    type: String,
    default: 'MXN'
  },
  locale: {
    type: String,
    default: 'es-MX'
  }
})

function safeUrl(u) {
  if (!u) return null
  // Si viene completo (http/https) lo usamos tal cual; si es relativo, prepend /storage
  if (/^https?:\/\//i.test(u)) return u
  if (u.startsWith('/')) return u
  return `/storage/${u}`
}

function money(n, locale = 'es-MX', currency = 'MXN') {
  const num = Number(n || 0)
  try {
    return new Intl.NumberFormat(locale, { style: 'currency', currency }).format(num)
  } catch {
    // Fallback simple
    return `${currency} ${num.toFixed(2)}`
  }
}

const items = computed(() => {
  const arr = Array.isArray(props.videoMaterials) ? props.videoMaterials : []
  return arr.map(m => {
    const qty = Number.isFinite(+m.quantity) ? +m.quantity : 0
    const price = Number.isFinite(+m.price) ? +m.price : 0
    const total = Number.isFinite(+m.total_cost) ? +m.total_cost : +(qty * price)
    const imageUrl = m.image_url ? safeUrl(m.image_url) : safeUrl(m.image)
    return {
      id: m.id,
      name: m.name || 'Material',
      notes: m.notes || '',
      quantity: qty,
      unit: m.unit || '',
      price,
      total,
      buyLink: m.buy_link || '',
      imageUrl
    }
  })
})

const hasItems = computed(() => items.value.length > 0)

const summary = computed(() => {
  if (props.materialsSummary) return props.materialsSummary
  // Si no te lo mandan del backend, lo calculamos aquí
  const count = items.value.length
  const quantity_sum = items.value.reduce((acc, it) => acc + (Number.isFinite(it.quantity) ? it.quantity : 0), 0)
  const total_cost_sum = items.value.reduce((acc, it) => acc + (Number.isFinite(it.total) ? it.total : 0), 0)
  return { count, quantity_sum, total_cost_sum }
})
</script>

<template>
  <div class="card border-0 shadow h-100">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h6 class="mb-0">Materiales del Video</h6>
      <small class="text-muted">
        Video #{{ videoId }} · {{ summary.count || 0 }} material(es)
      </small>
    </div>

    <div class="card-body p-0">
      <div v-if="hasItems">
        <ul class="list-group list-group-flush">
          <li
            v-for="mat in items"
            :key="mat.id"
            class="list-group-item d-flex align-items-start gap-3"
          >
            <!-- Miniatura -->
            <div class="flex-shrink-0" style="width:48px;">
              <img
                v-if="mat.imageUrl"
                :src="mat.imageUrl"
                alt=""
                class="rounded"
                style="width:48px;height:48px;object-fit:cover;"
              />
              <div v-else
                   class="d-flex align-items-center justify-content-center bg-light rounded"
                   style="width:48px;height:48px;">
                <i class="bi bi-box-seam text-muted"></i>
              </div>
            </div>

            <!-- Info principal -->
            <div class="flex-grow-1">
              <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-semibold">{{ mat.name }}</span>
                <span class="badge text-bg-secondary">
                  Cantidad {{ mat.quantity }} 
                </span>
              </div>

              <div v-if="mat.notes" class="text-muted small mt-1">
                {{ mat.notes }}
              </div>

              <div v-if="mat.buyLink" class="small mt-1">
                <a :href="mat.buyLink" target="_blank" rel="noopener" class="btn btn-primary rounded-pill btn-sm">
                  Comprar
                </a>
              </div>
            </div>

            <!-- Costos -->
            <div class="text-end">
              <div class="small text-muted">Unitario</div>
              <div class="fw-semibold">{{ money(mat.price, locale, currency) }}</div>
              <div class="small text-muted mt-2">Total</div>
              <div class="fw-semibold">{{ money(mat.total, locale, currency) }}</div>
            </div>
          </li>
        </ul>

        <!-- Resumen -->
        <div class="d-flex justify-content-end p-3 border-top small text-muted">
          <div class="me-3">Materiales: {{ summary.count }}</div>
          <div class="me-3">Cantidad total: {{ summary.quantity_sum }}</div>
          <div>Total materiales: <span class="fw-semibold">{{ money(summary.total_cost_sum, locale, currency) }}</span></div>
        </div>
      </div>

      <div v-else class="p-3">
        <div class="alert alert-light border mb-0">
          No hay materiales registrados para este video.
        </div>
      </div>
    </div>
  </div>
</template>
