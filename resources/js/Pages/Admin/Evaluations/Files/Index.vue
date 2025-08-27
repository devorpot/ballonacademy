<script setup>
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { route } from 'ziggy-js'
import draggable from 'vuedraggable'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  evaluation: Object,
  // Puede venir como array simple o como paginator { data, ... }
  files: { type: [Array, Object], default: () => [] },
})

const page = usePage()
const toast = ref(null)

const searchQuery = ref('')
const sortKey = ref('order')
const sortOrder = ref('asc')

const deletingId = ref(null)
const showDeleteModal = ref(false)
const isDeleting = ref(false)

const isOrdering = ref(false)

const showCreate = ref(false)
const showEdit = ref(false)
const editingFile = ref(null)

// Normaliza paginator/array
const baseList = computed(() => {
  if (Array.isArray(props.files)) return props.files
  if (props.files && Array.isArray(props.files.data)) return props.files.data
  return []
})

// Copia reactiva para vuedraggable
const files = ref([...baseList.value])

// Mantener sincronizado cuando cambie la prop files (por reload parcial)
watch(
  () => props.files,
  (val) => {
    const list = Array.isArray(val) ? val : (val?.data ?? [])
    files.value = [...list]
  }
)

onMounted(() => {
  if (page.props.flash?.success) {
    toast.value = { message: page.props.flash.success, type: 'success' }
  }
  if (page.props.flash?.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' }
  }
})

const filtered = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  let list = files.value
  if (q) {
    list = list.filter(f =>
      (f.title || '').toLowerCase().includes(q) ||
      (f.description || '').toLowerCase().includes(q) ||
      String(f.id).includes(q)
    )
  }
  return list
})

const sorted = computed(() => {
  const key = sortKey.value
  const dir = sortOrder.value
  return [...filtered.value].sort((a, b) => {
    const av = a[key] ?? ''
    const bv = b[key] ?? ''
    if (av < bv) return dir === 'asc' ? -1 : 1
    if (av > bv) return dir === 'asc' ? 1 : -1
    return 0
  })
})

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

// Eliminar
const prepareDelete = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}
const cancelDelete = () => {
  showDeleteModal.value = false
  deletingId.value = null
  isDeleting.value = false
}
const doDelete = () => {
  if (!deletingId.value) return
  isDeleting.value = true
  useForm().delete(
    route('admin.evaluations.files.destroy', [props.evaluation.id, deletingId.value]),
    {
      preserveScroll: true,
      onSuccess: () => {
        files.value = files.value.filter(f => f.id !== deletingId.value)
        cancelDelete()
        toast.value = { message: 'Archivo eliminado correctamente', type: 'success' }
      },
      onError: () => {
        isDeleting.value = false
        toast.value = { message: 'Ocurrió un error al eliminar', type: 'danger' }
      },
    }
  )
}

// Drag & Drop
const onDragEnd = () => saveOrder()
const saveOrder = () => {
  isOrdering.value = true
  const orders = files.value.map((f, idx) => ({ id: f.id, order: idx + 1 }))
  useForm({ orders }).post(route('admin.evaluations.files.reorder', props.evaluation.id), {
    preserveScroll: true,
    forceFormData: false,
    onSuccess: () => {
      files.value.forEach((f, idx) => { f.order = idx + 1 })
      toast.value = { message: 'Orden actualizado correctamente', type: 'success' }
      isOrdering.value = false
    },
    onError: () => {
      toast.value = { message: 'Error al guardar el orden', type: 'danger' }
      isOrdering.value = false
    },
  })
}

// Crear
const createForm = useForm({
  title: '',
  description: '',
  uploaded: '',
  order: '',
  file: null,
})
const onFileSelect = (e) => {
  const f = e.target.files?.[0]
  if (f) createForm.file = f
}
const submitCreate = () => {
  createForm.post(route('admin.evaluations.files.store', props.evaluation.id), {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['files'], preserveScroll: true })
      createForm.reset()
      showCreate.value = false
      toast.value = { message: 'Archivo agregado correctamente', type: 'success' }
    },
    onError: () => {
      toast.value = { message: 'Revisa los campos del formulario', type: 'danger' }
    },
  })
}

