<script setup>
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import StudentLayout from '@/Layouts/StudentLayout.vue';
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';
import CourseCard from '@/Components/Dashboard/Courses/CourseCard.vue'

const props = defineProps({
  evaluations: Array,
  filters: Object,
  course: Object,
  videos: Array,
});

const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const sortKey = ref('id');
const sortOrder = ref('asc');
const toast = ref(null);
const page = usePage();
const retryingId = ref(null);

onMounted(() => {
  if (page.props.flash.success) {
    toast.value = { message: page.props.flash.success, type: 'success' };
  }
  if (page.props.flash.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' };
  }
});

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const filteredEvaluations = computed(() => {
  if (!searchQuery.value) return props.evaluations;
  const query = searchQuery.value.toLowerCase();
  return props.evaluations.filter(e =>
    (e.eva_comments || '').toLowerCase().includes(query)
  );
});

const sortedEvaluations = computed(() => {
  let data = [...filteredEvaluations.value];
  data.sort((a, b) => {
    let aVal = a[sortKey.value], bVal = b[sortKey.value];
    return sortOrder.value === 'asc'
      ? String(aVal).localeCompare(String(bVal))
      : String(bVal).localeCompare(String(aVal));
  });
  return data;
});

const paginatedEvaluations = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedEvaluations.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => Math.ceil(sortedEvaluations.value.length / itemsPerPage.value));

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Eliminar intento anterior y redirigir
function retryEvaluation(eva) {
  if (!confirm('¿Estás seguro que deseas volver a realizar el test? Tu envío anterior será eliminado de forma permanente.')) {
    return;
  }
  retryingId.value = eva.id;
  router.delete(route('dashboard.evaluation-users.destroy-by-evaluation'), {  // <--- corrige aquí
    data: {
      evaluation_id: eva.id,
      course_id: eva.course_id
    },
    onSuccess: () => {
      retryingId.value = null;
    },
    onError: () => {
      alert('Ocurrió un error al intentar eliminar tu evaluación anterior.');
      retryingId.value = null;
    }
  });
}

</script>

