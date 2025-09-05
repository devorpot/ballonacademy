<!-- resources/js/Pages/Blog/Index.vue -->
<template>
  <StudentLayout>
    <Head title="Blog de Globoflexia" />

    <Breadcrumbs
      username="estudiante"
      :breadcrumbs="[
        { label: 'Dashboard', route: 'dashboard.index' },
        { label: 'Blog', route: 'dashboard.blog.index' }
      ]"
    />

    <section class="section-panel py-3">
      <div class="container-fluid">
        <div class="row my-3">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title text-uppercase mb-0">Blog de Globoflexia</h5>
                <div class="d-flex align-items-center gap-2">
                  <input
                    v-model="q"
                    type="search"
                    class="form-control form-control-sm"
                    placeholder="Buscar en el blog..."
                    style="min-width: 220px"
                  />
                </div>
              </div>

              <div class="card-body">
                <div class="row g-3">
                  <div
                    class="col-12 col-md-6 col-lg-4"
                    v-for="post in filtered"
                    :key="post.id"
                  >
                    <div class="card h-100 shadow-sm border-0">
                      <div class="position-relative">
                        <img
                          class="card-img-top blog-cover"
                          :src="post.image"
                          :alt="post.title"
                        />
                        <span class="badge text-bg-dark position-absolute top-0 start-0 m-2 rounded-pill px-3 py-2 small">
                          {{ post.category }}
                        </span>
                      </div>

                      <div class="card-body d-flex flex-column">
                        <h6 class="card-title two-lines mb-2" :title="post.title">
                          {{ post.title }}
                        </h6>

                        <div class="text-muted small d-flex align-items-center gap-2 mb-2 flex-wrap">
                          <span><i class="bi bi-calendar-event me-1"></i>{{ fmt(post.published_at) }}</span>
                          <span>•</span>
                          <span><i class="bi bi-hourglass-split me-1"></i>{{ post.read_time }} min</span>
                          <span class="ms-auto d-none d-md-inline text-truncate" :title="post.author">
                            <i class="bi bi-person-circle me-1"></i>{{ post.author }}
                          </span>
                        </div>

                        <p class="card-text text-muted small two-lines mb-3">
                          {{ post.excerpt }}
                        </p>

                        <div class="d-flex gap-1 flex-wrap mb-3">
                          <span
                            v-for="t in post.tags"
                            :key="t"
                            class="badge rounded-pill text-bg-light border small fw-normal"
                          >#{{ t }}</span>
                        </div>

                        <div class="mt-auto">
                          <Link
                            class="btn btn-primary w-100"
                            :href="route('dashboard.blog.show', { slug: post.slug })"
                          >
                            Leer entrada completa
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="!filtered.length" class="text-center text-muted py-5">
                  No se encontraron entradas para tu búsqueda.
                </div>
              </div>

              <div class="card-footer bg-transparent text-muted small">
                Mostrando {{ filtered.length }} de {{ posts.length }} entradas
              </div>
            </div>
          </div>
        </div>
      </div>  
    </section>
  </StudentLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Breadcrumbs from '@/Components/Dashboard/Ui/Breadcrumbs.vue'

const q = ref('')

const posts = [
  { id: 1, title: 'Globoflexia para principiantes: aprende a empezar', excerpt: 'Fundamentos para iniciarte y crear tus primeras figuras con seguridad.', image: 'https://placehold.co/900x500?text=Globoflexia+para+principiantes', slug: 'globoflexia-para-principiantes', category: 'Técnica', published_at: '2025-08-01', read_time: 6, author: 'Equipo AIG', tags: ['basics','figuras','tips'] },
  { id: 2, title: '10 figuras fáciles con globos que sorprenderán', excerpt: 'Guía práctica de figuras sencillas y rápidas para eventos.', image: 'https://placehold.co/900x500?text=10+figuras+fáciles', slug: '10-figuras-faciles-globos', category: 'Práctica', published_at: '2025-08-04', read_time: 5, author: 'Nadia', tags: ['lista','eventos','rápidas'] },
  { id: 3, title: 'Cómo elegir los mejores globos para tus creaciones', excerpt: 'Claves para escoger globos resistentes según el proyecto.', image: 'https://placehold.co/900x500?text=Mejores+globos', slug: 'elegir-mejores-globos', category: 'Materiales', published_at: '2025-08-07', read_time: 7, author: 'Daniel', tags: ['globos','calidad'] },
  { id: 4, title: 'Técnicas avanzadas de torsión en globoflexia', excerpt: 'Torsiones complejas para más realismo y detalle.', image: 'https://placehold.co/900x500?text=Torsión+avanzada', slug: 'tecnicas-avanzadas-torsion', category: 'Técnica', published_at: '2025-08-10', read_time: 9, author: 'Invitado', tags: ['avanzado','torsión'] },
  { id: 5, title: 'Errores comunes y cómo evitarlos', excerpt: 'Fallos frecuentes y cómo mejorar paso a paso.', image: 'https://placehold.co/900x500?text=Errores+comunes', slug: 'errores-comunes-globoflexia', category: 'Práctica', published_at: '2025-08-14', read_time: 4, author: 'Equipo AIG', tags: ['errores','mejora'] },
  { id: 6, title: 'Cómo organizar tu kit de globoflexia', excerpt: 'Herramientas y accesorios indispensables en tu maletín.', image: 'https://placehold.co/900x500?text=Kit+de+globoflexia', slug: 'organizar-kit-globoflexia', category: 'Materiales', published_at: '2025-08-18', read_time: 6, author: 'Nadia', tags: ['kit','herramientas'] },
  { id: 7, title: 'Decoraciones con globos para eventos', excerpt: 'Ideas aplicadas a bodas, cumpleaños y más.', image: 'https://placehold.co/900x500?text=Decoraciones+para+eventos', slug: 'decoraciones-globos-eventos', category: 'Decoración', published_at: '2025-08-22', read_time: 8, author: 'Daniel', tags: ['decoración','eventos'] },
  { id: 8, title: 'Combinar colores en globoflexia', excerpt: 'Usa paletas armónicas para composiciones atractivas.', image: 'https://placehold.co/900x500?text=Colores+en+globoflexia', slug: 'arte-combinar-colores-globoflexia', category: 'Diseño', published_at: '2025-08-26', read_time: 5, author: 'Equipo AIG', tags: ['color','paletas'] },
  { id: 9, title: 'Globoflexia como negocio', excerpt: 'Consejos prácticos para emprender con globos.', image: 'https://placehold.co/900x500?text=Globoflexia+como+negocio', slug: 'globoflexia-como-negocio', category: 'Negocio', published_at: '2025-08-29', read_time: 7, author: 'Invitado', tags: ['negocio','emprender'] }
]

const filtered = computed(() => {
  if (!q.value) return posts
  const s = q.value.toLowerCase()
  return posts.filter(p =>
    [p.title, p.excerpt, p.category, p.author, ...(p.tags||[])].join(' ').toLowerCase().includes(s)
  )
})

function fmt(d) {
  try {
    return new Date(d).toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: '2-digit' })
  } catch { return d }
}
</script>

<style scoped>
.blog-cover { height: 180px; object-fit: cover; }
.two-lines {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
</style>
