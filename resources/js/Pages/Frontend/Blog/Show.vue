<!-- resources/js/Pages/Blog/Show.vue -->
<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const props = defineProps({
  post: {
    type: Object,
    required: true
    // { title, image?, content?, category, published_at(ISO), read_time, author, tags[] }
  }
})

function fmt(d) {
  try {
    return new Date(d).toLocaleDateString('es-MX', {
      year: 'numeric', month: 'long', day: '2-digit'
    })
  } catch { return d }
}

// Portada: usa la del post o un placeholder de placehold.com
const coverSrc = computed(() =>
  props.post?.image
    ? props.post.image
    : `https://placehold.com/1200x420?text=${encodeURIComponent(props.post.title || 'Portada del artículo')}`
)

// Contenido: si no viene desde el servidor, usamos texto realista de demo con imagen de placehold.com
const contentHtml = computed(() => {
  if (props.post?.content) return props.post.content
  return `
  <p>En esta guía práctica te mostramos una metodología clara para mejorar tu técnica de globoflexia,
  desde la selección de materiales hasta el acabado de tus figuras. El objetivo es ayudarte a trabajar
  con mayor precisión y velocidad sin sacrificar la calidad de tus resultados.</p>

  <h2>Materiales recomendados</h2>
  <p>Elegir globos de buena calidad es fundamental. Prefiere calibres consistentes y acabados mate o
  perlado según el estilo de tu decoración. Una bomba de doble acción y un inflador eléctrico con control
  de presión te ahorrarán tiempo y reducirán roturas.</p>

  <div class="alert alert-info" role="alert">
    Consejo: Mantén siempre un juego de repuesto de boquillas y lubricante a base de agua para torsiones complejas.
  </div>

  <h3>Técnica básica de torsión</h3>
  <p>Dominar la pinza, bloqueo y twist en adelante te dará la base para construir figuras más complejas.
  Practica con secuencias cortas y repítelas hasta que puedas mantener el mismo tamaño de burbuja de forma consistente.</p>

  <ul>
    <li><strong>Calentamiento del globo:</strong> estira el látex para distribuir la tensión.</li>
    <li><strong>Presión controlada:</strong> inflar hasta un 85–90% y dejar cola de seguridad.</li>
    <li><strong>Medición visual:</strong> usa la distancia entre nudillos como referencia rápida.</li>
  </ul>

  <h3>Combinación de colores</h3>
  <p>Trabaja con paletas de 2 a 3 tonos. Un color principal, uno de soporte y un acento. Evita saturar con
  tonalidades muy similares si no tienes un patrón de ritmo y repetición que lo justifique.</p>

  <blockquote class="blockquote my-3">
    <p class="mb-0">“La simplicidad bien ejecutada suele verse más profesional que la complejidad sin control.”</p>
  </blockquote>

  <h2>Errores comunes y cómo evitarlos</h2>
  <p>Los fallos más frecuentes se originan por exceso de presión, torsiones desalineadas o mal anclaje.
  Asegura puntos de unión con giros de bloqueo y verifica la orientación antes de avanzar al siguiente tramo.</p>

  <img src="https://placehold.com/1000x320?text=Diagrama+de+Torsiones" alt="Diagrama de torsiones" class="img-fluid rounded my-3" />

  <h3>Checklist rápido</h3>
  <ol>
    <li>Calibra inflador y presión.</li>
    <li>Deja cola de seguridad adecuada.</li>
    <li>Mantén la proporción de burbujas.</li>
    <li>Refuerza uniones clave.</li>
    <li>Revisa alineación general.</li>
  </ol>

  <p>Con práctica constante y una selección correcta de materiales, tu flujo de trabajo se volverá más estable,
  y podrás escalar desde figuras básicas hasta estructuras decorativas de gran formato.</p>
  `
})
</script>

<template>
  <Head :title="post.title" />

  <StudentLayout>
    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Blog', route: 'dashboard.blog.index' },
        { label: post.title }
      ]"
    />

    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row my-3">
          <div class="col-12 col-lg-10 mx-auto">
            <div class="card shadow-sm border-0">
              <!-- Portada -->
              <img
                src="https://placehold.co/1200x400"
                :alt="post.title"
                class="card-img-top"
                style="max-height: 420px; object-fit: cover; width: 100%;"
              />

              <div class="card-body">
                <!-- Metadatos -->
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                  <span v-if="post.category" class="badge text-bg-dark rounded-pill px-3 py-2">
                    {{ post.category }}
                  </span>
                  <span class="text-muted small">
                    <i class="bi bi-calendar-event me-1"></i>{{ fmt(post.published_at) }}
                  </span>
                  <span v-if="post.read_time" class="text-muted small">
                    • <i class="bi bi-hourglass-split me-1"></i>{{ post.read_time }} min de lectura
                  </span>
                </div>

                <!-- Título -->
                <h1 class="fw-bold mb-2">{{ post.title }}</h1>

                <!-- Autor -->
                <div class="text-muted small d-flex align-items-center gap-2 mb-3">
                  <i class="bi bi-person-circle"></i>
                  <span>{{ post.author }}</span>
                </div>

                <!-- Contenido -->
                <article class="content lead" v-html="contentHtml"></article>

                <!-- Tags -->
                <div v-if="post.tags?.length" class="d-flex gap-2 flex-wrap mt-4">
                  <span
                    v-for="t in post.tags"
                    :key="t"
                    class="badge rounded-pill text-bg-light border small fw-normal"
                  >#{{ t }}</span>
                </div>
              </div>

              <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <Link :href="route('dashboard.blog.index')" class="btn btn-outline-secondary btn-sm">
                  Volver al blog
                </Link>

                <div class="d-flex align-items-center gap-2 text-muted small">
                  <i class="bi bi-share"></i>
                  Compartir
                </div>
              </div>
            </div>
          </div>  
        </div>
      </div>  
    </section>
  </StudentLayout>
</template>

<style scoped>
.content :is(h2, h3, h4) { margin-top: 1.25rem; }
.content p { line-height: 1.8; margin-bottom: 1rem; }
</style>
