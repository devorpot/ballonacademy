<template>
  <div>
    <h1>Register</h1>
    <form @submit.prevent="submit">
      <input v-model="form.email" type="email" placeholder="Email" />
      <input v-model="form.password" type="password" placeholder="Password" />
      <button type="submit">Entrar</button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: ''
})

function submit() {
  form.post('/login')
}
</script>
