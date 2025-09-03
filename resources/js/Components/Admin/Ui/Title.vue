<script setup>

const props = defineProps({
    title: String 
});

	
</script>
<template>
  <h4 class="admin-title my-1"><i class="bi bi-people-fill me-2"></i> &nbsp; {{title}}</h4>
</template>
