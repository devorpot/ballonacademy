<template>
  <!-- Filtros -->
  <section class="section-filters my-2">
    <div class="container-fluid">
      <CrudFilters
        v-model:searchQuery="searchQuery"
        :count="sortedStudents.length"
        placeholder="Buscar estudiantes..."
        item-label="estudiante(s)"
      />
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
                  <th class="col-id" @click="sortBy('id')" role="button">ID <i :class="sortIcon('id')"></i></th>
                  <th class="col-name" @click="sortBy('name')" role="button">Nombre <i :class="sortIcon('name')"></i></th>
                  <th class="col-email" @click="sortBy('email')" role="button">Email <i :class="sortIcon('email')"></i></th>
                  <th class="col-phone" @click="sortBy('phone')" role="button">Teléfono <i :class="sortIcon('phone')"></i></th>
                  <th class="col-country" @click="sortBy('country')" role="button">País <i :class="sortIcon('country')"></i></th>
                  <th class="col-status" @click="sortBy('active')" role="button">Estado <i :class="sortIcon('active')"></i></th>
                  <th class="text-end pe-4 col-actions">Acciones</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="student in paginatedStudents" :key="student.id">
                  <td class="text-muted">{{ student.id }}</td>

                  <td>
                    <div class="text-truncate" :title="`${student.firstname} ${student.lastname}`">
                      <strong>{{ student.firstname }} {{ student.lastname }}</strong>
                    </div>
                  </td>

                  <td>
                    <div class="text-truncate" :title="student.user.email">
                      {{ student.user.email }}
                    </div>
                  </td>

                  <td>
                    <div class="text-truncate" :title="student.phone || ''">
                      {{ student.phone }}
                    </div>
                  </td>

                  <td>
                    <div class="text-truncate" :title="student.country || ''">
                      {{ student.country }}
                    </div>
                  </td>

                  <td>
                    <span
                      class="badge rounded-pill"
                      :class="student.user.active ? 'bg-success' : 'bg-danger'"
                    >
                      {{ student.user.active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>

                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-info" @click="$emit('view', student)" title="Ver">
                        <i class="bi bi-eye-fill"></i>
                      </button>

                      <Link :href="route('admin.students.edit', student.id)" class="btn btn-outline-warning" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                      </Link>

                      <button
                        class="btn btn-outline-danger"
                        @click="$emit('delete', student.id)"
                        :disabled="isDeleting"
                        title="Eliminar"
                      >
                        <i class="bi bi-trash-fill"></i>
                      </button>

                      <Link
                        :href="route('admin.profiles.edit', student.user.id)"
                        class="btn btn-outline-secondary"
                        title="Datos fiscales"
                      >
                        <i class="bi bi-receipt-cutoff"></i>
                      </Link>

                      <!-- Activar solo si está inactivo -->
                      <button
                        v-if="!student.user.active"
                        class="btn btn-outline-success"
                        @click="$emit('activate', student)"
                        title="Activar usuario"
                      >
                        <i class="bi bi-lightning-charge"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="sortedStudents.length === 0">
                  <td colspan="7" class="text-center py-4 text-muted">
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

  <!-- Paginación -->
  <CrudPagination
    :current-page="currentPage"
    :total-pages="totalPages"
    @change-page="changePage"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'

const props = defineProps({
  students: { type: Array, default: () => [] },
  isDeleting: { type: Boolean, default: false }
})

const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const sortKey = ref('id')
const sortOrder = ref('asc')

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const sortIcon = (key) => {
  if (sortKey.value !== key) return 'bi bi-arrow-down-up'
  return sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down'
}

const filteredStudents = computed(() => {
  if (!searchQuery.value) return props.students
  const q = searchQuery.value.toLowerCase()
  return props.students.filter(s =>
    `${s.firstname} ${s.lastname}`.toLowerCase().includes(q) ||
    (s.user?.email || '').toLowerCase().includes(q) ||
    (s.student_id || '').toString().toLowerCase().includes(q) ||
    (s.phone || '').toLowerCase().includes(q) ||
    (s.country || '').toLowerCase().includes(q)
  )
})

const sortedStudents = computed(() => {
  const data = [...filteredStudents.value]
  data.sort((a, b) => {
    let aVal, bVal
    switch (sortKey.value) {
      case 'name':
        aVal = `${a.firstname} ${a.lastname}`.toLowerCase()
        bVal = `${b.firstname} ${b.lastname}`.toLowerCase()
        break
      case 'email':
        aVal = (a.user?.email || '').toLowerCase()
        bVal = (b.user?.email || '').toLowerCase()
        break
      case 'active':
        aVal = a.user?.active ? 1 : 0
        bVal = b.user?.active ? 1 : 0
        break
      default:
        aVal = (a[sortKey.value] ?? '').toString().toLowerCase()
        bVal = (b[sortKey.value] ?? '').toString().toLowerCase()
    }
    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
    return 0
  })
  return data
})

const paginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return sortedStudents.value.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() => Math.ceil(sortedStudents.value.length / itemsPerPage.value))

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
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
