<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import { route } from 'ziggy-js';
import draggable from 'vuedraggable';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';

import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldSelect from '@/Components/Admin/Fields/FieldSelect.vue';
import FieldSwitch from '@/Components/Admin/Fields/FieldSwitch.vue';

const props = defineProps({
  evaluation: Object,
  question: Object,
});

const maxOptions = 5;

// Inicializar opciones desde options_json
const initialOptions = (() => {
  // Si ya existen opciones en options_json, úsalas. Si no, inicializa tres vacías.
  if (props.question.options_json && props.question.options_json.length > 0) {
    return props.question.options_json.map((opt, idx) => ({
      id: idx + 1,
      value: opt.value ?? idx + 1,
      label: opt.label ?? ''
    }));
  }
  return [
    { id: 1, value: 1, label: '' },
    { id: 2, value: 2, label: '' },
    { id: 3, value: 3, label: '' }
  ];
})();
const options = ref([...initialOptions]);
let nextOptionId = options.value.length + 1;

// Inicializar form
const form = useForm({
  type: Number(props.question.type), // 0 = opción múltiple, 1 = abierta
  question: props.question.question,
  response_text: props.question.response_text ?? '',
  response_option: props.question.response_option ?? null,
  status: !!props.question.status,
});

const toast = ref(null);
const page = usePage();

onMounted(() => {
  if (page.props.flash.success) {
    toast.value = { message: page.props.flash.success, type: 'success' };
  }
  if (page.props.flash.error) {
    toast.value = { message: page.props.flash.error, type: 'danger' };
  }
});

const isMultiple = computed(() => form.type == 0);
const isOpen = computed(() => form.type == 1);

const canAddOption = computed(() => options.value.length < maxOptions);
const canRemoveOption = computed(() => options.value.length > 2);

const validOptions = computed(() =>
  options.value
    .map((opt, idx) => ({
      ...opt,
      number: idx + 1,
    }))
    .filter(opt => opt.label && opt.label.trim())
);

const isFormValid = computed(() => {
  if (!form.question.trim()) return false;
  if (form.type == 1 && !form.response_text.trim()) return false;
  if (form.type == 0) {
    if (validOptions.value.length < 2) return false;
    if (!form.response_option || !validOptions.value.find(o => o.value == form.response_option)) return false;
  }
  return true;
});

const addOption = () => {
  if (options.value.length < maxOptions) {
    options.value.push({ id: nextOptionId, value: nextOptionId, label: '' });
    nextOptionId++;
    nextTick(() => {
      if (options.value.length < 2) options.value.push({ id: nextOptionId++, value: nextOptionId, label: '' });
    });
  }
};

const removeOption = (idx) => {
  if (options.value.length > 2) {
    options.value.splice(idx, 1);
    if (
      form.response_option &&
      !validOptions.value.find(o => o.value == form.response_option)
    ) {
      form.response_option = null;
    }
  }
};

const handleTypeChange = () => {
  if (form.type == 0 && options.value.length < 2) {
    while (options.value.length < 2) options.value.push({ id: nextOptionId, value: nextOptionId, label: '' }), nextOptionId++;
  }
  if (form.type == 1) {
    form.response_option = null;
  }
};

const submit = () => {
  form.transform((data) => ({
    ...data,
    options_json: isMultiple.value
      ? validOptions.value.map(opt => ({
          value: opt.value,
          label: opt.label
        }))
      : [],
  })).put(route('admin.evaluation-questions.update', [props.evaluation.id, props.question.id]), {
    preserveScroll: true,
    onSuccess: () => {
      toast.value = { message: 'Pregunta actualizada correctamente', type: 'success' };
      setTimeout(() => window.location = route('admin.evaluation-questions.index', props.evaluation.id), 1000);
    }
  });
};
</script>

