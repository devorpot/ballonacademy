<!-- resources/js/Components/Admin/Lessons/VideoPicker.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'
import Draggable from 'vuedraggable'

const props = defineProps({
  lesson: { type: Object, required: true }, // { id, course_id, videos: [...] }
  refreshOnAttach: { type: Boolean, default: true }
})

const emit = defineEmits(['attached'])

/** UI state */
const query       = ref('')
const loading     = ref(false)
const saving      = ref(false)
const results     = ref([])
const alert       = ref({ type: '', message: '' }) // 'success' | 'danger' | ''

/** Build initial attached from props (persisted) */
const attached = ref(
  Array.isArray(props.lesson?.videos)
    ? props.lesson.videos.map(v => ({
        id: v.id,
        title: v.title,
        image: v.image_cover ? `/storage/${v.image_cover}` : null,
        duration: v.duration ?? null,
        size: v.size ?? null,
        pivot: {
          order: v.pivot?.order ?? null,
          active: Boolean(v.pivot?.active)
        },
        _persisted: true
      }))
    : []
)

/** New picks (not persisted yet) */
const selected = ref([])

/** Utils */
function humanDuration(d) {
  if (!d && d !== 0) return ''
  if (typeof d === 'string' && d.includes(':')) return d
  const sec = Number(d)
  if (Number.isNaN(sec)) return ''
  const h = Math.floor(sec / 3600)
  const m = Math.floor((sec % 3600) / 60)
  const s = sec % 60
  const pad = n => String(n).padStart(2, '0')
  return `${h > 0 ? pad(h) + ':' : ''}${pad(m)}:${pad(s)}`
}

function humanSize(sz) {
  if (!sz && sz !== 0) return ''
  const i = Math.floor(Math.log(sz) / Math.log(1024))
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  return `${(sz / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`
}

/** Avoid duplicates between attached & selected */
const allVideoIds = computed(() => new Set([
  ...attached.value.map(v => v.id),
  ...selected.value.map(v => v.id),
]))

let debouncing
watch(query, () => {
  if (debouncing) clearTimeout(debouncing)
  debouncing = setTimeout(fetchResults, 300)
})

/** Fetch autocomplete */
async function fetchResults() {
  const q = query.value.trim()
  if (!props.lesson?.course_id) {
    results.value = []
    return
  }
  loading.value = true
  try {
    const { data } = await axios.get(route('admin.videos.by-course', { course: props.lesson.course_id }), {
      params: { q, lesson_id: props.lesson.id }
    })
    const skip = allVideoIds.value
    results.value = (data?.data || []).filter(v => !skip.has(v.id))
  } catch (e) {
    results.value = []
  } finally {
    loading.value = false
  }
}

function pick(video) {
  if (allVideoIds.value.has(video.id)) return
  selected.value.push({ ...video, _persisted: false })
  // limpiar y cerrar el listado de resultados para una UX clara
  query.value = ''
  results.value = []
}

/** Remove locally (selected or attached UI only) */
function removeLocal(item, list = 'selected') {
  const confirmed = window.confirm('¿Eliminar este video de la lista?')
  if (!confirmed) return

  if (list === 'selected') {
    selected.value = selected.value.filter(v => v.id !== item.id)
    setAlert('success', 'Video eliminado de la selección.')
  } else {
    attached.value = attached.value.filter(v => v.id !== item.id)
    setAlert('success', 'Video eliminado de la vista (no persistido).')
  }
}

/** Save new selected attachments */
async function saveSelection() {
  if (!selected.value.length || saving.value) return
  saving.value = true
  clearAlert()

  const payload = {
    course_id: props.lesson.course_id,
    videos: selected.value.map(v => v.id)
  }

  try {
    const { data, status } = await axios.post(
      route('admin.lessons.videos.attach', { lesson: props.lesson.id }),
      payload
    )

    if (status === 201 || data?.status === 'ok') {
      // move selected -> attached as persisted
      selected.value.forEach(v => {
        attached.value.push({
          ...v,
          pivot: { order: null, active: true },
          _persisted: true
        })
      })
      selected.value = []
      setAlert('success', 'Selección guardada correctamente.')

      // Opcional: refrescar datos en el padre
      if (props.refreshOnAttach) emit('attached')
    } else {
      setAlert('danger', 'No se pudo guardar la selección.')
      console.error('Unexpected response:', status, data)
    }
  } catch (e) {
    setAlert('danger', 'Ocurrió un error al guardar la selección.')
    console.error('attachVideos error:', e?.response?.status, e?.response?.data || e)
  } finally {
    saving.value = false
  }
}

/** Detach persisted item (server) */
async function detachPersisted(item) {
  const confirmed = window.confirm('¿Eliminar este video de la lección?')
  if (!confirmed) return

  clearAlert()
  try {
    await axios.delete(route('admin.lessons.videos.detach', { lesson: props.lesson.id, video: item.id }))
    attached.value = attached.value.filter(v => v.id !== item.id)
    setAlert('success', 'Video eliminado correctamente.')
  } catch (e) {
    setAlert('danger', 'No se pudo eliminar el video.')
  }
}

