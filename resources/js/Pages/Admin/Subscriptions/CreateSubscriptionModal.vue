<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-plus-circle me-2"></i> Crear Suscripción
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': form.processing }">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <FieldSelect
                    id="user_id"
                    label="Usuario"
                    v-model="form.user_id"
                    :required="true"
                    :showValidation="touched.user_id"
                    :formError="form.errors.user_id"
                    :validateFunction="() => validateField('user_id')"
                    :options="props.users.map(u => ({ value: u.id, label: `${u.name} (${u.email})` }))"
                    @blur="() => handleBlur('user_id')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldSelect
                    id="course_id"
                    label="Curso"
                    v-model="form.course_id"
                    :required="true"
                    :showValidation="touched.course_id"
                    :formError="form.errors.course_id"
                    :validateFunction="() => validateField('course_id')"
                    :options="props.courses.map(c => ({ value: c.id, label: c.title }))"
                    @blur="() => handleBlur('course_id')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_amount"
                    label="Monto"
                    v-model="form.payment_amount"
                    :required="true"
                    :showValidation="touched.payment_amount"
                    :formError="form.errors.payment_amount"
                    :validateFunction="() => validateField('payment_amount')"
                    placeholder="Monto del pago"
                    @blur="() => handleBlur('payment_amount')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_currency"
                    label="Moneda"
                    v-model="form.payment_currency"
                    :required="true"
                    :showValidation="touched.payment_currency"
                    :formError="form.errors.payment_currency"
                    :validateFunction="() => validateField('payment_currency')"
                    placeholder="Moneda del pago"
                    @blur="() => handleBlur('payment_currency')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_status"
                    label="Estado del Pago"
                    v-model="form.payment_status"
                    :required="true"
                    :showValidation="touched.payment_status"
                    :formError="form.errors.payment_status"
                    :validateFunction="() => validateField('payment_status')"
                    placeholder="Estado del pago"
                    @blur="() => handleBlur('payment_status')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldDate
                    id="payment_date"
                    label="Fecha de Pago"
                    v-model="form.payment_date"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldDate
                    id="payment_refund_date"
                    label="Fecha de Reembolso"
                    v-model="form.payment_refund_date"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_refund_desc"
                    label="Descripción del Reembolso"
                    v-model="form.payment_refund_desc"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_type"
                    label="Tipo de Pago"
                    v-model="form.payment_type"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_card"
                    label="Tarjeta"
                    v-model="form.payment_card"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_card_type"
                    label="Tipo de Tarjeta"
                    v-model="form.payment_card_type"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_card_brand"
                    label="Marca de Tarjeta"
                    v-model="form.payment_card_brand"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_bank"
                    label="Banco"
                    v-model="form.payment_bank"
                  />
                </div>

                <div class="col-md-12 mb-3">
                  <FieldTextarea
                    id="payment_description"
                    label="Descripción"
                    v-model="form.payment_description"
                  />
                </div>
              </div>
            </div>
            <SpinnerOverlay v-if="form.processing" />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">
              <i class="bi bi-x-lg me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
              <i class="bi bi-save me-1"></i> Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

const props = defineProps({
  show: Boolean,
  users: { type: Array, default: () => [] },
  courses: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
  user_id: '',
  course_id: '',
  payment_amount: '',
  payment_currency: '',
  payment_description: '',
  payment_type: '',
  payment_card: '',
  payment_card_type: '',
  payment_card_brand: '',
  payment_bank: '',
  payment_date: '',
  payment_refund_date: '',
  payment_refund_desc: '',
  payment_status: ''
});

const touched = ref({});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
    return `El campo ${field.replace('_', ' ')} es obligatorio`;
  }
  return '';
};

const isFormValid = computed(() =>
  !validateField('user_id') &&
  !validateField('course_id') &&
  !validateField('payment_amount') &&
  !validateField('payment_currency') &&
  !validateField('payment_status')
);

const submit = () => {
  Object.keys(form).forEach(key => (touched.value[key] = true));

  if (isFormValid.value) {
    form.post(route('admin.subscriptions.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        emit('saved');
        emit('close');
      }
    });
  }
};
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>
