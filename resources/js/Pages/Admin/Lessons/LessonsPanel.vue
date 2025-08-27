<template>
  <Head :title="`Lecciones del Curso: ${course.title}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Cursos', route: 'admin.courses.index' },
        { label: `Lecciones - ${course.title}`, route: '' }
      ]"
    />

    <!-- Heading -->
    <section class="section-heading">
      <div class="container-fluid d-flex justify-content-between align-items-center mb-3">
        <h4 class="admin-title">
          <i class="bi bi-journal-text me-2"></i> Lecciones del Curso
        </h4>
        <Link
          :href="route('admin.lessons.create', { course_id: course.id })"
          class="btn btn-primary"
        >
          <i class="bi bi-plus-circle me-1"></i> Agregar Lección
        </Link>
      </div>
    </section>

    <!-- Filtros -->
    <section class="section-filters my-2">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-md-6">
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
          <div class="col-12 col-md-6 text-end mt-2 mt-md-0">
            <span class="badge bg-light text-dark">
              <i class="bi bi-journal-text me-1"></i>
              {{ filteredLessons.length }} lección(es)
            </span>
          </div>
        </div>
      </div>
    </section>


 

 
    <!-- Tabla -->
    <section class="section-data">
      <div class="container-fluid">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 40px;"></th>
                    <th>Order</th>
                    <th>Título</th>
                    <th>Publica</th>
                    <th>Activa</th>
                    <th class="text-center">Extras</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>

                <!-- Drag & Drop si no hay búsqueda -->
                <draggable
                  v-if="!searchQuery"
                  v-model="lessonList"
                  tag="tbody"
                  :animation="200"
                  handle=".drag-handle"
                  item-key="id"
                  @end="onDragEnd"
                >
                  <template #item="{ element: lesson }">
                    <tr>
                      <td>
                        <i class="bi bi-arrows-move drag-handle" style="cursor: grab;" title="Arrastrar para reordenar"></i>
                      </td>
                      <td>{{ lesson.order }}</td>
                      <td class="fw-semibold">{{ lesson.title }}</td>
                      <td>{{ formatDate(lesson.publish_on) }}</td>
                      <td>
                        <span class="badge" :class="lesson.active ? 'bg-success' : 'bg-secondary'">
                          {{ lesson.active ? 'Sí' : 'No' }}
                        </span>
                      </td>
                     <!-- dentro de la celda Extras, en ambos <tr> -->
 
                  <td class="text-center">
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                      <span class="badge" :class="countVideos(lesson) > 0 ? 'bg-primary' : 'bg-light text-dark'">
                        <i class="bi bi-film me-1"></i>{{ countVideos(lesson) }}
                      </span>
                      <span class="badge" :class="countEvaluations(lesson) > 0 ? 'bg-info text-dark' : 'bg-light text-dark'">
                        <i class="bi bi-ui-checks-grid me-1"></i>{{ countEvaluations(lesson) }}
                      </span>
                      <span class="badge" :class="countMaterials(lesson) > 0 ? 'bg-warning text-dark' : 'bg-light text-dark'">
                        <i class="bi bi-paperclip me-1"></i>{{ countMaterials(lesson) }}
                      </span>
                    </div>
                  </td>

                      <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                          <Link
                            :href="route('admin.lessons.show', lesson.id)"
                            class="btn btn-outline-primary"
                            title="Ver"
                          >
                            <i class="bi bi-eye-fill"></i>
                          </Link>
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
                            title="Eliminar"
                          >
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </draggable>



                <!-- Modo filtrado sin drag -->
                <tbody v-else>
                  <tr v-for="lesson in filteredLessons" :key="lesson.id">
                    <td></td>
                    <td>{{ lesson.order }}</td>
                    <td class="fw-semibold">{{ lesson.title }}</td>
                    <td>{{ formatDate(lesson.publish_on) }}</td>
                    <td>
                      <span class="badge" :class="lesson.active ? 'bg-success' : 'bg-secondary'">
                        {{ lesson.active ? 'Sí' : 'No' }}
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <span class="badge" :class="lesson.add_video ? 'bg-primary' : 'bg-light text-dark'">
                          <i class="bi bi-film me-1"></i>{{ (lesson.videos?.length ?? 0) }}
                        </span>
                        <span class="badge" :class="lesson.add_evaluation ? 'bg-info text-dark' : 'bg-light text-dark'">
                          <i class="bi bi-ui-checks-grid me-1"></i>{{ (lesson.evaluations?.length ?? 0) }}
                        </span>
                        <span class="badge" :class="lesson.add_materials ? 'bg-warning text-dark' : 'bg-light text-dark'">
                          <i class="bi bi-paperclip me-1"></i>{{ (lesson.materials?.length ?? 0) }}
                        </span>
                      </div>
                    </td>
                    <td class="text-end pe-4">
                      <div class="btn-group btn-group-sm">
                        <Link
                          :href="route('admin.lessons.show', lesson.id)"
                          class="btn btn-outline-primary"
                          title="Ver"
                        >
                          <i class="bi bi-eye-fill"></i>
                        </Link>
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
                          title="Eliminar"
                        >
                          <i class="bi bi-trash-fill"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!filteredLessons.length">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="bi bi-exclamation-circle me-2"></i>No hay lecciones que coincidan con la búsqueda
                    </td>
                  </tr>
                </tbody>
              </table>

              <div v-if="searchQuery" class="alert alert-info mt-2 mb-0 py-2 small">
                Para reordenar, limpia la búsqueda.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modales / Toasts -->
    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteLesson"
      title="¿Eliminar esta lección?"
      confirm-message="Esta acción no se puede deshacer"
      cancel-text="Cancelar"
      confirm-text="Eliminar"
    />

    <ToastNotification
      v-if="toast"
      :message="toast.message"
      :type="toast.type"
      @hidden="toast = null"
    />
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { route } from 'ziggy-js';
import draggable from 'vuedraggable';
import axios from 'axios';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';

