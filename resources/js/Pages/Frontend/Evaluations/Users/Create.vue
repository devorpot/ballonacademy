<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
  user_id: '',
  course_id: '',
  evaluation_id: '',
  comments: '',
  status: 0,
});

function submit() {
  form.post(route('admin.evaluation-users.store'), {
    preserveScroll: true,
    onSuccess: () => alert('Evaluación creada'),
  });
}
</script>

<template>
  <Head title="Crear Evaluación" />
  <div class="container py-6 max-w-xl">
    <h1 class="text-2xl font-bold mb-6">Crear Evaluación para Usuario</h1>
    <div class="mb-4">
      <label class="font-semibold mb-2 block">Usuario (ID)</label>
      <input v-model="form.user_id" type="text" class="form-control" />
    </div>
    <div class="mb-4">
      <label class="font-semibold mb-2 block">Curso (ID)</label>
      <input v-model="form.course_id" type="text" class="form-control" />
    </div>
    <div class="mb-4">
      <label class="font-semibold mb-2 block">Evaluación (ID)</label>
      <input v-model="form.evaluation_id" type="text" class="form-control" />
    </div>
    <div class="mb-4">
      <label class="font-semibold mb-2 block">Comentarios</label>
      <textarea v-model="form.comments" class="form-control"></textarea>
    </div>
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
      Crear Evaluación
    </button>
    <Link :href="route('admin.evaluation-users.index')" class="btn btn-link ms-3">Regresar</Link>
  </div>
</template>
