<template>
  <Head title="Gestión de Lecciones" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Lecciones', route: '' }
      ]"
    />
 
 Lesson/index

    <!-- Heading -->
    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="admin-title">
              <i class="bi bi-journal-text me-2"></i> &nbsp; Gestionar Lecciones
            </h4>

            <Link :href="route('admin.lessons.create')" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Nueva Lección
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-filters my-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card mb-4">
              <div class="card-body">
                <div class="row g-3 align-items-center">
                  <div class="col-md-4">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-search"></i></span>
                      <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar lecciones..."
                        v-model="searchQuery"
                      />
                    </div>
                  </div>

                  <div class="col-md-3">
                    <select class="form-select" v-model="activeFilter">
                      <option value="">Estado: Todos</option>
                      <option value="1">Activas</option>
                      <option value="0">Inactivas</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <input
                      type="date"
                      class="form-control"
                      v-model="publishOnFilter"
                      :max="today"
                      placeholder="Fecha de publicación"
                    />
                  </div>

                  <div class="col-md-2 text-end">
                    <span class="badge bg-light text-dark">
                      <i class="bi bi-journal-text me-1"></i>
                      {{ sortedLessons.length }} lección(es)
                    </span>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" @click="resetFilters">
                      Limpiar filtros
                    </button>
                  </div>
                </div>
              </div> <!-- card-body -->
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
                    <th @click="sortBy('id')" style="cursor: pointer;">
                      ID
                      <i :class="sortIcon('id')" class="ms-1" />
                    </th>
                    <th @click="sortBy('order')" style="cursor: pointer;">
                      Orden
                      <i :class="sortIcon('order')" class="ms-1" />
                    </th>
                    <th @click="sortBy('course')" style="cursor: pointer;">
                      Curso
                      <i :class="sortIcon('course')" class="ms-1" />
                    </th>
                    <th @click="sortBy('title')" style="cursor: pointer;">
                      Título
                      <i :class="sortIcon('title')" class="ms-1" />
                    </th>
                    <th @click="sortBy('publish_on')" style="cursor: pointer;">
                      Publica
                      <i :class="sortIcon('publish_on')" class="ms-1" />
                    </th>
                    <th @click="sortBy('active')" style="cursor: pointer;">
                      Activa
                      <i :class="sortIcon('active')" class="ms-1" />
                    </th>
                    <th>Docente</th>
                    <th class="text-center">Extras</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="lesson in paginatedLessons" :key="lesson.id">
                    <td>{{ lesson.id }}</td>
                    <td>{{ lesson.order ?? 0 }}</td>
                    <td>{{ lesson.course?.title ?? 'Sin curso' }}</td>
                    <td>{{ lesson.title }}</td>
                    <td>{{ formatDate(lesson.publish_on) }}</td>
                    <td>
                      <span
                        class="badge"
                        :class="lesson.active ? 'bg-success' : 'bg-secondary'"
                      >
                        {{ lesson.active ? 'Sí' : 'No' }}
                      </span>
                    </td>
                    <td>
                      <span v-if="lesson.teacher">
                        {{ teacherName(lesson.teacher) }}
                      </span>
                      <span v-else class="text-muted">N/A</span>
                    </td>
                   <td class="text-center">
  <div class="d-flex gap-2 justify-content-center flex-wrap">
    <!-- Videos -->
    <span
      class="badge"
      :class="countVideos(lesson) > 0 ? 'bg-primary' : 'bg-light text-dark'"
      title="Videos asignados a la lección"
    >
      <i class="bi bi-film me-1"></i>
      {{ countVideos(lesson) }}
    </span>

    <!-- Evaluaciones -->
    <span
      class="badge"
      :class="countEvaluations(lesson) > 0 ? 'bg-info text-dark' : 'bg-light text-dark'"
      title="Evaluaciones asignadas a la lección"
    >
      <i class="bi bi-ui-checks-grid me-1"></i>
      {{ countEvaluations(lesson) }}
    </span>

    <!-- Materiales (opcional, si lo manejas igual) -->
    <span
      class="badge"
      :class="countMaterials(lesson) > 0 ? 'bg-warning text-dark' : 'bg-light text-dark'"
      title="Materiales de apoyo"
    >
      <i class="bi bi-paperclip me-1"></i>
      {{ countMaterials(lesson) }}
    </span>
  </div>
</td>

                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link
                          :href="route('admin.lessons.edit', lesson.id)"
                          class="btn btn-outline-warning"
                          title="Editar"
                        >
                          <i class="bi bi-pencil-fill"></i>
                        </Link>
                        <button
                          class="btn btn-outline-danger"
                          @click="prepareDelete(lesson.id)"
                          :disabled="isDeleting"
                          title="Eliminar"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="sortedLessons.length === 0">
                    <td colspan="9" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No se encontraron lecciones
                    </td>
                  </tr>
                </tbody>
              </table>
            </div> <!-- table-responsive -->
          </div>
        </div>
      </div>
    </section>

    <!-- Paginación -->
    <section class="section-pagination my-2">
      <div class="container-fluid">
        <div class="d-flex justify-content-center my-4">
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }" @click="changePage(currentPage - 1)">
                <a class="page-link" href="#">Anterior</a>
              </li>
              <li
                v-for="page in totalPages"
                :key="page"
                class="page-item"
                :class="{ active: currentPage === page }"
                @click="changePage(page)"
              >
                <a class="page-link" href="#">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }" @click="changePage(currentPage + 1)">
                <a class="page-link" href="#">Siguiente</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </section>

    <!-- Modales / Toasts -->
    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteLesson"
      title="¿Deseas eliminar esta lección?"
      confirm-message="Por favor confirma la eliminación de esta lección"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';