<template>
  <Head title="Evaluaciones" />
  <StudentLayout>
     <SectionHeader title="Cursos Inscritos" />
     <Breadcrumbs username="estudiante" :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Cursos', route: 'dashboard.index' },
        { label: course.title, route: '' },
        { label: 'Evaluaciones', route: '' },
      ]" />
    <CourseCard :course="course" :videos="videos" />

    <CrudFilters v-model:searchQuery="searchQuery" :count="sortedEvaluations.length" placeholder="Buscar por comentario..." item-label="evaluación(es)" />



 
    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="row g-3">
          <div
            v-for="eva in paginatedEvaluations"
            :key="eva.id"
            class="col-12 col-md-6 col-lg-4"
          >
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-file-earmark-text me-2 fs-3 text-primary"></i>
                    <h5 class="card-title mb-0 flex-fill">
                      {{ eva.title }}
                    </h5>
                    <span
                      class="badge rounded-pill"
                      :class="eva.last_evaluation_user && eva.last_evaluation_user.status === 999
                        ? 'bg-success'
                        : eva.last_evaluation_user && eva.last_evaluation_user.status === 333
                          ? 'bg-danger'
                          : eva.last_evaluation_user && eva.last_evaluation_user.status === 222
                            ? 'bg-warning text-dark'
                            : eva.last_evaluation_user && eva.last_evaluation_user.status === 111
                              ? 'bg-info text-dark'
                              : 'bg-secondary'"
                    >
                      {{
                        eva.last_evaluation_user && eva.last_evaluation_user.status === 999
                          ? 'Aprobada'
                          : eva.last_evaluation_user && eva.last_evaluation_user.status === 333
                            ? 'No aprobada'
                            : eva.last_evaluation_user && eva.last_evaluation_user.status === 222
                              ? 'En revisión'
                              : eva.last_evaluation_user && eva.last_evaluation_user.status === 111
                                ? 'Enviada'
                                : 'No realizada'
                      }}
                    </span>
                  </div>
                  <div class="mb-2 small text-muted">
                    <i class="bi bi-calendar-event me-1"></i>
                    Publicada: {{ eva.eva_send_date }}
                  </div>
                  <div class="mb-3">
                    <strong>Comentarios:</strong>
                    <div class="text-body-secondary">
                      {{ eva.eva_comments || 'Sin comentarios' }}
                    </div>
                  </div>
                </div>
                <div class="mt-auto">
                  <!-- Ya realizada y aprobada -->
                  <template v-if="eva.user_has_evaluated && eva.last_evaluation_user && eva.last_evaluation_user.status === 999">
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-2 py-2 px-3">
                      <i class="bi bi-award-fill fs-4"></i>
                      <span>¡Felicidades! Has aprobado esta evaluación.</span>
                    </div>
                    <Link
                      class="btn btn-primary btn-sm px-4 d-inline-flex align-items-center gap-2"
                      :href="route('dashboard.courses.evaluations.evaluation.preview', { course: eva.course_id, evaluation: eva.id })"
                    >
                      <i class="bi bi-eye"></i> Ver resultados
                    </Link>
                  </template>
                  <!-- Ya realizada pero no aprobada, en revisión o enviada -->
                  <template v-else-if="eva.user_has_evaluated && eva.last_evaluation_user">
                    <div
                      class="alert alert-warning d-flex align-items-center gap-2 mb-2 py-2 px-3"
                      v-if="eva.last_evaluation_user.status === 222"
                    >
                      <i class="bi bi-hourglass-split fs-4"></i>
                      <span>
                        Tu evaluación está en revisión. Puedes volver a hacer el test si lo deseas, pero tu envío anterior será eliminado.
                      </span>
                    </div>
                    <div
                      class="alert alert-info d-flex align-items-center gap-2 mb-2 py-2 px-3"
                      v-else-if="eva.last_evaluation_user.status === 111"
                    >
                      <i class="bi bi-send-check-fill fs-4"></i>
                      <span>
                        Tu evaluación fue enviada. Puedes volver a hacer el test, pero tu envío anterior será eliminado.
                      </span>
                    </div>
                    <div
                      class="alert alert-danger d-flex align-items-center gap-2 mb-2 py-2 px-3"
                      v-else-if="eva.last_evaluation_user.status === 333"
                    >
                      <i class="bi bi-x-circle-fill fs-4"></i>
                      <span>
                        No aprobaste esta evaluación. Puedes volver a hacer el test, pero tu envío anterior será eliminado.
                      </span>
                    </div>
                    <button
                      class="btn btn-warning btn-sm fw-bold text-white px-4 d-inline-flex align-items-center gap-2"
                      @click="retryEvaluation(eva)"
                      :disabled="retryingId === eva.id"
                    >
                      <span v-if="retryingId === eva.id" class="spinner-border spinner-border-sm me-2"></span>
                      <i class="bi bi-arrow-repeat"></i>
                      Volver a Realizar Evaluación
                    </button>
                  </template>
                  <!-- Nunca la ha realizado -->
                  <template v-else>
                    <Link
                      class="btn btn-success btn-sm fw-bold px-4 d-inline-flex align-items-center gap-2"
                      :href="route('dashboard.courses.evaluations.evaluation.preview', { course: eva.course_id, evaluation: eva.id })"
                    >
                      <i class="bi bi-pencil-square"></i> Realizar Evaluación
                    </Link>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <!-- Si no hay evaluaciones -->
          <div v-if="sortedEvaluations.length === 0" class="col-12">
            <div class="alert alert-secondary text-center py-4">
              <i class="bi bi-exclamation-circle me-2"></i>
              No se encontraron evaluaciones
            </div>
          </div>
        </div>
      </div>
    </section>

    <CrudPagination :current-page="currentPage" :total-pages="totalPages" @change-page="changePage" />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </StudentLayout>
</template>
