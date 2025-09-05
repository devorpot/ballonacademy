<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'

const props = defineProps({
  username: { type: String, default: 'Usuario' },
  breadcrumbs: {
    type: Array,
    default: () => [{ label: 'Dashboard', route: 'dashboard.index' }]
  }
})

const page = usePage()
const User = computed(() => page.props.auth.user ?? {})

function isRouteName(value) {
  return typeof value === 'string' && !value.startsWith('http') && !value.startsWith('/')
}

const lastLabel = computed(() => props.breadcrumbs.at(-1)?.label || 'Dashboard')
const userInitial = computed(() => (props.username?.trim?.()[0] || 'U').toUpperCase())

// Para el dropdown mobile: items clicables salvo el último
const mobileItems = computed(() =>
  props.breadcrumbs.map((b, i) => ({
    ...b,
    isActive: i === props.breadcrumbs.length - 1
  }))
)
</script>

<template>
  <section class="section-breadcrumbs my-3">
    <div class="container-fluid">
      <div class="breadcrumbs-card bg-white border rounded-4 shadow-sm">
        <div class="row g-0 align-items-center">
          <div class="col-12 col-lg">
            <div class="d-flex align-items-center gap-2 p-2 p-md-4">
              <!-- (opcional) avatar
              <div class="avatar-circle d-none d-sm-inline-flex">
                <span class="avatar-initial">{{ userInitial }}</span>
              </div> -->

              <div class="flex-grow-1 ">
                <h1 class="h5 fw-medium mb-2 text-truncate" :title="lastLabel">
                  {{ lastLabel }}
                </h1>

                <!-- ====== Mobile: Dropdown de ruta ====== -->
                <div class="d-sm-none">
                  <div class="dropdown">
                    <button
                      class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-truncate"
                      type="button"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                      :title="lastLabel"
                    >
                      {{ lastLabel }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end w-100 shadow-sm">
                      <li v-for="(item, idx) in mobileItems" :key="idx">
                        <span v-if="item.isActive || !item.route" class="dropdown-item disabled small">
                          <i class="bi bi-dot me-1"></i>{{ item.label }}
                        </span>
                        <Link
                          v-else
                          class="dropdown-item small"
                          :href="isRouteName(item.route)
                            ? (item.params ? route(item.route, item.params) : route(item.route))
                            : item.route"
                        >
                          <i class="bi bi-chevron-right me-1"></i>{{ item.label }}
                        </Link>
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- ====== Desktop/Tablet: Breadcrumb tradicional ====== -->
                <nav aria-label="breadcrumb" class="breadcrumb-wrapper d-none d-sm-block">
                  <ol class="breadcrumb mb-0 small">
                    <li
                      v-for="(item, index) in breadcrumbs"
                      :key="index"
                      class="breadcrumb-item"
                      :class="{ active: index === breadcrumbs.length - 1 }"
                      aria-current="page"
                    >
                      <span
                        v-if="index === breadcrumbs.length - 1 || !item.route"
                        class="text-muted text-truncate d-inline-block breadcrumb-item-text"
                        :title="item.label"
                      >
                        {{ item.label }}
                      </span>

                      <Link
                        v-else
                        class="breadcrumb-link text-decoration-none"
                        :href="isRouteName(item.route)
                          ? (item.params ? route(item.route, item.params) : route(item.route))
                          : item.route"
                      >
                        <span class="text-truncate d-inline-block breadcrumb-item-text" :title="item.label">
                          {{ item.label }}
                        </span>
                      </Link>
                    </li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>

          <!-- Col: Acciones -->
          <div class="col-12 col-lg-auto">
            <div class="px-3 px-md-4 pb-3 pb-md-4 pt-0 pt-lg-4 d-flex gap-2 justify-content-start justify-content-lg-end">
              <slot name="actions" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style lang="less" scoped>
.section-breadcrumbs {}

.breadcrumbs-card {
  border-color: rgba(0, 0, 0, .06) !important;
}

/* Avatar opcional */
.avatar-circle {
  width: 56px; height: 56px; border-radius: 50%;
  display: inline-flex; align-items: center; justify-content: center;
  background: linear-gradient(180deg, #f3f6ff 0%, #eef2ff 100%);
  border: 1px solid rgba(13,110,253,.15);
  box-shadow: 0 2px 6px rgba(13,110,253,.08);
  flex: 0 0 auto;
}
.avatar-initial { font-weight: 700; color: #0d6efd; }

/* Breadcrumb (desktop/tablet) */
.breadcrumb-wrapper {
  overflow: auto; /* permite scroll horizontal si hace falta */
  -webkit-overflow-scrolling: touch;
  mask-image: linear-gradient(to right, transparent 0, black 12px, black calc(100% - 12px), transparent 100%);
}
.breadcrumb {
  --bs-breadcrumb-divider: '›';
  white-space: nowrap;
  margin-top: 2px;
  gap: .25rem;
}
.breadcrumb-item { max-width: 22ch; }
.breadcrumb-item + .breadcrumb-item::before {
  color: #adb5bd; padding-right: .5rem; padding-left: .25rem;
}
.breadcrumb-link { color: #0d6efd; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-item-text { max-width: 22ch; vertical-align: bottom; }

/* Dropdown mobile */
.dropdown-menu {
  max-height: 60vh;
  overflow: auto;
}
.dropdown-item.disabled {
  opacity: .7;
}

/* Responsivo */
@media (max-width: 575.98px) {
  .avatar-circle { width: 48px; height: 48px; }
  .breadcrumb-item, .breadcrumb-item-text { max-width: 18ch; }
}
@media (min-width: 1200px) {
  .breadcrumb-item, .breadcrumb-item-text { max-width: 28ch; }
}
</style>
