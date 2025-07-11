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
                <ButtonBack 
                  label="Volver" 
                  icon="bi bi-arrow-left" 
                  :href="route('admin.subscriptions.index')" 
                  class="btn btn-outline-secondary"
                />
                <button
                  class="btn btn-primary"
                  :disabled="form.processing || !isFormValid"
                  @click="submit"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i> 
                  <span v-if="form.processing">Procesando...</span>
                  <span v-else>Crear suscripción</span>
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="section-form my-2">
          <div class="container-fluid">
            <form @submit.prevent="submit" novalidate>
              <div class="card shadow-sm">
      
                <div class="card-body">
                  <div class="row">
                    <!-- Sección Usuario y Curso -->
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
                        @selected="user => handleUserSelected(user)"
                      />
                    </div>
                     <div class="col-md-6 mb-3">
                      <FieldText
                        id="payment_stripe_id"
                        label="ID de Transacción (Stripe)"
                        v-model="form.payment_stripe_id"
                        :required="true"
                        :showValidation="touched.payment_stripe_id"
                        :formError="form.errors.payment_stripe_id"
                        placeholder="Ej. ch_1A2b3C4d5E6f7G8h9I0j"
                        @blur="() => handleBlur('payment_stripe_id')"
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
                        @change="handleCourseChange"
                      />
                    </div>

                   <div class="col-md-6 mb-3">
                      <FieldMoney
                        id="payment_amount"
                        label="Monto Total"
                        v-model="form.payment_amount"
                        :required="true"
                        :showValidation="touched.payment_amount"
                        :formError="form.errors.payment_amount"
                        placeholder="0.00"
                        :readonly="true"
                        @blur="() => handleBlur('payment_amount')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelectApi
                        id="payment_currency"
                        label="Moneda"
                        v-model="form.payment_currency"
                        :required="true"
                        :readonly="true"
                        :formError="form.errors.payment_currency"
                        :showValidation="touched.payment_currency"
                        api-url="/admin/options/currencies"
                        @blur="() => handleBlur('payment_currency')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelectApi
                        id="payment_status_id"
                        label="Estado del Pago"
                        v-model="form.payment_status_id"
                        :required="true"
                        :formError="form.errors.payment_status_id"
                        :showValidation="touched.payment_status_id"
                        api-url="/admin/options/payment_status"
                        @blur="() => handleBlur('payment_status_id')"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSelectApi
                        id="payment_type_id"
                        label="Método de Pago"
                        v-model="form.payment_type_id"
                        :required="true"
                        :formError="form.errors.payment_type_id"
                        :showValidation="touched.payment_type_id"
                        api-url="/admin/options/payment_type"
                        @blur="() => handleBlur('payment_type_id')"
                        @change="handlePaymentTypeChange"
                      />
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldDate 
                        id="payment_date"
                        label="Fecha de Pago"
                        v-model="form.payment_date"
                        :required="true"
                        :max-date="new Date().toISOString().split('T')[0]"
                      />
                    </div>

                    <!-- Campos condicionales para tarjetas -->
                    <template v-if="showCardFields">
                      <div class="col-md-4 mb-3">
                        <FieldText 
                          id="payment_card" 
                          label="Últimos 4 dígitos" 
                          v-model="form.payment_card"
                          :maxlength="4"
                          pattern="[0-9]{4}"
                        />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText 
                          id="payment_card_type" 
                          label="Tipo de Tarjeta" 
                          v-model="form.payment_card_type"
                          placeholder="Ej. Crédito/Débito"
                        />
                      </div>
                      <div class="col-md-4 mb-3">
                        <FieldText 
                          id="payment_card_brand" 
                          label="Marca de Tarjeta" 
                          v-model="form.payment_card_brand"
                          placeholder="Ej. Visa, Mastercard"
                        />
                      </div>
                    </template>

                    <!-- Campos condicionales para bancos -->
                    <div class="col-md-6 mb-3" v-if="showBankField">
                      <FieldText 
                        id="payment_bank" 
                        label="Banco" 
                        v-model="form.payment_bank"
                        placeholder="Nombre del banco"
                      />
                    </div>

                    <div class="col-md-12 mb-3">
                      <FieldText 
                        id="payment_description" 
                        label="Descripción/Notas" 
                        v-model="form.payment_description"
                        placeholder="Detalles adicionales del pago"
                      />
                    </div>

                    <!-- Sección Cupón -->
                    <div class="col-12 mt-4 mb-2">
                      <h6 class="section-divider">
                        <span class="bg-white px-3">Información de Cupón</span>
                      </h6>
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSwitch
                        id="used_coupon"
                        label="¿Se aplicó cupón de descuento?"
                        v-model="form.used_coupon"
                        :showValidation="touched.used_coupon"
                        :formError="form.errors.used_coupon"
                        @blur="() => handleBlur('used_coupon')"
                      />
                    </div>

                    <template v-if="form.used_coupon">
                      <div class="col-md-6 mb-3">
                        <FieldText
                          id="coupon_id"
                          label="Código del Cupón"
                          v-model="form.coupon_id"
                          :required="form.used_coupon"
                          :showValidation="touched.coupon_id"
                          :formError="form.errors.coupon_id"
                          placeholder="Ej. CUPON10"
                          @blur="() => handleBlur('coupon_id')"
                        />
                      </div>

                      <div class="col-md-6 mb-3">
                        <FieldMoney
                          id="coupon_discount"
                          label="Descuento aplicado"
                          v-model="form.coupon_discount"
                          :required="form.used_coupon"
                          :showValidation="touched.coupon_discount"
                          :formError="form.errors.coupon_discount"
                          placeholder="0.00"
                          @blur="() => handleBlur('coupon_discount')"
                          @input="updateDiscountedAmount"
                        />
                      </div>

                      <div class="col-md-6 mb-3">
                        <div class="alert alert-success">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <strong>Monto original:</strong> {{ formatCurrency(originalAmount) }}<br>
                              <strong>Descuento:</strong> -{{ formatCurrency(form.coupon_discount || 0) }}
                            </div>
                            <div class="fs-5">
                              <strong>Total a pagar:</strong> {{ formatCurrency(discountedAmount) }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </template>

                    <!-- Sección Reembolso -->
                    <div class="col-12 mt-4 mb-2">
                      <h6 class="section-divider">
                        <span class="bg-white px-3">Información de Reembolso</span>
                      </h6>
                    </div>

                    <div class="col-md-6 mb-3">
                      <FieldSwitch
                        id="payment_refund"
                        label="¿Se realizó reembolso?"
                        v-model="form.payment_refund"
                        :showValidation="touched.payment_refund"
                        :formError="form.errors.payment_refund"
                        @blur="() => handleBlur('payment_refund')"
                      />
                    </div>

                    <template v-if="form.payment_refund">
                      <div class="col-md-6 mb-3">
                        <FieldDate 
                          id="payment_refund_date"
                          label="Fecha de Reembolso"
                          v-model="form.payment_refund_date"
                          :required="form.payment_refund"
                          :max-date="new Date().toISOString().split('T')[0]"
                        />
                      </div>

                      <div class="col-md-12 mb-3">
                        <FieldText 
                          id="payment_refund_desc" 
                          label="Motivo del Reembolso" 
                          v-model="form.payment_refund_desc"
                          :required="form.payment_refund"
                          placeholder="Razón del reembolso"
                        />
                      </div>
                    </template>
                  </div>
                </div>

                <div class="card-footer bg-light text-end">
                  <button 
                    type="submit" 
                    class="btn btn-primary" 
                    :disabled="form.processing || !isFormValid"
                  >
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-save me-2"></i> 
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Guardar suscripción</span>
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
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed, watch, onMounted } from 'vue'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue'
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue'
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue'
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue'
import FieldText from '@/Components/Admin/Fields/FieldText.vue'
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue'
import FieldUserSearch from '@/Components/Admin/Fields/FieldUserSearch.vue'
import FieldSelectApi from '@/Components/Admin/Fields/FieldSelectApi.vue'
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue'
import FieldMoney from '@/Components/Admin/Fields/FieldMoney.vue'

