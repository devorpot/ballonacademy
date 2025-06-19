<template>
  <Head title="Crear Nueva Suscripción" />
  <AdminLayout>
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
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <Title :title="'Crear Suscripción'" />
            <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.subscriptions.index')" />
          </div>
        </div>
      </div>
    </section>

    <section class="section-form my-2">
      <div class="container-fluid">
        <form @submit.prevent="submit" novalidate>
          <div class="card">
            <div class="card-body">
              <h6 class="text-muted mb-3">Datos de la suscripción</h6>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Usuario</label>
                  <select
                    class="form-control"
                    v-model="form.user_id"
                    :class="{ 'is-invalid': touched.user_id && validateField('user_id') }"
                    @blur="handleBlur('user_id')"
                  >
                    <option value="">Seleccione un usuario</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }} ({{ user.email }})
                    </option>
                  </select>
                  <div class="invalid-feedback">
                    {{ validateField('user_id') }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Curso</label>
                  <select
                    class="form-control"
                    v-model="form.course_id"
                    :class="{ 'is-invalid': touched.course_id && validateField('course_id') }"
                    @blur="handleBlur('course_id')"
                  >
                    <option value="">Seleccione un curso</option>
                    <option v-for="course in courses" :key="course.id" :value="course.id">
                      {{ course.title }}
                    </option>
                  </select>
                  <div class="invalid-feedback">
                    {{ validateField('course_id') }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Monto</label>
                  <input type="number" class="form-control" v-model="form.payment_amount" @blur="handleBlur('payment_amount')"
                         :class="{ 'is-invalid': touched.payment_amount && validateField('payment_amount') }">
                  <div class="invalid-feedback">
                    {{ validateField('payment_amount') }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Moneda</label>
                  <input type="text" class="form-control" v-model="form.payment_currency" @blur="handleBlur('payment_currency')"
                         :class="{ 'is-invalid': touched.payment_currency && validateField('payment_currency') }">
                  <div class="invalid-feedback">
                    {{ validateField('payment_currency') }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Descripción</label>
                  <input type="text" class="form-control" v-model="form.payment_description">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Tipo de Pago</label>
                  <input type="text" class="form-control" v-model="form.payment_type">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Tarjeta</label>
                  <input type="text" class="form-control" v-model="form.payment_card">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Tipo de Tarjeta</label>
                  <input type="text" class="form-control" v-model="form.payment_card_type">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Marca de Tarjeta</label>
                  <input type="text" class="form-control" v-model="form.payment_card_brand">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Banco</label>
                  <input type="text" class="form-control" v-model="form.payment_bank">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha de Pago</label>
                  <input type="date" class="form-control" v-model="form.payment_date">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha de Reembolso</label>
                  <input type="date" class="form-control" v-model="form.payment_refund_date">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Descripción del Reembolso</label>
                  <input type="text" class="form-control" v-model="form.payment_refund_desc">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Estado del Pago</label>
                  <input type="text" class="form-control" v-model="form.payment_status" @blur="handleBlur('payment_status')"
                         :class="{ 'is-invalid': touched.payment_status && validateField('payment_status') }">
                  <div class="invalid-feedback">
                    {{ validateField('payment_status') }}
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i class="bi bi-save me-2"></i>Guardar
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Title from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';

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

const handleBlur = (field) => {
  touched.value[field] = true;
};

const validateField = (field) => {
  if (!form[field]) return `El campo ${field} es obligatorio`;
  return '';
};

const isFormValid = computed(() => {
  return ['user_id', 'course_id', 'payment_amount', 'payment_currency', 'payment_status']
    .every(f => !validateField(f));
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
