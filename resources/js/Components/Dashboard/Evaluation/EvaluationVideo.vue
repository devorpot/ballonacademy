<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  evaluation: { type: Object, required: true }, // id, course_id
  lesson: Object,
  userHasSubmitted: { type: Boolean, default: false },
  lastEvaluationUser: { type: Object, default: null },
  lastComments: { type: String, default: '' },
  lastFiles: { type: String, default: '' }, // ruta relativa /storage
})

const ACCEPTED_MIME = ['video/mp4', 'video/webm', 'video/ogg']
const MAX_BYTES = 300 * 1024 * 1024 // 300 MB

const sending    = ref(false)
const retrying   = ref(false)
const previewUrl = ref(null)
const errorMsg   = ref('')
const comments   = ref(props.lastComments || '')
const file       = ref(null)
const uploadPct  = ref(null) // 0..100 o null

// Si existe registro previo, no se muestra el formulario
const hasPrevious = computed(() => props.userHasSubmitted && !!props.lastEvaluationUser)

// Resoluciones robustas (comentarios/archivo del último envío)
const resolvedLastComments = computed(() =>
  props.lastComments || props.lastEvaluationUser?.comments || props.lastEvaluationUser?.data?.comments || ''
)
const resolvedLastFiles = computed(() =>
  props.lastFiles || props.lastEvaluationUser?.files || props.lastEvaluationUser?.data?.files || ''
)

function pickFile(e) {
  errorMsg.value = ''
  const f = e.target.files?.[0]
  if (!f) { file.value = null; cleanupPreview(); return }
  if (!ACCEPTED_MIME.includes(f.type)) {
    errorMsg.value = 'Formato no permitido. Usa MP4, WebM u Ogg.'
    file.value = null; cleanupPreview(); return
  }
  if (f.size > MAX_BYTES) {
    errorMsg.value = `El archivo excede ${Math.round(MAX_BYTES/1024/1024)} MB.`
    file.value = null; cleanupPreview(); return
  }
  file.value = f
  makePreview(f)
}

function makePreview(f) {
  cleanupPreview()
  try { previewUrl.value = URL.createObjectURL(f) } catch { previewUrl.value = null }
}
function cleanupPreview() {
  if (previewUrl.value) { URL.revokeObjectURL(previewUrl.value); previewUrl.value = null }
}
onBeforeUnmount(() => cleanupPreview())

function clearForm() {
  comments.value = ''
  file.value = null
  uploadPct.value = null
  cleanupPreview()
  const input = document.getElementById('videoFileInput')
  if (input) input.value = ''
}

// Eliminar intento anterior y recargar limpio (obligatorio para permitir nuevo upload)
function retryUpload() {
  if (!confirm('¿Eliminar tu evaluación para subir un nuevo video? Esta acción no se puede deshacer.')) return
  retrying.value = true
  router.delete(route('dashboard.evaluation-users.destroy-by-evaluation'), {
    data: { evaluation_id: props.evaluation.id, course_id: props.evaluation.course_id },
    onSuccess: () => window.location.reload(),
    onError: () => {
      alert('Ocurrió un error al intentar eliminar tu envío anterior.')
      retrying.value = false
    },
  })
}

// Enviar video — mismo endpoint/estructura que EvaluationForm (sin preguntas/score)
function submitVideo() {
  errorMsg.value = ''
  if (!file.value) { errorMsg.value = 'Selecciona un archivo de video antes de enviar.'; return }

  sending.value = true
  uploadPct.value = 0

  const formData = new FormData()
  formData.append('course_id', props.evaluation.course_id)
  formData.append('data', JSON.stringify({
    evaluation_id: props.evaluation.id,
    answers: {},   // sin preguntas
    score: null,   // sin puntaje
  }))
  formData.append('comments', comments.value || '')
  formData.append('files', file.value)

  router.post(route('dashboard.evaluation-users.store'), formData, {
    forceFormData: true,
    onProgress: (event) => {
      if (event?.percentage != null) uploadPct.value = event.percentage
    },
    onSuccess: () => {
      window.location.href = route('dashboard.evaluations.thankyou', { evaluation: props.evaluation.id })
    },
    onError: (errors) => {
      const first = errors?.files || errors?.comments || Object.values(errors || {})[0]
      if (first) errorMsg.value = String(first)
      sending.value = false
      uploadPct.value = null
    },
  })
}
</script>

