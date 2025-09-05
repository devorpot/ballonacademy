<template>
  <teleport to="body">
    <div v-if="modelValue" class="modal-backdrop-custom" role="presentation" @click.self="close">
      <div
        class="modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lessonCompleteTitle"
      >
        <button type="button" class="btn-close position-absolute end-0 m-3" aria-label="Cerrar" @click="close" />

        <div class="row g-0">
          <!-- Imagen -->
          <div class="col-12 col-md-6">
            <img
              :src="imageUrl"
              class="w-100 h-100 object-fit-cover rounded-start"
              alt="Curso finalizado"
              loading="lazy"
            />
          </div>

          <!-- Contenido -->
          <div class="col-12 col-md-6 d-flex">
            <div class="p-4 d-flex flex-column justify-content-center w-100">
              <h3 id="lessonCompleteTitle" class="fw-bold mb-2">
                Felicidades, has finalizado el curso
              </h3>

              <p class="text-muted mb-3">
                Espera los resultados de tus evaluaciones para descargar el certificado del curso
                <span class="fw-semibold">“{{ courseTitle }}”</span>.


              </p>

              <p>Puedes volver a ver los videos las veces que quieras</p>

              <div class="d-flex gap-2 mt-2">
                <button class="btn btn-primary" @click="close">Entendido</button>
                <a
                  v-if="evaluationsUrl"
                  :href="evaluationsUrl"
                  class="btn btn-outline-secondary"
                >
                  Ver evaluaciones
                </a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </teleport>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

const emit = defineEmits(['update:modelValue'])
const props = defineProps({
  modelValue: { type: Boolean, default: false }, // v-model
  courseId:   { type: [Number, String], required: true },
  courseTitle:{ type: String, required: true },
  imageUrl:   { type: String, default: 'https://placehold.co/600x800' }, // placeholder
  showEvaluationsLink: { type: Boolean, default: true },
})

const evaluationsUrl = props.showEvaluationsLink
  ? route('dashboard.courses.evaluations.index', { course: props.courseId })
  : null

function close() {
  emit('update:modelValue', false)
}

function onKey(e) {
  if (e.key === 'Escape') close()
}

onMounted(() => document.addEventListener('keydown', onKey))
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(33, 37, 41, 0.6);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 1080;
}
.modal-card {
  width: 100%;
  max-width: 880px;
  background: var(--bs-body-bg);
  color: var(--bs-body-color);
  border-radius: .75rem;
  box-shadow: 0 20px 50px rgba(0,0,0,.2);
  overflow: hidden;
  position: relative;
}
.object-fit-cover { object-fit: cover; height: 100%; min-height: 300px; }
.btn-close { outline: none; }
@media (max-width: 767.98px) {
  .modal-card { max-width: 96vw; }
}
</style>
