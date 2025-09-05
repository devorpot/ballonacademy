<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

// Si ya usas estos componentes en tu sistema, descomenta las importaciones:
// import AdminLayout from '@/Layouts/AdminLayout.vue'
// import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
// import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'

const props = defineProps({
  submissions: { type: Object, required: true }, // paginate()
  course:      { type: Object, required: true },
  student:     { type: Object, required: true },
  filters:     { type: Object, required: true },
  statusOptions: { type: Array, default: () => [] }
})

const local = ref({
  q: props.filters.q || '',
  status: props.filters.status || '',
  per_page: props.filters.per_page || 20,
  sort: props.filters.sort || 'created_at',
  dir: props.filters.dir || 'desc'
})

const sortIcon = (key) => {
  if (local.value.sort !== key) return 'bi bi-arrow-down-up text-muted'
  return local.value.dir === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'
}

const emitSort = (key) => {
  local.value.sort = key
  local.value.dir  = local.value.dir === 'asc' ? 'desc' : 'asc'
  applyFilters()
}

const applyFilters = () => {
  router.get(
    route('admin.evaluation-users.byUserAndCourse', { course: props.course.id, user: props.student.id }),
    {
      q: local.value.q || undefined,
      status: local.value.status || undefined,
      per_page: local.value.per_page,
      sort: local.value.sort,
      dir: local.value.dir
    },
    { preserveScroll: true, preserveState: true, replace: true }
  )
}

const clearFilters = () => {
  local.value.q = ''
  local.value.status = ''
  local.value.per_page = 20
  local.value.sort = 'created_at'
  local.value.dir = 'desc'
  applyFilters()
}

const rows = computed(() => props.submissions.data || [])
</script>

<template>
  <Head :title="`Envíos de ${student.name} · ${course.title}`" />
  <!-- <AdminLayout> -->
    <div class="container-fluid py-4">
      <div class="row mb-3">
        <div class="col">
          <h1 class="h3 mb-0">
            <i class="bi bi-person-check me-2"></i>
            Envíos de {{ student.name }} — <small class="text-muted">{{ course.title }}</small>
          </h1>
        </div>
        <div class="col-auto">
          <Link :href="route('admin.evaluation-users.index', { course: course.id })" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Volver a envíos del curso
          </Link>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-12 col-md-4">
              <input
                v-model="local.q"
                type="search"
                class="form-control"
                placeholder="Buscar por comentario, evaluación, lección o video"
                @keyup.enter="applyFilters"
              />
            </div>
            <div class="col-6 col-md-3">
              <select class="form-select" v-model="local.status">
                <option value="">Todos los estatus</option>
                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <select class="form-select" v-model.number="local.per_page">
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
            </div>
            <div class="col-12 col-md-3 text-md-end">
              <div class="btn-group">
                <button class="btn btn-primary" @click="applyFilters">
                  <i class="bi bi-funnel me-1"></i> Aplicar
                </button>
                <button class="btn btn-outline-secondary" @click="clearFilters">
                  Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th role="button" @click="emitSort('id')">
                    ID <i :class="sortIcon('id')"></i>
                  </th>
                  <th>Evaluación</th>
                  <th>Lección</th>
                  <th>Video</th>
                  <th role="button" @click="emitSort('status')">
                    Estatus <i :class="sortIcon('status')"></i>
                  </th>
                  <th role="button" @click="emitSort('created_at')">
                    Enviado <i :class="sortIcon('created_at')"></i>
                  </th>
                  <th>Revisó</th>
                  <th class="text-end pe-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in rows" :key="item.id">
                  <td class="text-muted">{{ item.id }}</td>
                  <td>
                    <div class="text-truncate" :title="item.evaluation?.title || ''">
                      <strong>{{ item.evaluation?.title }}</strong>
                    </div>
                  </td>
                  <td>
                    <div class="text-truncate" :title="item.lesson?.title || ''">
                      {{ item.lesson?.title || '-' }}
                    </div>
                  </td>
                  <td>
                    <div class="text-truncate" :title="item.video?.title || ''">
                      {{ item.video?.title || '-' }}
                    </div>
                  </td>
                  <td>
                    <span class="badge "
                          :class="{
                            'bg-secondary': item.status === 111,  // pendiente
                            'bg-warning':  item.status === 222,  // en revisión
                            'bg-success':  item.status === 333,  // aprobado
                            'bg-danger':   item.status === 999   // rechazado
                          }">
                      {{ item.status_label }}
                    </span>
                  </td>
                  <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                  <td>{{ item.approved_user?.name || '-' }}</td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <Link :href="route('admin.evaluation-users.show', item.id)"
                            class="btn btn-outline-success"
                            title="Ver detalle">
                        <i class="bi bi-eye-fill"></i>
                      </Link>
                    </div>
                  </td>
                </tr>
                <tr v-if="rows.length === 0">
                  <td colspan="8" class="text-center py-4 text-muted">
                    <i class="bi bi-exclamation-circle me-2"></i>No hay envíos para este usuario en el curso
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <nav class="p-3 border-top d-flex justify-content-end">
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: !submissions.links.prev }">
                <Link class="page-link" :href="submissions.links.prev || '#'" preserve-scroll>
                  Anterior
                </Link>
              </li>
              <li class="page-item" :class="{ disabled: !submissions.links.next }">
                <Link class="page-link" :href="submissions.links.next || '#'" preserve-scroll>
                  Siguiente
                </Link>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  <!-- </AdminLayout> -->
</template>
