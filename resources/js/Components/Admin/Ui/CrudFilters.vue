<template>
  <section class="section-filters my-2">
    <div class="container-fluid">
      <div class="card mb-4">
        <div class="card-body">
          <div class="row justify-content-between align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input
                  type="text"
                  class="form-control"
                  :placeholder="placeholder"
                  :value="searchQuery"
                  @input="$emit('update:searchQuery', $event.target.value)"
                />
              </div>
            </div>
            <div class="col-md-4 text-end">
              <span class="badge rounded-pill bg-secondary text-white p-2">
                <i class="bi bi-card-text me-1"></i>{{ count }} {{ itemLabel }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  searchQuery: String,
  count: Number,
  placeholder: { type: String, default: 'Buscar...' },
  itemLabel: { type: String, default: 'resultado(s)' }
});
</script>