<template>

 
  <div class="container-fluid">
    <div class="row justify-content-center">
  
      <div class="col-md-10 col-lg-8 mx-auto">
        <div class="card mb-3">
         <div class="card-body">
            <h3>{{evaluation.title}}</h3>
            <h6>Curso : {{evaluation.course.title}}</h6>
            <p><strong>Instrucciones : </strong></p>
            <p>{{evaluation.eva_comments}}</p>
         </div>
        </div>
      </div>
      <div class="col-md-10 col-lg-8">
        

        <!-- Último envío (si existe) -->
        <div v-if="hasPrevious">
          <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
              <span>Tu último envío</span>
              <span class="badge"
                    :class="{
                      'bg-success': lastEvaluationUser?.status === 999,
                      'bg-danger': lastEvaluationUser?.status === 333,
                      'bg-secondary': ![999,333].includes(lastEvaluationUser?.status)
                    }">
                {{ lastEvaluationUser?.status === 999 ? 'Aprobado' : (lastEvaluationUser?.status === 333 ? 'No aprobado' : 'En revisión') }}
              </span>
            </div>
            <div class="card-body">
              <div v-if="resolvedLastFiles" class="mb-3 text-center">
                <video v-if="/\.(mp4|webm|ogg)$/i.test(resolvedLastFiles)"
                       controls
                       style="max-width: 100%; border-radius: .5rem;"
                       :src="`/storage/${resolvedLastFiles}`"></video>

                <div v-else>
                  <a class="btn btn-outline-secondary btn-sm"
                     :href="`/storage/${resolvedLastFiles}`"
                     target="_blank"
                     download>
                    Descargar archivo
                  </a>
                </div>
              </div>

              <div v-if="resolvedLastComments" class="mb-2">
                <div class="form-label fw-bold">Comentarios enviados</div>
                <div class="border rounded p-2 bg-light">{{ resolvedLastComments }}</div>
              </div>
            </div>
          </div>

          <!-- Bloqueo + acción para permitir nuevo envío -->
          <div class="text-center mb-4">
            <div class="alert alert-warning">
              Ya tienes un envío registrado. Para subir otro video debes <strong>eliminar</strong> tu evaluación actual.
            </div>
            <button class="btn btn-outline-danger" :disabled="retrying" @click="retryUpload">
              <span v-if="retrying" class="spinner-border spinner-border-sm me-2"></span>
              Eliminar mi evaluación y subir otro video
            </button>
          </div>
        </div>

        <!-- Formulario de carga (solo si NO hay envío previo) -->
        <div class="card" v-else>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-bold">Comentarios (opcional)</label>
              <textarea v-model="comments"
                        class="form-control"
                        rows="3"
                        placeholder="Agrega notas para el revisor"></textarea>
            </div>

            <div class="mb-3">
              <label for="videoFileInput" class="form-label fw-bold">Archivo de video</label>
              <input id="videoFileInput"
                     type="file"
                     class="form-control"
                     accept="video/mp4,video/webm,video/ogg"
                     @change="pickFile" />
              <div class="form-text">
                Formatos permitidos: MP4, WebM, Ogg. Tamaño máximo {{ Math.round(MAX_BYTES/1024/1024) }} MB.
              </div>

              <!-- Previsualización local -->
              <div v-if="previewUrl" class="mt-3">
                <video :src="previewUrl" controls style="max-width: 100%; border-radius: .5rem;"></video>
              </div>
            </div>

            <div v-if="errorMsg" class="alert alert-danger py-2">{{ errorMsg }}</div>

            <!-- Progreso de subida -->
            <div v-if="uploadPct !== null" class="progress my-3">
              <div class="progress-bar" role="progressbar"
                   :style="{ width: uploadPct + '%' }"
                   :aria-valuenow="uploadPct"
                   aria-valuemin="0"
                   aria-valuemax="100">
                {{ Math.round(uploadPct) }}%
              </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
              <button type="button"
                      class="btn btn-outline-secondary"
                      :disabled="sending"
                      @click="clearForm">
                Limpiar
              </button>
              <button type="button"
                      class="btn btn-primary"
                      :disabled="sending || !file"
                      @click="submitVideo">
                <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
                Enviar video
              </button>
            </div>
          </div>
        </div>

        <div class="alert alert-info mt-3" v-if="!hasPrevious">
          Sube tu video y agrega comentarios si lo deseas. Tras enviarlo, espera la revisión del instructor.
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-header .badge { font-weight: 600; }
</style>
