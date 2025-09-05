<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'
 
const props = defineProps({
  course: { type: Object, required: true }, // { id, title }
  groups: {
    type: Object,
    required: true,
    // {
    //   course: [Evaluation...],         // type=1
    //   video:  [Evaluation...],         // type=2
    //   lesson: [{ lesson_title, evaluations: [Evaluation...] }], // type=3
    // }
  },
  canSubmitEvaluation: { type: Boolean, default: true },
  videos: { type: Array, default: () => [] },
})

const retryingId = ref(null)

function statusBadgeClass(status) {
  if (status === 999) return 'bg-success'
  if (status === 333) return 'bg-danger'
  if (status === 222) return 'bg-warning text-dark'
  if (status === 111) return 'bg-info text-dark'
  return 'bg-secondary'
}

function statusLabel(status, hasLast) {
  if (!hasLast) return 'No realizada'
  if (status === 999) return 'Aprobada'
  if (status === 333) return 'No aprobada'
  if (status === 222) return 'En revisión'
  if (status === 111) return 'Enviada'
  return 'Sin estado'
}

function previewHref(eva) {
  return route('dashboard.courses.evaluations.evaluation.preview', {
    course: eva.course_id,
    evaluation: eva.id,
  })
}

async function retryEvaluation(eva) {
  if (!confirm('Esto eliminará tu envío anterior. ¿Deseas continuar?')) return
  try {
    retryingId.value = eva.id
    await router.post(
      route('dashboard.courses.evaluations.retry', {
        course: eva.course_id,
        evaluation: eva.id,
      }),
      {},
      { preserveScroll: true }
    )
  } finally {
    retryingId.value = null
  }
}

const hasCourseEvals = computed(() => props.groups?.course?.length > 0)
const hasVideoEvals  = computed(() => props.groups?.video?.length > 0)
const hasLessonEvals = computed(() => props.groups?.lesson?.length > 0)
</script>