const props = defineProps({
  courses: Array,
  subscription: Object, // 👈 para Edit
  defaultCurrency: {
    type: String,
    default: 'USD'
  }
})

const form = useForm({
  user_id: props.subscription?.user_id || '',
  course_id: props.subscription?.course_id || '',
  payment_type_id: props.subscription?.payment_type_id || 1,
  payment_status_id: props.subscription?.payment_status_id || 1,
  payment_amount: props.subscription?.payment_amount || '',
  payment_currency: props.subscription?.payment_currency || props.defaultCurrency,
  payment_description: props.subscription?.payment_description || '',
  payment_card: props.subscription?.payment_card || '',
  payment_card_type: props.subscription?.payment_card_type || '',
  payment_card_brand: props.subscription?.payment_card_brand || '',
  payment_bank: props.subscription?.payment_bank || '',
  payment_date: props.subscription?.payment_date || new Date().toISOString().split('T')[0],
  payment_refund: props.subscription?.payment_refund || false,
  payment_refund_date: props.subscription?.payment_refund_date || '',
  payment_refund_desc: props.subscription?.payment_refund_desc || '',
  payment_stripe_id: props.subscription?.payment_stripe_id || '',
  used_coupon: props.subscription?.used_coupon || false,
  coupon_id: props.subscription?.coupon_id || '',
  coupon_discount: props.subscription?.coupon_discount || ''
})

