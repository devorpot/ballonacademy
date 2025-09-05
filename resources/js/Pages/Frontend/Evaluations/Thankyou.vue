<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({
  evaluation: { type: Object, required: true }
})

const isOpenDetails = ref(true)

// Campos con fallback seguros
const evalTitle  = computed(() => props.evaluation?.title ?? 'Evaluación')
const course     = computed(() => props.evaluation?.course ?? null)
const courseName = computed(() => course.value?.title ?? 'Curso')
const sentAt     = computed(() => new Date().toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' }))
</script>

<template>
  <Head title="¡Evaluación Enviada!" />
  <StudentLayout>


    <div class="thankyou-hero py-5">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-8">

            <!-- Icono de éxito -->
            <div class="success-wrap mx-auto mb-4">
              <i class="bi bi-check2-circle"></i>
              <span class="pulse"></span>
            </div>



            <h1 class="fw-bold mb-3 title">¡Gracias por enviar tu evaluación!</h1>
            <p class="lead text-body-tertiary mb-4">
              Hemos recibido tus respuestas. Tu instructor revisará tu envío y te notificará el resultado.
            </p>

            <!-- Acciones principales -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
              <Link :href="route('dashboard.courses.index')" class="btn btn-primary btn-lg">
                Volver a Mis Cursos
              </Link>
              <Link :href="route('dashboard.index')" class="btn btn-outline-secondary btn-lg">
                Ir al Inicio
              </Link>
            </div>

            <!-- Detalles del envío (toggle) -->
            <div class="details card shadow-sm mx-auto">
              <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-journal-check text-primary"></i>
                  <span class="fw-semibold">Detalles del envío</span>
                </div>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center"
                  @click="isOpenDetails = !isOpenDetails"
                  :aria-expanded="isOpenDetails"
                  aria-controls="details-body"
                  :title="isOpenDetails ? 'Ocultar' : 'Mostrar'"
                >
                  <i v-if="isOpenDetails" class="bi bi-chevron-down"></i>
                  <i v-else class="bi bi-dash-lg"></i>
                </button>
              </div>

              <Transition name="fade">
                <div v-show="isOpenDetails" id="details-body" class="card-body text-start">
                  <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Evaluación</dt>
                    <dd class="col-sm-8">{{ evalTitle }}</dd>

                    <dt class="col-sm-4 text-muted">Curso</dt>
                    <dd class="col-sm-8">{{ courseName }}</dd>

                    <dt class="col-sm-4 text-muted">Fecha de envío</dt>
                    <dd class="col-sm-8">{{ sentAt }}</dd>

                    <dt class="col-sm-4 text-muted">Estado</dt>
                    <dd class="col-sm-8">
                      En revisión
                      <span class="badge bg-info-subtle text-info-emphasis ms-2">Pendiente de revisión</span>
                    </dd>
                  </dl>
                  <hr class="my-3" />
                  <p class="mb-0 text-muted">
                    Recibirás una notificación en tu panel y/o correo electrónico cuando haya una actualización.
                  </p>
                </div>
              </Transition>
            </div>

          </div>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>

<style scoped>
.thankyou-hero {
  background: radial-gradient(1200px 500px at 50% -20%, #e6f3ff 0%, transparent 60%), #fff;
  min-height: calc(100dvh - 66px);
  display: flex;
  align-items: center;
}

.title { color: #1B8FD5; }

/* Icono de éxito con pulso */
.success-wrap {
  width: 88px; height: 88px;
  border-radius: 50%;
  background: #e8f5ff;
  display: grid; place-items: center;
  position: relative;
}
.success-wrap i {
  font-size: 2.75rem;
  color: #1B8FD5;
}
.success-wrap .pulse {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(27, 143, 213, 0.35);
  animation: pulse 1.8s ease-out infinite;
}
@keyframes pulse {
  0%   { box-shadow: 0 0 0 0 rgba(27, 143, 213, 0.35); }
  70%  { box-shadow: 0 0 0 18px rgba(27, 143, 213, 0); }
  100% { box-shadow: 0 0 0 0 rgba(27, 143, 213, 0); }
}

/* Tarjeta de detalles */
.details { max-width: 720px; }
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
