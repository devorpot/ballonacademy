<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { route } from 'ziggy-js';

const props = defineProps({
  course: Object
});
</script>

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

      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <h5 class="fw-bold">Información Básica</h5>
              <hr>
              <div class="mb-3">
                <label class="form-label text-muted">Título:</label>
                <p class="form-control-plaintext">{{ course.title }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Nivel:</label>
                <p class="form-control-plaintext">{{ course.level || 'N/A' }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Creación:</label>
                <p class="form-control-plaintext">{{ new Date(course.created_at).toLocaleString() }}</p>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <h5 class="fw-bold">Descripción</h5>
              <hr>
              <div class="mb-3">
                <label class="form-label text-muted">Descripción Corta:</label>
                <p class="form-control-plaintext">{{ course.description_short || 'N/A' }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Descripción Completa:</label>
                <p class="form-control-plaintext">{{ course.description || 'N/A' }}</p>
              </div>
              <div class="mb-3" v-if="course.image_cover">
                <label class="form-label text-muted">Imagen de Portada:</label>
                <div>
                  <img :src="course.image_cover" alt="Cover" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                </div>
              </div>
              <div class="mb-3" v-if="course.logo">
                <label class="form-label text-muted">Logo:</label>
                <div>
                  <img :src="course.logo" alt="Logo" class="img-fluid rounded shadow-sm" style="max-height: 100px;">
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <Link :href="route('admin.courses.edit', course.id)" class="btn btn-warning me-2">
              <i class="bi bi-pencil-fill me-2"></i>Editar
            </Link>
            <Link :href="route('admin.courses.index')" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Volver al Listado
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
