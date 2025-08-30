<template>
  <section class="video-comments">
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item" role="presentation">
        <button
          class="nav-link"
          :class="{ active: activeTab === 'list' }"
          type="button"
          role="tab"
          @click="switchTab('list')"
        >
          Comentarios <span v-if="meta.total !== null" class="badge bg-secondary ms-1">{{ meta.total }}</span>
        </button>
      </li>
      <li v-if="canComment" class="nav-item" role="presentation">
        <button
          class="nav-link"
          :class="{ active: activeTab === 'new' }"
          type="button"
          role="tab"
          @click="switchTab('new')"
        >
          Nuevo comentario
        </button>
      </li>
    </ul>

    <!-- Panel: Lista -->
    <div v-show="activeTab === 'list'">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted" v-if="meta.total !== null">
          Mostrando {{ comments.length }} de {{ meta.total }}
        </small>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-sm btn-outline-secondary" @click="refresh" :disabled="loading">
            Recargar
          </button>
        </div>
      </div>

      <div v-if="loading" class="text-center py-4">Cargando comentarios…</div>

      <div v-else>
        <div v-if="comments.length === 0" class="text-muted py-4">
          Aún no hay comentarios en este video.
        </div>

        <ul class="list-group mb-3">
          <li v-for="c in comments" :key="c.id" class="list-group-item">
            <div class="d-flex">
              <img
                v-if="c.user?.avatar"
                :src="c.user.avatar"
                class="rounded-circle me-3"
                width="40"
                height="40"
                alt="avatar"
              />
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <strong>{{ c.user?.name || 'Usuario' }}</strong>
                    <small class="text-muted ms-2">{{ formatDate(c.created_at) }}</small>
                  </div>
                </div>
                <p class="mb-2 mt-2" style="white-space: pre-wrap;">{{ c.comment }}</p>

                <!-- Acciones opcionales (like/dislike) -->
                <div class="d-flex align-items-center gap-3">
                  <button
                    class="btn btn-sm btn-outline-primary"
                    :disabled="reactingId === c.id"
                    @click="like(c)"
                  >
                    Me gusta ({{ c.likes ?? 0 }})
                  </button>
                  <button
                    class="btn btn-sm btn-outline-danger"
                    :disabled="reactingId === c.id"
                    @click="dislike(c)"
                  >
                    No me gusta ({{ c.dislikes ?? 0 }})
                  </button>
                </div>
              </div>
            </div>
          </li>
        </ul>

        <!-- Paginación -->
        <nav v-if="meta.last_page > 1" aria-label="Paginación de comentarios">
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
              <button class="page-link" @click="goToPage(1)" :disabled="meta.current_page === 1">Primera</button>
            </li>
            <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
              <button class="page-link" @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page === 1">
                Anterior
              </button>
            </li>

            <li
              v-for="p in pageWindow"
              :key="p"
              class="page-item"
              :class="{ active: p === meta.current_page }"
            >
              <button class="page-link" @click="goToPage(p)">{{ p }}</button>
            </li>

            <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
              <button class="page-link" @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page">
                Siguiente
              </button>
            </li>
            <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
              <button class="page-link" @click="goToPage(meta.last_page)" :disabled="meta.current_page === meta.last_page">
                Última
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Panel: Nuevo comentario -->
    <div v-if="canComment" v-show="activeTab === 'new'">
      <div class="card">
        <div class="card-body">
          <div class="mb-2">
            <label class="form-label">Tu comentario</label>
            <textarea
              v-model.trim="form.comment"
              class="form-control"
              rows="4"
              placeholder="Escribe tu comentario…"
              @blur="touched = true"
            ></textarea>
            <div v-if="touched && !form.comment" class="invalid-feedback d-block">
              El comentario es obligatorio
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-outline-secondary" type="button" @click="resetForm" :disabled="posting">
              Limpiar
            </button>
            <button class="btn btn-primary" type="button" @click="submit" :disabled="!form.comment || posting">
              Publicar
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'

/**
 * Props esperadas
 */
