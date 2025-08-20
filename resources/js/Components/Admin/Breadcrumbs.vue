<template>
  <div class="w-100 d-block my-3">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-11">
          <div class="card border rounded-3 shadow-sm p-0" style="background: none;">
            <div class="card-body py-3 px-4">
              <div class="row align-items-center">
                <div class="col-12 col-md-6">
                  <div class="d-block">
                    <h2 class="fw-bold h5 mb-1" style="font-size: 1.35rem;">Dashboard</h2>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb mb-0" style="font-size: 0.95rem;">
                        <li class="breadcrumb-item">
                          <a :href="route('admin.dashboard')" class="text-decoration-none text-primary">Dashboard</a>
                        </li>
                        <!-- Puedes agregar más breadcrumb-items aquí -->
                      </ol>
                    </nav>
                  </div>
                </div>
                <div class="col-12 col-md-6 text-end">
                  <p class="mb-0" style="font-size: 0.95rem;">Bienvenido <span class="fw-semibold">@admin</span></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js'
</script>
