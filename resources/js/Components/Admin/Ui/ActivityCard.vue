<template>
  <div class="card shadow-sm h-100">
    <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2">
      <h6 class="mb-0 fw-bold header-title-truncate">
        <i v-if="icon" :class="`${icon} me-1`"></i> {{ title }}
      </h6>
      <Link v-if="allHref" :href="allHref" class="btn btn-primary rounded-pill btn-sm flex-shrink-0">
        Ir a la sección
      </Link>
    </div>

    <div class="list-group list-group-flush">
      <div v-if="!items?.length" class="p-3 text-muted">Sin registros.</div>

      <div
        v-for="item in sliced"
        :key="keyFor(item)"
        class="list-group-item d-flex align-items-center justify-content-between"
      >
        <div class="d-flex align-items-center gap-3 w-100">
          <!-- Avatar -->
          <div class="user-avatar d-inline-flex align-items-center flex-shrink-0">
            <img
              v-if="avatarUrl(item)"
              :src="avatarUrl(item)"
              class="rounded-circle object-fit-cover"
              :alt="nickname(item) || 'avatar'"
              style="width:42px;height:42px;"
            />
            <div
              v-else
              class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-muted"
              style="width:42px;height:42px;"
            >
              <i class="bi bi-image"></i>
            </div>
          </div>

          <!-- Textos -->
          <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold clamp-1 break-anywhere">
              <slot name="title" :item="item">
                {{ defaultTitle(item) }}
              </slot>
            </div>
            <div class="text-muted small clamp-2 break-anywhere mt-1">
              <slot name="subtitle" :item="item">
                {{ defaultSubtitle(item) }}
              </slot>
            </div>
          </div>
        </div>
        <!-- Si aquí agregas acciones a la derecha, usa .flex-shrink-0 en su contenedor -->
      </div>
    </div>
  </div>
</template>


<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

/**
 * Props:
 * - title, icon, items, rows, allHref
 * - routeName/paramKey: construcción global del href
 * - itemUrlBase: alternativa sin Ziggy
 * - getHref: función (item) => string|null para construir el href por ítem
 * - storagePrefix: para profile_image relativo a storage
 */
const props = defineProps({
  title: { type: String, required: true },
  icon: { type: String, default: '' },
  items: { type: Array, default: () => [] },
  rows: { type: Number, default: 6 },
  allHref: { type: String, default: '' },
  routeName: { type: String, default: '' },
  paramKey: { type: String, default: 'id' },
  itemUrlBase: { type: String, default: '' },
  getHref: { type: Function, default: null },
  storagePrefix: { type: String, default: '/storage/' },
})

const sliced = computed(() => props.items.slice(0, props.rows))

function keyFor(item) {
  return `${props.title}-${item?.[props.paramKey] ?? item?.id ?? Math.random().toString(36).slice(2)}`
}

function nickname(item) {
  const u = item?.user || {}
  return u.nickname || u.name || (u.email ? u.email.split('@')[0] : '')
}

function makeUrlFromStorage(path) {
  if (!path) return null
  if (/^https?:\/\//i.test(path)) return path
  if (path.startsWith('/')) return path
  return `${props.storagePrefix}${path}`
}

function avatarUrl(item) {
  const u = item?.user || {}
  // prioridad: user.avatar > user.profile.profile_image
  const direct = u.avatar
  const fromProfile = u.profile?.profile_image
  return direct ? direct : makeUrlFromStorage(fromProfile)
}

function detailHref(item) {
  // 1) getHref por ítem (máxima prioridad)
  if (typeof props.getHref === 'function') {
    try { return props.getHref(item) || null } catch { /* noop */ }
  }
  // 2) routeName/paramKey global
  const idVal = item?.[props.paramKey]
  if (props.routeName && idVal != null) {
    try { return route(props.routeName, idVal) } catch { return null }
  }
  // 3) base url simple
  if (props.itemUrlBase && idVal != null) return `${props.itemUrlBase}${idVal}`
  // 4) fallback a item._next si viene armado desde el mapper
  const n = item?._next
  if (n?.routeName && n?.paramVal != null) {
    try { return route(n.routeName, n.paramVal) } catch { return null }
  }
  return null
}

function defaultTitle(item) {
  if (item?.title) return item.title
  const n = nickname(item)
  const t = item?.metaTitle || ''
  return t ? `${n} · ${t}` : n
}
function defaultSubtitle(item) {
  return item?.subtitle || ''
}
</script>

<style>
.object-fit-cover { object-fit: cover; }

/* El título del header no debe empujar el botón en pantallas estrechas */
.header-title-truncate {
  min-width: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Cortar palabras largas sin espacios (URLs, tokens, etc.) */
.break-anywhere {
  overflow-wrap: anywhere;   /* moderno */
  word-break: break-word;    /* fallback */
}

/* Truncado multilínea */
.clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;     /* cambia a 3 si quieres más líneas */
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Ajustes extra para móviles: si prefieres apilar título/botón */
@media (max-width: 575.98px) {
  .card-header {
    flex-wrap: wrap;
  }
  .card-header .header-title-truncate {
    flex: 1 1 auto;
    min-width: 0;
    margin-bottom: .25rem;
  }
}
</style>
