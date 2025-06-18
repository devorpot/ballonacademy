<template>
  <Head title="Crear Nuevo Usuario" />
    <AdminLayout>
      <Breadcrumbs username="admin" :breadcrumbs="[
                  { label: 'Dashboard', route: 'admin.dashboard' },
                  { label: 'Usuarios', route: 'admin.users.index' },
                  { label: 'Crear', route: '' } // sin ruta porque es la actual
                ]"
                />
      <!--section-heading-->
      <section class="section-heading my-2">
        <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <div v-if="form.hasErrors" class="alert alert-danger">
                  <div v-for="error in Object.values(form.errors).flat()" :key="error">
                    {{ error }}
                  </div>
                </div>
              </div>
            </div>  
            <div class="row mb-4">
              <div class="col-12 d-flex justify-content-between align-items-center">
                  <Title :title="'Crear Usuario'" />
                  <ButtonBack 
                    label="Volver"
                    icon="bi bi-arrow-left"
                    :href="route('admin.users.index')"
                  />

              </div>
            </div>
        </div>
      </section>
      <!--/section-heading-->
      <!--section-form-->
      <section class="section-form  my-2">
         <div class="container-fluid ">
            <div class="row">
              <div class="col-12">
                 <!--form-->
                   <form @submit.prevent="submit" novalidate>
                    <div class="card">
                      <!--card-->
                        <div class="card-body">
                          <div class="row">
                              <!-- Campo Nombre -->
                              <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input
                                  type="text"
                                  class="form-control"
                                  id="name"
                                  v-model="form.name"
                                  @blur="handleBlur('name')"
                                  :class="{
                                    'is-invalid': (touched.name && validateName()) || form.errors.name
                                  }"
                                >
                                <div class="invalid-feedback">
                                  {{ touched.name ? validateName() : '' || form.errors.name }}
                                </div>
                              </div>

                             <!-- Ejemplo para el campo email -->
                            <div class="col-md-6 mb-3">
                              <label for="email" class="form-label">Email</label>
                              <input
                                type="email"
                                class="form-control"
                                id="email"
                                v-model="form.email"
                                @blur="handleBlur('email')"
                                :class="{
                                  'is-invalid': (touched.email && validateEmail()) || form.errors.email
                                }"
                              >
                              <div class="invalid-feedback">
                                <!-- Muestra primero el error del servidor, si existe -->
                                {{ form.errors.email || (touched.email ? validateEmail() : '') }}
                              </div>
                            </div>
                              <!-- Campo Contraseña -->
                              <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input
                                  type="password"
                                  class="form-control"
                                  id="password"
                                  v-model="form.password"
                                  @blur="handleBlur('password')"
                                  :class="{
                                    'is-invalid': (touched.password && validatePassword()) || form.errors.password
                                  }"
                                >
                                <div class="invalid-feedback">
                                  {{ touched.password ? validatePassword() : '' || form.errors.password }}
                                </div>
                                <small class="form-text text-muted">
                                  Requisitos: mínimo 8 caracteres, al menos una mayúscula y un número
                                </small>
                              </div>

                              <!-- Campo Confirmar Contraseña -->
                              <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input
                                  type="password"
                                  class="form-control"
                                  id="password_confirmation"
                                  v-model="form.password_confirmation"
                                  @blur="handleBlur('password_confirmation')"
                                  :class="{
                                    'is-invalid': (touched.password_confirmation && validatePasswordConfirmation()) || form.errors.password_confirmation
                                  }"
                                >
                                <div class="invalid-feedback">
                                  {{ touched.password_confirmation ? validatePasswordConfirmation() : '' || form.errors.password_confirmation }}
                                </div>
                              </div>

                              <!-- Selección de Roles -->
                              <div class="col-12 mb-3">
                                <label class="form-label">Roles</label>

                                <div class="form-check mb-2">
                                  <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="select_all_roles"
                                    v-model="allRolesSelected"
                                    @change="handleRoleChange"
                                  >
                                  <label class="form-check-label fw-bold" for="select_all_roles">Seleccionar todos los roles</label>
                                </div>

                                <div class="row">
                                  <div class="col-md-3 mb-2" v-for="role in roles" :key="role.id">
                                    <div class="form-check">
                                      <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :id="'role_' + role.id"
                                        :value="role.id"
                                        v-model="form.role_ids"
                                        @change="handleRoleChange"
                                      >
                                      <label class="form-check-label" :for="'role_' + role.id">{{ role.label }}</label>
                                    </div>
                                  </div>
                                </div>

                                <div v-if="(touched.role_ids && validateRoles()) || form.errors.role_ids || form.errors.role" class="text-danger mt-2">
                                  {{ touched.role_ids ? validateRoles() : '' || form.errors.role_ids || form.errors.role }}
                                </div>
                              </div>

                              <!-- Botón de envío -->
                             
                            </div>
                         
                        </div>
                        <div class="card-footer text-end">

                                  <button 
                                    type="submit" 
                                    class="btn btn-primary" 
                                    :disabled="form.processing || !isFormValid"
                                    >
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    <i class="bi bi-save me-2"></i>Guardar
                                  </button>
                           
                          </div>

                        </div>
                        <!--/card-->
                     </form>
                   <!--/form-->
              </div>
            </div>
          </div>
      </section>
      <!--/section-form-->
    </AdminLayout>

