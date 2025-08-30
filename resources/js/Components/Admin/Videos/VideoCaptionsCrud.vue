<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'

const props = defineProps({
  video: { type: Object, required: true }
})
const emit = defineEmits(['saved'])

/* Estado */
const items = ref([])
const showModal = ref(false)
const editingId = ref(null)
const toast = ref(null)
const loading = reactive({ list: false, submit: false, remove: false })

/* Paginación en cliente */
const perPage = ref(10)
const currentPage = ref(1)
const pagedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return items.value.slice(start, start + perPage.value)
})
function onPageChange(p) { currentPage.value = p }

/* Confirmación de borrado */
const confirm = reactive({ visible: false, item: null })

/* Form (solo archivo, sin caja de texto) */
const form = reactive({
  lang: 'es',
  file: null
})
const touched = reactive({ lang: false, file: false })
const errors = reactive({ lang: '', file: '' })

function resetForm() {
  editingId.value = null
  Object.assign(form, { lang: 'es', file: null })
  Object.keys(touched).forEach(k => touched[k] = false)
  Object.keys(errors).forEach(k => errors[k] = '')
}

function openModal(item = null) {
  showModal.value = true
  if (item) {
    editingId.value = item.id
    Object.assign(form, {
      lang: item.lang || 'es',
      file: null
    })
  } else {
    resetForm()
  }
}

function closeModal() { showModal.value = false; resetForm() }
function cancelEdit() { closeModal() }

function touch(field) { touched[field] = true }
function onFileChange(e) {
  form.file = e.target.files?.[0] || null
}

function validate() {
  let ok = true
  Object.keys(errors).forEach(k => errors[k] = '')
  if (!form.lang) { errors.lang = 'Selecciona un idioma'; ok = false }

  // En creación, el archivo es obligatorio
  if (!editingId.value && !form.file) {
    errors.file = 'Sube un archivo .vtt/.srt'
    ok = false
  }
  return ok
}

function storageUrl(path) { return path ? `/storage/${path}` : null }

/* Carga lista */
async function fetchItems() {
  loading.list = true
  try {
    const { data } = await axios.get(route('admin.videos.captions.index', props.video.id))
    items.value = Array.isArray(data) ? data : (data?.data || [])
    currentPage.value = 1
  } catch {
    items.value = []
  } finally {
    loading.list = false
  }
}

/* Guardar */
async function submitItem() {
  Object.keys(touched).forEach(k => touched[k] = true)
  if (!validate()) return

  loading.submit = true
  const fd = new FormData()
  fd.append('lang', form.lang)
  if (form.file) fd.append('file', form.file)

  try {
    if (editingId.value) {
      fd.append('_method', 'PUT')
      await axios.post(
        route('admin.videos.captions.update', { video: props.video.id, caption: editingId.value }),
        fd
      )
      toast.value = { message: 'Subtítulo actualizado', type: 'success' }
    } else {
      await axios.post(
        route('admin.videos.captions.store', props.video.id),
        fd
      )
      toast.value = { message: 'Subtítulo creado', type: 'success' }
    }

    closeModal()
    await fetchItems()
    emit('saved')
  } catch (e) {
    if (e.response?.data?.errors) {
      for (const [k, v] of Object.entries(e.response.data.errors)) {
        errors[k] = Array.isArray(v) ? v[0] : v
      }
      toast.value = { message: 'Corrige los campos marcados', type: 'danger' }
    } else {
      console.error(e)
      toast.value = { message: e?.response?.data?.message || 'Error al guardar', type: 'danger' }
    }
  } finally {
    loading.submit = false
  }
}

/* Eliminar */
function askDelete(item) { confirm.item = item; confirm.visible = true }
async function confirmDelete() {
  const item = confirm.item
  confirm.visible = false
  if (!item) return

  // Optimistic UI
  const idx = items.value.findIndex(x => x.id === item.id)
  if (idx === -1) return
  const snapshot = items.value[idx]
  items.value.splice(idx, 1)

  try {
    await axios.delete(route('admin.videos.captions.destroy', { video: props.video.id, caption: item.id }))
    toast.value = { message: 'Subtítulo eliminado', type: 'success' }
    await fetchItems()
  } catch {
    // rollback
    items.value.splice(idx, 0, snapshot)
    toast.value = { message: 'No se pudo eliminar', type: 'danger' }
  }
}

