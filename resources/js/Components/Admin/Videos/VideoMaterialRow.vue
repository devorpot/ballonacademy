<template>
  <div>
    <!-- Botón para abrir el modal de agregar -->
    <button class="btn btn-outline-primary mb-3" @click="openModal()">
      <i class="bi bi-plus-circle"></i> Agregar material
    </button>

    <div class="modal fade show" tabindex="-1" style="display:block;" v-if="showModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Editar material' : 'Agregar material' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitMaterial" enctype="multipart/form-data">
              <FieldText
                :id="`material-name-${videoId}`"
                label="Nombre del material"
                v-model="materialForm.name"
                :required="true"
                :showValidation="touched.name"
                :formError="formErrors.name"
                @blur="() => handleBlur('name')"
                placeholder="Ejemplo: Globo metálico"
                class="mb-2"
              />
              <FieldText
                :id="`material-quantity-${videoId}`"
                label="Cantidad"
                v-model="materialForm.quantity"
                :showValidation="touched.quantity"
                :formError="formErrors.quantity"
                @blur="() => handleBlur('quantity')"
                placeholder="Ejemplo: 10"
                class="mb-2"
              />
              <FieldText
                :id="`material-unit-${videoId}`"
                label="Unidad"
                v-model="materialForm.unit"
                :showValidation="touched.unit"
                :formError="formErrors.unit"
                @blur="() => handleBlur('unit')"
                placeholder="Ejemplo: piezas"
                class="mb-2"
              />
              <FieldMoney
                :id="`material-price-${videoId}`"
                label="Precio"
                v-model="materialForm.price"
                min="0"
                :showValidation="touched.price"
                :formError="formErrors.price"
                @blur="() => handleBlur('price')"
                placeholder="Ejemplo: 15"
                class="mb-2"
              />
              <FieldImage
  :id="`material-image-${videoId}`"
  label="Imagen"
  v-model="materialForm.image"
  :showValidation="touched.image"
  :formError="formErrors.image"
  :initialPreview="materialForm.image_preview"
  @image-removed="onImageRemoved"
  @blur="() => handleBlur('image')"
  class="mb-2"
/>

              <FieldTextarea
                :id="`material-notes-${videoId}`"
                label="Notas"
                v-model="materialForm.notes"
                :showValidation="touched.notes"
                :formError="formErrors.notes"
                @blur="() => handleBlur('notes')"
                rows="2"
                placeholder="Información adicional"
                class="mb-2"
              />
              <FieldText
                :id="`material-buy-link-${videoId}`"
                label="Enlace de compra (opcional)"
                v-model="materialForm.buy_link"
                type="url"
                :showValidation="touched.buy_link"
                :formError="formErrors.buy_link"
                @blur="() => handleBlur('buy_link')"
                placeholder="https://..."
                class="mb-2"
              />
              <div class="text-end">
                <button type="submit" class="btn btn-success btn-sm" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else :class="editingId ? 'bi bi-pencil' : 'bi bi-plus-circle'"></i>
                  {{ editingId ? 'Guardar cambios' : 'Guardar material' }}
                </button>
                <button
                  v-if="editingId"
                  type="button"
                  class="btn btn-secondary btn-sm ms-2"
                  @click="cancelEdit"
                  :disabled="saving"
                >Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showModal"></div>

    <!-- Tabla de materiales -->
    <table class="table table-bordered align-middle mt-3" v-if="materials.length">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Cantidad</th>
          <th>Unidad</th>
          <th>Precio</th>
          <th>Imagen</th>
          <th>Notas</th>
          <th>Enlace</th>
          <th style="width: 100px;"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="mat in materials" :key="mat.id">
          <td>{{ mat.name }}</td>
          <td>{{ mat.quantity }}</td>
          <td>{{ mat.unit }}</td>
          <td>{{ mat.price }}</td>
          <td>

    
             <ImageThumb :src="`/storage/${mat.image}`" size="thumb" />
            
          </td>
          <td>{{ mat.notes }}</td>
          <td>
            <a v-if="mat.buy_link" :href="mat.buy_link" target="_blank" rel="noopener">Ver enlace</a>
          </td>
          <td>
            <button class="btn btn-sm btn-primary me-2" @click="editMaterial(mat)">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" @click="removeMaterial(mat.id)">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-else class="text-center text-muted my-3">
      No has agregado materiales.
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import FieldText from '@/Components/Admin/Fields/FieldText.vue';
import FieldImage from '@/Components/Admin/Fields/FieldImage.vue';
import FieldTextarea from '@/Components/Admin/Fields/FieldTextarea.vue';
import FieldMoney from '@/Components/Admin/Fields/FieldMoney.vue';
import ImageThumb from '@/Components/Admin/Ui/ImageThumb.vue';

