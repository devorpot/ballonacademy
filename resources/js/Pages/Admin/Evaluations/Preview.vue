<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';

const props = defineProps({
  evaluation: Object,
  questions: Array
});

// Creamos un objeto reactivo para las respuestas
const responses = ref({});
</script>

<template>
  <Head :title="`Previsualización de Evaluación #${evaluation.id}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Evaluaciones', route: 'admin.evaluations.index' },
        { label: `Previsualizar Evaluación #${evaluation.id}`, route: '' }
      ]"
    />

    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="bi bi-eye me-2"></i>
                Previsualización de la evaluación
              </h5>
              <Link class="btn btn-outline-secondary" :href="route('admin.evaluations.index')">
                <i class="bi bi-arrow-left"></i> Volver
              </Link>
            </div>
            <div class="card-body">
              <form>
                <div v-for="question in questions" :key="question.id" class="mb-4">
                  <div class="mb-2 fw-bold">
                    {{ question.order }}. {{ question.question }}
                    <span class="badge bg-info ms-2" v-if="question.points">Puntos: {{ question.points }}</span>
                  </div>

                  <!-- Pregunta de texto -->
                  <FieldText
                    v-if="question.type === 1"
                    :id="`preview-q-${question.id}`"
                    v-model="responses[question.id]"
                    :label="null"
                    placeholder="Respuesta abierta"
                  />

                  <!-- Pregunta de opción múltiple con radios usando options_json -->
                  <div v-else>
                    <div
                      class="form-check"
                      v-for="option in question.options_json"
                      :key="option.value"
                    >
                      <input
                        class="form-check-input"
                        type="radio"
                        :id="`preview-q-${question.id}-opt${option.value}`"
                        :name="`preview-q-${question.id}`"
                        :value="option.value"
                        v-model="responses[question.id]"
                        disabled
                      />
                      <label class="form-check-label" :for="`preview-q-${question.id}-opt${option.value}`">
                        {{ option.label }}
                      </label>
                    </div>
                  </div>
                </div>
              </form>

              <div class="alert alert-info mt-4">
                Este es un formulario solo para previsualización. No se puede responder desde aquí.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.card {
  margin-top: 2rem;
}
</style>