/**
 * Vista previa en edición (con cache-buster)
 */
const isImage = (t) => /^(jpg|jpeg|png|webp|gif|bmp|svg|image\/)/i.test(t || '')
const isVideo = (t) => /^(mp4|mov|avi|webm|mkv|video\/)/i.test(t || '')
const isPdf   = (t) => /^(pdf|application\/pdf)$/i.test(t || '')
const isText  = (t) => /^(txt|text\/plain)$/i.test(t || '')

// Dado el registro, retorna la URL pública del archivo en storage/public con cache-buster
const storageUrl = (file) => {
  if (!file?.file_uploaded) return null
  // usa updated_at si viene del backend (recomendado) o Date.now() como fallback
  const ver = (file.updated_at ? new Date(file.updated_at).getTime() : Date.now())
  return `/storage/${file.file_uploaded}?v=${ver}`
}

// URL del nuevo archivo seleccionado (reemplazo) si existe
const newFileObjectUrl = ref(null)
const revokePreviewUrl = () => {
  if (newFileObjectUrl.value) {
    URL.revokeObjectURL(newFileObjectUrl.value)
    newFileObjectUrl.value = null
  }
}
onBeforeUnmount(revokePreviewUrl)

const editForm = useForm({
  title: '',
  description: '',
  uploaded: '',
  order: '',
  file: null, // reemplazo opcional
})

watch(() => editForm.file, (f) => {
  revokePreviewUrl()
  if (f) newFileObjectUrl.value = URL.createObjectURL(f)
})

// Tipo lógico del archivo para decidir el render de la vista previa
const previewType = computed(() => {
  if (editForm.file) {
    const mime = editForm.file.type || ''
    if (mime) return mime
    const name = editForm.file.name || ''
    const ext  = name.split('.').pop()?.toLowerCase()
    return ext || ''
  }
  return editingFile.value?.type || ''
})

// Fuente priorizando el archivo nuevo, luego el almacenado (con cache-buster)
const previewSrc = computed(() => {
  if (newFileObjectUrl.value) return newFileObjectUrl.value
  return storageUrl(editingFile.value)
})

// Abrir edición
const openEdit = (file) => {
  editingFile.value = { ...file }
  editForm.reset()
  revokePreviewUrl()
  editForm.title = file.title || ''
  editForm.description = file.description || ''
  editForm.uploaded = file.uploaded || ''
  editForm.order = file.order || ''
  editForm.file = null
  showEdit.value = true
}

// Cerrar modal de edición limpiando recursos
const closeEditModal = () => {
  showEdit.value = false
  editForm.reset()
  revokePreviewUrl()
  editingFile.value = null
}

// Editar (reemplazo de archivo incluido)
const submitEdit = () => {
  if (!editingFile.value) return
  editForm.put(
    route('admin.evaluations.files.update', [props.evaluation.id, editingFile.value.id]),
    {
      preserveScroll: true,
      forceFormData: true, // importante para enviar file
      onSuccess: () => {
        // Recargar lista y cerrar modal
        router.reload({ only: ['files'], preserveScroll: true })
        closeEditModal()
        toast.value = { message: 'Archivo actualizado correctamente', type: 'success' }
      },
      onError: () => {
        toast.value = { message: 'No se pudo actualizar el archivo', type: 'danger' }
      },
      onFinish: () => {
        // Si el backend redirige sin onSuccess, intenta cerrar igualmente cuando hay éxito reciente
        if (editForm.recentlySuccessful) {
          closeEditModal()
        }
      }
    }
  )
}
</script>


