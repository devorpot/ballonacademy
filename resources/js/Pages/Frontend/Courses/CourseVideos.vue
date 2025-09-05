<!-- resources/js/Pages/Frontend/Courses/VideosFlat.vue -->
<template>
  <Head :title="`Videos de ${course.title}`" />

  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Mis Cursos', route: 'dashboard.courses.index' },
        { label: `${course.title} — Videos`, route: '' }
      ]"
    />

    <!-- Cabecera -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row align-items-center g-2">
          <div class="col-12 col-lg-8">
            <h3 class="mb-1">{{ course.title }}</h3>
            <small class="text-muted">
              {{ summary.completed }} / {{ summary.total }} videos completados
            </small>
            <div class="progress mt-2" style="height: 6px;">
              <div
                class="progress-bar"
                :style="{ width: progressPct + '%' }"
                :class="progressClass"
                role="progressbar"
              />
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="input-group mt-2 mt-lg-0">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input
                v-model="q"
                type="search"
                class="form-control"
                placeholder="Buscar por título o lección..."
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Grid de videos -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div v-if="!videos?.length" class="alert alert-info">
          Aún no hay videos disponibles en este curso.
        </div>

        <div v-else class="row g-3">
          <div
            v-for="v in filtered"
            :key="`${v.lesson_id}-${v.id}`"
            class="col-12 col-sm-6 col-lg-4 col-xl-3"
          >
            <div class="card h-100 shadow-sm">
              <div class="ratio ratio-16x9 card-img-top bg-light">
                <img
                  class="img-fluid rounded-top object-fit-cover"
                  :src="v.thumbnail ? `/storage/${v.thumbnail}` : 'https://placehold.co/600x338'"
                  :alt="v.title"
                />
              </div>

              <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start gap-2 mb-1">
                  <i
                    class="bi fs-5"
                    :class="v.is_ended ? 'bi-check2-circle text-success' : (v.is_accessible ? 'bi-play-btn text-primary' : 'bi-lock-fill text-warning')"
                    :title="videoTooltip(v)"
                  />
                  <h6 class="card-title mb-0 flex-grow-1">
                    {{ v.title }}
                  </h6>
                </div>

                <small class="text-muted d-block mb-2">
                  Lección {{ (v.lesson_order ?? 0).toString().padStart(2,'0') }}
                  <span class="text-body-secondary">·</span>
                  {{ v.lesson_title }}
                </small>

                <p v-if="v.description" class="text-muted small mb-3">
                  {{ truncate(v.description, 120) }}
                </p>

                <div class="mt-auto d-flex align-items-center justify-content-between">
                  <small v-if="v.duration" class="text-muted">
                    <i class="bi bi-clock me-1"></i>{{ v.duration }}
                  </small>

                  <div class="ms-auto">
                    <Link
                      v-if="v.is_accessible"
                      :href="route('dashboard.courses.lessons.videos.show', {
                        course: course.id,
                        lesson: v.lesson_id,
                        video: v.id
                      })"
                      class="btn btn-primary btn-sm  px-3"
                    >
                      Ver video
                    </Link>
                    <button
                      v-else
                      class="btn btn-outline-secondary btn-sm  px-3"
                      disabled
                      title="Bloqueado hasta terminar los anteriores"
                    >
                      Bloqueado
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div> <!-- /col -->
        </div> <!-- /row -->
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'
import { route } from 'ziggy-js'
import { computed, ref } from 'vue'

const props = defineProps({
  course: Object,
  videos: Array,
  summary: { type: Object, default: () => ({ completed: 0, total: 0 }) }
})

const q = ref('')

const progressPct = computed(() => {
  const t = props.summary?.total || 0
  const d = props.summary?.completed || 0
  if (!t) return 0
  return Math.min(100, Math.round((d / t) * 100))
})
const progressClass = computed(() => {
  if (progressPct.value >= 100) return 'bg-success'
  if (progressPct.value >= 50) return 'bg-primary'
  return 'bg-secondary'
})

const filtered = computed(() => {
  const term = q.value.trim().toLowerCase()
  if (!term) return props.videos || []
  return (props.videos || []).filter(v =>
    (v.title || '').toLowerCase().includes(term) ||
    (v.lesson_title || '').toLowerCase().includes(term)
  )
})

function truncate(str, n) {
  if (!str) return ''
  return str.length > n ? str.slice(0, n - 1) + '…' : str
}
function videoTooltip(v) {
  if (v.is_ended) return 'Completado'
  if (!v.is_accessible) return 'Bloqueado hasta terminar los anteriores'
  return 'Disponible'
}
</script>

<style scoped>
.object-fit-cover { object-fit: cover; }
.card-title { line-height: 1.2; }
</style>
