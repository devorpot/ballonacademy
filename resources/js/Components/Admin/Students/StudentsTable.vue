<template>
  <!-- Filtros -->
  <section class="section-filters my-2">
    <div class="container-fluid">
      <!-- Si tu CrudFilters soporta v-model y slot, úsalo; si no, puedes dejar este toolbar simple -->
      <div class="card">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
          <!-- Búsqueda -->
          <div class="flex-grow-1">
            <CrudFilters
              v-model:searchQuery="localSearch"
              :count="students.meta?.total ?? 0"
              placeholder="Buscar estudiantes..."
              item-label="estudiante(s)"
            />
          </div>

          <!-- País -->
          <div class="ms-auto">
            <select class="form-select" style="min-width: 180px" v-model="localCountry">
              <option :value="null">Todos los países</option>
              <option v-for="c in (filters.countries || [])" :key="c" :value="c">
                {{ c }}
              </option>
            </select>
          </div>

          <!-- Activo -->
          <div>
            <select class="form-select" style="min-width: 160px" v-model="localActive">
              <option :value="null">Todos</option>
              <option value="1">Activos</option>
              <option value="0">Inactivos</option>
            </select>
          </div>

          <!-- PerPage -->
          <div>
            <select class="form-select" style="min-width: 120px" v-model.number="localPerPage">
              <option :value="10">10</option>
              <option :value="15">15</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Tabla -->