<template>
  <Head :title="`Archivos de Evaluación #${evaluation.id}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Evaluaciones', route: 'admin.evaluations.index' },
        { label: `Archivos de Evaluación #${evaluation.id}`, route: 'admin.evaluations.index' }
      ]"
    />

    <section class="section-heading mb-2">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-12 d-flex justify-content-between">
            <h4 class="admin-title">
              <i class="bi bi-folder2-open me-2"></i> Archivos de Evaluación #{{ evaluation.id }}
            </h4>
            <button class="btn btn-primary" @click="showCreate = true">
              <i class="bi bi-plus-lg me-1"></i> Nuevo Archivo
            </button>
          </div>
        </div>
      </div>
    </section>

    <CrudFilters
      v-model:searchQuery="searchQuery"
      :count="files.length"
      placeholder="Buscar archivos..."
      item-label="archivo(s)"
    />

    <section class="section-data my-2">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="small text-muted" v-if="isOrdering">
              Guardando orden…
            </div>
            <div class="text-end">
              <Link class="btn btn-secondary btn-sm me-2" :href="route('admin.evaluations.index')">
                Volver a Evaluaciones
              </Link>
           
            </div>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:40px;"></th>
                    <th @click="sortBy('order')" style="cursor: pointer;">
                      Orden
                      <i :class="sortKey === 'order' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th @click="sortBy('title')" style="cursor: pointer;">
                      Título
                      <i :class="sortKey === 'title' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th>Tipo</th>
                    <th class="text-end">Tamaño</th>
                    <th @click="sortBy('uploaded')" style="cursor: pointer;">
                      Fecha subida
                      <i :class="sortKey === 'uploaded' ? (sortOrder === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down') : 'bi bi-arrow-down-up'"></i>
                    </th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>

                <draggable
                  tag="tbody"
                  v-model="files"
                  item-key="id"
                  handle=".drag-handle"
                  :animation="180"
                  @end="onDragEnd"
                >
                  <template #item="{ element: file }">
                    <tr>
                      <td class="drag-handle" style="cursor:grab;"><i class="bi bi-list"></i></td>
                      <td>{{ file.order }}</td>
                      <td>
                        <div class="fw-semibold">{{ file.title }}</div>
                        <div class="text-muted small text-truncate" style="max-width: 380px;">
                          {{ file.description }}
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark">{{ file.type || 'N/D' }}</span>
                      </td>
                      <td class="text-end">
                        <span v-if="file.size">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</span>
                        <span v-else>N/D</span>
                      </td>
                      <td>{{ file.uploaded || '—' }}</td>
                      <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                          <a
                            class="btn btn-outline-secondary"
                            :href="route('admin.evaluations.files.download', [evaluation.id, file.id])"
                            title="Descargar"
                          >
                            <i class="bi bi-download"></i>
                          </a>
                       
                          <button class="btn btn-warning" @click="openEdit(file)" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                          </button>
                         
                          <button class="btn btn-danger" @click="prepareDelete(file.id)" :disabled="isDeleting" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </draggable>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Crear -->
    <div class="modal fade show" tabindex="-1" style="display:block;" v-if="showCreate" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Nuevo Archivo</h5>
            <button type="button" class="btn-close" @click="showCreate=false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="c_title" class="form-label">Título</label>
              <input id="c_title" type="text" class="form-control" v-model="createForm.title">
              <div class="invalid-feedback d-block" v-if="createForm.errors.title">{{ createForm.errors.title }}</div>
            </div>

            <div class="mb-3">
              <label for="c_description" class="form-label">Descripción</label>
              <textarea id="c_description" rows="3" class="form-control" v-model="createForm.description"></textarea>
              <div class="invalid-feedback d-block" v-if="createForm.errors.description">{{ createForm.errors.description }}</div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="c_uploaded" class="form-label">Fecha subida</label>
                <input id="c_uploaded" type="date" class="form-control" v-model="createForm.uploaded">
                <div class="invalid-feedback d-block" v-if="createForm.errors.uploaded">{{ createForm.errors.uploaded }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="c_order" class="form-label">Orden</label>
                <input id="c_order" type="number" class="form-control" v-model="createForm.order" min="0" placeholder="Auto">
                <div class="invalid-feedback d-block" v-if="createForm.errors.order">{{ createForm.errors.order }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="c_file" class="form-label">Archivo</label>
                <input id="c_file" type="file" class="form-control" @change="onFileSelect" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.avi,.mov,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt">
                <div class="invalid-feedback d-block" v-if="createForm.errors.file">{{ createForm.errors.file }}</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="showCreate=false">Cancelar</button>
            <button class="btn btn-primary" :disabled="createForm.processing" @click="submitCreate">
              <span v-if="createForm.processing" class="spinner-border spinner-border-sm me-2"></span>
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showCreate" class="modal-backdrop fade show"></div>

    <!-- Modal Editar -->
    <div class="modal fade show" tabindex="-1" style="display:block;" v-if="showEdit" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Archivo</h5>
            <button type="button" class="btn-close" @click="showEdit=false"></button>
          </div>
          <div class="modal-body" v-if="editingFile">
            <div class="mb-3">
              <label for="e_title" class="form-label">Título</label>
              <input id="e_title" type="text" class="form-control" v-model="editForm.title">
              <div class="invalid-feedback d-block" v-if="editForm.errors.title">{{ editForm.errors.title }}</div>
            </div>

            <div class="mb-3">
              <label for="e_description" class="form-label">Descripción</label>
              <textarea id="e_description" rows="3" class="form-control" v-model="editForm.description"></textarea>
              <div class="invalid-feedback d-block" v-if="editForm.errors.description">{{ editForm.errors.description }}</div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="e_uploaded" class="form-label">Fecha subida</label>
                <input id="e_uploaded" type="date" class="form-control" v-model="editForm.uploaded">
                <div class="invalid-feedback d-block" v-if="editForm.errors.uploaded">{{ editForm.errors.uploaded }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="e_order" class="form-label">Orden</label>
                <input id="e_order" type="number" class="form-control" v-model="editForm.order" min="0">
                <div class="invalid-feedback d-block" v-if="editForm.errors.order">{{ editForm.errors.order }}</div>
              </div>
              <div class="col-md-12 mb-3">
                <label for="e_file" class="form-label">Reemplazar archivo (opcional)</label>
                <input id="e_file" type="file" class="form-control" @change="onEditFileSelect" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.avi,.mov,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt">
                <div class="invalid-feedback d-block" v-if="editForm.errors.file">{{ editForm.errors.file }}</div>
              </div>
            </div>

            <div class="alert alert-light small">
              <div><strong>Actual:</strong> {{ editingFile.title }}</div>
              <div v-if="editingFile.type"><strong>Tipo:</strong> {{ editingFile.type }}</div>
              <div v-if="editingFile.size"><strong>Tamaño:</strong> {{ (editingFile.size/1024/1024).toFixed(2) }} MB</div>
              <div v-if="editingFile.uploaded"><strong>Fecha:</strong> {{ editingFile.uploaded }}</div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-light" @click="showEdit=false">Cancelar</button>
            <button class="btn btn-primary" :disabled="editForm.processing" @click="submitEdit">
              <span v-if="editForm.processing" class="spinner-border spinner-border-sm me-2"></span>
              Guardar cambios
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showEdit" class="modal-backdrop fade show"></div>

    <ConfirmDeleteModal
      :show="showDeleteModal"
      :loading="isDeleting"
      @close="cancelDelete"
      @confirm="doDelete"
      title="¿Deseas eliminar este archivo?"
      confirm-message="Por favor confirma la eliminación del archivo"
      warning-text="Esta acción no se puede deshacer."
      cancel-text="No, cancelar"
      confirm-text="Sí, eliminar"
    />

    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </AdminLayout>
</template>

<style scoped>
.table-hover tbody tr:hover { background-color: #f8f9fa; }
.table td, .table th { vertical-align: middle; }
.modal { display: block; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); }
.text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
