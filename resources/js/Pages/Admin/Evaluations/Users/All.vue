<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, reactive, computed, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import Title from '@/Components/Admin/Ui/Title.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  submissions: { type: Object, required: true }, // paginador Laravel
  filters: { type: Object, default: () => ({ user:null, course:null, status:null, q:'', per_page:20, sort:'created_at', dir:'desc' }) },
  userOptions:   { type: Array, default: () => [] },
  courseOptions: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
})

const toast = ref(null)

const state = reactive({
  user: props.filters.user ?? null,
  course: props.filters.course ?? null,
  status: props.filters.status ?? null,
  q: props.filters.q ?? '',
  perPage: Number(props.filters.per_page ?? 20),
  sort: props.filters.sort ?? 'created_at',
  dir: props.filters.dir ?? 'desc',
  page: Number(props.submissions?.meta?.current_page ?? props.submissions?.current_page ?? 1),
})

// Usa SIEMPRE lo que viene en props
const list = computed(() => props.submissions)

const pager = computed(() => {
  const c = list.value || {}
  const m = c.meta || {}
  return {
    current_page: Number(m.current_page ?? c.current_page ?? 1),
    last_page:    Number(m.last_page    ?? c.last_page    ?? 1),
  }
})
const currentPage = computed(() => pager.value.current_page)

function formatDate(dt) {
  if (!dt) return '—'
  try { return new Date(dt).toLocaleString() } catch { return dt }
}

function statusBadgeClass(val) {
  switch (String(val)) {
    case '111': return 'badge text-bg-warning'
    case '222': return 'badge text-bg-info'
    case '333': return 'badge text-bg-success'
    case '999': return 'badge text-bg-danger'
    default:    return 'badge text-bg-secondary'
  }
}
function statusLabel(val) {
  const opt = props.statusOptions.find(o => String(o.value) === String(val))
  return opt ? opt.label : '—'
}

// Refresca con Inertia (no axios)
function reloadWithInertia(extra = {}) {
  Inertia.get(
    route('admin.evaluation-users.all'),
    {
      user: state.user || undefined,
      course: state.course || undefined,
      status: state.status || undefined,
      q: state.q || undefined,
      per_page: state.perPage,
      sort: state.sort,
      dir: state.dir,
      page: state.page,
      ...extra
    },
    { preserveState: true, preserveScroll: true, replace: true }
  )
}

let qTimer = null
watch(() => state.q, () => {
  clearTimeout(qTimer)
  qTimer = setTimeout(() => { state.page = 1; reloadWithInertia() }, 350)
})
watch(() => state.user,   () => { state.page = 1; reloadWithInertia() })
watch(() => state.course, () => { state.page = 1; reloadWithInertia() })
watch(() => state.status, () => { state.page = 1; reloadWithInertia() })
watch(() => state.perPage,() => { state.page = 1; reloadWithInertia() })
watch(() => state.sort,   () => { state.page = 1; reloadWithInertia() })
watch(() => state.dir,    () => { state.page = 1; reloadWithInertia() })

function onPageChange(n) {
  state.page = Math.max(1, Math.min(n, pager.value.last_page || 1))
  reloadWithInertia()
}

function toggleSort(key) {
  if (state.sort === key) {
    state.dir = state.dir === 'asc' ? 'desc' : 'asc'
  } else {
    state.sort = key
    state.dir = 'asc'
  }
}
</script>