const props = defineProps({
  lessons: Array // [{ id, order, title, publish_on, active, course:{id,title}, teacher:{firstname,lastname}, add_video, add_evaluation, add_materials, videos, evaluations, materials }]
});

const searchQuery = ref('');
const activeFilter = ref('');
const publishOnFilter = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const toast = ref(null);

const sortKey = ref('id');
const sortOrder = ref('asc');

const page = usePage();
const today = new Date().toISOString().slice(0, 10);

onMounted(() => {
  if (page.props.flash?.success) toast.value = { message: page.props.flash.success, type: 'success' };
  if (page.props.flash?.error) toast.value = { message: page.props.flash.error, type: 'danger' };
});

const sortIcon = (key) => {
  if (sortKey.value !== key) return 'bi bi-arrow-down-up';
  return sortOrder.value === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down';
};

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const teacherName = (t) => {
  if (!t) return '';
  const first = t.firstname ?? '';
  const last = t.lastname ?? '';
  return `${first} ${last}`.trim();
};

const formatDate = (d) => {
  if (!d) return '—';
  // Soporta objetos Date, string 'YYYY-MM-DD' o ISO
  const date = typeof d === 'string' ? new Date(d) : d;
  if (Number.isNaN(date.getTime())) return d;
  return date.toISOString().slice(0, 10);
};

/** Cuenta videos desde lesson_videos; fallback a videos si existiera. Por defecto solo activos. */
const countVideos = (lesson, onlyActive = true) => {
  const arr = Array.isArray(lesson.lesson_videos) ? lesson.lesson_videos
            : Array.isArray(lesson.videos) ? lesson.videos
            : [];
  if (onlyActive) {
    // lesson_videos: item.active; videos (fallback) puede no tener active
    return arr.filter(v => typeof v.active === 'boolean' ? v.active : true).length;
  }
  return arr.length;
};

/** Cuenta evaluaciones desde lesson_evaluations; fallback a evaluations si existiera. */
const countEvaluations = (lesson, onlyActive = true) => {
  const arr = Array.isArray(lesson.lesson_evaluations) ? lesson.lesson_evaluations
            : Array.isArray(lesson.evaluations) ? lesson.evaluations
            : [];
  if (onlyActive) {
    // lesson_evaluations: item.active; evaluations (fallback) a veces no trae active
    return arr.filter(e => typeof e.active === 'boolean' ? e.active : true).length;
  }
  return arr.length;
};

/** Si vas a manejar materiales igual que videos/evals. Ajusta a tu estructura real. */
const countMaterials = (lesson, onlyActive = true) => {
  const arr = Array.isArray(lesson.lesson_materials) ? lesson.lesson_materials
            : Array.isArray(lesson.materials) ? lesson.materials
            : [];
  if (onlyActive) {
    return arr.filter(m => typeof m.active === 'boolean' ? m.active : true).length;
  }
  return arr.length;
};

const filteredLessons = computed(() => {
  let data = props.lessons || [];
  // texto
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    data = data.filter(l =>
      (l.title || '').toLowerCase().includes(q) ||
      (l.description_short || '').toLowerCase().includes(q) ||
      (l.course?.title || '').toLowerCase().includes(q) ||
      teacherName(l.teacher).toLowerCase().includes(q)
    );
  }
  // estado
  if (activeFilter.value !== '') {
    const val = activeFilter.value === '1';
    data = data.filter(l => !!l.active === val);
  }
  // fecha publicación exacta (opcional)
  if (publishOnFilter.value) {
    data = data.filter(l => formatDate(l.publish_on) === publishOnFilter.value);
  }
  return data;
});

const sortedLessons = computed(() => {
  const data = [...filteredLessons.value];
  data.sort((a, b) => {
    let aVal, bVal;

    switch (sortKey.value) {
      case 'course':
        aVal = (a.course?.title || '').toLowerCase();
        bVal = (b.course?.title || '').toLowerCase();
        break;
      case 'publish_on':
        aVal = formatDate(a.publish_on);
        bVal = formatDate(b.publish_on);
        break;
      case 'active':
        aVal = a.active ? 1 : 0;
        bVal = b.active ? 1 : 0;
        break;
      case 'order':
        aVal = a.order ?? 0;
        bVal = b.order ?? 0;
        break;
        case 'videos_count':
          aVal = countVideos(a); bVal = countVideos(b); 
        break;
        case 'evals_count':
         aVal = countEvaluations(a); bVal = countEvaluations(b); 
      break;
      default:
        aVal = (a[sortKey.value] ?? '').toString().toLowerCase();
        bVal = (b[sortKey.value] ?? '').toString().toLowerCase();
    }

    if (typeof aVal === 'number' && typeof bVal === 'number') {
      return sortOrder.value === 'asc' ? aVal - bVal : bVal - aVal;
    }
    return sortOrder.value === 'asc'
      ? String(aVal).localeCompare(String(bVal))
      : String(bVal).localeCompare(String(aVal));
  });
  return data;
});

const paginatedLessons = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedLessons.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(sortedLessons.value.length / itemsPerPage.value))
);

const changePage = (pageNum) => {
  if (pageNum >= 1 && pageNum <= totalPages.value) {
    currentPage.value = pageNum;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const resetFilters = () => {
  searchQuery.value = '';
  activeFilter.value = '';
  publishOnFilter.value = '';
};

const prepareDelete = (id) => {
  deletingId.value = id;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingId.value = null;
  isDeleting.value = false;
};

const deleteLesson = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;

  Inertia.delete(route('admin.lessons.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete();
      toast.value = { message: 'Lección eliminada correctamente', type: 'success' };
    },
    onError: () => {
      isDeleting.value = false;
      toast.value = { message: 'Ocurrió un error al eliminar la lección', type: 'danger' };
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};
</script>
