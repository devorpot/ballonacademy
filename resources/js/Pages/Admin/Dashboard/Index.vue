<template>
  <Head title="Dashboard" />
  <AdminLayout>
    <div class="position-relative">
    	<!--Layout-->
       <Breadcrumbs
          username="admin"
          :breadcrumbs="[
            { label: 'Dashboard', route: '#' }
          ]" /><!--/Breadcrumbs-->
 
      <!--/Layout-->
	</div>
  </AdminLayout>
</template>

<script setup>
 import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Admin/Ui/Breadcrumbs.vue';
import CrudFilters from '@/Components/Admin/Ui/CrudFilters.vue';
import CrudPagination from '@/Components/Admin/Ui/CrudPagination.vue';
import ConfirmDeleteModal from '@/Components/Admin/ConfirmDeleteModal.vue';
import ToastNotification from '@/Components/Admin/Ui/ToastNotification.vue';
import CreateCertificateModal from '@/Pages/Admin/Certificates/CreateCertificateModal.vue';
import ShowCertificateModal from '@/Pages/Admin/Certificates/ShowCertificateModal.vue';

</script>
<style scoped>
.table td, .table th {
  vertical-align: middle;
}
</style>
