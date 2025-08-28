<template>
  <Head :title="`Detalles del Curso: ${course.title}`" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
              <i class="bi bi-journal-text me-2"></i>Detalles del Curso: {{ course.title }}
            </h1>
            <Link :href="route('admin.courses.index')" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Volver
            </Link>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Información Básica</h5>

          <div class="row">
            <div class="col-md-4 mb-3" v-for="(field, index) in textFields" :key="index">
              <label class="form-label text-muted">{{ field.label }}</label>
              <p class="form-control-plaintext">{{ field.value }}</p>
            </div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Archivos</h5>
          <div class="d-flex flex-wrap gap-5">
            <div v-if="course.image_cover" class="text-center">
              <label class="form-label text-muted d-block">Imagen de Portada</label>
              <img
                :src="course.image_cover"
                alt="Portada"
                class="img-thumbnail"
                style="width: 200px; cursor: pointer;"
                @click="openLightbox(course.image_cover)"
              >
            </div>

            <div v-if="course.logo" class="text-center">
              <label class="form-label text-muted d-block">Logo</label>
              <img
                :src="course.logo"
                alt="Logo"
                class="img-thumbnail"
                style="width: 150px; cursor: pointer;"
                @click="openLightbox(course.logo)"
              >
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4 gap-2">
            <Link :href="route('admin.courses.edit', course.id)" class="btn btn-warning">
              <i class="bi bi-pencil-fill me-1"></i>Editar
            </Link>
            <Link :href="route('admin.courses.index')" class="btn btn-outline-secondary">
              <i class="bi bi-list me-1"></i>Listado
            </Link>
          </div>
        </div>
      </div>

      <!-- Estudiantes (si los necesitas visibles aquí) -->
      <div class="card shadow-sm mt-4" v-if="(course.students || []).length">
        <div class="card-body">
          <h5 class="fw-bold mb-3">
            <i class="bi bi-people me-2"></i>Estudiantes asignados ({{ course.students.length }})
          </h5>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width: 64px;">#</th>
                  <th>Nombre</th>
                  <th>Email</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(s, i) in course.students" :key="s.id">
                  <td>{{ i + 1 }}</td>
                  <td>{{ s.name }}</td>
                  <td>{{ s.email }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Lightbox -->
      <div v-if="lightboxSrc" class="lightbox" @click.self="closeLightbox">
        <div class="lightbox-content">
          <img :src="lightboxSrc" alt="Vista ampliada">
          <button type="button" class="btn btn-light btn-close-lightbox" @click="closeLightbox">
            <i class="bi bi-x-circle-fill"></i>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'

const props = defineProps({
  course: { type: Object, required: true }
})

const lightboxSrc = ref(null)
const openLightbox = (src) => { lightboxSrc.value = src }
const closeLightbox = () => { lightboxSrc.value = null }

// Campos de texto (usando computed para tomar props reactivamente)
const textFields = computed(() => ([
  { label: 'Título', value: props.course.title ?? 'N/A' },
  { label: 'Nivel', value: props.course.level ?? 'N/A' },
  { label: 'Descripción Corta', value: props.course.description_short ?? 'N/A' },
  { label: 'Descripción Completa', value: props.course.description ?? 'N/A' },
  { label: 'Fecha de Inicio', value: formatDate(props.course.date_start) },
  { label: 'Fecha de Fin', value: formatDate(props.course.date_end) },
  { label: 'Fecha de Creación', value: formatDateTime(props.course.created_at) },
]))

function formatDate(value) {
  if (!value) return 'N/A'
  try {
    return new Intl.DateTimeFormat('es-MX', { year: 'numeric', month: 'short', day: '2-digit' })
      .format(new Date(value))
  } catch { return value }
}

function formatDateTime(value) {
  if (!value) return 'N/A'
  try {
    return new Intl.DateTimeFormat('es-MX', {
      year: 'numeric', month: 'short', day: '2-digit',
      hour: '2-digit', minute: '2-digit'
    }).format(new Date(value))
  } catch { return value }
}
</script>

<style scoped>
.lightbox {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.85);
  display: flex; align-items: center; justify-content: center;
  z-index: 1050;
}
.lightbox-content { position: relative; text-align: center; }
.lightbox-content img {
  max-width: 90vw; max-height: 90vh; border: 2px solid #fff;
}
.btn-close-lightbox {
  position: absolute; top: -10px; right: -10px;
  padding: 0.25rem 0.5rem; border-radius: 50%; font-size: 1.2rem;
}
</style>
