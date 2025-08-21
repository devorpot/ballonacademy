<script setup>
import { Inertia } from '@inertiajs/inertia'
import { Head, usePage } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import Title from '@/Components/Admin/Ui/Title.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

import ShowStudentModal from '@/Components/Admin/Students/ShowStudentModal.vue'
import CreateStudentModal from '@/Components/Admin/Students/CreateStudentModal.vue'
import StudentsTable from '@/Components/Admin/Students/StudentsTable.vue'

// Modal de activación existente (por usuario)
import ActivationUserModal from '@/Components/Admin/User/ActivationUserModal.vue'

// Nuevo: crear activación manual
import CreateActivationModal from '@/Components/Admin/User/CreateActivationModal.vue'

const props = defineProps({
  // IMPORTANTE: ahora 'students' es realmente una lista de USERS con rol student (con relación 'profile')
  students: Array,
  courses: Array
})

const deletingId = ref(null)
const showDeleteModal = ref(false)
const isDeleting = ref(false)
const showCreateModal = ref(false)
const showViewModal = ref(false)
const selectedStudent = ref(null) // ahora será un USER
const toast = ref(null)

// Activación por usuario existente
const showActivateModal = ref(false)
const userToActivate = ref(null)

// Crear activación manual
const showCreateActivationModal = ref(false)

const page = usePage()

onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

// Recibe un USER y abre el modal de detalle
const openViewModal = (user) => {
  selectedStudent.value = user
  showViewModal.value = true
}

const onCreated = () => {
  toast.value = { message: 'Estudiante creado exitosamente', type: 'success' }
  showCreateModal.value = false
}

const prepareDelete = (userId) => {
  deletingId.value = userId
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}

const deleteStudent = () => {
  if (!deletingId.value) return
  isDeleting.value = true
  // IMPORTANTE: la ruta debe aceptar {user} o id del usuario
  Inertia.delete(route('admin.students.destroy', deletingId.value), {
    preserveScroll: true,
    onSuccess: () => {
      cancelDelete()
      toast.value = { message: 'Estudiante eliminado exitosamente', type: 'success' }
    },
    onError: () => {
      isDeleting.value = false
      toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' }
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}

// Abrir modal de activación desde la tabla: ahora el emisor debe mandar el USER
const openActivateModal = (user) => {
  userToActivate.value = user // ya no user.user
  showActivateModal.value = true
}

const onActivationSent = () => {
  toast.value = { message: 'Se envió la activación al correo indicado', type: 'success' }
}
</script>

<template>
  <Head title="Gestión de Estudiantes" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Estudiantes', route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 d-flex justify-content-between align-items-center gap-2">
            <Title :title="'Gestionar Estudiantes'" />
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" @click="showCreateActivationModal = true">
                <i class="bi bi-lightning-charge me-1"></i> Crear Activación
              </button>
              <button class="btn btn-primary" @click="showCreateModal = true">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Estudiante
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- IMPORTANTE:
         StudentsTable ahora debe leer directamente propiedades de USER:
         - id, name, email
         - profile?.firstname, profile?.lastname, profile?.phone, etc.
         Y debe emitir:
         @view="openViewModal(user)"
         @delete="prepareDelete(user.id)"
         @activate="openActivateModal(user)"
    -->
    <StudentsTable
      :students="students"
      :isDeleting="isDeleting"
      @view="openViewModal"
      @delete="prepareDelete"
      @activate="openActivateModal"
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

    <ToastNotification
      v-if="toast"
      :message="toast.message"
      :type="toast.type"
      @hidden="toast = null"
    />

    <CreateStudentModal
      v-if="showCreateModal"
      :courses="props.courses"
      :show="showCreateModal"
      @saved="onCreated"
      @close="showCreateModal = false"
    />

    <!-- ShowStudentModal: si internamente esperaba un objeto 'student' (modelo Student),
         ahora recibirá un USER. Ajusta su implementación para leer de user/profile.
         Puedes mantener la prop name como 'student' para no romper, pero su shape es de USER. -->
    <ShowStudentModal
      v-if="showViewModal"
      :show="showViewModal"
      :student="selectedStudent"
      @close="showViewModal = false"
    />

    <!-- Modal de Activación existente (por usuario) -->
    <ActivationUserModal
      v-if="showActivateModal && userToActivate"
      :show="showActivateModal"
      :user="userToActivate"
      @close="showActivateModal = false"
      @sent="onActivationSent"
    />

    <!-- Crear Activación manual -->
    <CreateActivationModal
      v-if="showCreateActivationModal"
      :show="showCreateActivationModal"
      :courses="props.courses"
      @close="showCreateActivationModal = false"
    />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
</style>
