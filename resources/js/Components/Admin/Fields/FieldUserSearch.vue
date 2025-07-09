<!-- resources/js/Components/Admin/Fields/FieldUserSearch.vue -->
<template>
  <div class="position-relative">
    <label :for="id" class="form-label fw-bold">{{ label }}</label>
    <input
      :id="id"
      type="search"
      class="form-control"
      :placeholder="placeholder"
      v-model="search"
      @input="fetchUsers"
      @blur="() => emit('blur')"
    />

    <!-- Dropdown resultados -->
    <div class="list-group position-absolute w-100 z-3" v-if="showDropdown && results.length">
      <button
        type="button"
        class="list-group-item list-group-item-action"
        v-for="user in results"
        :key="user.value"
        @mousedown.prevent="selectUser(user)"
      >
        {{ user.label }}
      </button>
    </div>

    <div v-if="showValidation && formError" class="invalid-feedback d-block">
      {{ formError }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  id: String,
  label: String,
  modelValue: [String, Number],
  placeholder: {
    type: String,
    default: 'Buscar por nombre o correo'
  },
  fetchUrl: {
    type: String,
    default: 'admin/users/search'
  },
  required: Boolean,
  formError: String,
  showValidation: Boolean,
})

const emit = defineEmits(['update:modelValue', 'blur'])

const search = ref('')
const results = ref([])
const showDropdown = ref(false)

const fetchUsers = async () => {
  if (search.value.length < 2) {
    results.value = []
    showDropdown.value = false
    return
  }

  try {
    const response = await axios.get(props.fetchUrl, {
      params: { search: search.value }
    })
    results.value = response.data
    showDropdown.value = true
  } catch (err) {
    console.error('Error buscando usuarios', err)
  }
}

const selectUser = (user) => {
  emit('update:modelValue', user.value)
  search.value = user.label
  showDropdown.value = false
}

watch(() => props.modelValue, async (newVal) => {
  if (newVal && !search.value) {
    try {
      const response = await axios.get(`${props.fetchUrl}/${newVal}`)
      if (response.data && response.data.label) {
        search.value = response.data.label
      }
    } catch (e) {
      console.error('Error cargando usuario seleccionado', e)
    }
  }
}, { immediate: true })

</script>
