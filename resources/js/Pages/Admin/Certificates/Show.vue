<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { route } from 'ziggy-js';

const props = defineProps({
  certificate: Object
});
</script>

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

      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <h5 class="fw-bold">Información Básica</h5>
              <hr>
              <div class="mb-3">
                <label class="form-label text-muted">Usuario:</label>
                <p class="form-control-plaintext">{{ certificate.user.name }} ({{ certificate.user.email }})</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Maestro:</label>
                <p class="form-control-plaintext">{{ certificate.master.firstname }} {{ certificate.master.lastname }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Autorizado por:</label>
                <p class="form-control-plaintext">{{ certificate.authorized_by }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Inicio:</label>
                <p class="form-control-plaintext">{{ certificate.date_start }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Fin:</label>
                <p class="form-control-plaintext">{{ certificate.date_end }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Expedición:</label>
                <p class="form-control-plaintext">{{ certificate.date_expedition }}</p>
              </div>
              <div class="mb-3" v-if="certificate.comments">
                <label class="form-label text-muted">Comentarios:</label>
                <p class="form-control-plaintext">{{ certificate.comments }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Creación:</label>
                <p class="form-control-plaintext">{{ new Date(certificate.created_at).toLocaleString() }}</p>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <h5 class="fw-bold">Archivos</h5>
              <hr>
              <div class="mb-3">
                <label class="form-label text-muted">Foto:</label>
                <p class="form-control-plaintext">{{ certificate.photo }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Logo:</label>
                <p class="form-control-plaintext">{{ certificate.logo }}</p>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <Link :href="route('admin.certificates.edit', certificate.id)" class="btn btn-warning me-2">
              <i class="bi bi-pencil-fill me-2"></i>Editar
            </Link>
            <Link :href="route('admin.certificates.index')" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Volver al Listado
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
