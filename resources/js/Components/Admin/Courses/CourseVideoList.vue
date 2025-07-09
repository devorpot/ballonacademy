<template>
  <section class="section-videos my-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Videos del Curso ({{ videos.length }})</h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Título</th>
              <th>Descripción corta</th>
              <th>URL</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(video, index) in videos" :key="video.id">
              <td>{{ index + 1 }}</td>
              <td>{{ video.title }}</td>
              <td>{{ video.description_short }}</td>
              <td>
                <span v-if="video.video_url">{{ video.video_url }}</span>
                <span v-else class="text-muted">N/A</span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary" @click="$emit('edit', video)">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <button class="btn btn-outline-danger" @click="$emit('delete', video)">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-if="videos.length === 0" class="text-center text-muted my-3">
          No hay videos registrados.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  videos: {
    type: Array,
    required: true
  }
});

defineEmits(['edit', 'delete']);
</script>
