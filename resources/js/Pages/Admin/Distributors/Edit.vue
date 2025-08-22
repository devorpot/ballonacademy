<template>
  <Head title="Editar Distribuidor" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Distribuidores', route: 'admin.distributors.index' },
          { label: 'Editar', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.distributors.index')" />
              <button
                type="button"
                class="btn btn-primary"
                :disabled="form.processing || !isFormValid"
                @click="submit"
              >
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i class="bi bi-save me-2"></i>Guardar cambios
              </button>
            </div>
          </div>
        </div>
      </section>

      <section class="section-form my-2">
        <div class="container-fluid">
          <form @submit.prevent="submit" enctype="multipart/form-data" novalidate>
            <div class="card">
              <div class="card-body">
                <div class="row">
                  <!-- Nombre -->
                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="name"
                      label="Nombre"
                      v-model="form.name"
                      :required="true"
                      :showValidation="touched.name"
                      :formError="form.errors.name"
                      :validateFunction="() => validateRequired('name', 'El nombre es obligatorio')"
                      placeholder="Nombre del distribuidor"
                      @blur="() => handleBlur('name')"
                    />
                  </div>

                  <!-- Logo -->
                  <div class="col-md-6 mb-3">
                    <FieldImage
                      id="logo"
                      label="Logo"
                      v-model="form.logo"
                      :showValidation="touched.logo"
                      :formError="form.errors.logo"
                      :initialPreview="logoPreview"
                      @blur="() => handleBlur('logo')"
                      @image-removed="onRemoveLogo"
                    />
                  </div>

                  <!-- Descripción -->
                  <div class="col-md-12 mb-3">
                    <FieldTextarea
                      id="description"
                      label="Descripción"
                      v-model="form.description"
                      placeholder="Descripción breve del distribuidor"
                    />
                  </div>

                  <!-- País (en tu base de edición lo pones como Select con stateOptions) -->
                  <div class="col-md-6 mb-3">
                    <FieldSelect
                      id="country"
                      label="Estado/País (country)"
                      v-model="form.country"
                      :required="true"
                      :showValidation="touched.country"
                      :formError="form.errors.country"
                      :validateFunction="() => validateRequired('country', 'El país es obligatorio')"
                      :options="stateOptions"
                      @blur="() => handleBlur('country')"
                    />
                  </div>

                  <!-- State (texto libre en tu base de edición) -->
                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="state"
                      label="Estado"
                      v-model="form.state"
                      placeholder="Ej. CDMX"
                    />
                  </div>

                  <!-- Región y Zona (renombradas en tu base de edición) -->
                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="region"
                      label="Municipio"
                      v-model="form.region"
                      placeholder="Ej. Guadalajara"
                    />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldText
                      id="zone"
                      label="Colonia"
                      v-model="form.zone"
                      placeholder="Ej. Universitaria"
                    />
                  </div>

                  <!-- Dirección -->
                  <div class="col-md-12 mb-3">
                    <FieldText
                      id="address"
                      label="Dirección"
                      v-model="form.address"
                      :required="true"
                      :showValidation="touched.address"
                      :formError="form.errors.address"
                      :validateFunction="() => validateRequired('address', 'La dirección es obligatoria')"
                      placeholder="Calle, número, colonia, ciudad, CP"
                      @blur="() => handleBlur('address')"
                    />
                  </div>

                  <!-- Google Maps / Lat / Lng -->
                  <div class="col-md-6 mb-3">
                    <FieldUrl
                      id="gmap_link"
                      label="Enlace de Google Maps"
                      v-model="form.gmap_link"
                      :showValidation="touched.gmap_link"
                      :formError="form.errors.gmap_link"
                      placeholder="https://maps.google.com/..."
                      @blur="() => handleBlur('gmap_link')"
                    />
                  </div>

                  <div class="col-md-3 mb-3">
                    <FieldText
                      id="lat"
                      label="Latitud"
                      v-model="form.lat"
                      placeholder="Ej. 19.4326"
                    />
                  </div>

                  <div class="col-md-3 mb-3">
                    <FieldText
                      id="lng"
                      label="Longitud"
                      v-model="form.lng"
                      placeholder="-99.1332"
                    />
                  </div>

                  <!-- Contacto -->
                  <div class="col-md-4 mb-3">
                    <FieldEmail
                      id="email"
                      label="Email"
                      v-model="form.email"
                      :showValidation="touched.email"
                      :formError="form.errors.email"
                      placeholder="correo@dominio.com"
                      @blur="() => handleBlur('email')"
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <FieldPhone
                      id="whatsapp"
                      label="WhatsApp"
                      v-model="form.whatsapp"
                      :showValidation="touched.whatsapp"
                      :formError="form.errors.whatsapp"
                      placeholder="+52 55 0000 0000"
                      @blur="() => handleBlur('whatsapp')"
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <FieldPhone
                      id="phone"
                      label="Teléfono"
                      v-model="form.phone"
                      :showValidation="touched.phone"
                      :formError="form.errors.phone"
                      placeholder="+52 55 0000 0000"
                      @blur="() => handleBlur('phone')"
                    />
                  </div>

                  <!-- URLs sociales -->
                  <div class="col-md-6 mb-3">
                    <FieldUrl
                      id="website"
                      label="Sitio web"
                      v-model="form.website"
                      :showValidation="touched.website"
                      :formError="form.errors.website"
                      placeholder="https://..."
                      @blur="() => handleBlur('website')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldUrl
                      id="facebook"
                      label="Facebook"
                      v-model="form.facebook"
                      :showValidation="touched.facebook"
                      :formError="form.errors.facebook"
                      placeholder="https://facebook.com/..."
                      @blur="() => handleBlur('facebook')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldUrl
                      id="instagram"
                      label="Instagram"
                      v-model="form.instagram"
                      :showValidation="touched.instagram"
                      :formError="form.errors.instagram"
                      placeholder="https://instagram.com/..."
                      @blur="() => handleBlur('instagram')"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldUrl
                      id="tiktok"
                      label="TikTok"
                      v-model="form.tiktok"
                      :showValidation="touched.tiktok"
                      :formError="form.errors.tiktok"
                      placeholder="https://www.tiktok.com/@..."
                      @blur="() => handleBlur('tiktok')"
                    />
                  </div>
                </div>
              </div>

              <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i>Guardar cambios
                </button>
              </div>
            </div>
          </form>
        </div>
      </section>
    </div>

    <SpinnerOverlay v-if="form.processing" />
  </AdminLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'

import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue'
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue'
import FieldEmail from '@/Components/Admin/Fields/FieldEmail.vue'
import FieldPhone from '@/Components/Admin/Fields/FieldPhone.vue'
import FieldUrl from '@/Components/Admin/Fields/FieldUrl.vue'

const props = defineProps({
  distributor: { type: Object, required: true },
  countries:   { type: Array, default: () => [] }
})

const stateOptions = props.countries.map(c => ({ value: c, label: c }))

// Previews
const logoPreview = props.distributor.logo ? `/storage/${props.distributor.logo}` : null

// Form en modo edición
const form = useForm({
  _method: 'PUT',
  name: props.distributor.name ?? '',
  logo: null,            // archivo nuevo (si se cambia)
  remove_logo: false,    // flag para borrar logo existente

  description: props.distributor.description ?? '',

  country: props.distributor.country ?? '',
  state: props.distributor.state ?? '',
  region: props.distributor.region ?? '',
  zone: props.distributor.zone ?? '',
  address: props.distributor.address ?? '',

  gmap_link: props.distributor.gmap_link ?? '',
  lat: props.distributor.lat ?? '',
  lng: props.distributor.lng ?? '',

  email: props.distributor.email ?? '',
  whatsapp: props.distributor.whatsapp ?? '',
  phone: props.distributor.phone ?? '',

  website: props.distributor.website ?? '',
  facebook: props.distributor.facebook ?? '',
  instagram: props.distributor.instagram ?? '',
  tiktok: props.distributor.tiktok ?? ''
})

const touched = ref({})

const handleBlur = (field) => {
  touched.value[field] = true
}

const validateRequired = (field, message) => {
  const val = form[field]
  if (val === null || val === undefined) return message
  if (typeof val === 'string' && val.trim() === '') return message
  return ''
}

const isFormValid = computed(() => {
  return !validateRequired('name', 'El nombre es obligatorio') &&
         !validateRequired('country', 'El país es obligatorio') &&
         !validateRequired('address', 'La dirección es obligatoria')
})

// Eliminar logo existente desde el componente de imagen
const onRemoveLogo = () => {
  form.remove_logo = true
  form.logo = null
  touched.value.logo = true
}

const submit = () => {
  ;['name','country','address'].forEach(k => touched.value[k] = true)

  if (isFormValid.value) {
    form.post(route('admin.distributors.update', props.distributor.id), {
      forceFormData: true,
      preserveScroll: true
    })
  }
}
</script>

<style scoped>
/* Estilos opcionales */
</style>
