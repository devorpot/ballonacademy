<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content shadow-lg border-0">
        <div class="modal-header bg-light border-0">
          <h5 class="modal-title">
            <i class="bi bi-lightning-charge me-2"></i> Activar usuario
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')" aria-label="Cerrar"></button>
        </div>

        <form @submit.prevent="submit">
          <div class="modal-body">
            <div class="mb-3">
              <label for="email_to" class="form-label fw-semibold">Enviar activación a</label>
              <input
                id="email_to"
                type="email"
                class="form-control"
                v-model.trim="form.email_to"
                :class="{ 'is-invalid': form.errors.email_to }"
                placeholder="correo@ejemplo.com"
              />
              <div class="invalid-feedback">{{ form.errors.email_to }}</div>
              <small class="text-muted">
                Debe ser un correo que no pertenezca a ningún usuario registrado.
              </small>
            </div>

            <div class="mb-3 form-check form-switch">
              <input
                class="form-check-input"
                type="checkbox"
                id="assign_password"
                v-model="form.assign_password"
              />
              <label class="form-check-label" for="assign_password">
                Asignar password manualmente
              </label>
            </div>

            <div class="mb-3" v-if="form.assign_password">
              <label for="password" class="form-label fw-semibold">Nuevo password</label>
              <input
                id="password"
                type="password"
                class="form-control"
                v-model="form.password"
                :class="{ 'is-invalid': form.errors.password }"
                placeholder="Mínimo 10 caracteres"
              />
              <div class="invalid-feedback">{{ form.errors.password }}</div>
            </div>

          </div>

          <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-outline-secondary" @click="$emit('close')">
              <i class="bi bi-x-lg me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
              <i class="bi bi-send me-1"></i> Enviar activación
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal-backdrop fade show" @click="$emit('close')" />
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { watch, computed } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  user: { type: Object, required: true } // debe venir con id y email
})
const emit = defineEmits(['close', 'sent'])

const form = useForm({
  email_to: props.user?.email ?? '',
  assign_password: false,
  password: ''
})

watch(() => props.show, (val) => {
  if (val) {
    form.reset()
    form.email_to = props.user?.email ?? ''
  }
})

const submit = () => {
  form.post(route('admin.users.activation.send', props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('sent')
      emit('close')
    }
  })
}
</script>
