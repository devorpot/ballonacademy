<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';

const props = defineProps({
  evaluations: Object
});

const searchQuery = ref('');

const filteredEvaluations = computed(() => {
  if (!searchQuery.value) return props.evaluations.data;
  const query = searchQuery.value.toLowerCase();
  return props.evaluations.data.filter(eva =>
    (eva.user?.name || '').toLowerCase().includes(query) ||
    (eva.course?.title || '').toLowerCase().includes(query)
  );
});
</script>

<template>
  <Head title="Gestión de Evaluaciones de Usuarios" />
  <AdminLayout>

    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
       { label: 'Evaluaciones', route: 'admin.evaluations.index' },
        { label: 'Usuarios', route: '' }
      ]"
    />

 
      <CrudFilters
        v-model:searchQuery="searchQuery"
        :count="filteredEvaluations.length"
        placeholder="Buscar evaluaciones por usuario o curso..."
        item-label="evaluación(es)"
      />
          <section class="section-data my-2">
            <div class="container-fluid py-2">
              <div class="row">
                <div class="col-12">
                  <div class="card shadow">
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                              <th class="px-4 py-2">ID</th>
                              <th class="px-4 py-2">Usuario</th>
                              <th class="px-4 py-2">Curso</th>
                              <th class="px-4 py-2">Estado</th>
                              <th class="px-4 py-2">Fecha</th>
                              <th class="px-4 py-2 text-end"></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="eva in filteredEvaluations" :key="eva.id">
                              <td class="px-4 py-2">{{ eva.id }}</td>
                              <td class="px-4 py-2">{{ eva.user?.name || '-' }}</td>
                              <td class="px-4 py-2">{{ eva.course?.title || '-' }}</td>
                              <td class="px-4 py-2">
                      
                                <span v-if="eva.status === 999" class="text-success fw-semibold">Aprobada</span>
                                <span v-else-if="eva.status === 333" class="text-danger fw-semibold">No aprobada</span>
                                <span v-else-if="eva.status === 222" class="text-primary fw-semibold">En revisión</span>
                                <span v-else>Enviada</span>
                              </td>

                              <td class="px-4 py-2">{{ eva.created_at ? new Date(eva.created_at).toLocaleString() : '-' }}</td>
                              <td class="px-4 py-2 text-end">
                                <Link :href="route('admin.evaluation-users.show', eva.id)" class="btn btn-sm btn-primary">
                                  <i class="bi bi-eye"></i> Ver
                                </Link>
                            
                              </td>
                            </tr>
                            <tr v-if="!filteredEvaluations.length">
                              <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-exclamation-circle me-2"></i>No se encontraron evaluaciones
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <!-- Paginación original de Laravel -->
                  <div class="d-flex justify-content-end mt-4">
                    <Link
                      v-if="props.evaluations.prev_page_url"
                      :href="props.evaluations.prev_page_url"
                      class="btn btn-outline-secondary btn-sm me-2"
                    >Anterior</Link>
                    <Link
                      v-if="props.evaluations.next_page_url"
                      :href="props.evaluations.next_page_url"
                      class="btn btn-outline-secondary btn-sm"
                    >Siguiente</Link>
                  </div>
                </div>
              </div>
            </div>
          </section>
  </AdminLayout>
</template>

<style scoped>
.table td, .table th {
  vertical-align: middle;
}
</style>
