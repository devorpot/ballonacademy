<template>
  <Head :title="`Dashboard`" />

  <StudentLayout>

 

    <!-- MIS CURSOS -->
    <section class="section-panel py-3" v-if="courses && courses.length">
      <div class="container-fluid">
        <div class="row my-3">
          <div class="col-12">
            <SectionHeader title="Mis Cursos" :link="route('dashboard.courses.index')" />
          </div>
        </div>
        <div class="row">
          <div
            class="col-12 col-md-4 mb-3"
            v-for="course in courses"
            :key="course.id"
          >
 
            <CardCourseThumb
              :title="course.title"
              :videosCount="course.videos_count"
               :image="course.image_cover ? `/storage/${course.image_cover}` : '/images/default-cover.jpg'"
              :detailsUrl="route('dashboard.courses.show', course.id)"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- MENSAJE SI NO HAY CURSOS -->
    <section class="section-panel py-3" v-else>
      <div class="container-fluid text-center py-5">
        <p class="text-muted mb-0">Aún no estás inscrito en ningún curso.</p>
      </div>
    </section>

 
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row my-3">
          <div class="col-12">
            <SectionHeader title="Videos Vistos" link="/videos" />
          </div>
        </div>
        <div class="row my-3">
          <div class="col-12 col-md-3" v-for="n in 4" :key="n">
            <CardVideoThumb />
          </div>
        </div>
      </div>
    </section>


    <!-- BLOG DE GLOBOFLEXIA -->
    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row my-3">
          <div class="col-12">
            <SectionHeader title="Blog de Globoflexia" link="/blog" />
          </div>
        </div>
        <div class="row my-3">
          <div class="col-12 col-md-3" v-for="n in 4" :key="n">
            <CourseBlogPost
              title="Los 5 Animales de Globoflexia que Todo Principiante Debería Aprender"
              image="https://placehold.co/320x120"
              postUrl="/blog/animales-globoflexia"
            />
          </div>
        </div>
      </div>
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head } fro m '@inertiajs/vue3';
import axios from 'axios';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import CardVideoThumb from '@/Components/Dashboard/Cards/CardVideoThumb.vue';
import CardCourseThumb from '@/Components/Dashboard/Cards/CardCourseThumb.vue';
import CourseBlogPost from '@/Components/Dashboard/Cards/CardBlogPost.vue';
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue';
import { route } from 'ziggy-js';

// Props que vienen desde el backend
defineProps({
  courses: {
    type: Array,
    default: () => [],
  },
  videos: {
    type: Array,
    default: () => [],
  },
});
</script>
