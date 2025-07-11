<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-plus-circle me-2"></i> Crear Suscripción
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit" novalidate>
          <div class="modal-body position-relative">
            <div :class="{ 'blur-overlay': form.processing }">
              <div class="row">
                <!-- Campos requeridos -->
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
                    :options="courseOptions"
                    @blur="() => handleBlur('course_id')"
                    @change="handleCourseChange"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldMoney
                    id="payment_amount"
                    label="Monto"
                    v-model="form.payment_amount"
                    :required="true"
                    :showValidation="touched.payment_amount"
                    :formError="form.errors.payment_amount"
                    placeholder="0.00"
                    @blur="() => handleBlur('payment_amount')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldSelect
                    id="payment_currency"
                    label="Moneda"
                    v-model="form.payment_currency"
                    :required="true"
                    :showValidation="touched.payment_currency"
                    :formError="form.errors.payment_currency"
                    :options="currencyOptions"
                    @blur="() => handleBlur('payment_currency')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldSelect
                    id="payment_status_id"
                    label="Estado del Pago"
                    v-model="form.payment_status_id"
                    :required="true"
                    :showValidation="touched.payment_status_id"
                    :formError="form.errors.payment_status_id"
                    :options="paymentStatusOptions"
                    @blur="() => handleBlur('payment_status_id')"
                  />
                </div>

                <div class="col-md-6 mb-3">
                  <FieldSelect
                    id="payment_type_id"
                    label="Método de Pago"
                    v-model="form.payment_type_id"
                    :required="true"
                    :showValidation="touched.payment_type_id"
                    :formError="form.errors.payment_type_id"
                    :options="paymentTypeOptions"
                    @blur="() => handleBlur('payment_type_id')"
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

                <div class="col-md-6 mb-3">
                  <FieldText
                    id="payment_stripe_id"
                    label="ID de Transacción (Stripe)"
                    v-model="form.payment_stripe_id"
                    :required="true"
                    :showValidation="touched.payment_stripe_id"
                    :formError="form.errors.payment_stripe_id"
                    placeholder="Ej. ch_1A2b3C4d5E6f7G8h9I0j"
                  />
                </div>

                <!-- Campo opcional -->
                <div class="col-12 mb-3">
                  <FieldTextarea
                    id="payment_description"
                    label="Descripción/Notas"
                    v-model="form.payment_description"
                    placeholder="Información adicional sobre el pago"
                  />
                </div>
              </div>
            </div>
            <SpinnerOverlay v-if="form.processing" />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="emit('close')">
              <i class="bi bi-x-lg me-1"></i> Cancelar
            </button>
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="form.processing || !isFormValid"
            >
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
              <i class="bi bi-save me-1"></i> 
              <span v-if="form.processing">Guardando...</span>
              <span v-else>Guardar</span>
            </button>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldDate from '@/Components/Admin/Fields/FieldDate.vue';
import FieldUserSearch from '@/Components/Admin/Fields/FieldUserSearch.vue';
import FieldMoney from '@/Components/Admin/Fields/FieldMoney.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

const props = defineProps({
  show: Boolean,
  courses: { 
    type: Array, 
    default: () => [],
    required: true
  }
});

const emit = defineEmits(['close', 'saved']);

// Formulario con solo campos requeridos
const form = useForm({
  user_id: '',
  course_id: '',
  payment_amount: '',
  payment_currency: 'USD',
  payment_status_id: '',
  payment_type_id: '',
  payment_date: new Date().toISOString().split('T')[0],
  payment_stripe_id: '',
  payment_description: ''
});

const touched = ref({});
const currencyOptions = ref([]);
const paymentStatusOptions = ref([]);
const paymentTypeOptions = ref([]);

// Opciones de cursos formateadas
const courseOptions = computed(() => 
  props.courses.map(course => ({
    value: course.id,
    label: course.title
  }))
);

// Validación de campos
const validateField = (field) => {
  if (!form[field] || (typeof form[field] === 'string' && !form[field].trim())) {
    return `Este campo es obligatorio`;
  }
  return '';
};

// Validación general del formulario
const isFormValid = computed(() => {
  const requiredFields = [
    'user_id',
    'course_id',
    'payment_amount',
    'payment_currency',
    'payment_status_id',
    'payment_type_id',
    'payment_stripe_id'
  ];
  
  return requiredFields.every(field => !validateField(field));
});

const handleBlur = (field) => {
  touched.value[field] = true;
};

// Actualizar monto cuando se selecciona un curso
const handleCourseChange = () => {
  const course = props.courses.find(c => c.id == form.course_id);
  if (course) {
    form.payment_amount = course.price;
    form.payment_currency = course.currency_id || 'USD';
  }
};

// Envío del formulario
const submit = () => {
  Object.keys(form).forEach(key => touched.value[key] = true);
  
  if (!isFormValid.value) return;

  form.post(route('admin.subscriptions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved');
      emit('close');
      form.reset();
    }
  });
};

// Cargar opciones desde API
onMounted(async () => {
  try {
    const [currencies, statuses, types] = await Promise.all([
      axios.get('/admin/options/currencies'),
      axios.get('/admin/options/payment_status'),
      axios.get('/admin/options/payment_type')
    ]);
    
    currencyOptions.value = currencies.data;
    paymentStatusOptions.value = statuses.data;
    paymentTypeOptions.value = types.data;
  } catch (error) {
    console.error('Error loading options:', error);
  }
});
</script>

<style scoped>
.blur-overlay {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
  transition: filter 0.3s ease;
}

.modal-content {
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-bottom: 1px solid #dee2e6;
  padding: 1rem 1.5rem;
}

.modal-title {
  font-weight: 600;
  color: #343a40;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  border-top: 1px solid #dee2e6;
  padding: 1rem 1.5rem;
}

.btn-close {
  font-size: 0.75rem;
}
</style>