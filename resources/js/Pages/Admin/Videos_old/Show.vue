<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { route } from 'ziggy-js';

const props = defineProps({
  course: Object,
  video: Object
});
</script>

<template>
  <Head :title="`Detalles del Video: ${video.title}`" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
              <i class="bi bi-film me-2"></i>Detalles del Video: {{ video.title }}
            </h1>
            <Link :href="route('admin.videos.index', course.id)" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Volver a Videos
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
                <p class="form-control-plaintext">{{ video.title }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">URL del Video:</label>
                <p class="form-control-plaintext">{{ video.video_url }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Creación:</label>
                <p class="form-control-plaintext">{{ new Date(video.created_at).toLocaleString() }}</p>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <h5 class="fw-bold">Detalles</h5>
              <hr>
              <div class="mb-3">
                <label class="form-label text-muted">Descripción Corta:</label>
                <p class="form-control-plaintext">{{ video.description_short || 'N/A' }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Descripción:</label>
                <p class="form-control-plaintext">{{ video.description || 'N/A' }}</p>
              </div>
              <div class="mb-3" v-if="video.comments">
                <label class="form-label text-muted">Comentarios:</label>
                <p class="form-control-plaintext">{{ video.comments }}</p>
              </div>
              <div class="mb-3" v-if="video.image_cover">
                <label class="form-label text-muted">Imagen de Portada:</label>
                <div>
                  <img :src="video.image_cover" alt="Cover" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <Link :href="route('admin.videos.edit', [course.id, video.id])" class="btn btn-warning me-2">
              <i class="bi bi-pencil-fill me-2"></i>Editar
            </Link>
            <Link :href="route('admin.videos.index', course.id)" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Volver al Listado
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
