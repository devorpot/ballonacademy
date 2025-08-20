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

// nombre de ruta vs URL
function isRouteName(value) {
  return typeof value === 'string' && !value.startsWith('http') && !value.startsWith('/')
}

// último título como encabezado
const lastLabel = computed(() => props.breadcrumbs.at(-1)?.label || 'Dashboard')

// inicial del avatar
const userInitial = computed(() => (props.username?.trim?.()[0] || 'U').toUpperCase())
</script>

<template>
  <section class="section-breadcrumbs my-3">
    <div class="container-fluid">
      <div class="breadcrumbs-card bg-white border rounded-4 shadow-sm">
        <div class="row g-0 align-items-center">
          <!-- Col: Avatar + título + breadcrumb -->
          <div class="col-12 col-lg">
            <div class="d-flex align-items-center gap-3 p-3 p-md-4">
              <!-- Avatar circular (opcional, se muestra si hay username) -->
               

              <div class="flex-grow-1 min-w-0">
                <h1 class="h4 fw-medium mb-1 text-truncate" :title="lastLabel">
                  {{ lastLabel }}
                </h1>

                <!-- Breadcrumb navegable -->
                <nav aria-label="breadcrumb" class="breadcrumb-wrapper">
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

          <!-- Col: Acciones (opcional) -->
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
.section-breadcrumbs {
  /* separador visual respecto a la cabecera */
}

/* Tarjeta del encabezado */
.breadcrumbs-card {
  /* línea sutil arriba para integrarse con el resto de paneles */
  border-color: rgba(0, 0, 0, .06) !important;
}

/* Avatar redondo (coherente con la UI del resto de paneles) */
.avatar-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(180deg, #f3f6ff 0%, #eef2ff 100%);
  border: 1px solid rgba(13, 110, 253, .15);
  box-shadow: 0 2px 6px rgba(13, 110, 253, .08);
  flex: 0 0 auto;
}
.avatar-initial {
  font-weight: 700;
  color: #0d6efd;
}

/* Breadcrumb */
.breadcrumb-wrapper {
  /* permite scroll horizontal suave cuando hay muchas migas en pantallas pequeñas */
  overflow: hidden;
}
.breadcrumb {
  --bs-breadcrumb-divider: '›';
  white-space: nowrap;
  margin-top: 2px;
  gap: .25rem;
}
.breadcrumb-item {
  max-width: 22ch; /* cada miga no se come todo el ancho */
}
.breadcrumb-item + .breadcrumb-item::before {
  color: #adb5bd;
  padding-right: .5rem;
  padding-left: .25rem;
}
.breadcrumb-link {
  color: #0d6efd;
}
.breadcrumb-link:hover {
  text-decoration: underline;
}

/* El texto de cada miga se trunca correctamente */
.breadcrumb-item-text {
  max-width: 22ch;
  vertical-align: bottom;
}

/* Responsivo */
@media (max-width: 575.98px) {
  .avatar-circle { width: 48px; height: 48px; }
  .breadcrumb-item,
  .breadcrumb-item-text { max-width: 18ch; }
}
@media (min-width: 1200px) {
  .breadcrumb-item,
  .breadcrumb-item-text { max-width: 28ch; }
}
</style>
