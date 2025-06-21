 

<template>
  <Head :title="`Detalles del Certificado #${certificate.id}`" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
              <i class="bi bi-card-text me-2"></i>Detalles del Certificado #{{ certificate.id }}
            </h1>
            <Link :href="route('admin.certificates.index')" class="btn btn-secondary">
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

          <div v-if="certificate.comments" class="mb-4">
            <label class="form-label text-muted">Comentarios</label>
            <div class="border rounded p-2 bg-light">{{ certificate.comments }}</div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Archivos</h5>
          <div class="d-flex flex-column align-items-center gap-3">
            <div v-if="certificate.photo" class="text-center">
              <label class="form-label text-muted">Foto</label><br>
              <img
                :src="`/storage/${certificate.photo}`"
                alt="Foto"
                class="img-thumbnail"
                style="width: 150px; cursor: pointer;"
                @click="openLightbox(`/storage/${certificate.photo}`)"
              >
            </div>
            <div v-if="certificate.logo" class="text-center">
              <label class="form-label text-muted">Logo</label><br>
              <img
                :src="`/storage/${certificate.logo}`"
                alt="Logo"
                class="img-thumbnail"
                style="width: 150px; cursor: pointer;"
                @click="openLightbox(`/storage/${certificate.logo}`)"
              >
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4 gap-2">
            <Link :href="route('admin.certificates.edit', certificate.id)" class="btn btn-warning">
              <i class="bi bi-pencil-fill me-1"></i>Editar
            </Link>
            <Link :href="route('admin.certificates.index')" class="btn btn-outline-secondary">
              <i class="bi bi-list me-1"></i>Listado
            </Link>
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
  import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { route } from 'ziggy-js';
import { ref } from 'vue';

const props = defineProps({
  certificate: Object
});

const lightboxSrc = ref(null);
const openLightbox = (src) => {
  lightboxSrc.value = src;
};
const closeLightbox = () => {
  lightboxSrc.value = null;
};
const textFields = [
  {
    label: 'Usuario',
    value: `${props.certificate.user.name} (${props.certificate.user.email})`
  },
  {
    label: 'Maestro',
    value: `${props.certificate.master.firstname} ${props.certificate.master.lastname}`
  },
  {
    label: 'Autorizado por',
    value: props.certificate.authorized_by
  },
  {
    label: 'Fecha de Inicio',
    value: new Date(props.certificate.date_start).toLocaleDateString()
  },
  {
    label: 'Fecha de Fin',
    value: new Date(props.certificate.date_end).toLocaleDateString()
  },
  {
    label: 'Fecha de Expedición',
    value: new Date(props.certificate.date_expedition).toLocaleDateString()
  },
  {
    label: 'Fecha de Creación',
    value: new Date(props.certificate.created_at).toLocaleString()
  }
];
</script>

<style scoped>
.lightbox {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}
.lightbox-content {
  position: relative;
  text-align: center;
}
.lightbox-content img {
  max-width: 90vw;
  max-height: 90vh;
  border: 2px solid #fff;
}
.btn-close-lightbox {
  position: absolute;
  top: -10px;
  right: -10px;
  padding: 0.25rem 0.5rem;
  border-radius: 50%;
  font-size: 1.2rem;
}
</style>