<section class="section-data my-2">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="col-id" role="button" @click="emitSort('users.id')">
                  ID <i :class="sortIcon('users.id')"></i>
                </th>
                <th class="col-image">Imagen</th>
                <th class="col-name" role="button" @click="emitSort('users.name')">
                  Nombre <i :class="sortIcon('users.name')"></i>
                </th>
                <th class="col-email" role="button" @click="emitSort('users.email')">
                  Email <i :class="sortIcon('users.email')"></i>
                </th>
                <th class="col-phone" role="button" @click="emitSort('profiles.whatsapp')">
                  Teléfono <i :class="sortIcon('profiles.whatsapp')"></i>
                </th>
                <th class="col-country" role="button" @click="emitSort('profiles.country')">
                  País <i :class="sortIcon('profiles.country')"></i>
                </th>
                <th class="col-status" role="button" @click="emitSort('users.active')">
                  Estado <i :class="sortIcon('users.active')"></i>
                </th>
                <th class="text-end pe-4 col-actions">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="student in rows" :key="student.id">
                <td class="text-muted">{{ student.id }}</td>

                <!-- Nueva columna con la imagen -->
                <td>
                  <img
                    v-if="student.profile?.profile_image"
                    :src="`/storage/${student.profile.profile_image}`"
                    alt="Foto"
                    class="rounded-circle"
                    style="width:40px; height:40px; object-fit:cover;"
                  />
                  <span v-else class="text-muted small">Sin foto</span>
                </td>

                <td>
                  <div
                    class="text-truncate"
                    :title="displayFullName(student)"
                  >
                    <strong>{{ displayFullName(student) || student.name }}</strong>
                  </div>
                </td>

                <td>
                  <div class="text-truncate" :title="student.email">
                    {{ student.email }}
                  </div>
                </td>

                <td>
                  <div class="text-truncate" :title="student.profile?.whatsapp || ''">
                    {{ student.profile?.whatsapp }}
                  </div>
                </td>

                <td>
                  <div class="text-truncate" :title="student.profile?.country || ''">
                    {{ student.profile?.country }}
                  </div>
                </td>

                <td>
                  <span class="badge rounded-pill" :class="student.active ? 'bg-success' : 'bg-danger'">
                    {{ student.active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>

                <td class="text-end pe-4">
                  <div class="btn-group btn-group-sm">
                    <Link
                      :href="route('admin.students.show', { user: student.id })"
                      class="btn btn-outline-success"
                      title="Mostrar"
                    >
                      <i class="bi bi-eye-fill"></i>
                    </Link>

                    <Link
                      :href="route('admin.students.edit', { user: student.id })"
                      class="btn btn-outline-primary"
                      title="Editar"
                    >
                      <i class="bi bi-pencil-fill"></i>
                    </Link>

                    <button
                      class="btn btn-outline-danger"
                      @click="emit('delete', student.id)"
                      :disabled="isDeleting"
                      title="Eliminar"
                    >
                      <i class="bi bi-trash-fill"></i>
                    </button>

                    <button
                      v-if="!student.active"
                      class="btn btn-outline-success"
                      @click="emit('activate', student)"
                      title="Activar usuario"
                    >
                      <i class="bi bi-lightning-charge"></i>
                    </button>

                    <Link
                      :href="route('admin.evaluation-users.byUser', { user: student.id })"
                      class="btn btn-outline-info btn-sm"
                    >
                      <i class="bi bi-journal-text me-1"></i> Evaluaciones
                    </Link>

                  </div>
                </td>
              </tr>

              <tr v-if="rows.length === 0">
                <td colspan="8" class="text-center py-4 text-muted">
                  <i class="bi bi-exclamation-circle me-2"></i>No se encontraron estudiantes
                </td>
              </tr>
            </tbody>
          </table>
        </div><!-- /.table-responsive -->
      </div>
    </div>
  </div>
</section>


  <!-- Paginación (servidor) -->
  <CrudPagination
    :current-page="students.meta?.current_page || 1"
    :total-pages="students.meta?.last_page || 1"
    @change-page="page => emit('page', page)"
  />
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'

const props = defineProps({
  // Objeto paginado del backend: { data, meta, links }
  students: { type: Object, required: true },
  // Filtros controlados por el padre (estado fuente de la verdad)
  filters: {
    type: Object,
    default: () => ({
      q: '',
      country: null,
      countries: [],
      active: null,
      sortBy: 'users.id',
      sortDir: 'desc',
      perPage: 15,
      page: 1
    })
  },
  isDeleting: { type: Boolean, default: false }
})

const emit = defineEmits([
  'view', 'delete', 'activate',
  'update:search', 'update:country', 'update:active', 'update:perPage',
  'sort', 'page'
])

/**
 * Estado local "controlado" para inputs, rehidratado desde props.filters.
 * Cada cambio emite al padre; el padre refresca y reinyecta props.filters.
 */
const localSearch = ref(props.filters.q ?? '')
const localCountry = ref(props.filters.country ?? null)
const localActive = ref(props.filters.active ?? null)
const localPerPage = ref(Number(props.filters.perPage ?? 15))

watch(() => props.filters.q, v => { if (v !== localSearch.value) localSearch.value = v ?? '' })
watch(() => props.filters.country, v => { if (v !== localCountry.value) localCountry.value = v ?? null })
watch(() => props.filters.active, v => { if (v !== localActive.value) localActive.value = v ?? null })
watch(() => props.filters.perPage, v => { const n = Number(v ?? 15); if (n !== localPerPage.value) localPerPage.value = n })

// Debounce de búsqueda
let t = null
watch(localSearch, (val) => {
  clearTimeout(t)
  t = setTimeout(() => emit('update:search', val || ''), 300)
})

watch(localCountry, (val) => emit('update:country', val ?? null))
watch(localActive, (val) => emit('update:active', val ?? null))
watch(localPerPage, (val) => emit('update:perPage', Number(val) || 15))

// Filas de la tabla (del servidor)
const rows = computed(() => Array.isArray(props.students?.data) ? props.students.data : [])

// Utilidades UI
function displayFullName(s) {
  const fn = s.profile?.firstname || ''
  const ln = s.profile?.lastname || ''
  return `${fn} ${ln}`.trim()
}

function sortIcon(colKey) {
  if (props.filters.sortBy !== colKey) return 'bi bi-arrow-down-up'
  return props.filters.sortDir === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down'
}

function emitSort(colKey) {
  const nextDir =
    props.filters.sortBy === colKey
      ? (props.filters.sortDir === 'asc' ? 'desc' : 'asc')
      : 'asc'
  emit('sort', { sortBy: colKey, sortDir: nextDir })
}
</script>

<style scoped>
.col-id { width: 80px; }
.col-name { min-width: 200px; }
.col-email { min-width: 240px; }
.col-phone { min-width: 160px; }
.col-country { min-width: 140px; }
.col-status { width: 120px; }
.col-actions { min-width: 260px; }

.text-truncate {
  max-width: 100%;
  display: inline-block;
  overflow: hidden;
  text-overflow: ellipsis;
  vertical-align: bottom;
  white-space: nowrap;
}
</style>
