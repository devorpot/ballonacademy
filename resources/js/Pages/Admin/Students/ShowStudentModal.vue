<template>
  <Head :title="`Perfil de ${displayName}`" />

  <AdminLayout>
    <div class="container-fluid py-3">
      <!-- Breadcrumbs + Actions -->
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Estudiantes', route: 'admin.students.index' },
            { label: displayName, route: '' }
          ]"
        />

        <div class="d-flex gap-2">
          <Link :href="route('admin.students.edit', { user: user.id })" class="btn btn-warning">
            <i class="bi bi-pencil-fill me-1"></i>Editar
          </Link>
          <Link :href="route('admin.students.index')" class="btn btn-outline-secondary">
            <i class="bi bi-list me-1"></i>Listado
          </Link>
        </div>
      </div>

      <!-- Header Card -->
      <div class="card shadow-sm mb-3 overflow-hidden">
        <div class="row g-0">
          <div class="col-lg-4">
            <div class="position-relative h-100 bg-light d-flex align-items-center justify-content-center">
              <img
                v-if="coverUrl"
                :src="coverUrl"
                alt="Cover"
                class="img-fluid w-100 h-100 object-fit-cover"
                style="min-height: 200px"
              />
              <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center text-muted" style="min-height: 200px">
                Sin portada
              </div>

              <div class="position-absolute bottom-0 start-0 p-3">
                <img
                  v-if="avatarUrl"
                  :src="avatarUrl"
                  alt="Avatar"
                  class="rounded-circle border border-3 border-white"
                  width="84" height="84"
                />
                <div v-else class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                     style="width:84px;height:84px">
                  {{ initials }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <div class="card-body">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h1 class="h4 mb-0">{{ displayName }}</h1>
                <span class="badge" :class="user.active ? 'bg-success' : 'bg-danger'">
                  {{ user.active ? 'Activo' : 'Inactivo' }}
                </span>
              </div>

              <div class="text-muted small mb-2">
                ID: {{ user.id }} · Creado el {{ createdAt }}
              </div>

              <div class="row">
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Email (cuenta)</label>
                  <p class="form-control-plaintext">{{ user.email }}</p>
                </div>
                <div class="col-md-6 mb-2" v-if="profile?.personal_email">
                  <label class="form-label text-muted">Email personal</label>
                  <p class="form-control-plaintext">{{ profile.personal_email }}</p>
                </div>

                <div class="col-md-6 mb-2" v-if="profile?.whatsapp">
                  <label class="form-label text-muted">WhatsApp</label>
                  <p class="form-control-plaintext">{{ profile.whatsapp }}</p>
                </div>
                <div class="col-md-6 mb-2" v-if="profile?.country">
                  <label class="form-label text-muted">País</label>
                  <p class="form-control-plaintext">{{ profile.country }}</p>
                </div>

                <div class="col-12" v-if="profile?.description">
                  <label class="form-label text-muted">Descripción</label>
                  <p class="form-control-plaintext mb-0">{{ profile.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div><!-- /row -->
      </div>

      <!-- Personal & Social -->
      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="fw-bold mb-3">Datos personales</h5>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Nombre(s)</label>
                  <p class="form-control-plaintext">{{ profile?.firstname || '—' }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Apellido(s)</label>
                  <p class="form-control-plaintext">{{ profile?.lastname || '—' }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Nickname</label>
                  <p class="form-control-plaintext">{{ profile?.nickname || '—' }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Locale</label>
                  <p class="form-control-plaintext">{{ user.locale || '—' }}</p>
                </div>
              </div>

              <h6 class="fw-bold mt-3">Redes</h6>
              <div class="row">
                <div class="col-md-6 mb-2" v-if="profile?.website">
                  <label class="form-label text-muted">Sitio web</label>
                  <p class="form-control-plaintext">
                    <a :href="profile.website" target="_blank" rel="noopener">{{ profile.website }}</a>
                  </p>
                </div>
                <div class="col-md-6 mb-2" v-if="profile?.facebook">
                  <label class="form-label text-muted">Facebook</label>
                  <p class="form-control-plaintext"><a :href="profile.facebook" target="_blank" rel="noopener">{{ profile.facebook }}</a></p>
                </div>
                <div class="col-md-6 mb-2" v-if="profile?.instagram">
                  <label class="form-label text-muted">Instagram</label>
                  <p class="form-control-plaintext"><a :href="profile.instagram" target="_blank" rel="noopener">{{ profile.instagram }}</a></p>
                </div>
                <div class="col-md-6 mb-2" v-if="profile?.tiktok">
                  <label class="form-label text-muted">TikTok</label>
                  <p class="form-control-plaintext"><a :href="profile.tiktok" target="_blank" rel="noopener">{{ profile.tiktok }}</a></p>
                </div>
                <div class="col-md-6 mb-0" v-if="profile?.youtube">
                  <label class="form-label text-muted">YouTube</label>
                  <p class="form-control-plaintext"><a :href="profile.youtube" target="_blank" rel="noopener">{{ profile.youtube }}</a></p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Billing -->
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="fw-bold mb-3">Datos de facturación</h5>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">RFC</label>
                  <p class="form-control-plaintext">{{ profile?.rfc || '—' }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Razón social</label>
                  <p class="form-control-plaintext">{{ profile?.business_name || '—' }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Correo para factura</label>
                  <p class="form-control-plaintext">{{ profile?.billing_email || '—' }}</p>
                </div>

                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Régimen fiscal</label>
                  <p class="form-control-plaintext">{{ profile?.tax_regime || '—' }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label text-muted">Uso de CFDI</label>
                  <p class="form-control-plaintext">{{ profile?.cfdi_use || '—' }}</p>
                </div>

                <div class="col-12">
                  <label class="form-label text-muted">Dirección fiscal</label>
                  <p class="form-control-plaintext mb-0">
                    {{ fullAddress || '—' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /row -->
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'

const props = defineProps({
  user: { type: Object, required: true },
  profile: { type: Object, required: true }
})

const user = props.user
const profile = props.profile

const displayName = computed(() => {
  const first = profile?.firstname || ''
  const last  = profile?.lastname || ''
  const full  = `${first} ${last}`.trim()
  return full || user.name || user.email
})

const createdAt = computed(() => {
  if (!user?.created_at) return '—'
  try {
    return new Date(user.created_at).toLocaleString()
  } catch {
    return user.created_at
  }
})

const avatarUrl = computed(() => profile?.profile_image ? `/storage/${profile.profile_image}` : null)
const coverUrl  = computed(() => profile?.cover_image   ? `/storage/${profile.cover_image}`   : null)
const initials  = computed(() => {
  const s = (displayName.value || '').trim()
  return s ? s.split(/\s+/).map(p => p[0]).slice(0,2).join('').toUpperCase() : 'U'
})

const fullAddress = computed(() => {
  const p = profile || {}
  const parts = [
    p.street, p.external_number, p.internal_number && `Int. ${p.internal_number}`,
    p.neighborhood, p.municipality, p.state, p.postal_code
  ].filter(Boolean)
  return parts.join(', ')
})
</script>

<style scoped>
.object-fit-cover { object-fit: cover; }
</style>