const props = defineProps({
  course: Object // con relación lessons precargada
});

// Búsqueda
const searchQuery = ref('');

// Lista reactiva base (ordenada por 'order')
const lessonList = ref(
  [...(props.course.lessons || [])].sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
);

// Filtro computado
const filteredLessons = computed(() => {
  if (!searchQuery.value) return lessonList.value;
  const q = searchQuery.value.toLowerCase();
  return lessonList.value.filter(l =>
    (l.title || '').toLowerCase().includes(q) ||
    (l.description_short || '').toLowerCase().includes(q)
  );
});

// Modal y estado de eliminación
const showDeleteModal = ref(false);
const deletingId = ref(null);
const isDeleting = ref(false);
const toast = ref(null);

// Drag & Drop: actualizar orden en UI y backend
const onDragEnd = async () => {
  // Reflejar visualmente nuevo orden
  lessonList.value.forEach((l, idx) => (l.order = idx + 1));

  // Enviar al backend
  const order = lessonList.value.map(l => l.id);
  try {
    await axios.post(
      route('admin.courses.lessons.reorder', { course: props.course.id }),
      { order }
    );
    toast.value = { message: 'Orden de lecciones actualizado', type: 'success' };
  } catch (e) {
    console.error('Error al actualizar orden:', e, e?.response?.data);
    toast.value = {
      message: `No se pudo actualizar el orden. ${e?.response?.data?.message || ''}`,
      type: 'danger'
    };
  }
};

/** Cuenta videos desde lesson_videos; fallback a videos si existiera. Por defecto solo activos. */
const countVideos = (lesson, onlyActive = true) => {
  const arr = Array.isArray(lesson.lesson_videos) ? lesson.lesson_videos
            : Array.isArray(lesson.videos) ? lesson.videos
            : [];
  if (onlyActive) {
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
    return arr.filter(e => typeof e.active === 'boolean' ? e.active : true).length;
  }
  return arr.length;
};

/** Ajusta a tu estructura real si manejas materiales a nivel de lección. */
const countMaterials = (lesson, onlyActive = true) => {
  const arr = Array.isArray(lesson.lesson_materials) ? lesson.lesson_materials
            : Array.isArray(lesson.materials) ? lesson.materials
            : [];
  if (onlyActive) {
    return arr.filter(m => typeof m.active === 'boolean' ? m.active : true).length;
  }
  return arr.length;
};


// Eliminar lección
const prepareDelete = (id) => {
  deletingId.value = id;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  deletingId.value = null;
};

const deleteLesson = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;

  // Igual que en VideosPanel, usamos el destroy del resource
  Inertia.delete(route('admin.lessons.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      lessonList.value = [...(props.course.lessons || [])].sort((a, b) => (a.order ?? 0) - (b.order ?? 0));

      toast.value = { message: 'Lección eliminada correctamente', type: 'success' };
      cancelDelete();
    },
    onError: () => {
      toast.value = { message: 'Error al eliminar la lección', type: 'danger' };
      isDeleting.value = false;
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};

// Utilidad fechas
const formatDate = (d) => {
  if (!d) return '—';
  const date = typeof d === 'string' ? new Date(d) : d;
  if (Number.isNaN(date.getTime())) return d;
  return date.toISOString().slice(0, 10);
};
</script>