<template>
  <Head title="Evaluaciones" />
  <StudentLayout>
    <SectionHeader title="Cursos Inscritos" />
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Cursos', route: 'dashboard.index' },
        { label: course.title, route: '' },
        { label: 'Evaluaciones', route: '' },
      ]"
    />

    <section class="section-panel my-3">
     <div class="container-fluid">
        <div class="accordion" id="evaluationsAccordion">

        <!-- ITEM 1: Curso -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingCourse">
            <button
              class="accordion-button"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseCourse"
              aria-expanded="true"
              aria-controls="collapseCourse"
            >
              <i class="bi bi-mortarboard text-primary me-2"></i>
              <span class="me-2">Evaluaciones del curso</span>
              <span v-if="hasCourseEvals" class="badge bg-secondary">{{ groups.course.length }}</span>
            </button>
          </h2>
          <div
            id="collapseCourse"
            class="accordion-collapse collapse show"
            aria-labelledby="headingCourse"
            data-bs-parent="#evaluationsAccordion"
          >
            <div class="accordion-body">
              <div v-if="!hasCourseEvals" class="alert alert-secondary mb-0">
                No hay evaluaciones de curso.
              </div>

              <div v-else class="container-fluid">
                <div class="row g-3">
                  <div
                    v-for="eva in groups.course"
                    :key="`course-${eva.id}`"
                    class="col-12 col-md-6 col-lg-4"
                  >
                    <div class="card shadow-sm h-100">
                      <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                          <h5 class="card-title mb-0 me-2 text-truncate">{{ eva.title }}</h5>
                          <span class="badge " :class="statusBadgeClass(eva?.last_evaluation_user?.status)">
                            {{ statusLabel(eva?.last_evaluation_user?.status, !!eva?.last_evaluation_user) }}
                          </span>
                        </div>

                        <div class="small text-muted mb-2">
                          <i class="bi bi-calendar-event me-1"></i> Publicada: {{ eva.eva_send_date || '—' }}
                        </div>

                        <div class="mb-3">
                          <strong>Comentarios:</strong>
                          <div class="text-body-secondary">
                            {{ eva.eva_comments || 'Sin comentarios' }}
                          </div>
                        </div>

                        <div class="mt-auto d-flex gap-2">
                          <template v-if="eva.user_has_evaluated && eva.last_evaluation_user && eva.last_evaluation_user.status === 999">
                            <Link class="btn btn-primary btn-sm w-100" :href="previewHref(eva)">
                              <i class="bi bi-eye"></i> Ver resultados
                            </Link>
                          </template>
                          <template v-else-if="eva.user_has_evaluated && eva.last_evaluation_user">
                            <button
                              class="btn btn-warning btn-sm text-white fw-bold w-100"
                              @click="retryEvaluation(eva)"
                              :disabled="retryingId === eva.id"
                            >
                              <span v-if="retryingId === eva.id" class="spinner-border spinner-border-sm me-2"></span>
                              <i class="bi bi-arrow-repeat"></i> Reintentar
                            </button>
                          </template>
                          <template v-else>
                            <Link class="btn btn-success btn-sm fw-bold w-100" :href="previewHref(eva)">
                              <i class="bi bi-pencil-square"></i> Realizar
                            </Link>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div> <!-- col -->
                </div> <!-- row -->
              </div> <!-- container-fluid -->
            </div> <!-- accordion-body -->
          </div> <!-- collapse -->
        </div> <!-- accordion-item -->

        <!-- ITEM 2: Video -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingVideo">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseVideo"
              aria-expanded="false"
              aria-controls="collapseVideo"
            >
              <i class="bi bi-camera-reels text-primary me-2"></i>
              <span class="me-2">Evaluaciones por video</span>
              <span v-if="hasVideoEvals" class="badge bg-secondary">{{ groups.video.length }}</span>
            </button>
          </h2>
          <div
            id="collapseVideo"
            class="accordion-collapse collapse"
            aria-labelledby="headingVideo"
            data-bs-parent="#evaluationsAccordion"
          >
            <div class="accordion-body">
              <div v-if="!hasVideoEvals" class="alert alert-secondary mb-0">
                No hay evaluaciones por video.
              </div>

              <div v-else class="container-fluid">
                <div class="row g-3">
                  <div
                    v-for="eva in groups.video"
                    :key="`video-${eva.id}`"
                    class="col-12 col-md-6 col-lg-4"
                  >
                    <div class="card shadow-sm h-100">
                      <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                          <h5 class="card-title mb-0 me-2 text-truncate">{{ eva.title }}</h5>
                          <span class="badge " :class="statusBadgeClass(eva?.last_evaluation_user?.status)">
                            {{ statusLabel(eva?.last_evaluation_user?.status, !!eva?.last_evaluation_user) }}
                          </span>
                        </div>

                        <div class="small text-muted mb-1">
                          <i class="bi bi-film me-1"></i> Video: {{ eva.video?.title || `#${eva.video_id}` }}
                        </div>
                        <div class="small text-muted mb-2">
                          <i class="bi bi-calendar-event me-1"></i> Publicada: {{ eva.eva_send_date || '—' }}
                        </div>

                        <div class="mb-3">
                          <strong>Comentarios:</strong>
                          <div class="text-body-secondary">
                            {{ eva.eva_comments || 'Sin comentarios' }}
                          </div>
                        </div>

                        <div class="mt-auto d-flex gap-2">
                          <template v-if="eva.user_has_evaluated && eva.last_evaluation_user && eva.last_evaluation_user.status === 999">
                            <Link class="btn btn-primary btn-sm w-100" :href="previewHref(eva)">
                              <i class="bi bi-eye"></i> Ver resultados
                            </Link>
                          </template>
                          <template v-else-if="eva.user_has_evaluated && eva.last_evaluation_user">
                            <button
                              class="btn btn-warning btn-sm text-white fw-bold w-100"
                              @click="retryEvaluation(eva)"
                              :disabled="retryingId === eva.id"
                            >
                              <span v-if="retryingId === eva.id" class="spinner-border spinner-border-sm me-2"></span>
                              <i class="bi bi-arrow-repeat"></i> Reintentar
                            </button>
                          </template>
                          <template v-else>
                            <Link class="btn btn-success btn-sm fw-bold w-100" :href="previewHref(eva)">
                              <i class="bi bi-pencil-square"></i> Realizar
                            </Link>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div> <!-- col -->
                </div> <!-- row -->
              </div> <!-- container-fluid -->
            </div> <!-- accordion-body -->
          </div> <!-- collapse -->
        </div> <!-- accordion-item -->

        <!-- ITEM 3: Lección -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingLesson">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapseLesson"
              aria-expanded="false"
              aria-controls="collapseLesson"
            >
              <i class="bi bi-journal-text text-primary me-2"></i>
              <span class="me-2">Evaluaciones por lección</span>
              <span v-if="hasLessonEvals" class="badge bg-secondary">{{ groups.lesson.length }}</span>
            </button>
          </h2>
          <div
            id="collapseLesson"
            class="accordion-collapse collapse"
            aria-labelledby="headingLesson"
            data-bs-parent="#evaluationsAccordion"
          >
            <div class="accordion-body">

              <div v-if="!hasLessonEvals" class="alert alert-secondary mb-0">
                No hay evaluaciones por lección.
              </div>

              <div v-else class="accordion" id="lessonInnerAccordion">
                <div
                  class="accordion-item"
                  v-for="(group, idx) in groups.lesson"
                  :key="`lesson-${idx}`"
                >
                  <h2 class="accordion-header" :id="`heading-lesson-${idx}`">
                    <button
                      class="accordion-button"
                      type="button"
                      data-bs-toggle="collapse"
                      :data-bs-target="`#collapse-lesson-${idx}`"
                      :aria-controls="`collapse-lesson-${idx}`"
                      :aria-expanded="idx === 0 ? 'true' : 'false'"
                    >
                      {{ group.lesson_title || 'Sin lección' }}
                      <span class="badge bg-secondary ms-2">{{ group.evaluations?.length || 0 }}</span>
                    </button>
                  </h2>

                  <div
                    :id="`collapse-lesson-${idx}`"
                    class="accordion-collapse collapse"
                    :class="{ show: idx === 0 }"
                    :aria-labelledby="`heading-lesson-${idx}`"
                    data-bs-parent="#lessonInnerAccordion"
                  >
                    <div class="accordion-body">
                      <div class="container-fluid">
                        <div class="row g-3">
                          <div
                            v-for="eva in group.evaluations"
                            :key="`lesson-eva-${eva.id}`"
                            class="col-12 col-md-6 col-lg-4"
                          >
                            <div class="card shadow-sm h-100">
                              <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                  <h5 class="card-title mb-0 me-2 text-truncate">{{ eva.title }}</h5>
                                  <span class="badge " :class="statusBadgeClass(eva?.last_evaluation_user?.status)">
                                    {{ statusLabel(eva?.last_evaluation_user?.status, !!eva?.last_evaluation_user) }}
                                  </span>
                                </div>

                                <div class="small text-muted mb-2">
                                  <i class="bi bi-calendar-event me-1"></i> Publicada: {{ eva.eva_send_date || '—' }}
                                </div>

                                <div class="mb-3">
                                  <strong>Comentarios:</strong>
                                  <div class="text-body-secondary">
                                    {{ eva.eva_comments || 'Sin comentarios' }}
                                  </div>
                                </div>

                                <div class="mt-auto d-flex gap-2">
                                  <template v-if="eva.user_has_evaluated && eva.last_evaluation_user && eva.last_evaluation_user.status === 999">
                                    <Link class="btn btn-primary btn-sm w-100" :href="previewHref(eva)">
                                      <i class="bi bi-eye"></i> Ver resultados
                                    </Link>
                                  </template>
                                  <template v-else-if="eva.user_has_evaluated && eva.last_evaluation_user">
                                    <button
                                      class="btn btn-warning btn-sm text-white fw-bold w-100"
                                      @click="retryEvaluation(eva)"
                                      :disabled="retryingId === eva.id"
                                    >
                                      <span v-if="retryingId === eva.id" class="spinner-border spinner-border-sm me-2"></span>
                                      <i class="bi bi-arrow-repeat"></i> Reintentar
                                    </button>
                                  </template>
                                  <template v-else>
                                    <Link class="btn btn-success btn-sm fw-bold w-100" :href="previewHref(eva)">
                                      <i class="bi bi-pencil-square"></i> Realizar
                                    </Link>
                                  </template>
                                </div>
                              </div>
                            </div>
                          </div> <!-- col -->
                        </div> <!-- row -->
                      </div> <!-- container-fluid -->
                    </div> <!-- accordion-body -->
                  </div> <!-- collapse -->
                </div> <!-- accordion-item (inner) -->
              </div> <!-- accordion inner -->

            </div> <!-- accordion-body -->
          </div> <!-- collapse -->
        </div> <!-- accordion-item -->

      </div> <!-- accordion root -->
     </div>
    </section>
  </StudentLayout>
</template>
