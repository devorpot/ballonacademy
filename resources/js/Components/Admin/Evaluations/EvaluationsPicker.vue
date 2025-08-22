<!-- resources/js/Components/Admin/Lessons/EvaluationPicker.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'
import Draggable from 'vuedraggable'

const props = defineProps({
  // Se espera lesson con: { id, course_id, evaluations?: [...] }
  lesson: { type: Object, required: true },
  refreshOnAttach: { type: Boolean, default: true }
})

const emit = defineEmits(['attached'])

/** UI state */
const query   = ref('')
const loading = ref(false)
const saving  = ref(false)
const results = ref([])
const alert   = ref({ type: '', message: '' }) // 'success' | 'danger' | ''

/** Inicializar "attached" desde props (persistidos) */
const attached = ref(
  Array.isArray(props.lesson?.evaluations)
    ? props.lesson.evaluations.map(e => ({
        id: e.id,
        title: e.title,
        status: e.status ?? null,
        pivot: {
          order: e.pivot?.order ?? null,
          active: Boolean(e.pivot?.active)
        },
        _persisted: true
      }))
    : []
)

/** Selección nueva (no persistida aún) */
const selected = ref([])

/** Evitar duplicados entre attached & selected */
const allEvalIds = computed(() => new Set([
  ...attached.value.map(e => e.id),
  ...selected.value.map(e => e.id),
]))

/** Debounce búsqueda */
let debouncing
watch(query, () => {
  if (debouncing) clearTimeout(debouncing)
  debouncing = setTimeout(fetchResults, 300)
})

/** Fetch autocomplete (por curso) */
async function fetchResults() {
  const q = query.value.trim()
  if (!props.lesson?.course_id) {
    results.value = []
    return
  }
  loading.value = true
  try { 
    // Ajusta este nombre de ruta a tu endpoint real de búsqueda de evaluaciones por curso
    const { data } = await axios.get(
      route('admin.evaluations.by-course', { course: props.lesson.course_id }),
      { params: { q, lesson_id: props.lesson.id } }
    )
    const skip = allEvalIds.value
    // Se espera data.data como arreglo [{id,title,status}]
    results.value = (data?.data || []).filter(e => !skip.has(e.id))
  } catch (e) {
    results.value = []
  } finally {
    loading.value = false
  }
}

function pick(evaluation) {
  if (allEvalIds.value.has(evaluation.id)) return
  selected.value.push({ ...evaluation, _persisted: false })
  // limpiar y cerrar el listado para una UX clara
  query.value = ''
  results.value = []
}

/** Remover localmente (selected o attached solo en UI) */
function removeLocal(item, list = 'selected') {
  const confirmed = window.confirm('¿Eliminar esta evaluación de la lista?')
  if (!confirmed) return

  if (list === 'selected') {
    selected.value = selected.value.filter(e => e.id !== item.id)
    setAlert('success', 'Evaluación eliminada de la selección.')
  } else {
    attached.value = attached.value.filter(e => e.id !== item.id)
    setAlert('success', 'Evaluación eliminada de la vista (no persistido).')
  }
}

/** Guardar nuevas asignaciones */
async function saveSelection() {
  if (!selected.value.length || saving.value) return
  saving.value = true
  clearAlert()

  const payload = {
    course_id: props.lesson.course_id,
    evaluations: selected.value.map(e => e.id)
  }

  try {
    const { data, status } = await axios.post(
      route('admin.lessons.evaluations.attach', { lesson: props.lesson.id }),
      payload
    )

    if (status === 201 || data?.status === 'ok') {
      // mover selected -> attached como persistidos
      selected.value.forEach(e => {
        attached.value.push({
          ...e,
          pivot: { order: null, active: true },
          _persisted: true
        })
      })
      selected.value = []
      setAlert('success', 'Selección guardada correctamente.')

      if (props.refreshOnAttach) emit('attached')
    } else {
      setAlert('danger', 'No se pudo guardar la selección.')
      console.error('Unexpected response:', status, data)
    }
  } catch (e) {
    setAlert('danger', 'Ocurrió un error al guardar la selección.')
    console.error('attachEvaluations error:', e?.response?.status, e?.response?.data || e)
  } finally {
    saving.value = false
  }
}

/** Desasignar persistido (server) */
async function detachPersisted(item) {
  const confirmed = window.confirm('¿Eliminar esta evaluación de la lección?')
  if (!confirmed) return

  clearAlert()
  try {
    await axios.delete(
      route('admin.lessons.evaluations.detach', { lesson: props.lesson.id, evaluation: item.id })
    )
    attached.value = attached.value.filter(e => e.id !== item.id)
    setAlert('success', 'Evaluación eliminada correctamente.')
  } catch (e) {
    setAlert('danger', 'No se pudo eliminar la evaluación.')
  }
}
 
/** Drag & drop: persistir nuevo orden */
async function persistOrder() {
  try {
    const orderedIds = attached.value.map(e => e.id)
    await axios.post(
      route('admin.lessons.evaluations.reorder', { lesson: props.lesson.id }),
      { evaluations: orderedIds }
    )
    setAlert('success', 'Orden actualizado.')
  } catch (e) {
    setAlert('danger', 'No se pudo actualizar el orden.')
  }
}

/** Alerts helpers */
function setAlert(type, message) {
  alert.value.type = type
  alert.value.message = message
  setTimeout(() => clearAlert(), 4000)
}
function clearAlert() {
  alert.value = { type: '', message: '' }
}
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
      <strong>Asignar evaluaciones a la lección</strong>
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
          <label class="form-label small text-muted">Buscar evaluaciones por título</label>
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
              <div class="flex-fill">
                <div class="fw-semibold">{{ r.title }}</div>
                <div class="small text-muted">
                  <span v-if="r.status">Estado: {{ r.status }}</span>
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
                <th>Título</th>
                <th style="width:160px;">Estado</th>
                <th style="width:80px;"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in selected" :key="`sel-${e.id}`">
                <td class="fw-semibold">{{ e.title }}</td>
                <td>{{ e.status ?? '—' }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger" @click="removeLocal(e, 'selected')">
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
          <h6 class="fw-bold mb-0">Evaluaciones asignadas</h6>
          <small class="text-muted" v-if="attached.length">Arrastra para reordenar</small>
        </div>

        <div v-if="attached.length" class="table-responsive mt-2">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width:40px;"></th> <!-- drag handle -->
                <th>Título</th>
                <th style="width:160px;">Estado</th>
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
              <template #item="{ element: e }">
                <tr :key="`att-${e.id}`">
                  <td class="text-muted">
                    <i class="bi bi-grip-vertical drag-handle" style="cursor:grab;"></i>
                  </td>
                  <td class="fw-semibold">{{ e.title }}</td>
                  <td>{{ e.status ?? '—' }}</td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-danger" @click="detachPersisted(e)">
                      <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                  </td>
                </tr>
              </template>
            </Draggable>
          </table>
        </div>

        <div v-else class="text-muted">Aún no hay evaluaciones asignadas a esta lección.</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.list-group-item { cursor: pointer; }
.list-group-item:focus { outline: 2px solid #0d6efd33; }
.drag-handle { cursor: grab; }
</style>