const props = defineProps({
  videoId: { type: Number, required: true },
  courseId: { type: Number, required: true },
  perPage: { type: Number, default: 30 },       // 30 por requerimiento
  canComment: { type: Boolean, default: false }, // si el usuario puede comentar
})

/**
 * Estado
 */
const activeTab = ref('list')
const loading = ref(false)
const posting = ref(false)
const reactingId = ref(null)

const comments = ref([])
const meta = ref({
  current_page: 1,
  per_page: props.perPage,
  total: null,
  last_page: 1,
})
const form = ref({ comment: '' })
const touched = ref(false)

/**
 * Métodos utilitarios
 */
const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'list' && comments.value.length === 0) {
    fetchComments(1)
  }
}

const formatDate = (iso) => {
  try {
    return new Date(iso).toLocaleString('es-MX', {
      dateStyle: 'medium',
      timeStyle: 'short'
    })
  } catch {
    return iso
  }
}

/**
 * Carga de comentarios
 */
const fetchComments = async (page = 1) => {
  loading.value = true
  try {
    const { data } = await axios.get(
      route('dashboard.video-comments.index', { videoId: props.videoId }),
      { params: { per_page: props.perPage, page } }
    )

    comments.value = data.data || []
    meta.value.current_page = data.meta.current_page
    meta.value.per_page     = data.meta.per_page
    meta.value.total        = data.meta.total
    meta.value.last_page    = data.meta.last_page
  } catch (e) {
    // Manejo básico de error
    console.error(e)
  } finally {
    loading.value = false
  }
}

const refresh = () => fetchComments(meta.value.current_page)

const goToPage = (p) => {
  if (p < 1 || p > meta.value.last_page || p === meta.value.current_page) return
  fetchComments(p)
}

/**
 * Ventana de páginas (paginación compacta)
 */
const pageWindow = computed(() => {
  const total = meta.value.last_page
  const current = meta.value.current_page
  const span = 3 // 3 antes y 3 después
  const start = Math.max(1, current - span)
  const end = Math.min(total, current + span)
  const pages = []
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

/**
 * Publicar comentario
 */
const resetForm = () => {
  form.value.comment = ''
  touched.value = false
}

const submit = async () => {
  touched.value = true
  if (!form.value.comment) return

  posting.value = true
  try {
    const payload = {
      course_id: props.courseId,
      video_id: props.videoId,
      comment: form.value.comment,
    }
    const { data } = await axios.post(route('dashboard.video-comments.store'), payload)

    // Insertar optimistamente al inicio si estás en la lista
    if (activeTab.value === 'list') {
      comments.value.unshift(data.data)
    }

    // Actualizar conteo total si existe
    if (meta.value.total !== null) meta.value.total += 1

    resetForm()
    activeTab.value = 'list'
  } catch (e) {
    console.error(e)
  } finally {
    posting.value = false
  }
}

/**
 * Reacciones
 */
const like = async (c) => {
  reactingId.value = c.id
  try {
    // Optimismo: actualizar UI mientras responde
    c.likes = (c.likes || 0) + 1
    const { data } = await axios.post(route('dashboard.video-comments.like', { id: c.id }))
    c.likes = data.likes
    c.dislikes = data.dislikes
  } catch (e) {
    console.error(e)
    // En error sería ideal recargar el ítem o la página actual
    refresh()
  } finally {
    reactingId.value = null
  }
}

const dislike = async (c) => {
  reactingId.value = c.id
  try {
    c.dislikes = (c.dislikes || 0) + 1
    const { data } = await axios.post(route('dashboard.video-comments.dislike', { id: c.id }))
    c.likes = data.likes
    c.dislikes = data.dislikes
  } catch (e) {
    console.error(e)
    refresh()
  } finally {
    reactingId.value = null
  }
}

/**
 * Auto-carga al montar
 */
onMounted(() => {
  fetchComments(1)
})
</script>

<style scoped>
.video-comments .list-group-item {
  border-left: 0;
  border-right: 0;
}
</style>
