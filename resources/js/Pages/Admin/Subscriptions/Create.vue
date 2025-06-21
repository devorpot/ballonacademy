<template>
  <Head title="Crear Nueva Suscripción" />
  <AdminLayout>
    <div class="position-relative">
      <div :class="{ 'blur-overlay': form.processing }">
        <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: 'admin.dashboard' },
            { label: 'Suscripciones', route: 'admin.subscriptions.index' },
            { label: 'Crear', route: '' }
          ]"
        />

        <section class="section-heading my-2">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.subscriptions.index')" />
                <button
                  class="btn btn-primary"
                  :disabled="form.processing || !isFormValid"
                  @click="submit"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-1"></i> Guardar
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="section-form my-2">
          <div class="container-fluid">
            <form @submit.prevent="submit" novalidate>
              <div class="card">
                <div class="card-body">
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
                        :options="userOptions"
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
                        :options="courseOptions"
                        @blur="() => handleBlur('course_id')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText
                        id="payment_amount"
                        label="Monto"
                        type="number"
                        v-model="form.payment_amount"
                        :required="true"
                        :showValidation="touched.payment_amount"
                        :formError="form.errors.payment_amount"
                        :validateFunction="() => validateField('payment_amount')"
                        placeholder="Ingrese el monto"
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
                        placeholder="Ingrese la moneda"
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
                        placeholder="Ingrese el estado"
                        @blur="() => handleBlur('payment_status')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_description" label="Descripción" v-model="form.payment_description" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_type" label="Tipo de Pago" v-model="form.payment_type" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_card" label="Tarjeta" v-model="form.payment_card" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_card_type" label="Tipo de Tarjeta" v-model="form.payment_card_type" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_card_brand" label="Marca de Tarjeta" v-model="form.payment_card_brand" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_bank" label="Banco" v-model="form.payment_bank" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldDate id="payment_date" label="Fecha de Pago" v-model="form.payment_date" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldDate id="payment_refund_date" label="Fecha de Reembolso" v-model="form.payment_refund_date" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_refund_desc" label="Descripción del Reembolso" v-model="form.payment_refund_desc" />
                    </div>
                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Guardar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';

import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';

const props = defineProps({
  users: Array,
  courses: Array
});

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
const userOptions = props.users.map(u => ({ value: u.id, label: `${u.name} (${u.email})` }));
const courseOptions = props.courses.map(c => ({ value: c.id, label: c.title }));

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
    return `El campo ${field.replace('_', ' ')} es obligatorio`;
  }
  return '';
};

const isFormValid = computed(() => {
  return !validateField('user_id') &&
         !validateField('course_id') &&
         !validateField('payment_amount') &&
         !validateField('payment_currency') &&
         !validateField('payment_status');
});

const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);

  if (isFormValid.value) {
    form.post(route('admin.subscriptions.store'), {
      preserveScroll: true,
      onSuccess: () => form.reset()
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
