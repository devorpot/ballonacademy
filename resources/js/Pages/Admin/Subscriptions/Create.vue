<template>
  <Head title="Crear Suscripción" />
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
                  <i class="bi bi-save me-2"></i> Crear suscripción
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
                       <FieldUserSearch
                    id="user_id"
                    label="Alumno"
                    v-model="form.user_id"
                    :required="true"
                    :showValidation="touched.user_id"
                    :formError="form.errors.user_id"
                    fetch-url="/admin/students/search"
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
                        id="payment_stripe_id"
                        label="Stripe ID"
                        type="number"
                        v-model="form.payment_stripe_id"
                        :required="true"
                        :showValidation="touched.payment_stripe_id"
                        :formError="form.errors.payment_stripe_id"
                        :validateFunction="() => validateField('payment_stripe_id')"
                        placeholder="Ingrese el monto"
                        :readonly="true"
                        @blur="() => handleBlur('payment_stripe_id')"
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
                        :readonly="true"
                        @blur="() => handleBlur('payment_amount')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelectApi
                        id="payment_currency"
                        label="Moneda"
                        v-model="form.payment_currency"
                        :formError="form.errors.payment_currency"
                        :showValidation="touched.payment_currency"
                        api-url="/admin/options/currencies"
                        @blur="() => handleBlur('payment_currency')"
                      />
                    </div>
                    <div class="col-md-6 mb-3">
                      <FieldSwitch
                        id="used_coupon"
                        label="¿Se usó cupón?"
                        v-model="form.used_coupon"
                        :showValidation="touched.used_coupon"
                        :formError="form.errors.used_coupon"
                        @blur="() => handleBlur('used_coupon')"
                      />
                    </div>

                    <div class="col-md-6 mb-3" v-if="form.used_coupon">
                      <FieldText
                        id="coupon_id"
                        label="ID del Cupón"
                        v-model="form.coupon_id"
                        :required="true"
                        :showValidation="touched.coupon_id"
                        :formError="form.errors.coupon_id"
                        :validateFunction="() => validateField('coupon_id')"
                        placeholder="Ej. CUPON10"
                        @blur="() => handleBlur('coupon_id')"
                      />
                    </div>

                    <!-- Campo descuento -->
                  <div class="col-md-6 mb-3" v-if="form.used_coupon">
                    <FieldMoney
                      id="coupon_discount"
                      label="Descuento aplicado"
                      v-model="form.coupon_discount"
                      :required="true"
                      :showValidation="touched.coupon_discount"
                      :formError="form.errors.coupon_discount"
                      :validateFunction="() => validateField('coupon_discount')"
                      placeholder="Ej. 150"
                      @blur="() => handleBlur('coupon_discount')"
                    />
                  </div>

                  <!-- Mostrar total con descuento -->
                  <div class="col-md-6 mb-3" v-if="form.used_coupon && form.coupon_discount">
                    <div class="alert alert-info">
                      <strong>Total con descuento:</strong> ${{ discountedAmount }}
                    </div>
                  </div>

                  <div class="col-md-6 mb-3">
                    <FieldSelect
                      id="payment_status"
                      label="Estado del Pago"
                      v-model="form.payment_status"
                      :required="true"
                      :showValidation="touched.payment_status"
                      :formError="form.errors.payment_status"
                      :validateFunction="() => validateField('payment_status')"
                      :options="[
                        { value: 'Pagado', label: 'Pagado' },
                        { value: 'Cancelado', label: 'Cancelado' },
                        { value: 'En Proceso', label: 'En Proceso' }
                      ]"
                      @blur="() => handleBlur('payment_status')"
                    />
                  </div>


                            
                  <div class="col-md-6 mb-3">
                    <FieldSelect
                      id="payment_type"
                      label="Tipo de Pago"
                      v-model="form.payment_type"
                      :showValidation="touched.payment_type"
                      :formError="form.errors.payment_type"
                      :validateFunction="() => validateField('payment_type')"
                      :options="[
                        { value: 'Tarjeta', label: 'Tarjeta' },
                        { value: 'Pago en Oxxo', label: 'Pago en Oxxo' },
                        { value: 'GPay', label: 'GPay' },
                        { value: 'ApplePay', label: 'ApplePay' },
                        { value: 'PayPal', label: 'PayPal' },
                        { value: 'Efectivo', label: 'Efectivo' },
                        { value: 'Deposito Tarjeta', label: 'Depósito Tarjeta' }
                      ]"
                      @blur="() => handleBlur('payment_type')"
                    />
                  </div>


                    <div class="col-md-6 mb-3" v-if="form.payment_type=='Tarjeta'">
                      <FieldText id="payment_card" label="Tarjeta" v-model="form.payment_card" />
                    </div>

                    <div class="col-md-6 mb-3" v-if="form.payment_type=='Tarjeta'">
                      <FieldText id="payment_card_type" label="Tipo de Tarjeta" v-model="form.payment_card_type" />
                    </div>

                    <div class="col-md-6 mb-3" v-if="form.payment_type=='Tarjeta'">
                      <FieldText id="payment_card_brand" label="Marca de Tarjeta" v-model="form.payment_card_brand" />
                    </div>

                    <div class="col-md-6 mb-3" v-if="form.payment_type=='Tarjeta'">
                      <FieldText id="payment_bank" label="Banco" v-model="form.payment_bank" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldText id="payment_description" label="Descripción" v-model="form.payment_description" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldDate id="payment_date" label="Fecha de Pago" v-model="form.payment_date" />
                    </div>

                       <div class="col-md-6 mb-3">
                      <FieldSwitch
                        id="payment_refund"
                        label="¿Se regreso el dinero?"
                        v-model="form.payment_refund"
                        :showValidation="touched.payment_refund"
                        :formError="form.errors.payment_refund"
                        @blur="() => handleBlur('payment_refund')"
                      />
                    </div>

                    <div class="col-md-6 mb-3" v-if="form.payment_refund==true">
                      <FieldDate id="payment_refund_date" label="Fecha de Reembolso" v-model="form.payment_refund_date" />
                    </div>

                    <div class="col-md-6 mb-3" v-if="form.payment_refund==true">
                      <FieldText id="payment_refund_desc" label="Descripción del Reembolso" v-model="form.payment_refund_desc" />
                    </div>
                  </div>
                </div>

                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> Guardar cambios
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
      
      </div>
      <SpinnerOverlay v-if="form.processing" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed, watch } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldUserSearch from '@/Components/Admin/Fields/FieldUserSearch.vue';