const props = defineProps({
  videoId: { type: [Number, String], required: true }
});
const emit = defineEmits(['saved']);

const showModal = ref(false);
const saving = ref(false);
const materials = ref([]);
const editingId = ref(null);

const materialForm = reactive({
  name: '',
  quantity: '',
  unit: '',
  notes: '',
  image: null,
  image_preview: null,
  price: '',
  buy_link: '',
  image_removed: false    // NUEVO
});


const touched = reactive({
  name: false,
  quantity: false,
  unit: false,
  notes: false,
  image: false,
  price: false,
  buy_link: false
});

const formErrors = reactive({
  name: '',
  quantity: '',
  unit: '',
  notes: '',
  image: '',
  price: '',
  buy_link: ''
});

function openModal(material = null) {
  showModal.value = true;
    if (material) {
    editingId.value = material.id;
    Object.assign(materialForm, {
      name: material.name,
      quantity: material.quantity,
      unit: material.unit,
      notes: material.notes,
      image: null,
      image_preview: material.image ? `/storage/${material.image}` : null,
      price: material.price,
      buy_link: material.buy_link,
      image_removed: false     // Resetear flag al abrir modal
    });
  } else {
    resetForm();
  }
  Object.keys(touched).forEach(k => touched[k] = false);
  Object.keys(formErrors).forEach(k => formErrors[k] = '');
}
function closeModal() {
  showModal.value = false;
  resetForm();
}
function resetForm() {
   editingId.value = null;
  Object.assign(materialForm, {
    name: '',
    quantity: '',
    unit: '',
    notes: '',
    image: null,
    image_preview: null,
    price: '',
    buy_link: '',
    image_removed: false    // Resetear flag al limpiar
  });
  Object.keys(touched).forEach(k => touched[k] = false);
  Object.keys(formErrors).forEach(k => formErrors[k] = '');
}
function cancelEdit() {
  resetForm();
  closeModal();
}
function handleBlur(field) {
  touched[field] = true;
}
async function fetchMaterials() {
  try {
    const { data } = await axios.get(route('admin.videos.materials.index', props.videoId));
    materials.value = data;
  } catch (e) {
    materials.value = [];
  }
}

// Limpia ambos campos cuando se borra la imagen desde FieldImage
function onImageRemoved() {
  materialForm.image = null;
  materialForm.image_preview = null;
  materialForm.image_removed = true;   // Marcar como eliminada
}

function validateForm() {
  let valid = true;
  Object.keys(formErrors).forEach(k => formErrors[k] = '');
  if (!materialForm.name.trim()) {
    formErrors.name = 'El nombre es obligatorio';
    valid = false;
  }
  if (materialForm.price && isNaN(materialForm.price)) {
    formErrors.price = 'El precio debe ser numérico';
    valid = false;
  }
  // Puedes agregar más validaciones aquí
  return valid;
}

async function submitMaterial() {
  Object.keys(touched).forEach(k => touched[k] = true);
  if (!validateForm()) return;
  saving.value = true;
  const formData = new FormData();
  formData.append('name', materialForm.name);
  formData.append('quantity', materialForm.quantity || '');
  formData.append('unit', materialForm.unit || '');
  formData.append('notes', materialForm.notes || '');
  formData.append('price', materialForm.price || '');
  formData.append('buy_link', materialForm.buy_link || '');

 if (materialForm.image instanceof File) {
    formData.append('image', materialForm.image);
  }
  if (materialForm.image_removed) {
    formData.append('remove_image', '1');
  }

  try {
    if (editingId.value) {
      await axios.post(
        route('admin.videos.materials.update', { video: props.videoId, material: editingId.value }),
        formData,
        {
          headers: { 'Content-Type': 'multipart/form-data' },
          params: { _method: 'PUT' }
        }
      );
    } else {
      await axios.post(route('admin.videos.materials.store', props.videoId), formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }
    closeModal();
    await fetchMaterials();
    emit('saved');
  } catch (e) {
    if (e.response?.data?.errors) {
      Object.assign(formErrors, e.response.data.errors);
    }
    alert(e?.response?.data?.message || e);
  } finally {
    saving.value = false;
  }
}

function editMaterial(material) {
  openModal(material);
}

async function removeMaterial(id) {
  if (!confirm('¿Eliminar material?')) return;
  try {
    await axios.delete(route('admin.videos.materials.destroy', { video: props.videoId, material: id }));
    await fetchMaterials();
  } catch (e) {
    alert('Error al eliminar material');
  }
}

onMounted(fetchMaterials);
watch(() => props.videoId, fetchMaterials);
</script>
