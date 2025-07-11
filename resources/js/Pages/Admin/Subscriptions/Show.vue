<template>
  <Head title="Detalle de Suscripción" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Suscripciones', route: 'admin.subscriptions.index' },
        { label: 'Detalle', route: '' }
      ]"
    />

    <section class="section-heading">
      <div class="container-fluid">
        <div class="row mb-3">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.subscriptions.index')" />
          </div>
        </div>
      </div>
    </section>

    <section class="section-data mb-5">
      <div class="container-fluid">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Alumno:</strong>
                <div>{{ subscription.user?.name }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <strong>Curso:</strong>
                <div>{{ subscription.course?.title }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <strong>Monto:</strong>
                <div>{{ formatCurrency(subscription.payment_amount) }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <strong>Moneda:</strong>
                <td>{{ subscription.currency?.code || 'N/A' }}</td>
              </div>

              <div class="col-md-6 mb-3">
                <strong>Estado de Pago:</strong>
                <div>{{ subscription.payment_status?.name || subscription.payment_status }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <strong>Método de Pago:</strong>
                <div>{{ subscription.payment_type?.name || subscription.payment_type }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <strong>ID de Transacción (Stripe):</strong>
                <div>{{ subscription.payment_stripe_id }}</div>
              </div>

              <div class="col-md-6 mb-3">
                <strong>Fecha de Pago:</strong>
                <div>{{ subscription.payment_date }}</div>
              </div>

              <div class="col-md-6 mb-3" v-if="subscription.payment_card">
                <strong>Tarjeta (4 dígitos):</strong>
                <div>{{ subscription.payment_card }}</div>
              </div>

              <div class="col-md-6 mb-3" v-if="subscription.payment_card_type">
                <strong>Tipo de Tarjeta:</strong>
                <div>{{ subscription.payment_card_type }}</div>
              </div>

              <div class="col-md-6 mb-3" v-if="subscription.payment_card_brand">
                <strong>Marca de Tarjeta:</strong>
                <div>{{ subscription.payment_card_brand }}</div>
              </div>

              <div class="col-md-6 mb-3" v-if="subscription.payment_bank">
                <strong>Banco:</strong>
                <div>{{ subscription.payment_bank }}</div>
              </div>

              <div class="col-md-12 mb-3" v-if="subscription.payment_description">
                <strong>Descripción:</strong>
                <div>{{ subscription.payment_description }}</div>
              </div>

              <div class="col-12 my-4" v-if="subscription.used_coupon">
                <h6 class="section-divider">
                  <span class="bg-white px-3">Cupón Aplicado</span>
                </h6>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <strong>Código del Cupón:</strong>
                    <div>{{ subscription.coupon_id }}</div>
                  </div>

                  <div class="col-md-6 mb-3">
                    <strong>Descuento:</strong>
                    <div>-{{ formatCurrency(subscription.coupon_discount) }}</div>
                  </div>
                </div>
              </div>

              <div class="col-12 my-4" v-if="subscription.payment_refund">
                <h6 class="section-divider">
                  <span class="bg-white px-3">Reembolso</span>
                </h6>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <strong>Fecha de Reembolso:</strong>
                    <div>{{ subscription.payment_refund_date }}</div>
                  </div>

                  <div class="col-md-6 mb-3">
                    <strong>Motivo:</strong>
                    <div>{{ subscription.payment_refund_desc }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'

const props = defineProps({
  subscription: Object
})

const formatCurrency = (value) => {
  const amount = parseFloat(value) || 0
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 2
  }).format(amount)
}
</script>

<style scoped>
.section-divider {
  border-bottom: 1px solid #ddd;
  line-height: 0.1em;
  margin: 20px 0;
  text-align: center;
}
.section-divider span {
  background: #fff;
  padding: 0 10px;
  font-weight: bold;
  font-size: 0.9rem;
}
</style>
