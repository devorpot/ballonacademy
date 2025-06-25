<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-person-plus me-2"></i> Asignar Alumnos al Curso: {{ course.title }}
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <input type="text" class="form-control mb-3" v-model="search" placeholder="Buscar alumno por nombre o email">
          
          <div v-if="filteredStudents.length > 0">
            <div v-for="student in filteredStudents" :key="student.id" class="form-check">
              <input 
                class="form-check-input" 
                type="checkbox" 
                :value="student.id"
                v-model="selected"
                :id="`student-${student.id}`"
              >
              <label :for="`student-${student.id}`" class="form-check-label">
                {{ student.name }} ({{ student.email }})
              </label>
            </div>
          </div>
          <p v-else class="text-muted">No hay resultados para "{{ search }}"</p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="emit('close')">
            Cancelar
          </button>
          <button type="button" class="btn btn-success" @click="assign" :disabled="processing">
            <span v-if="processing" class="spinner-border spinner-border-sm me-1"></span>
            Guardar Asignación
          </button>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';

const props = defineProps({
  show: Boolean,
  course: Object,
  students: Array // Lista de todos los alumnos que carga el padre
});

const emit = defineEmits(['close', 'saved']);

const search = ref('');
const selected = ref([]);
const processing = ref(false);

const filteredStudents = computed(() => {
  const q = search.value.toLowerCase();
  return props.students.filter(s => 
    s.name.toLowerCase().includes(q) || 
    s.email.toLowerCase().includes(q)
  );
});

const assign = async () => {
  processing.value = true;
  try {
    await axios.post(
      route('admin.courses.assign-students', props.course.id),
      { students: selected.value },
      { headers: { 'Content-Type': 'application/json' } }
    );
    emit('saved');
    emit('close');

  } catch (err) {
    console.error(err);
  } finally {
    processing.value = false;
  }
};


const openAssignModal = async (courseId) => {
  const response = await axios.get(route('admin.courses.show', courseId));
  course.value = response.data.course;
  students.value = await axios.get(route('admin.students.list')).then(res => res.data);
  showAssignModal.value = true;
};

// Reset selected when modal opens
watch(() => props.show, (val) => {
  if (val) {
    selected.value = props.course.students?.map(s => s.id) || [];
  }
});
</script>
