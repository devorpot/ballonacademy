<template>
  <Head title="Dashboard" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[{ label: 'Dashboard', route: '#' }]"
      />

      <section class="section-panel py-3">
        <div class="container-fluid">
          <div class="row">

            <!-- Videos vistos -->
             
            <!-- Últimos alumnos -->
            <div class="col-12 col-lg-6 mb-3">
              <ActivityCard
                title="Últimos alumnos"
                icon="bi bi-people"
                :items="studentsItems"
                :rows="6"
                :all-href="routeOr('#','admin.students.index')"
                route-name="admin.students.show"
                param-key="id"
                class=""
              >
                <template #title="{ item }">
                  {{ item.user?.nickname || item.user?.name || 'Alumno' }}
                  <span v-if="item.meta" class="text-muted">· {{ item.meta }}</span>
                </template>
                <template #subtitle="{ item }">
                  {{ item.subtitle }}
                </template>
              </ActivityCard>
            </div>
                   <div class="col-12 col-lg-6 mb-3">
              <ActivityCard
                title="Envíos de Evaluaciones"
                icon="bi bi-clipboard-check"
                :items="evaluationUsersItems"
                :rows="6"
                :all-href="routeOr('#','admin.evaluations.users.index')"
                route-name="admin.evaluations.users.show"
                param-key="id"
              >
                <template #title="{ item }">
                  {{ item.user?.nickname || item.user?.name || 'Usuario' }} · {{ item.metaTitle || 'Evaluación' }}
                </template>
                <template #subtitle="{ item }">
                  {{ item.subtitle }}
                </template>
              </ActivityCard>
            </div>

            
          </div>
        </div>
      </section>

      <section class="section-panel py-3">
        <div class="container-fluid">
          <div class="row g-3">
            <!-- Actividad de Cursos -->
   

            <!-- Envíos de Evaluaciones -->
     
            <!-- Actividad general -->
            <div class="col-12">
              <ActivityCard
                title="Actividad general"
                icon="bi bi-activity"
                :items="activitiesItems"
                :rows="8"
                :all-href="routeOr('#','admin.activities.index')"
                :get-href="activityHref"
              >
                <template #title="{ item }">
                  {{ item.user?.nickname || item.user?.name || 'Usuario' }} · {{ item.metaTitle || 'Actividad' }}
                </template>
                <template #subtitle="{ item }">
                  {{ item.subtitle }}
                </template>
              </ActivityCard>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ActivityCard from '@/Components/Admin/Ui/ActivityCard.vue'

const props = defineProps({
  courseActivities: { type: Array, default: () => [] },
  evaluationUsers:  { type: Array, default: () => [] },
  videoActivities:  { type: Array, default: () => [] },
  students:         { type: Array, default: () => [] },
  activities:       { type: Array, default: () => [] },
})

function routeOr(fallback, name, param = undefined) {
  try {
    return param !== undefined ? route(name, param) : route(name)
  } catch {
    return fallback
  }
}
function formatDate(dt) {
  if (!dt) return ''
  try {
    return new Date(dt).toLocaleString()
  } catch {
    return dt
  }
}

/* 1) CourseActivity -> items estándar */
const courseActivitiesItems = computed(() => {
  return (props.courseActivities || []).map(it => ({
    id: it.id,
    course_id: it.course?.id,
    user: it.user,
    metaTitle: it.course?.title || 'Curso',
    subtitle: `${it.activity || ''}${it.activity ? ' · ' : ''}${formatDate(it.activity_date)}`,
  }))
})

/* 2) EvaluationUser -> items estándar */
const evaluationUsersItems = computed(() => {
  return (props.evaluationUsers || []).map(it => ({
    id: it.id,
    user: it.user,
    metaTitle: it.evaluation?.title || 'Evaluación',
    subtitle: `${it.course?.title || 'Curso'} · ${it.video?.title || 'Video'}${it.status_lbl ? ' · ' + it.status_lbl : ''} · ${formatDate(it.updated_at)}`,
  }))
})

/* 3) VideoActivity -> items estándar */
const videoActivitiesItems = computed(() => {
  return (props.videoActivities || []).map(it => ({
    id: it.id,
    video_id: it.video?.id,
    user: it.user,
    metaTitle: it.video?.title || 'Video',
    subtitle: `${it.course?.title || 'Curso'} · ${formatDate(it.updated_at)}`,
  }))
})

/* 4) Students -> items estándar */
const studentsItems = computed(() => {
  return (props.students || []).map(it => ({
    id: it.id,
    user: it.user,
    meta: it.country || '',
    subtitle: `Registrado el ${formatDate(it.created_at)}`,
  }))
})

/* 5) Activities -> items con _next */
const activitiesItems = computed(() => {
  return (props.activities || []).map(it => {
    let nextRouteName = null
    let nextParamVal  = null

    if (it.video?.id) {
      nextRouteName = 'admin.videos.show'
      nextParamVal  = it.video.id
    } else if (it.course?.id) {
      nextRouteName = 'admin.courses.show'
      nextParamVal  = it.course.id
    } else if (it.evaluation?.id) {
      nextRouteName = 'admin.evaluations.show'
      nextParamVal  = it.evaluation.id
    } else {
      nextRouteName = 'admin.activities.show'
      nextParamVal  = it.id
    }

    return {
      id: it.id,
      user: it.user,
      metaTitle: prettyLabel(it.type, 'Actividad'),
      subtitle: [
        it.description || '',
        it.course?.title ? ` · ${ it.course.title }` : '',
        it.video?.title ? ` · ${ it.video.title }` : '',
        it.evaluation?.title ? ` · ${ it.evaluation.title }` : '',
        ` · ${ formatDate(it.created_at) }`,
      ].join(''),
      _next: { routeName: nextRouteName, paramVal: nextParamVal },
    }
  })
})

function prettyLabel(val, fallback = 'Actividad') {
  // Si es un enum/objeto con label() o label:
  if (val && typeof val === 'object') {
    if (typeof val.label === 'function') return val.label()
    if (typeof val.label === 'string') return prettify(val.label)
    // Si viene como { value: 'something' }:
    if ('value' in val) return prettify(String(val.value))
  }
  // Si es null/undefined:
  if (val === null || val === undefined) return fallback
  // Para número/string/boolean:
  return prettify(String(val))
}

function prettify(str) {
  return String(str)
    .replace(/_/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, m => m.toUpperCase())
}

/* builder de href para actividad general */
function activityHref(item) {
  const n = item?._next
  if (!n?.routeName || n?.paramVal == null) return null
  try {
    return route(n.routeName, n.paramVal)
  } catch {
    return null
  }
}
</script>

<style scoped>
/* estilos mínimos; ActivityCard ya trae los suyos */
</style>
