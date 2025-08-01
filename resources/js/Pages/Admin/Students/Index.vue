<script setup>
import { Inertia } from '@inertiajs/inertia';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';
import ShowStudentModal from '@/Pages/Admin/Students/ShowStudentModal.vue';
import CreateStudentModal from '@/Pages/Admin/Students/CreateStudentModal.vue';
import StudentsTable from '@/Components/Admin/Students/StudentsTable.vue';

const props = defineProps({
  students: Array,
  courses: Array
});

const deletingId = ref(null);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const showCreateModal = ref(false);
const showViewModal = ref(false);
const selectedStudent = ref(null);
const toast = ref(null);

const page = usePage();

onMounted(() => {
  if (page.props.flash.success) {
    toast.value = { message: page.props.flash.success, type: 'success' };
  }
  if (page.props.flash.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' };
  }
});

const openViewModal = (student) => {
  selectedStudent.value = student;
  showViewModal.value = true;
};

const onCreated = () => {
  toast.value = { message: 'Estudiante creado exitosamente', type: 'success' };
  showCreateModal.value = false;
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

const deleteStudent = () => {
  if (!deletingId.value) return;
  isDeleting.value = true;
  Inertia.delete(route('admin.students.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete();
      toast.value = { message: 'Estudiante eliminado exitosamente', type: 'success' };
    },
    onError: () => {
      isDeleting.value = false;
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' };
    },
    onFinish: () => {
      isDeleting.value = false;
    }
  });
};
</script>

<template>
  <Head title="Gestión de Estudiantes" />
  <AdminLayout>
    <Breadcrumbs username="admin" :breadcrumbs="[
      { label: 'Dashboard', route: 'admin.dashboard' },
      { label: 'Estudiantes', route: '' }
    ]" />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Gestionar Estudiantes'" />
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Estudiante
            </button>
          </div>
        </div>
      </div>
    </section>

    <StudentsTable
      :students="students"
      :isDeleting="isDeleting"
      @view="openViewModal"
      @delete="prepareDelete"
    />

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="deleteStudent"
      title="¿Deseas eliminar este estudiante?"
      confirm-message="Por favor confirma la eliminación de este estudiante"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />

    <CreateStudentModal
      v-if="showCreateModal"
      :courses="props.courses"
      :show="showCreateModal"
      @saved="onCreated"
      @close="showCreateModal = false"
    />

    <ShowStudentModal
      v-if="showViewModal"
      :show="showViewModal"
      :student="selectedStudent"
      @close="showViewModal = false"
    />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td, .table th {
  vertical-align: middle;
}
</style>
