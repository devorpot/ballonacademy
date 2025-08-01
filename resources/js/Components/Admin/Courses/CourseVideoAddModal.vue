<!-- CourseVideoAddModal.vue -->
<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-camera-video-fill me-2"></i> 
            {{ editingId ? 'Editar Video' : 'Agregar Video' }}
          </h5>
          <button type="button" class="btn-close" @click="emit('close')" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body position-relative">
          <CourseVideoForm
            :courses="courses"
            :teachers="teachers"
            v-model="form"
            :processing="processing"
            :errors="errors"
            :submit-label="editingId ? 'Actualizar Video' : 'Guardar Video'"
            @submit="onSubmit"
          >
            <template #footer>
              <div class="d-flex justify-content-end gap-2 mt-2">
                <button type="button" class="btn btn-secondary" @click="emit('close')">
                  <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-success" :disabled="processing">
                  <span v-if="processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-save me-1"></i> {{ editingId ? 'Actualizar Video' : 'Guardar Video' }}
                </button>
              </div>
            </template>
          </CourseVideoForm>
          <SpinnerOverlay v-if="processing" />
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" @click="emit('close')" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import CourseVideoForm from '@/Components/Admin/Courses/CourseVideoForm.vue';
import SpinnerOverlay from '@/Components/Admin/Ui/SpinnerOverlay.vue';

// videoDefault también aquí:
const videoDefault = {
  id: null,
  title: '',
  description: '',
  description_short: '',
  comments: '',
  video_path: null,
  image_cover: null,
  teacher_id: '',
  course_id: '',
};

const props = defineProps({
  show: Boolean,
  courses: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  editingId: Number,
  videoData: Object
});

const emit = defineEmits(['close', 'saved']);

// SIEMPRE mergea con videoDefault:
const form = ref({ ...videoDefault, ...props.videoData });
const processing = ref(false);
const errors = ref({});

const onSubmit = async (values) => {
  processing.value = true;
  try {
    // Aquí iría la lógica de guardado (axios/post o Inertia)
    emit('saved');
    emit('close');
  } catch (e) {
    // Manejo de errores
  } finally {
    processing.value = false;
  }
};

// Cada que cambie videoData (nuevo video/editar) mergea de nuevo
watch(() => props.videoData, (data) => {
  form.value = { ...videoDefault, ...data };
}, { immediate: true });

</script>
