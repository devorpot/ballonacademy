<template>
  <div class="card shadow-sm h-100 text-center">
    <Link :href="postUrl" class="text-decoration-none text-dark">
      
      <!-- Imagen -->
      <img :src="image" alt="Imagen del post" class="card-img-top rounded-top" style="object-fit: cover; height: 160px;" />
      
      <!-- Título -->
      <div class="card-body px-3 py-2">
        <h6 class="fw-bold mb-0" style="font-size: 0.95rem; line-height: 1.2;">
          {{ title }}
        </h6>
      </div>

    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: String,
  image: String,
  postUrl: String
});
</script>