import FieldSelectApi from '@/Components/Admin/Fields/FieldSelectApi.vue';
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue';
import FieldMoney from '@/Components/Admin/Fields/FieldMoney.vue';

const props = defineProps({
  users: Array,
  courses: Array
});

const form = useForm({
  user_id: '',
  course_id: '',
  payment_amount: '',
  payment_currency: 'MXN',
  payment_description: '',
  payment_type: 'Tarjeta',
  payment_card: '',
  payment_card_type: '',
  payment_card_brand: '',
  payment_bank: '',
  payment_date: new Date().toISOString().split('T')[0],
  payment_refund: false,
  payment_refund_date: '',
  payment_refund_desc: '',
  payment_status: 'Pagado',
  payment_stripe_id: '',
  used_coupon: false,
  coupon_id: '',
  coupon_discount: ''
});

const touched = ref({});
const originalAmount = ref(0);

const userOptions = props.users.map(u => ({ value: u.id, label: `${u.name} (${u.email})` }));
const courseOptions = props.courses.map(c => ({ value: c.id, label: c.title }));

const discountedAmount = computed(() => {
  const base = originalAmount.value;
  const discount = parseFloat(form.coupon_discount || 0);
  if (isNaN(base) || isNaN(discount)) return base;
  return form.used_coupon ? Math.max(0, base - discount) : base;
});

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  const base = parseFloat(originalAmount.value);
  const discount = parseFloat(form.coupon_discount || 0);

  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
    if ((field === 'coupon_id' || field === 'coupon_discount') && form.used_coupon) {
      return `El campo ${field.replace('_', ' ')} es obligatorio`;
    }
    if (!form.used_coupon && (field === 'coupon_id' || field === 'coupon_discount')) {
      return '';
    }
    return `El campo ${field.replace('_', ' ')} es obligatorio`;
  }

  if (field === 'coupon_discount' && form.used_coupon) {
    if (isNaN(discount)) return 'El descuento debe ser un número válido';
    if (discount > base) return 'El descuento no puede ser mayor al monto original';
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
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => form.reset()
    });
  }
};

watch(() => form.course_id, (newCourseId) => {
  const selectedCourse = props.courses.find(course => course.id === newCourseId);
  if (selectedCourse) {
    originalAmount.value = parseFloat(selectedCourse.price || 0);
    form.payment_amount = selectedCourse.price;
  }
});

watch([
  () => form.used_coupon,
  () => form.coupon_discount
], () => {
  if (form.used_coupon && form.coupon_discount) {
    const base = originalAmount.value;
    const discount = parseFloat(form.coupon_discount || 0);
    const discounted = Math.max(0, base - discount);
    form.payment_amount = discounted.toFixed(2);
  } else {
    form.payment_amount = originalAmount.value;
  }
});
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>