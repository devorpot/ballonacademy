<template>
  <div>
    <!-- Toast -->
    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @close="toast=null" />

    <!-- Overlay global de carga -->
 
    <!-- Botón para abrir el modal de agregar -->
    <button class="btn btn-outline-primary mb-3" @click="openModal()">
      <i class="bi bi-plus-circle"></i> Agregar recurso
    </button>

    <!-- Modal crear/editar -->
    <div class="modal fade show" tabindex="-1" style="display:block;" v-if="showModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editingId ? 'Editar recurso' : 'Agregar recurso' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="submitResource" enctype="multipart/form-data">
              <!-- Título -->
              <div class="mb-2">
                <label :for="`vr-title-${video.id}`" class="form-label">Título</label>
                <input
                  :id="`vr-title-${video.id}`"
                  type="text"
                  class="form-control"
                  v-model.trim="form.title"
                  @blur="touch('title')"
                  :class="{ 'is-invalid': touched.title && errors.title }"
                  placeholder="Ejemplo: Guía del curso"
                  required
                />
                <div class="invalid-feedback" v-if="touched.title && errors.title">{{ errors.title }}</div>
              </div>

              <!-- Descripción -->
              <div class="mb-2">
                <label :for="`vr-desc-${video.id}`" class="form-label">Descripción</label>
                <textarea
                  :id="`vr-desc-${video.id}`"
                  class="form-control"
                  v-model.trim="form.description"
                  rows="3"
                  @blur="touch('description')"
                  :class="{ 'is-invalid': touched.description && errors.description }"
                  placeholder="Breve descripción del recurso"
                ></textarea>
                <div class="invalid-feedback" v-if="touched.description && errors.description">{{ errors.description }}</div>
              </div>

              <!-- Tipo + Fecha -->
              <div class="row g-2">
                <div class="col-12 col-md-4">
                  <label :for="`vr-type-${video.id}`" class="form-label">Tipo</label>
                  <select
                    :id="`vr-type-${video.id}`"
                    class="form-select"
                    v-model.number="form.type"
                    @change="onTypeChange"
                    @blur="touch('type')"
                    :class="{ 'is-invalid': touched.type && errors.type }"
                    required
                  >
                    <option :value="1">Archivo (PDF, DOC, DOCX)</option>
                    <option :value="2">Video (MP4)</option>
                    <option :value="3">Imagen (JPG, PNG, WEBP)</option>
                  </select>
                  <div class="invalid-feedback" v-if="touched.type && errors.type">{{ errors.type }}</div>
                </div>

                <div class="col-12 col-md-4">
                  <label :for="`vr-uploaded-${video.id}`" class="form-label">Fecha</label>
                  <input
                    :id="`vr-uploaded-${video.id}`"
                    type="date"
                    class="form-control"
                    v-model="form.uploaded"
                    @blur="touch('uploaded')"
                    :class="{ 'is-invalid': touched.uploaded && errors.uploaded }"
                  />
                  <div class="invalid-feedback" v-if="touched.uploaded && errors.uploaded">{{ errors.uploaded }}</div>
                </div>
              </div>

              <!-- Inputs de archivo según tipo -->
              <div class="mt-3">
                <!-- Archivo -->
                <div v-if="form.type === 1">
                  <label :for="`vr-file-${video.id}`" class="form-label">Archivo (PDF/DOC/DOCX)</label>
                  <input
                    :id="`vr-file-${video.id}`"
                    type="file"
                    class="form-control"
                    accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    @change="onFileChange($event, 'file')"
                    :class="{ 'is-invalid': touched.file && errors.file }"
                  />
                  <div class="form-text" v-if="existing.file_src && !newFileSelected">
                    Archivo actual:
                    <a :href="storageUrl(existing.file_src)" target="_blank" rel="noopener">Ver archivo</a>
                  </div>
                  <div class="invalid-feedback" v-if="touched.file && errors.file">{{ errors.file }}</div>
                </div>

                <!-- Video -->
                <div v-else-if="form.type === 2">
                  <label :for="`vr-video-${video.id}`" class="form-label">Video (MP4)</label>
                  <input
                    :id="`vr-video-${video.id}`"
                    type="file"
                    class="form-control"
                    accept="video/mp4"
                    @change="onFileChange($event, 'video')"
                    :class="{ 'is-invalid': touched.video && errors.video }"
                  />
                  <div class="form-text" v-if="existing.video_src && !newVideoSelected">
                    Video actual:
                    <a :href="storageUrl(existing.video_src)" target="_blank" rel="noopener">Ver video</a>
                  </div>
                  <div class="invalid-feedback" v-if="touched.video && errors.video">{{ errors.video }}</div>
                </div>

                <!-- Imagen -->
                <div v-else-if="form.type === 3">
                  <label :for="`vr-image-${video.id}`" class="form-label">Imagen (JPG/PNG/WEBP)</label>
                  <input
                    :id="`vr-image-${video.id}`"
                    type="file"
                    class="form-control"
                    accept="image/*"
                    @change="onFileChange($event, 'image')"
                    :class="{ 'is-invalid': touched.image && errors.image }"
                  />
                  <div class="form-text" v-if="existing.img_src && !newImageSelected">
                    Imagen actual:
                    <a :href="storageUrl(existing.img_src)" target="_blank" rel="noopener">Ver imagen</a>
                  </div>
                  <div class="invalid-feedback" v-if="touched.image && errors.image">{{ errors.image }}</div>
                </div>
              </div>

              <!-- Acciones modal -->
              <div class="text-end mt-3">
                <button type="submit" class="btn btn-success btn-sm" :disabled="loading.submit">
                  <span v-if="loading.submit" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else :class="editingId ? 'bi bi-pencil' : 'bi bi-plus-circle'"></i>
                  {{ editingId ? 'Guardar cambios' : 'Guardar recurso' }}
                </button>
                <button
                  v-if="editingId"
                  type="button"
                  class="btn btn-secondary btn-sm ms-2"
                  @click="cancelEdit"
                  :disabled="loading.submit"
                >Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showModal"></div>

    <!-- Tabla de recursos -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mt-3" v-if="pagedResources.length">
        <thead class="table-light">
          <tr>
            <th>Título</th>
            <th class="d-none d-md-table-cell">Descripción</th>
            <th>Tipo</th>
            <th>Archivo</th>
            <th class="d-none d-md-table-cell">Fecha</th>
            <th style="width: 110px;"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="res in pagedResources" :key="res.id">
            <td class="fw-semibold">{{ res.title }}</td>
            <td class="text-muted small d-none d-md-table-cell">
              <span v-if="(res.description || '').length <= 80">{{ res.description }}</span>
              <span v-else :title="res.description">{{ res.description.slice(0, 80) }}…</span>
            </td>
            <td>
              <span
                class="badge"
                :class="{
                  'bg-secondary': res.type === 1,
                  'bg-primary': res.type === 2,
                  'bg-success': res.type === 3
                }"
              >
                <i class="bi me-1"
                   :class="{
                     'bi-file-earmark-text': res.type === 1,
                     'bi-camera-video': res.type === 2,
                     'bi-image': res.type === 3
                   }"
                ></i>
                {{ typeLabel(res.type) }}
              </span>
            </td>
            <td>
              <a
                v-if="fileHref(res)"
                :href="fileHref(res)"
                target="_blank"
                rel="noopener"
                class="btn btn-sm btn-outline-secondary"
                title="Ver recurso"
              >
                Abrir
              </a>
            </td>
            <td class="d-none d-md-table-cell">
              <span class="text-muted small">{{ res.uploaded || '—' }}</span>
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-primary me-2" @click="editResource(res)" title="Editar">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" @click="askDelete(res)" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="text-center text-muted my-3">
        No has agregado recursos.
      </div>
    </div>

    <!-- Paginación -->
    <CrudPagination
      v-if="resources.length > perPage"
      :total="resources.length"
      :per-page="perPage"
      :current-page="currentPage"
      @change="onPageChange"
      class="mt-3"
    />

    <!-- Confirmación de borrado -->
    <ConfirmDeleteModal
      v-if="confirm.visible"
      :show="confirm.visible"
      :title="'Eliminar recurso'"
      :message="`¿Seguro que deseas eliminar “${confirm.item?.title || 'Recurso'}”?`"
      @confirm="confirmDelete"
      @cancel="confirm.visible=false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'