/** Drag & drop: persist new order */
async function persistOrder() {
  // enviar arreglo de ids en su nuevo orden
  try {
    const orderedIds = attached.value.map(v => v.id)
    await axios.post(route('admin.lessons.videos.reorder', { lesson: props.lesson.id }), {
      videos: orderedIds
    })
    setAlert('success', 'Orden actualizado.')
  } catch (e) {
    setAlert('danger', 'No se pudo actualizar el orden.')
  }
}

/** Alerts helpers */
function setAlert(type, message) {
  alert.value.type = type
  alert.value.message = message
  // auto-hide after 4s
  setTimeout(() => clearAlert(), 4000)
}
function clearAlert() {
  alert.value = { type: '', message: '' }
}
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
      <strong>Asignar videos a la lección</strong>
      <div class="text-muted small">Curso: #{{ lesson.course_id }} · Lección: #{{ lesson.id }}</div>
    </div>

    <div class="card-body">

      <!-- Alerts -->
      <div v-if="alert.message" class="mb-3">
        <div
          class="alert py-2 px-3"
          :class="{
            'alert-success': alert.type === 'success',
            'alert-danger': alert.type === 'danger'
          }"
          role="alert"
        >
          {{ alert.message }}
        </div>
      </div>

      <!-- Search + Save row -->
      <div class="row g-2 align-items-end mb-3">
        <div class="col-12 col-md-8 position-relative">
          <label class="form-label small text-muted">Buscar videos por título</label>
          <input
            type="text"
            class="form-control"
            placeholder="Escribe para buscar..."
            v-model="query"
          />
          <div v-if="loading" class="form-text">Buscando...</div>

          <!-- Autocomplete results -->
          <div
            v-if="results.length"
            class="list-group position-absolute w-100 mt-1"
            style="z-index: 1050; max-height: 240px; overflow-y: auto;"
          >
            <button
              v-for="r in results"
              :key="r.id"
              type="button"
              class="list-group-item list-group-item-action d-flex align-items-center"
              @click="pick(r)"
            >
              <img v-if="r.image" :src="r.image" alt="" class="me-2 rounded" style="width:44px;height:28px;object-fit:cover;">
              <div class="flex-fill">
                <div class="fw-semibold">{{ r.title }}</div>
                <div class="small text-muted">
                  {{ humanDuration(r.duration) }}
                  <span v-if="r.duration && r.size"> · </span>
                  {{ humanSize(r.size) }}
                </div>
              </div>
              <i class="bi bi-plus-circle ms-2"></i>
            </button>
          </div>
        </div>

        <div class="col-12 col-md-4 text-md-end">
          <label class="form-label small text-muted d-block">&nbsp;</label>
          <button class="btn btn-primary" :disabled="saving || !selected.length" @click="saveSelection">
            <span v-if="saving" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            <i v-else class="bi bi-save me-2"></i>
            Guardar selección
          </button>
        </div>
      </div>

      <!-- Selected (pending attach) -->
      <div v-if="selected.length" class="mb-4">
        <h6 class="fw-bold">Por adjuntar</h6>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width:80px;">Imagen</th>
                <th>Título</th>
                <th style="width:160px;">Duración</th>
                <th style="width:80px;"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="v in selected" :key="`sel-${v.id}`">
                <td>
                  <img v-if="v.image" :src="v.image" alt="" class="rounded" style="width:70px;height:44px;object-fit:cover;">
                </td>
                <td class="fw-semibold">{{ v.title }}</td>
                <td>{{ humanDuration(v.duration) }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger" @click="removeLocal(v, 'selected')">
                    <i class="bi bi-trash me-1"></i> Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Attached (persisted) with drag & drop -->
      <div>
        <div class="d-flex align-items-center justify-content-between">
          <h6 class="fw-bold mb-0">Videos asignados</h6>
          <small class="text-muted" v-if="attached.length">Arrastra para reordenar</small>
        </div>

        <div v-if="attached.length" class="table-responsive mt-2">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width:40px;"></th> <!-- drag handle -->
                <th style="width:80px;">Imagen</th>
                <th>Título</th>
                <th style="width:160px;">Duración</th>
                <th style="width:80px;"></th>
              </tr>
            </thead>

            <Draggable
              tag="tbody"
              v-model="attached"
              item-key="id"
              handle=".drag-handle"
              @end="persistOrder"
            >
              <template #item="{ element: v }">
                <tr :key="`att-${v.id}`">
                  <td class="text-muted">
                    <i class="bi bi-grip-vertical drag-handle" style="cursor:grab;"></i>
                  </td>
                  <td>
                    <img v-if="v.image" :src="v.image" alt="" class="rounded" style="width:70px;height:44px;object-fit:cover;">
                  </td>
                  <td class="fw-semibold">{{ v.title }}</td>
                  <td>{{ humanDuration(v.duration) }}</td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-danger" @click="detachPersisted(v)">
                      <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                  </td>
                </tr>
              </template>
            </Draggable>
          </table>
        </div>

        <div v-else class="text-muted">Aún no hay videos asignados a esta lección.</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.list-group-item { cursor: pointer; }
/* Mejoras de foco para accesibilidad */
.list-group-item:focus { outline: 2px solid #0d6efd33; }
/* Cursor para la manija de drag */
.drag-handle { cursor: grab; }
</style>