<template>
  <Head title="Envíos de Evaluaciones" />

  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Evaluaciones', route: '' },
        { label: 'Envíos de usuarios', route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center gap-2">
            <Title :title="'Envíos de evaluaciones (todos)'" />
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-data py-3">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-md-2">
                <label class="form-label">Usuario</label>
                <select v-model="state.user" class="form-select">
                  <option :value="null">Todos</option>
                  <option v-for="u in userOptions" :key="u.value" :value="u.value">{{ u.label }}</option>
                </select>
              </div>

              <div class="col-12 col-md-2">
                <label class="form-label">Curso</label>
                <select v-model="state.course" class="form-select">
                  <option :value="null">Todos</option>
                  <option v-for="c in courseOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
                </select>
              </div>

              <div class="col-12 col-md-2">
                <label class="form-label">Estatus</label>
                <select v-model="state.status" class="form-select">
                  <option :value="null">Todos</option>
                  <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">Buscar</label>
                <input v-model="state.q" type="text" class="form-control" placeholder="Alumno, curso, evaluación, comentario…" />
              </div>

              <div class="col-6 col-md-1 ms-auto">
                <label class="form-label">Por página</label>
                <select v-model.number="state.perPage" class="form-select">
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
              </div>

              

            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabla -->
<section class="section-panel">
    <div class="container-fluid">
      <div class="card shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:80px" @click="toggleSort('id')" role="button">#</th>
                <th style="min-width:220px">Usuario</th>
                <th>Curso</th>
                <th>Evaluación</th>
                <th>Lección / Video</th>
                <th style="min-width:120px" @click="toggleSort('status')" role="button">Estatus</th>
                <th style="min-width:160px" @click="toggleSort('created_at')" role="button">Fecha</th>
                <th style="min-width:140px">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="!list?.data?.length">
                <td colspan="8" class="text-center py-4 text-muted">Sin envíos registrados.</td>
              </tr>

              <tr v-for="row in list.data" :key="row.id">
                <td>{{ row.id }}</td>

                <td>
                  <div class="fw-semibold">{{ row.user?.name || '—' }}</div>
                  <small class="text-muted">{{ row.user?.email || `ID: ${row.user_id ?? '—'}` }}</small>
                </td>

                <td>
                  <div class="text-truncate" style="max-width: 240px;">
                    {{ row.course?.title || (row.course_id ? `#${row.course_id}` : '—') }}
                  </div>
                </td>

                <td>
                  <div class="text-truncate" style="max-width: 260px;">
                    {{ row.evaluation?.title || (row.evaluation_id ? `#${row.evaluation_id}` : '—') }}
                  </div>
                </td>

                <td class="small">
                  <div v-if="row.evaluation?.lesson || row.evaluation?.video || row.video">
                    <div v-if="row.evaluation?.lesson">Lección: {{ row.evaluation.lesson.title }}</div>
                    <div v-if="row.evaluation?.video">Video: {{ row.evaluation.video.title }}</div>
                    <div v-else-if="row.video">Video: {{ row.video.title }}</div>
                  </div>
                  <div v-else class="text-muted">—</div>
                </td>

                <td><span :class="statusBadgeClass(row.status)">{{ statusLabel(row.status) }}</span></td>
                <td>{{ formatDate(row.created_at) }}</td>

                <td class="text-nowrap">
          
                  <Link class="btn btn-sm btn-primary " :href="route('admin.evaluation-users.show', { id: row.id })">
                    <i class="bi bi-box-arrow-up-right"></i> Detalles
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card-body border-top">
          <nav v-if="list && (list.meta || typeof list.current_page !== 'undefined')">
            <ul class="pagination mb-0 justify-content-end">
              <li class="page-item" :class="{ disabled: currentPage <= 1 }">
                <button class="page-link" @click="onPageChange(currentPage - 1)" :disabled="currentPage <= 1">Anterior</button>
              </li>
              <li class="page-item disabled">
                <span class="page-link">Página {{ pager.current_page }} de {{ pager.last_page }}</span>
              </li>
              <li class="page-item" :class="{ disabled: currentPage >= pager.last_page }">
                <button class="page-link" @click="onPageChange(currentPage + 1)" :disabled="currentPage >= pager.last_page">Siguiente</button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </section>

    <!-- Modal Detalles (sin 'type') -->
    <div v-if="showDetails" class="modal fade show" tabindex="-1" style="display:block;" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalles del envío #{{ selected?.id }}</h5>
            <button type="button" class="btn-close" @click="closeDetails"></button>
          </div>
          <div class="modal-body">
            <dl class="row mb-0">
              <dt class="col-sm-3">Fecha</dt>
              <dd class="col-sm-9">{{ formatDate(selected?.created_at) }}</dd>

              <dt class="col-sm-3">Alumno</dt>
              <dd class="col-sm-9">
                <div class="fw-semibold">{{ selected?.user?.name || '—' }}</div>
                <div class="text-muted small">{{ selected?.user?.email || (selected?.user_id ? `ID: ${selected.user_id}` : '—') }}</div>
              </dd>

              <dt class="col-sm-3">Curso</dt>
              <dd class="col-sm-9">
                {{ selected?.course?.title || (selected?.course_id ? `#${selected.course_id}` : '—') }}
              </dd>

              <dt class="col-sm-3">Evaluación</dt>
              <dd class="col-sm-9">
                {{ selected?.evaluation?.title || (selected?.evaluation_id ? `#${selected.evaluation_id}` : '—') }}
              </dd>

              <dt class="col-sm-3">Lección</dt>
              <dd class="col-sm-9">
                {{ selected?.evaluation?.lesson?.title || '—' }}
              </dd>

              <dt class="col-sm-3">Video</dt>
              <dd class="col-sm-9">
                {{ selected?.evaluation?.video?.title || '—' }}
              </dd>

              <dt class="col-sm-3">Estatus</dt>
              <dd class="col-sm-9">
                <span :class="statusBadgeClass(selected?.status)">{{ statusLabel(selected?.status) }}</span>
              </dd>

              <!-- Importante: NO mostrar 'type' -->
            </dl>
          </div>
          <div class="modal-footer">
            <Link class="btn btn-outline-secondary" :href="route('admin.evaluation-users.show', { id: selected?.id })">
              Abrir detalle
            </Link>
            <button class="btn btn-primary" @click="closeDetails">Cerrar</button>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
    </div>

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
</style>
