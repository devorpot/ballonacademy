<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { route } from 'ziggy-js';

const props = defineProps({
  subscription: Object
});
</script>

<template>
  <Head :title="`Detalles de la Suscripción #${subscription.id}`" />

  <AdminLayout>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
              <i class="bi bi-card-checklist me-2"></i>Detalles de la Suscripción #{{ subscription.id }}
            </h1>
            <Link :href="route('admin.subscriptions.index')" class="btn btn-secondary">
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
                <p class="form-control-plaintext">{{ subscription.user.name }} ({{ subscription.user.email }})</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Curso:</label>
                <p class="form-control-plaintext">{{ subscription.course.title }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Monto:</label>
                <p class="form-control-plaintext">{{ subscription.payment_amount }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Moneda:</label>
                <p class="form-control-plaintext">{{ subscription.payment_currency }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Estado del Pago:</label>
                <p class="form-control-plaintext">{{ subscription.payment_status }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Pago:</label>
                <p class="form-control-plaintext">{{ subscription.payment_date }}</p>
              </div>
              <div class="mb-3" v-if="subscription.payment_refund_date">
                <label class="form-label text-muted">Fecha de Reembolso:</label>
                <p class="form-control-plaintext">{{ subscription.payment_refund_date }}</p>
              </div>
              <div class="mb-3" v-if="subscription.payment_refund_desc">
                <label class="form-label text-muted">Descripción del Reembolso:</label>
                <p class="form-control-plaintext">{{ subscription.payment_refund_desc }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Fecha de Creación:</label>
                <p class="form-control-plaintext">{{ new Date(subscription.created_at).toLocaleString() }}</p>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <h5 class="fw-bold">Detalles del Pago</h5>
              <hr>
              <div class="mb-3">
                <label class="form-label text-muted">Tipo de Pago:</label>
                <p class="form-control-plaintext">{{ subscription.payment_type }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Tarjeta:</label>
                <p class="form-control-plaintext">{{ subscription.payment_card }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Tipo de Tarjeta:</label>
                <p class="form-control-plaintext">{{ subscription.payment_card_type }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Marca de Tarjeta:</label>
                <p class="form-control-plaintext">{{ subscription.payment_card_brand }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label text-muted">Banco:</label>
                <p class="form-control-plaintext">{{ subscription.payment_bank }}</p>
              </div>
              <div class="mb-3" v-if="subscription.payment_description">
                <label class="form-label text-muted">Descripción:</label>
                <p class="form-control-plaintext">{{ subscription.payment_description }}</p>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <Link :href="route('admin.subscriptions.edit', subscription.id)" class="btn btn-warning me-2">
              <i class="bi bi-pencil-fill me-2"></i>Editar
            </Link>
            <Link :href="route('admin.subscriptions.index')" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Volver al Listado
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
