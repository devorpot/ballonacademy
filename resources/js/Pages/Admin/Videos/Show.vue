<template>
  <Head :title="`Ver Video: ${video.title}`" />
  <AdminLayout>
    <div class="position-relative">
      <Breadcrumbs
        username="admin"
        :breadcrumbs="[
          { label: 'Dashboard', route: 'admin.dashboard' },
          { label: 'Cursos', route: 'admin.courses.index' },
          { label: 'Videos', route: 'admin.videos.index' },
          { label: 'Detalle', route: '' }
        ]"
      />

      <section class="section-heading my-2">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-12 d-flex justify-content-between align-items-center">
              <ButtonBack label="Volver" icon="bi bi-arrow-left" :href="route('admin.videos.index')" />
            </div>
          </div>
        </div>
      </section>


      
   
      <section class="section-form my-2">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <div class="row g-4">
                <div class="col-md-6">
                  <strong class="d-block text-muted">Título</strong>
                  <p>{{ video.title }}</p>
                </div>

                <div class="col-md-6">
                  <strong class="d-block text-muted">URL del video</strong>
                  <p>{{ video.video_url }}</p>
                </div>

                <div class="col-md-6">
                  <strong class="d-block text-muted">Descripción</strong>
                  <p>{{ video.description || '—' }}</p>
                </div>

                <div class="col-md-6">
                  <strong class="d-block text-muted">Descripción corta</strong>
                  <p>{{ video.description_short || '—' }}</p>
                </div>

                <div class="col-md-6">
                  <strong class="d-block text-muted">Comentarios</strong>
                  <p>{{ video.comments || '—' }}</p>
                </div>

                <div class="col-md-6">
                  <strong class="d-block text-muted">Curso</strong>
                  <p>{{ courseName }}</p>
                </div>

                <div class="col-md-6">
                  <strong class="d-block text-muted">Profesor</strong>
                  <p>{{ teacherName }}</p>
                </div>

                <div class="col-md-6">
                  <strong class="d-block text-muted">Imagen de portada</strong>
                  <div v-if="video.image_cover" class="border p-2 rounded bg-light">
                    <img :src="'/storage/' + video.image_cover" class="img-fluid rounded" style="max-height: 200px;" />
                  </div>
                  <div v-else>
                    <em>No hay imagen</em>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <Link :href="route('admin.videos.edit', video.id)" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i> Editar
              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';

const props = defineProps({
  video: Object,
  courses: Array,
  teachers: Array
});

const courseName = computed(() => {
  const course = props.courses.find(c => c.id === props.video.course_id);
  return course ? course.title : '—';
});

const teacherName = computed(() => {
  const teacher = props.teachers.find(t => t.id === props.video.teacher_id);
  return teacher ? `${teacher.firstname} ${teacher.lastname}` : '—';
});
</script>
