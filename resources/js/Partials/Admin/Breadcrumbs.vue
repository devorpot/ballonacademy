<script setup>
 
import { Link } from '@inertiajs/vue3';
 
import { route } from 'ziggy-js'
 
</script>

<template>
   <div class="w-100 d-block my-3">
        <div class="container">
           <div class="row align-items-center">
               <div class="col-12 col-md-6">
                   <div class="d-block">
                       <h2 class="fw-bold h3">Dashboard</h2>
                       <nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a  :href="route('admin.dashboard')">Dashboard</a></li>
                           <!-- <li class="breadcrumb-item active" aria-current="page">Library</li> -->
                          </ol>
                        </nav>
                   </div>
               </div>

               <div class="col-12 col-md-6 text-end">
                    <div class="d-block">
                        <p>Bienvenido @admin</p>
                    </div>
               </div>
           </div>
       </div>
   </div>
      

</template>