const isEditing = computed(() => !!props.subscription?.id)
const touched = ref({})
const originalAmount = ref(parseFloat(props.subscription?.course?.price) || 0)

const courseOptions = computed(() =>
  props.courses.map(c => ({
    value: c.id,
    label: `${c.title} - ${formatCurrency(c.price)}`
  }))
)

const selectedCourse = computed(() =>
  props.courses.find(c => c.id == form.course_id)
)

watch(() => form.course_id, (newVal) => {
  if (!isEditing.value) {
    const course = props.courses.find(c => c.id == newVal)
    if (course) {
      originalAmount.value = parseFloat(course.price) || 0
      form.payment_amount = course.price
      form.payment_currency = course.currency_id
    }
  }
})

const showCardFields = computed(() =>
  [1, 2, 7, 8].includes(parseInt(form.payment_type_id))
)

const showBankField = computed(() =>
  [1, 2, 3, 7, 8].includes(parseInt(form.payment_type_id))
)

const discountedAmount = computed(() => {
  const base = parseFloat(originalAmount.value) || 0
  const discount = parseFloat(form.coupon_discount) || 0
  return !form.used_coupon ? base : Math.max(0, base - discount)
})

const isFormValid = computed(() => {
  const requiredFields = [
    'user_id',
    'course_id',
    'payment_amount',
    'payment_currency',
    'payment_status_id',
    'payment_type_id',
    'payment_stripe_id'
  ]

  for (const field of requiredFields) {
    if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
      return false
    }
  }

  if (form.used_coupon) {
    if (!form.coupon_id || !form.coupon_discount) return false
    if (parseFloat(form.coupon_discount) > originalAmount.value) return false
  }

  if (form.payment_refund && !form.payment_refund_date) return false

  return true
})

const updateDiscountedAmount = () => {
  if (form.used_coupon && form.coupon_discount) {
    form.payment_amount = Math.max(0, originalAmount.value - parseFloat(form.coupon_discount)).toFixed(2)
  } else {
    form.payment_amount = originalAmount.value
  }
}

const handleBlur = (field) => {
  touched.value[field] = true
}

const validateField = (field) => {
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
    return `El campo es obligatorio`
  }

  if (field === 'coupon_discount' && form.used_coupon) {
    const discount = parseFloat(form.coupon_discount)
    if (isNaN(discount)) return 'Debe ser un número válido'
    if (discount > originalAmount.value) return 'No puede ser mayor al monto original'
  }

  return ''
}

const formatCurrency = (value) => {
  const amount = parseFloat(value) || 0
  const options = {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
    useGrouping: true
  }
  let formatted = new Intl.NumberFormat('es-ES', options).format(amount)
  return amount % 1 === 0 ? formatted.replace(/,\d{2}$/, '') : formatted
}

const submit = () => {
  Object.keys(form).forEach((key) => (touched.value[key] = true))
  if (!isFormValid.value) return

  form.payment_amount = discountedAmount.value

  const action = isEditing.value
    ? form.put(route('admin.subscriptions.update', props.subscription.id))
    : form.post(route('admin.subscriptions.store'))

  action
    .then(() => {
      if (!isEditing.value) form.reset()
    })
    .catch(() => {
      // manejar errores
    })
}

// Watchers para cupones y reembolsos
watch(() => form.used_coupon, (val) => {
  if (!val) {
    form.coupon_id = ''
    form.coupon_discount = ''
    form.payment_amount = originalAmount.value
  }
})

watch(() => form.coupon_discount, () => {
  if (form.used_coupon) updateDiscountedAmount()
})

watch(() => form.payment_refund, (val) => {
  if (!val) {
    form.payment_refund_date = ''
    form.payment_refund_desc = ''
  }
})
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}
</style>