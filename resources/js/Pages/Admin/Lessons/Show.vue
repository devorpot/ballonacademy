<template>
  <Head :title="`Ver Lección: ${lesson.title}`" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Cursos', route: 'admin.courses.index' },
          { label: 'Lecciones', route: 'admin.lessons.index' },
          { label: 'Detalle', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.lessons.index')" />
              <div class="d-flex gap-2">
                <Link :href="route('admin.lessons.edit', lesson.id)" class="btn btn-outline-primary">
                  <i class="bi bi-pencil-square me-1"></i> Editar
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section-form my-2">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <div class="row g-4">
                <!-- Columna izquierda -->
                <div class="col-md-6">
                  <strong class="d-block text-muted">Título</strong>
                  <p class="mb-3">{{ lesson.title }}</p>

                  <strong class="d-block text-muted">Fecha de publicación</strong>
                  <p class="mb-3">{{ publishOnHuman }}</p>

                  <strong class="d-block text-muted">Curso</strong>
                  <p class="mb-3">{{ courseName }}</p>

                  <strong class="d-block text-muted">Profesor</strong>
                  <p class="mb-3">{{ teacherName }}</p>

                  <strong class="d-block text-muted">Orden</strong>
                  <p class="mb-3">{{ lesson.order ?? '—' }}</p>
                </div>

                <!-- Columna derecha -->
                <div class="col-md-6">
                  <strong class="d-block text-muted">Descripción corta</strong>
                  <p class="mb-3">{{ lesson.description_short || '—' }}</p>

                  <strong class="d-block text-muted">Instrucciones</strong>
                  <p class="mb-3" style="white-space: pre-wrap;">{{ lesson.instructions || '—' }}</p>

                  <strong class="d-block text-muted">Estado</strong>
                  <div class="mb-3">
                    <span
                      class="badge"
                      :class="lesson.active ? 'bg-success' : 'bg-secondary'"
                    >
                      {{ lesson.active ? 'Activa' : 'Inactiva' }}
                    </span>
                  </div>

                  <strong class="d-block text-muted">Opciones</strong>
                  <div class="d-flex flex-wrap gap-2">
                    <span class="badge" :class="lesson.add_video ? 'bg-primary' : 'bg-light text-muted border'">
                      Agregar videos: {{ lesson.add_video ? 'Sí' : 'No' }}
                    </span>
                    <span class="badge" :class="lesson.add_evaluation ? 'bg-primary' : 'bg-light text-muted border'">
                      Agregar evaluaciones: {{ lesson.add_evaluation ? 'Sí' : 'No' }}
                    </span>
                    <span class="badge" :class="lesson.add_materials ? 'bg-primary' : 'bg-light text-muted border'">
                      Agregar materiales: {{ lesson.add_materials ? 'Sí' : 'No' }}
                    </span>
                  </div>
                </div>

                <!-- Colecciones -->
                <div class="col-12">
                  <hr />
                  <div class="row g-3">
                    <div class="col-md-4">
                      <div class="p-3 border rounded bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center">
                          <strong>Videos</strong>
                          <span class="badge bg-dark">{{ videosCount }}</span>
                        </div>
                        <p class="text-muted mb-0 small">
                          {{ videosCount > 0 ? 'Esta lección tiene videos asociados.' : 'Sin videos.' }}
                        </p>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="p-3 border rounded bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center">
                          <strong>Evaluaciones</strong>
                          <span class="badge bg-dark">{{ evaluationsCount }}</span>
                        </div>
                        <p class="text-muted mb-0 small">
                          {{ evaluationsCount > 0 ? 'Esta lección tiene evaluaciones asociadas.' : 'Sin evaluaciones.' }}
                        </p>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="p-3 border rounded bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center">
                          <strong>Materiales</strong>
                          <span class="badge bg-dark">{{ materialsCount }}</span>
                        </div>
                        <p class="text-muted mb-0 small">
                          {{ materialsCount > 0 ? 'Esta lección tiene materiales asociados.' : 'Sin materiales.' }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Metadatos -->
                <div class="col-12">
                  <hr />
                  <div class="row">
                    <div class="col-md-6 small text-muted">
                      <strong>Creada:</strong> {{ formatDateTime(lesson.created_at) || '—' }}
                    </div>
                    <div class="col-md-6 small text-muted text-md-end">
                      <strong>Actualizada:</strong> {{ formatDateTime(lesson.updated_at) || '—' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <Link :href="route('admin.lessons.edit', lesson.id)" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i> Editar
              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'

const props = defineProps({
  lesson: { type: Object, required: true },   // { id, title, publish_on, description_short, instructions, course_id, teacher_id, order, active, add_video, add_evaluation, add_materials, videos, evaluations, materials, created_at, updated_at }
  courses: { type: Array, default: () => [] }, // [{id, title}]
  teachers: { type: Array, default: () => [] } // [{id, firstname, lastname}]
})

// Helpers
function toDateInput(value) {
  if (!value) return ''
  // Permite 'YYYY-MM-DD' o ISO
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value
  const d = new Date(value)
  if (isNaN(d.getTime())) return ''
  const yyyy = d.getFullYear()
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

function formatDateTime(value) {
  if (!value) return ''
  const d = new Date(value)
  if (isNaN(d.getTime())) return ''
  return d.toLocaleString()
}

// Computed UI
const courseName = computed(() => {
  const c = props.courses.find(c => c.id === props.lesson.course_id)
  return c ? c.title : '—'
})

const teacherName = computed(() => {
  const t = props.teachers.find(t => t.id === props.lesson.teacher_id)
  if (!t) return '—'
  return `${t.firstname ?? ''} ${t.lastname ?? ''}`.trim() || '—'
})

const publishOnHuman = computed(() => {
  const d = toDateInput(props.lesson.publish_on)
  return d || '—'
})

const videosCount = computed(() => Array.isArray(props.lesson?.videos) ? props.lesson.videos.length : 0)
const evaluationsCount = computed(() => Array.isArray(props.lesson?.evaluations) ? props.lesson.evaluations.length : 0)
const materialsCount = computed(() => Array.isArray(props.lesson?.materials) ? props.lesson.materials.length : 0)
</script>
