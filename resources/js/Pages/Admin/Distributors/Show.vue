<template>
  <Head :title="`Distribuidor: ${distributor.name}`" />

  <AdminLayout>
       <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Distribuidores', route: 'admin.distributors.index' },
          { label: 'Detalle', route: '' }
        ]"
      />
       <section class="section-heading my-2">
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
              <i class="bi bi-building me-2"></i>Distribuidor: {{ distributor.name }}
            </h1>
            <Link :href="route('admin.distributors.index')" class="btn btn-secondary">
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
              <p class="form-control-plaintext">
                <template v-if="field.isLink && field.value">
                  <a :href="field.value" target="_blank" rel="noopener">{{ field.display || field.value }}</a>
                </template>
                <template v-else-if="field.isMail && field.value">
                  <a :href="`mailto:${field.value}`">{{ field.value }}</a>
                </template>
                <template v-else-if="field.isTel && field.value">
                  <a :href="`tel:${field.value}`">{{ field.value }}</a>
                </template>
                <template v-else>
                  {{ field.value || '—' }}
                </template>
              </p>
            </div>
          </div>

          <div v-if="distributor.description" class="mb-4">
            <label class="form-label text-muted">Descripción</label>
            <div class="border rounded p-2 bg-light">{{ distributor.description }}</div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Logo</h5>
          <div class="d-flex flex-wrap align-items-center gap-3">
            <div v-if="distributor.logo" class="text-center">
              <img
                :src="`/storage/${distributor.logo}`"
                alt="Logo"
                class="img-thumbnail"
                style="width: 150px; cursor: pointer;"
                @click="openLightbox(`/storage/${distributor.logo}`)"
              >
            </div>
            <div v-else class="text-muted">Sin logo</div>
          </div>

          <div class="d-flex justify-content-end mt-4 gap-2">
            <Link :href="route('admin.distributors.edit', distributor.id)" class="btn btn-warning">
              <i class="bi bi-pencil-fill me-1"></i>Editar
            </Link>
            <Link :href="route('admin.distributors.index')" class="btn btn-outline-secondary">
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
  </section>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'

const props = defineProps({
  distributor: { type: Object, required: true }
})

const distributor = props.distributor

const lightboxSrc = ref(null)
const openLightbox = (src) => { lightboxSrc.value = src }
const closeLightbox = () => { lightboxSrc.value = null }

const textFields = computed(() => [
  { label: 'Nombre', value: distributor.name },
  { label: 'País', value: distributor.country },
  { label: 'Estado', value: distributor.state },
  { label: 'Municipio', value: distributor.region },
  { label: 'Colonia', value: distributor.zone },
  { label: 'Dirección', value: distributor.address },

  // Mapa y coordenadas
  { label: 'Google Maps', value: distributor.gmap_link, isLink: true, display: 'Ver en mapa' },
  { label: 'Latitud', value: distributor.lat },
  { label: 'Longitud', value: distributor.lng },

  // Contacto
  { label: 'Email', value: distributor.email, isMail: true },
  { label: 'WhatsApp', value: distributor.whatsapp, isTel: true },
  { label: 'Teléfono', value: distributor.phone, isTel: true },

  // Web y redes
  { label: 'Sitio Web', value: distributor.website, isLink: true },
  { label: 'Facebook', value: distributor.facebook, isLink: true },
  { label: 'Instagram', value: distributor.instagram, isLink: true },
  { label: 'TikTok', value: distributor.tiktok, isLink: true },
])
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