</template>
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';



//Admin UI
import Title  from '@/Components/Admin/Ui/Title.vue';
import Breadcrumbs  from '@/Components/Admin/Ui/Breadcrumbs.vue';
import ButtonBack from '@/Components/Admin/Ui/ButtonBack.vue';

const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  }
});

const form = useForm({ 
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_ids: []
});

// Estados para controlar campos tocados
const touched = ref({
  name: false,
  email: false,
  password: false,
  password_confirmation: false,
  role_ids: false
});

// Computed para selección de todos los roles
const allRolesSelected = computed({
  get: () => form.role_ids.length === props.roles.length,
  set: (value) => {
    form.role_ids = value ? props.roles.map(role => role.id) : [];
    touched.value.role_ids = true; // Marcar como tocado al cambiar
  }
});

// Funciones de validación
const validateName = () => {
  if (!form.name.trim()) return 'El nombre es requerido';
  if (form.name.length < 3) return 'El nombre debe tener al menos 3 caracteres';
  return '';
};

const validateEmail = () => {
  if (!form.email.trim()) return 'El email es requerido';
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.email)) return 'Ingrese un email válido';
  return '';
};

const validatePassword = () => {
  if (!form.password) return 'La contraseña es requerida';
  if (form.password.length < 8) return 'Debe tener al menos 8 caracteres';
  if (!/[A-Z]/.test(form.password)) return 'Debe contener al menos una mayúscula';
  if (!/[0-9]/.test(form.password)) return 'Debe contener al menos un número';
  return '';
};

const validatePasswordConfirmation = () => {
  if (form.password !== form.password_confirmation) return 'Las contraseñas no coinciden';
  return '';
};

const validateRoles = () => {
  if (form.role_ids.length === 0) return 'Seleccione al menos un rol';
  return '';
};

// Validación general del formulario
const isFormValid = computed(() => {
  return !validateName() && 
         !validateEmail() && 
         !validatePassword() && 
         !validatePasswordConfirmation() && 
         !validateRoles();
});

// Manejar blur (cuando el campo pierde el foco)
const handleBlur = (field) => {
  touched.value[field] = true;
};

// Manejar cambios en roles individuales
const handleRoleChange = () => {
  touched.value.role_ids = true;
};

 

// Envío del formulario
const submit = () => {
  // Marcar todos los campos como tocados al enviar
  Object.keys(touched.value).forEach(key => {
    touched.value[key] = true;
  });

  if (isFormValid.value) {
    form.post(route('admin.users.store'), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        // Limpiar los estados de "touched"
        Object.keys(touched.value).forEach(key => {
          touched.value[key] = false;
        });
      },
      onError: (errors) => {
        // Inertia automáticamente inyecta los errores en form.errors
        // No necesitas hacer nada adicional aquí
        console.log('Errores del servidor:', errors);
      }
    });
  }
};
</script>
