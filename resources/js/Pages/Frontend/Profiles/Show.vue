<!-- resources/js/Pages/Profile/SocialProfile.vue -->
<script setup>
import { ref, computed,onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

// Props esperados desde el servidor (puedes pasar tu objeto profile real)
const props = defineProps({
  profile: {
    type: Object,
    required: true,
  },
})

// Derivados
const fullName = computed(() => `${props.profile.firstname ?? ''} ${props.profile.lastname ?? ''}`.trim())
const nickname = computed(() => `@${props.profile.nickname || 'artist'}`)

// Galería ficticia (9 imágenes). Reemplaza con tus URLs reales (ej. /storage/..)
const gallery = ref([
  'https://picsum.photos/seed/a/600/400',
  'https://picsum.photos/seed/b/600/400',
  'https://picsum.photos/seed/c/600/400',
  'https://picsum.photos/seed/d/600/400',
  'https://picsum.photos/seed/e/600/400',
  'https://picsum.photos/seed/f/600/400',
  'https://picsum.photos/seed/g/600/400',
  'https://picsum.photos/seed/h/600/400',
  'https://picsum.photos/seed/i/600/400',
])

// Timeline básico
const posts = ref([
  {
    id: 1,
    type: 'text', // text | image | video
    author: fullName.value,
    avatar: props.profile.profile_image ? `/storage/${props.profile.profile_image}` : 'https://i.pravatar.cc/80?img=12',
    content: 'Mejorando la tecnica con globos.',
    mediaUrl: null,
    createdAt: 'hace 2 h',
    likes: 12,
    dislikes: 1,
    comments: [
      { id: 1, author: 'Lori', text: 'Se ve excelente' },
    ],
  },
  {
    id: 2,
    type: 'image',
    author: fullName.value,
    avatar: props.profile.profile_image ? `/storage/${props.profile.profile_image}` : 'https://i.pravatar.cc/80?img=12',
    content: 'Resultado final del arco pastel.',
    mediaUrl: 'https://picsum.photos/seed/picsum/1024/640',
    createdAt: 'ayer',
    likes: 56,
    dislikes: 2,
    comments: [],
  },
  {
    id: 3,
    type: 'image',
    author: fullName.value,
    avatar: props.profile.profile_image ? `/storage/${props.profile.profile_image}` : 'https://i.pravatar.cc/80?img=12',
    content: 'Buscando ',
    mediaUrl: 'https://picsum.photos/seed/balloon/1024/640',
    createdAt: 'ayer',
    likes: 56,
    dislikes: 2,
    comments: [],
  },
    {
    id: 4,
    type: 'image',
    author: fullName.value,
    avatar: props.profile.profile_image ? `/storage/${props.profile.profile_image}` : 'https://i.pravatar.cc/80?img=12',
    content: 'Buscando ',
    mediaUrl: 'https://picsum.photos/seed/balloon/1024/640',
    createdAt: 'ayer',
    likes: 56,
    dislikes: 2,
    comments: [],
  },
])

// Crear post (modal)
const postForm = ref({
  type: 'text', // text | image | video
  text: '',
  file: null,
  preview: null,
  videoUrl: '',
})
function resetPostForm() {
  postForm.value = { type: 'text', text: '', file: null, preview: null, videoUrl: '' }
}
function onPickFile(e) {
  const f = e.target.files?.[0]
  postForm.value.file = f || null
  if (f) {
    const reader = new FileReader()
    reader.onload = () => { postForm.value.preview = reader.result }
    reader.readAsDataURL(f)
  } else {
    postForm.value.preview = null
  }
}
function submitPost() {
  // Simulación de guardado local
  const newId = posts.value.length ? Math.max(...posts.value.map(p => p.id)) + 1 : 1
  const base = {
    id: newId,
    author: fullName.value,
    avatar: props.profile.profile_image ? `/storage/${props.profile.profile_image}` : 'https://i.pravatar.cc/80?img=12',
    content: postForm.value.text,
    createdAt: 'justo ahora',
    likes: 0,
    dislikes: 0,
    comments: [],
  }
  if (postForm.value.type === 'text') {
    posts.value.unshift({ ...base, type: 'text', mediaUrl: null })
  } else if (postForm.value.type === 'image') {
    posts.value.unshift({ ...base, type: 'image', mediaUrl: postForm.value.preview })
  } else if (postForm.value.type === 'video') {
    posts.value.unshift({ ...base, type: 'video', mediaUrl: postForm.value.videoUrl })
  }
  // Cerrar modal con Bootstrap
  const modalEl = document.getElementById('modalCreatePost')
  const modal = window.bootstrap?.Modal.getOrCreateInstance(modalEl)
  modal?.hide()
  resetPostForm()
}

// Interacciones
function likePost(p) { p.likes++ }
function dislikePost(p) { p.dislikes++ }

// Modal de imagen
const selectedImage = ref(null)
function openImage(url) {
  selectedImage.value = url
  const modalEl = document.getElementById('modalImageView')
  const modal = window.bootstrap?.Modal.getOrCreateInstance(modalEl)
  modal?.show()
}

// Datos “A quién seguir” y “Noticias” ficticios
const suggestions = ref([
  { id: 1, name: 'Judy Nguyen', role: 'Artista', avatar: 'https://i.pravatar.cc/80?img=1' },
  { id: 2, name: 'Amanda Reed', role: 'Decoradora', avatar: 'https://i.pravatar.cc/80?img=2' },
  { id: 3, name: 'Billy Vasquez', role: 'Globero', avatar: 'https://i.pravatar.cc/80?img=3' },
])
const news = ref([
  { id: 1, title: 'Tendencias en arcos orgánicos 2025', time: '2 h' },
  { id: 2, title: 'Cómo cotizar decoraciones corporativas', time: '4 h' },
  { id: 3, title: '10 errores comunes en montaje', time: '6 h' },
])

const currentPost = ref(null)
const commentForm = ref({ text: '' })

function openPost(p) {
  // Clon superficial para no mutar la lista hasta que el usuario accione
  currentPost.value = { ...p, comments: [...(p.comments || [])] }

  // Si no hay comentarios, inventa algunos
  if (!currentPost.value.comments.length) {
    currentPost.value.comments = [
      { id: 1, author: 'Judy Nguyen', text: 'Me encanta la paleta de colores y el acabado.' },
      { id: 2, author: 'Amanda Reed', text: 'Buen trabajo en la proporción del arco y la base.' },
      { id: 3, author: 'Billy Vasquez', text: 'Sería útil ver el proceso de montaje paso a paso.' },
    ]
  }

  const el = document.getElementById('modalPostView')
  const modal = window.bootstrap?.Modal.getOrCreateInstance(el)
  modal?.show()
}

function closePost() {
  const el = document.getElementById('modalPostView')
  const modal = window.bootstrap?.Modal.getOrCreateInstance(el)
  modal?.hide()
  currentPost.value = null
  commentForm.value.text = ''
}

function submitComment() {
  const text = commentForm.value.text?.trim()
  if (!text) return
  const nextId = (currentPost.value.comments.at(-1)?.id || 0) + 1
  currentPost.value.comments.push({
    id: nextId,
    author: fullName.value || 'Usuario',
    text,
  })
  commentForm.value.text = ''
}

function likeCurrent() {
  if (!currentPost.value) return
  currentPost.value.likes++
}
function dislikeCurrent() {
  if (!currentPost.value) return
  currentPost.value.dislikes++
}
</script>

<template>
  <GuestLayout>
    <Head title="Perfil | Red de Artistas del Globo" />

    <!-- NAVBAR -->
    <p class="text-center"><span class="text-uppercase"><small>Diseño Demo</small></span> </p>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary border-bottom sticky-top rounded">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold text-uppercase" href="#">
         <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-internacional.png" alt="" style="max-width:80px; height:auto; ">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div id="mainNav" class="collapse navbar-collapse">
          <!-- Menú principal -->
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-house-door me-1"></i>Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-people me-1"></i>Comunidad</a></li>
      
          </ul>

          <!-- Iconos -->
          <ul class="navbar-nav ms-auto align-items-center gap-2">
            <li class="nav-item">
              <a class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreatePost">
                <i class="bi bi-plus-circle me-1"></i>Crear post
              </a>
            </li>
            <li class="nav-item">
              <a class="btn btn-light position-relative" href="#" title="Notificaciones">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="btn btn-light" href="#" title="Comentarios">
                <i class="bi bi-chat-dots"></i>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="btn btn-light dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                <img
                  :src="profile.profile_image ? `/storage/${profile.profile_image}` : 'https://i.pravatar.cc/80?img=12'"
                  class="rounded-circle me-2" width="32" height="32" alt="avatar">
                <span class="small">{{ fullName || 'Usuario' }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Perfil</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Salir</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- CONTENIDO -->
    <section class="py-3">
      <div class="container-fluid">
        <div class="row g-3">
          <!-- LEFT -->
          <aside class="col-12 col-lg-3">
            <div class="card border-0 shadow-sm mb-3">
              <div class="ratio ratio-16x9 bg-light rounded-top"
                   :style="profile.cover_image ? `background:url(/storage/${profile.cover_image}) center/cover` : ''">
              </div>
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <img
                    :src="profile.profile_image ? `/storage/${profile.profile_image}` : 'https://i.pravatar.cc/120?img=12'"
                    class="rounded-circle border border-2 border-white shadow me-3"
                    width="72" height="72" alt="avatar">
                  <div>
                    <h5 class="mb-0">{{ fullName || 'Artista' }}</h5>
                    <div class="text-muted small">{{ nickname }}</div>
                  </div>
                </div>
                <hr>
                <p class="mb-2"><i class="bi bi-geo-alt me-1"></i>{{ profile.country || 'País no especificado' }}</p>
                <p class="mb-0 text-muted">
                  {{ profile.description || 'Sin descripción aún.' }}
                </p>
              </div>
            </div>

            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-images me-2"></i>Galería</h6>
              </div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-4" v-for="(img, i) in gallery" :key="i">
                    <a role="button" @click="openImage(img)">
                      <img :src="img" class="img-fluid rounded" alt="gallery">
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </aside>

          <!-- CENTER -->
          <main class="col-12 col-lg-6">
            <!-- Composer simple -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-body d-flex align-items-center gap-2">
                <img
                  :src="profile.profile_image ? `/storage/${profile.profile_image}` : 'https://i.pravatar.cc/80?img=12'"
                  class="rounded-circle" width="44" height="44" alt="avatar">
                <button class="form-control text-start" data-bs-toggle="modal" data-bs-target="#modalCreatePost">
                  Comparte tus ideas o sube una foto/video…
                </button>
              </div>
            </div>

            <!-- Timeline -->
            <div v-for="p in posts" :key="p.id" class="card border-0 shadow-sm mb-3">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <img :src="p.avatar" class="rounded-circle me-2" width="40" height="40" alt="avatar">
                  <div>
                    <div class="fw-semibold">{{ p.author }}</div>
                    <div class="text-muted small">{{ p.createdAt }}</div>
                  </div>
                </div>
                <p class="mb-2" v-if="p.content">{{ p.content }}</p>

                <div v-if="p.type === 'image' && p.mediaUrl" class="mb-2">
                  <a role="button" @click="openImage(p.mediaUrl)">
                    <img :src="p.mediaUrl" class="img-fluid rounded" alt="post image">
                  </a>
                </div>

                <div v-if="p.type === 'video' && p.mediaUrl" class="mb-2 ratio ratio-16x9">
                  <iframe :src="p.mediaUrl" title="video" allowfullscreen class="rounded border"></iframe>
                </div>

                <!-- Acciones del post -->
<div class="d-flex gap-2">
  <button class="btn btn-sm btn-light" @click="likePost(p)">
    <i class="bi bi-hand-thumbs-up me-1"></i>Like {{ p.likes }}
  </button>
  <button class="btn btn-sm btn-light" @click="dislikePost(p)">
    <i class="bi bi-hand-thumbs-down me-1"></i>Dislike {{ p.dislikes }}
  </button>
  <button class="btn btn-sm btn-light">
    <i class="bi bi-chat-left-text me-1"></i>Comentar {{ p.comments.length }}
  </button>

  <!-- Nuevo: abrir modal de detalle -->
  <button class="btn btn-sm btn-outline-primary" @click="openPost(p)">
    <i class="bi bi-eye me-1"></i>Ver post
  </button>
</div>

              </div>
            </div>
          </main>

          <!-- RIGHT -->
          <aside class="col-12 col-lg-3">
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>A quién seguir</h6>
              </div>
              <div class="card-body">
                <div v-for="s in suggestions" :key="s.id" class="d-flex align-items-center justify-content-between mb-3">
                  <div class="d-flex align-items-center">
                    <img :src="s.avatar" class="rounded-circle me-2" width="40" height="40" alt="">
                    <div>
                      <div class="fw-semibold">{{ s.name }}</div>
                      <div class="text-muted small">{{ s.role }}</div>
                    </div>
                  </div>
                  <button class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i>Seguir
                  </button>
                </div>
                <a href="#" class="btn btn-light w-100">Ver más</a>
              </div>
            </div>

            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-newspaper me-2"></i>Noticias</h6>
              </div>
              <div class="list-group list-group-flush">
                <a v-for="n in news" :key="n.id" href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                  <span class="me-3">{{ n.title }}</span>
                  <span class="badge bg-light text-muted">{{ n.time }}</span>
                </a>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>

    <!-- MODAL: Crear post -->
    <div class="modal fade" id="modalCreatePost" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Crear post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <!-- Tipo -->
            <div class="mb-3">
              <label class="form-label">Tipo de post</label>
              <div class="btn-group w-100">
                <input class="btn-check" type="radio" id="pt-text" value="text" v-model="postForm.type">
                <label class="btn btn-outline-secondary" for="pt-text"><i class="bi bi-card-text me-1"></i>Texto</label>

                <input class="btn-check" type="radio" id="pt-image" value="image" v-model="postForm.type">
                <label class="btn btn-outline-secondary" for="pt-image"><i class="bi bi-image me-1"></i>Imagen</label>

                <input class="btn-check" type="radio" id="pt-video" value="video" v-model="postForm.type">
                <label class="btn btn-outline-secondary" for="pt-video"><i class="bi bi-camera-video me-1"></i>Video</label>
              </div>
            </div>

            <!-- Texto -->
            <div class="mb-3">
              <label class="form-label">Escribe algo</label>
              <textarea class="form-control" rows="3" v-model="postForm.text" placeholder="Comparte tu proyecto, dudas o tips"></textarea>
            </div>

            <!-- Imagen -->
            <div v-if="postForm.type === 'image'" class="mb-3">
              <label class="form-label">Selecciona una imagen</label>
              <input type="file" class="form-control" accept="image/*" @change="onPickFile">
              <div v-if="postForm.preview" class="mt-2">
                <img :src="postForm.preview" class="img-fluid rounded" alt="preview">
              </div>
            </div>

            <!-- Video -->
            <div v-if="postForm.type === 'video'" class="mb-3">
              <label class="form-label">URL del video (YouTube/Vimeo/embed)</label>
              <input type="url" class="form-control" v-model="postForm.videoUrl" placeholder="https://www.youtube.com/embed/xxxx">
              <div v-if="postForm.videoUrl" class="ratio ratio-16x9 mt-2">
                <iframe :src="postForm.videoUrl" title="preview" allowfullscreen class="rounded border"></iframe>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" @click="submitPost">
              <i class="bi bi-send me-1"></i>Publicar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Ver imagen -->
    <div class="modal fade" id="modalImageView" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <button type="button" class="btn-close ms-auto m-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          <img v-if="selectedImage" :src="selectedImage" class="img-fluid rounded-bottom" alt="full">
        </div>
      </div>
    </div>

    <!-- MODAL: Ver Post con comentarios -->
<div class="modal fade" id="modalPostView" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <img
            :src="currentPost?.avatar || (profile.profile_image ? `/storage/${profile.profile_image}` : 'https://i.pravatar.cc/80?img=12')"
            class="rounded-circle me-2" width="40" height="40" alt="avatar">
          <div>
            <div class="fw-semibold">{{ currentPost?.author }}</div>
            <div class="text-muted small">{{ currentPost?.createdAt }}</div>
          </div>
        </div>
        <button type="button" class="btn-close" @click="closePost" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <!-- Media -->
          <div class="col-12 col-lg-7">
            <div v-if="currentPost?.type === 'image' && currentPost?.mediaUrl">
              <img :src="currentPost.mediaUrl" class="img-fluid rounded w-100" alt="post image">
            </div>
            <div v-else-if="currentPost?.type === 'video' && currentPost?.mediaUrl" class="ratio ratio-16x9">
              <iframe :src="currentPost.mediaUrl" title="video" allowfullscreen class="rounded border"></iframe>
            </div>
            <div v-else class="p-4 border rounded text-muted">
              Sin imagen o video
            </div>
          </div>

          <!-- Lado derecho: contenido y comentarios -->
          <div class="col-12 col-lg-5">
            <!-- Texto -->
            <p class="mb-3" v-if="currentPost?.content">{{ currentPost.content }}</p>

            <!-- Acciones -->
            <div class="d-flex gap-2 mb-3">
              <button class="btn btn-sm btn-light" @click="likeCurrent">
                <i class="bi bi-hand-thumbs-up me-1"></i>Like {{ currentPost?.likes || 0 }}
              </button>
              <button class="btn btn-sm btn-light" @click="dislikeCurrent">
                <i class="bi bi-hand-thumbs-down me-1"></i>Dislike {{ currentPost?.dislikes || 0 }}
              </button>
            </div>

            <hr>

            <!-- Lista de comentarios -->
            <div class="mb-3" style="max-height: 320px; overflow:auto">
              <div v-for="c in currentPost?.comments || []" :key="c.id" class="d-flex mb-3">
                <div class="flex-shrink-0 me-2">
                  <span class="bg-secondary-subtle d-inline-flex rounded-circle align-items-center justify-content-center" style="width:36px;height:36px">
                    <i class="bi bi-person"></i>
                  </span>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold small">{{ c.author }}</div>
                  <div>{{ c.text }}</div>
                </div>
              </div>
              <div v-if="!currentPost?.comments?.length" class="text-muted small">
                Aún no hay comentarios
              </div>
            </div>

            <!-- Formulario de comentario -->
            <form @submit.prevent="submitComment" class="d-flex gap-2">
              <input
                v-model="commentForm.text"
                type="text"
                class="form-control"
                placeholder="Escribe un comentario">
              <button class="btn btn-primary" type="submit">
                <i class="bi bi-send"></i>
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" @click="closePost">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

  </GuestLayout>
</template>

<style scoped>
/* Ajustes suaves para acercar el look del ejemplo y mejorar móvil */
.card { border-radius: 0.75rem; }
.navbar .btn { border-radius: 999px; }
.list-group-item { border: 0; }
@media (max-width: 991.98px) {
  .navbar-brand { font-size: 1rem; }
}

/* Opcional: mejora la barra de scroll de comentarios en el modal */
#modalPostView .modal-body [style*="overflow:auto"]::-webkit-scrollbar {
  height: 8px;
  width: 8px;
}
#modalPostView .modal-body [style*="overflow:auto"]::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,.15);
  border-radius: 8px;
}
</style>