onMounted(fetchItems)
watch(() => props.video?.id, fetchItems)
</script>

<template>
  <div>
    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @close="toast=null" />

    <button class="btn btn-outline-primary mb-3" @click="openModal()">
      <i class="bi bi-cc-square"></i> Agregar subtítulo
    </button>

    <!-- Modal -->
    <div class="modal fade show" tabindex="-1" style="display:block;" v-if="showModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Editar subtítulo' : 'Agregar subtítulo' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitItem" enctype="multipart/form-data">
              <div class="row g-2">
                <div class="col-12 col-md-4">
                  <label class="form-label">Idioma</label>
                  <select class="form-select" v-model="form.lang" @blur="touch('lang')" :class="{ 'is-invalid': touched.lang && errors.lang }">
                    <option value="es">Español (es)</option>
                    <option value="en">English (en)</option>
                    <option value="fr">Français (fr)</option>
                    <option value="pt">Português (pt)</option>
                  </select>
                  <div class="invalid-feedback" v-if="touched.lang && errors.lang">{{ errors.lang }}</div>
                </div>

                <div class="col-12 col-md-8">
                  <label class="form-label">Archivo (.vtt / .srt)</label>
                  <input
                    type="file"
                    class="form-control"
                    accept=".vtt,.srt,text/vtt,application/x-subrip"
                    @change="onFileChange"
                    @blur="touch('file')"
                    :class="{ 'is-invalid': touched.file && errors.file }"
                  />
                  <!-- Mostrar archivo existente si se está editando -->
                  <div v-if="editingId && items.find(i => i.id === editingId)?.file" class="form-text">
                    Ya existe un archivo para este subtítulo.
                    <a :href="storageUrl(items.find(i => i.id === editingId)?.file)" target="_blank" rel="noopener">
                      Ver archivo actual
                    </a>
                  </div>
                  <div class="invalid-feedback" v-if="touched.file && errors.file">{{ errors.file }}</div>
                </div>
              </div>

              <div class="text-end mt-3">
                <button type="submit" class="btn btn-success btn-sm" :disabled="loading.submit">
                  <span v-if="loading.submit" class="spinner-border spinner-border-sm me-1"></span>
                  {{ editingId ? 'Guardar cambios' : 'Guardar subtítulo' }}
                </button>
                <button type="button" class="btn btn-secondary btn-sm ms-2" @click="cancelEdit" :disabled="loading.submit">
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showModal"></div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mt-3" v-if="pagedItems.length">
        <thead class="table-light">
          <tr>
            <th>Idioma</th>
            <th>Archivo</th>
            <th style="width: 110px;"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cap in pagedItems" :key="cap.id">
            <td class="fw-semibold">{{ cap.lang }}</td>
            <td>
              <a v-if="cap.file" :href="storageUrl(cap.file)" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                Abrir archivo
              </a>
              <span v-else class="text-muted">—</span>
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-primary me-2" @click="openModal(cap)" title="Editar">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" @click="askDelete(cap)" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="text-center text-muted my-3">
        No hay subtítulos.
      </div>
    </div>

    <CrudPagination
      v-if="items.length > perPage"
      :total="items.length"
      :per-page="perPage"
      :current-page="currentPage"
      @change="onPageChange"
      class="mt-3"
    />

    <ConfirmDeleteModal
      v-if="confirm.visible"
      :show="confirm.visible"
      title="Eliminar subtítulo"
      :message="`¿Seguro que deseas eliminar el subtítulo ${confirm.item?.lang || ''}?`"
      @confirm="confirmDelete"
      @cancel="confirm.visible=false"
    />
  </div>
</template>