import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue'
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue'
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue'

const props = defineProps({
  video: { type: Object, required: true }
})
const emit = defineEmits(['saved'])

/* Estado */
const resources = ref([])
const showModal = ref(false)
const editingId = ref(null)
const toast = ref(null)
const loading = reactive({ list: false, submit: false, remove: false })

/* Paginación en cliente */
const perPage = ref(10)
const currentPage = ref(1)
const pagedResources = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return resources.value.slice(start, start + perPage.value)
})
function onPageChange(page) { currentPage.value = page }

/* Confirmación de borrado */
const confirm = reactive({
  visible: false,
  item: null
})

/* Form */
const form = reactive({
  title: '',
  description: '',
  type: 1,
  uploaded: '',
  file: null,
  video: null,
  image: null
})
const existing = reactive({
  file_src: null,
  video_src: null,
  img_src: null
})
const touched = reactive({
  title: false, description: false, type: false, uploaded: false, file: false, video: false, image: false
})
const errors = reactive({
  title: '', description: '', type: '', uploaded: '', file: '', video: '', image: ''
})

/* Flags de archivo seleccionado */
const newFileSelected = ref(false)
const newVideoSelected = ref(false)
const newImageSelected = ref(false)

/* Utils */
function storageUrl(path) { return path ? `/storage/${path}` : null }
function typeLabel(t) { return t === 1 ? 'Archivo' : t === 2 ? 'Video' : 'Imagen' }
function fileHref(res) {
  if (res.type === 1 && res.file_src) return storageUrl(res.file_src)
  if (res.type === 2 && res.video_src) return storageUrl(res.video_src)
  if (res.type === 3 && res.img_src) return storageUrl(res.img_src)
  return null
}
function touch(field) { touched[field] = true }