<template>
  <Head :title="`Editar Pregunta | Evaluación #${evaluation.id}`" />
  <AdminLayout>
    <Breadcrumbs
      username="admin"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'admin.dashboard' },
        { label: 'Evaluaciones', route: 'admin.evaluations.index' },
        { label: `Preguntas Evaluación #${evaluation.id}`, route: 'admin.evaluation-questions.index', params: [evaluation.id] },
        { label: 'Editar Pregunta', route: '' }
      ]"
    />

    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-10 col-lg-12">
          <div class="card">
            <form @submit.prevent="submit">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <FieldSelect
                      id="type"
                      label="Tipo de Pregunta"
                      v-model="form.type"
                      :options="[
                        { value: 0, label: 'Opción múltiple' },
                        { value: 1, label: 'Abierta' }
                      ]"
                      required
                      :formError="form.errors.type"
                      @change="handleTypeChange"
                    />
                  </div>
                  <div class="col-md-6 mb-3">
                    <FieldSwitch
                      id="status"
                      label="Pregunta activa"
                      v-model="form.status"
                      :formError="form.errors.status"
                    />
                  </div>
                  <div class="col-md-12 mb-3">
                    <FieldText
                      id="question"
                      label="Pregunta"
                      v-model="form.question"
                      :required="true"
                      :maxlength="255"
                      :formError="form.errors.question"
                      placeholder="Escribe la pregunta"
                    />
                  </div>
                  <div class="col-md-12 mb-3">
                    <template v-if="isMultiple">
                      <label class="form-label mb-2">Opciones (Arrastra para reordenar)</label>
                      <draggable v-model="options" handle=".drag-handle" :animation="150" item-key="id">
                        <template #item="{ element: option, index: idx }">
                          <div class="d-flex align-items-center mb-2">
                            <span class="drag-handle me-2" style="cursor:grab"><i class="bi bi-list"></i></span>
                            <FieldText
                              :id="`option_${option.id}`"
                              :label="null"
                              v-model="option.label"
                              :required="idx < 2"
                              :maxlength="255"
                              :placeholder="`Opción ${idx + 1}`"
                              class="flex-grow-1"
                            />
                            <button v-if="canRemoveOption" type="button" class="btn btn-outline-danger btn-sm ms-2" @click="removeOption(idx)" title="Quitar opción">
                              <i class="bi bi-x"></i>
                            </button>
                          </div>
                        </template>
                      </draggable>
                      <button
                        type="button"
                        class="btn btn-outline-success btn-sm my-2"
                        @click="addOption"
                        :disabled="!canAddOption"
                      >
                        <i class="bi bi-plus-lg"></i> Agregar opción
                      </button>
                      <div class="mb-3 mt-3">
                        <FieldSelect
                          id="response_option"
                          label="Respuesta correcta"
                          v-model="form.response_option"
                          :options="validOptions.map(opt => ({
                            value: opt.value,
                            label: `${opt.value}. ${opt.label}`
                          }))"
                          :required="true"
                          :disabled="validOptions.length < 2"
                          :formError="form.errors.response_option"
                          placeholder="Selecciona la opción correcta"
                        />
                      </div>
                    </template>
                  </div>
                  <div class="col-md-6 mb-3">
                    <template v-if="isOpen">
                      <FieldText
                        id="response_text"
                        label="Respuesta de texto esperada"
                        v-model="form.response_text"
                        :required="true"
                        :maxlength="1000"
                        :textarea="true"
                        :formError="form.errors.response_text"
                        placeholder="Respuesta que esperas del usuario"
                      />
                    </template>
                  </div>
                </div>
              </div>
              <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" :disabled="form.processing || !isFormValid">
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-save me-2"></i> Guardar Cambios
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <ToastNotification v-if="toast" :message="toast.message" :type="toast.type" @hidden="toast = null" />
  </AdminLayout>
</template>

<style scoped>
.card {
  margin-top: 2rem;
}
.drag-handle {
  cursor: grab;
  color: #6c757d;
  font-size: 1.3em;
}
</style>
