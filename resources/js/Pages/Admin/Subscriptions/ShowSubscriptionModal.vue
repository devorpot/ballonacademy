<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-card-checklist me-2"></i> Suscripción #{{ subscription.id }}
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <h5 class="fw-bold mb-3">Información Básica</h5>
          <div class="row">
            <div class="col-md-4 mb-3" v-for="(field, index) in textFields" :key="index">
              <label class="form-label text-muted">{{ field.label }}</label>
              <p class="form-control-plaintext">{{ field.value }}</p>
            </div>
          </div>

          <h5 class="fw-bold mt-4 mb-3">Detalles del Pago</h5>
          <div class="row">
            <div class="col-md-4 mb-3" v-for="(field, index) in paymentFields" :key="'payment-' + index">
              <label class="form-label text-muted">{{ field.label }}</label>
              <p class="form-control-plaintext">{{ field.value }}</p>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" @click="emit('close')">
            <i class="bi bi-x-lg me-1"></i> Cerrar
          </button>
        </div>
      </div>
    </div>

    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: Boolean,
  subscription: Object
});

const emit = defineEmits(['close']);

const textFields = computed(() => [
  { label: 'Usuario', value: `${props.subscription.user.name} (${props.subscription.user.email})` },
  { label: 'Curso', value: props.subscription.course.title },
  { label: 'Monto', value: props.subscription.payment_amount },
  { label: 'Moneda', value: props.subscription.payment_currency },
  { label: 'Estado del Pago', value: props.subscription.payment_status },
  { label: 'Fecha de Pago', value: props.subscription.payment_date || '-' },
  { label: 'Fecha de Reembolso', value: props.subscription.payment_refund_date || '-' },
  { label: 'Descripción del Reembolso', value: props.subscription.payment_refund_desc || '-' },
  { label: 'Fecha de Creación', value: props.subscription.created_at ? new Date(props.subscription.created_at).toLocaleString() : '-' }
]);

const paymentFields = computed(() => [
  { label: 'Tipo de Pago', value: props.subscription.payment_type || '-' },
  { label: 'Tarjeta', value: props.subscription.payment_card || '-' },
  { label: 'Tipo de Tarjeta', value: props.subscription.payment_card_type || '-' },
  { label: 'Marca de Tarjeta', value: props.subscription.payment_card_brand || '-' },
  { label: 'Banco', value: props.subscription.payment_bank || '-' },
  { label: 'Descripción', value: props.subscription.payment_description || '-' }
]);
</script>

<style scoped>
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.table td, .table th {
  vertical-align: middle;
}
</style>