/* Modal */
function resetForm() {
  editingId.value = null
  Object.assign(form, { title: '', description: '', type: 1, uploaded: '', file: null, video: null, image: null })
  Object.assign(existing, { file_src: null, video_src: null, img_src: null })
  Object.keys(touched).forEach(k => touched[k] = false)
  Object.keys(errors).forEach(k => errors[k] = '')
  newFileSelected.value = newVideoSelected.value = newImageSelected.value = false
}
function openModal(resource = null) {
  showModal.value = true
  if (resource) {
    editingId.value = resource.id
    Object.assign(form, {
      title: resource.title || '',
      description: resource.description || '',
      type: resource.type || 1,
      uploaded: resource.uploaded || ''
    })
    Object.assign(existing, {
      file_src: resource.file_src || null,
      video_src: resource.video_src || null,
      img_src: resource.img_src || null
    })
  } else {
    resetForm()
  }
}
function closeModal() { showModal.value = false; resetForm() }
function cancelEdit() { closeModal() }

/* Archivos */
function onTypeChange() {
  form.file = form.video = form.image = null
  newFileSelected.value = newVideoSelected.value = newImageSelected.value = false
}
function onFileChange(e, which) {
  const file = e.target.files?.[0] || null
  if (which === 'file') { form.file = file; newFileSelected.value = !!file }
  if (which === 'video') { form.video = file; newVideoSelected.value = !!file }
  if (which === 'image') { form.image = file; newImageSelected.value = !!file }
}

