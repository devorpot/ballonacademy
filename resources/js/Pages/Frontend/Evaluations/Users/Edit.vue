<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  evaluationUser: Object,
});

const form = useForm({
  status: props.evaluationUser.status,
  approved_user_id: props.evaluationUser.approved_user_id,
});

function submit() {
  form.put(route('admin.evaluation-users.update-status', props.evaluationUser.id), {
    preserveScroll: true,
    onSuccess: () => alert('Estado actualizado'),
  });
}
</script>

<template>
  <Head :title="`Editar Evaluación #${evaluationUser.id}`" />
  <div class="container py-6 max-w-xl">
    <h1 class="text-2xl font-bold mb-6">Editar Evaluación</h1>
    <div class="mb-4">
      <label class="font-semibold mb-2 block">Estado</label>
      <select v-model="form.status" class="form-select">
        <option :value="0">Enviada</option>
        <option :value="1">En revisión</option>
        <option :value="2">Aprobada</option>
        <option :value="3">No aprobada</option>
      </select>
    </div>
    <button class="btn btn-success" @click="submit" :disabled="form.processing">
      Guardar cambios
    </button>
    <Link :href="route('admin.evaluation-users.index')" class="btn btn-link ms-3">Regresar</Link>
  </div>
</template>