/* Validación */
function validate() {
  let ok = true
  Object.keys(errors).forEach(k => errors[k] = '')
  if (!form.title) { errors.title = 'El título es obligatorio'; ok = false }
  if (![1,2,3].includes(+form.type)) { errors.type = 'Tipo inválido'; ok = false }
  if (!editingId.value) {
    if (form.type === 1 && !form.file) { errors.file = 'Selecciona un archivo'; ok = false }
    if (form.type === 2 && !form.video) { errors.video = 'Selecciona un video'; ok = false }
    if (form.type === 3 && !form.image) { errors.image = 'Selecciona una imagen'; ok = false }
  }
  return ok
}

/* Data */
async function fetchResources() {
  loading.list = true
  try {
    const { data } = await axios.get(route('admin.videos.resources.index', props.video.id))
    resources.value = Array.isArray(data) ? data : (data?.data || [])
    currentPage.value = 1
  } catch {
    resources.value = []
  } finally {
    loading.list = false
  }
}

/* Guardar */
async function submitResource() {
  Object.keys(touched).forEach(k => touched[k] = true)
  if (!validate()) return

  loading.submit = true
  const fd = new FormData()
  fd.append('title', form.title)
  fd.append('description', form.description || '')
  fd.append('type', String(form.type))
  if (form.uploaded) fd.append('uploaded', form.uploaded)
  if (form.type === 1 && form.file)  fd.append('file', form.file)
  if (form.type === 2 && form.video) fd.append('video', form.video)
  if (form.type === 3 && form.image) fd.append('image', form.image)

  try {
    if (editingId.value) {
      await axios.post(
        route('admin.videos.resources.update', { video: props.video.id, resource: editingId.value }),
        fd,
        { headers: { 'Content-Type': 'multipart/form-data' }, params: { _method: 'PUT' } }
      )
      toast.value = { message: 'Recurso actualizado', type: 'success' }
    } else {
      await axios.post(
        route('admin.videos.resources.store', props.video.id),
        fd,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      )
      toast.value = { message: 'Recurso creado', type: 'success' }
    }
    closeModal()
    await fetchResources()
    emit('saved')
  } catch (e) {
    if (e.response?.data?.errors) {
      for (const [k, v] of Object.entries(e.response.data.errors)) {
        errors[k] = Array.isArray(v) ? v[0] : v
      }
      toast.value = { message: 'Corrige los campos marcados', type: 'danger' }
    } else {
      toast.value = { message: e?.response?.data?.message || 'Error al guardar', type: 'danger' }
    }
  } finally {
    loading.submit = false
  }
}

/* Eliminar con confirmación + Optimistic UI */
function askDelete(item) {
  confirm.item = item
  confirm.visible = true
}
async function confirmDelete() {
  const item = confirm.item
  confirm.visible = false
  if (!item) return

  // Optimistic UI: quita de la tabla y recuerda índice para posible rollback
  const idx = resources.value.findIndex(r => r.id === item.id)
  if (idx === -1) return
  const snapshot = resources.value[idx]
  resources.value.splice(idx, 1)

  loading.remove = true
  try {
    await axios.delete(
      route('admin.videos.resources.destroy', { video: props.video.id, resource: item.id })
    )
    toast.value = { message: 'Recurso eliminado', type: 'success' }

    // Si después de eliminar quedaste en página vacía, regresa una página y repinta
    const maxPage = Math.max(1, Math.ceil(resources.value.length / perPage.value))
    if (currentPage.value > maxPage) currentPage.value = maxPage
    // Opcional: volver a sincronizar con backend
    await fetchResources()
  } catch (e) {
    // Rollback
    resources.value.splice(idx, 0, snapshot)
    toast.value = { message: 'No se pudo eliminar el recurso', type: 'danger' }
  } finally {
    loading.remove = false
  }
}

onMounted(fetchResources)
watch(() => props.video?.id, fetchResources)
</script>
