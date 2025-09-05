<template>
  <section class="video-comments">

    <div class="card border-0 shadow my-3">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
          <h6 class="fw-bold mb-0">
            <i class="bi bi-chat-dots me-1"></i> Comentarios
          </h6>

          <button
            type="button"
           class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center"
            @click="isOpenComments = !isOpenComments"
            :aria-expanded="isOpenComments"
            :aria-controls="commentsBodyId"
            :title="isOpenComments ? 'Ocultar' : 'Mostrar'"
          >
            <i v-if="isOpenComments" class="bi bi-chevron-down"></i>
            <i v-else class="bi bi-dash-lg"></i>
          </button>
        </div>

        <Transition name="collapse-fade">
          <div v-show="isOpenComments" :id="commentsBodyId" class="card-body">
          <!-- Tabs -->
          <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
              <button
                class="nav-link d-flex align-items-center"
                :class="{ active: activeTab === 'list' }"
                type="button"
                role="tab"
                @click="switchTab('list')"
              >
                <i class="bi bi-chat-text me-2"></i>
                Comentarios
                <span v-if="meta.total !== null" class="badge bg-secondary ms-2">{{ meta.total }}</span>
              </button>
            </li>

            <li v-if="canComment" class="nav-item" role="presentation">
              <button
                class="nav-link d-flex align-items-center"
                :class="{ active: activeTab === 'new' }"
                type="button"
                role="tab"
                @click="switchTab('new')"
              >
                <i class="bi bi-pencil-square me-2"></i>
                Nuevo comentario
              </button>
            </li>
          </ul>

          <!-- Panel: Lista -->
          <div v-show="activeTab === 'list'">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <small class="text-muted" v-if="meta.total !== null">
                Mostrando <strong>{{ comments.length }}</strong> de <strong>{{ meta.total }}</strong>
              </small>

              <div class="d-flex align-items-center gap-2">
                <button
                  class="btn btn-sm  rounded-pill btn-primary d-flex align-items-center"
                  @click="refresh"
                  :disabled="loading"
                  data-bs-toggle="tooltip"
                  title="Recargar comentarios"
                >
                  <i class="bi bi-arrow-clockwise" :class="{ 'spin': loading }"></i>
                  <span class="ms-2 d-none d-sm-inline">Recargar</span>
                </button>
              </div>
            </div>

            <!-- Cargando -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border" role="status" aria-hidden="true"></div>
              <div class="text-muted mt-2">Cargando comentarios…</div>
            </div>

            <!-- Sin comentarios -->
            <div v-else-if="comments.length === 0" class="card border-0 bg-light-subtle py-5 text-center">
              <i class="bi bi-chat-left-text fs-1 text-muted"></i>
              <p class="text-muted mt-2 mb-0">Aún no hay comentarios en este video.</p>
            </div>

            <!-- Lista -->
            <ul v-else class="list-group mb-3">
              <li
                v-for="c in comments"
                :key="c.id"
                class="list-group-item border-0 border-bottom"
              >
                <div class="d-flex">
                  <!-- Avatar -->
                  <div class="me-3 flex-shrink-0">
                    <template v-if="c.user?.avatar">
                      <img
                        :src="c.user.avatar"
                        class="rounded-circle object-fit-cover shadow-sm"
                        width="40" height="40" alt="avatar"
                      />
                    </template>
                    <template v-else>
                      <div class="rounded-circle bg-body-secondary text-muted d-flex align-items-center justify-content-center shadow-sm"
                           style="width:40px;height:40px;">
                        <i class="bi bi-person"></i>
                      </div>
                    </template>
                  </div>

                  <!-- Contenido -->
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="lh-sm">
                        <strong>{{ c.user?.name || 'Usuario' }}</strong>
                        <small class="text-muted ms-2">
                          <i class="bi bi-clock me-1"></i>{{ formatDate(c.created_at) }}
                        </small>
                      </div>
                      <!-- Badges opcionales (ej. moderación/estado) -->
                    </div>

                    <p class="mb-2 mt-2 text-break" style="white-space: pre-wrap;">{{ c.comment }}</p>

                    <!-- Acciones -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        :disabled="reactingId === c.id"
                        @click="like(c)"
                      >
                        <i class="bi bi-hand-thumbs-up me-1"></i>
                        <span class="d-none d-sm-inline"></span>
                        <span class="badge bg-primary-subtle text-primary-emphasis ms-2">{{ c.likes ?? 0 }}</span>
                      </button>

                      <button
                        class="btn btn-sm btn-outline-danger"
                        :disabled="reactingId === c.id"
                        @click="dislike(c)"
                      >
                        <i class="bi bi-hand-thumbs-down me-1"></i>
                        <span class="d-none d-sm-inline"></span>
                        <span class="badge bg-danger-subtle text-danger-emphasis ms-2">{{ c.dislikes ?? 0 }}</span>
                      </button>

                      <button
                        v-if="canComment"
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="() => { ensureReplyState(c.id); repliesState[c.id].open = true; }"
                      >
                        <i class="bi bi-reply me-1"></i>
                        
                      </button>

                      <button
                        v-if="(c.replies_count ?? 0) > 0"
                        class="btn btn-sm btn-link text-decoration-none"
                        type="button"
                        @click="() => toggleReplies(c)"
                      >
                        <i class="bi" :class="repliesState[c.id]?.open ? 'bi-chat-dots-fill' : 'bi-chat-dots'"></i>
                        <span class="ms-1">
                          {{ repliesState[c.id]?.open ? 'Ocultar' : 'Ver' }} {{ c.replies_count }} respuestas
                        </span>
                      </button>
                    </div>

                    <!-- Respuestas -->
                    <div v-if="repliesState[c.id]?.open" class="mt-3 ps-3 border-start border-3 border-light-subtle">
                      <!-- Formulario respuesta -->
                      <div v-if="canComment" class="mb-3">
                        <label class="form-label d-flex align-items-center mb-1">
                          <i class="bi bi-reply-fill me-2"></i> Tu respuesta
                        </label>
                        <textarea
                          v-model.trim="repliesState[c.id].replyText"
                          class="form-control"
                          rows="3"
                          placeholder="Escribe tu respuesta…"
                          :maxlength="maxCommentLength"
                        ></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                          <small class="text-muted" v-if="maxCommentLength">
                            {{ (repliesState[c.id].replyText?.length || 0) }} / {{ maxCommentLength }}
                          </small>
                          <div class="d-flex gap-2">
                            <button
                              class="btn btn-sm btn-outline-secondary"
                              type="button"
                              @click="repliesState[c.id].replyText = ''"
                              :disabled="repliesState[c.id].replying || !repliesState[c.id].replyText"
                            >
                              <i class="bi bi-x-circle me-1"></i> Limpiar
                            </button>
                            <button
                              class="btn btn-sm btn-primary"
                              type="button"
                              :disabled="repliesState[c.id].replying || !repliesState[c.id].replyText"
                              @click="() => submitReply(c)"
                            >
                              <i class="bi bi-send me-1"></i> Responder
                            </button>
                          </div>
                        </div>
                      </div>

                      <!-- Lista de respuestas -->
                      <div v-if="repliesState[c.id].loading" class="text-muted py-2">
                        <i class="bi bi-arrow-repeat me-1 spin"></i> Cargando respuestas…
                      </div>

                      <ul v-else class="list-group list-group-flush">
                        <li v-for="r in repliesState[c.id].items" :key="r.id" class="list-group-item px-0">
                          <div class="d-flex">
                            <div class="me-2 flex-shrink-0">
                              <template v-if="r.user?.avatar">
                                <img
                                  :src="r.user.avatar"
                                  class="rounded-circle object-fit-cover shadow-sm"
                                  width="32" height="32" alt="avatar"
                                />
                              </template>
                              <template v-else>
                                <div class="rounded-circle bg-body-secondary text-muted d-flex align-items-center justify-content-center shadow-sm"
                                     style="width:32px;height:32px;">
                                  <i class="bi bi-person"></i>
                                </div>
                              </template>
                            </div>

                            <div class="flex-grow-1">
                              <div class="d-flex align-items-center lh-sm">
                                <strong>{{ r.user?.name || 'Usuario' }}</strong>
                                <small class="text-muted ms-2">
                                  <i class="bi bi-clock me-1"></i>{{ formatDate(r.created_at) }}
                                </small>
                              </div>

                              <p class="mb-2 mt-1 text-break" style="white-space: pre-wrap;">{{ r.comment }}</p>

                              <div class="d-flex flex-wrap align-items-center gap-2">
                                <button
                                  class="btn btn-xs btn-outline-primary"
                                  :disabled="reactingId === r.id"
                                  @click="like(r)"
                                >
                                  <i class="bi bi-hand-thumbs-up me-1"></i>
                                  <span class="d-none d-sm-inline"></span>
                                  <span class="badge bg-primary-subtle text-primary-emphasis ms-2">{{ r.likes ?? 0 }}</span>
                                </button>
                                <button
                                  class="btn btn-xs btn-outline-danger"
                                  :disabled="reactingId === r.id"
                                  @click="dislike(r)"
                                >
                                  <i class="bi bi-hand-thumbs-down me-1"></i>
                                  <span class="d-none d-sm-inline"></span>
                                  <span class="badge bg-danger-subtle text-danger-emphasis ms-2">{{ r.dislikes ?? 0 }}</span>
                                </button>
                              </div>
                            </div>
                          </div>
                        </li>
                      </ul>

                      <!-- Paginación de respuestas -->
                      <nav v-if="repliesState[c.id].meta.last_page > 1" class="mt-2" aria-label="Paginación de respuestas">
                        <ul class="pagination pagination-sm mb-0">
                          <li class="page-item" :class="{ disabled: repliesState[c.id].meta.current_page === 1 }">
                            <button class="page-link" @click="goToRepliesPage(c.id, repliesState[c.id].meta.current_page - 1)">
                              <i class="bi bi-chevron-left"></i>
                            </button>
                          </li>
                          <li class="page-item disabled">
                            <span class="page-link">
                              Página {{ repliesState[c.id].meta.current_page }} de {{ repliesState[c.id].meta.last_page }}
                            </span>
                          </li>
                          <li class="page-item" :class="{ disabled: repliesState[c.id].meta.current_page === repliesState[c.id].meta.last_page }">
                            <button class="page-link" @click="goToRepliesPage(c.id, repliesState[c.id].meta.current_page + 1)">
                              <i class="bi bi-chevron-right"></i>
                            </button>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  </div>
                </div>
              </li>
            </ul>

            <!-- Paginación principal -->
            <nav v-if="meta.last_page > 1" aria-label="Paginación de comentarios">
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                  <button class="page-link" @click="goToPage(1)" :disabled="meta.current_page === 1" data-bs-toggle="tooltip" title="Primera">
                    <i class="bi bi-chevron-bar-left"></i>
                  </button>
                </li>
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                  <button class="page-link" @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page === 1" data-bs-toggle="tooltip" title="Anterior">
                    <i class="bi bi-chevron-left"></i>
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
                  <button class="page-link" @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" data-bs-toggle="tooltip" title="Siguiente">
                    <i class="bi bi-chevron-right"></i>
                  </button>
                </li>
                <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                  <button class="page-link" @click="goToPage(meta.last_page)" :disabled="meta.current_page === meta.last_page" data-bs-toggle="tooltip" title="Última">
                    <i class="bi bi-chevron-bar-right"></i>
                  </button>
                </li>
              </ul>
            </nav>
          </div>

          <!-- Panel: Nuevo comentario -->
          <div v-if="canComment" v-show="activeTab === 'new'">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-pencil-square me-2"></i>
                  <h6 class="mb-0">Escribe un comentario</h6>
                </div>

                <div class="mb-2">
                  <textarea
                    v-model.trim="form.comment"
                    class="form-control"
                    rows="4"
                    placeholder="Comparte tu opinión…"
                    @blur="touched = true"
                    :maxlength="maxCommentLength"
                  ></textarea>
                  <div class="d-flex justify-content-between mt-1">
                    <div class="invalid-feedback d-block" v-if="touched && !form.comment">
                      El comentario es obligatorio
                    </div>
                    <small class="text-muted ms-auto" v-if="maxCommentLength">
                      {{ (form.comment?.length || 0) }} / {{ maxCommentLength }}
                    </small>
                  </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <button
                    class="btn btn-outline-secondary d-flex align-items-center"
                    type="button"
                    @click="resetForm"
                    :disabled="posting"
                  >
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                  </button>
                  <button
                    class="btn btn-primary d-flex align-items-center"
                    type="button"
                    @click="submit"
                    :disabled="!form.comment || posting"
                  >
                    <i class="bi bi-send me-1"></i> Publicar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>



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
  perPage: { type: Number, default: 10 },       // 30 por requerimiento
  canComment: { type: Boolean, default: false }, // si el usuario puede comentar
})

/**
 * Estado
 */
const activeTab = ref('list')
const loading = ref(false)
const posting = ref(false)
const reactingId = ref(null)


const isOpenComments = ref(true)
const commentsBodyId = computed(() => `comments-body-${props.videoId ?? 'x'}`)

const maxCommentLength = 1000

const comments = ref([])
const meta = ref({
  current_page: 1,
  per_page: props.perPage,
  total: null,
  last_page: 1,
})
const form = ref({ comment: '' })
const touched = ref(false)
const repliesState = ref({}) 
/**
 * Métodos utilitarios
 */
const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'list' && comments.value.length === 0) {
    fetchComments(1)
  }
}



const fetchReplies = async (parentId, page = 1) => {
  ensureReplyState(parentId)
  const rs = repliesState.value[parentId]
  rs.loading = true
  try {
    const { data } = await axios.get(
      route('dashboard.video-comments.replies', { parentId }),
      { params: { per_page: rs.meta.per_page, page } }
    )
    rs.items = data.data || []
    rs.meta.current_page = data.meta.current_page
    rs.meta.last_page    = data.meta.last_page
    rs.meta.per_page     = data.meta.per_page
    rs.meta.total        = data.meta.total
  } catch (e) {
    console.error(e)
  } finally {
    rs.loading = false
  }
}

const toggleReplies = async (parent) => {
  ensureReplyState(parent.id)
  const rs = repliesState.value[parent.id]
  rs.open = !rs.open
  if (rs.open && rs.items.length === 0) {
    await fetchReplies(parent.id, 1)
  }
}

const goToRepliesPage = async (parentId, p) => {
  const rs = repliesState.value[parentId]
  if (!rs) return
  if (p < 1 || p > rs.meta.last_page || p === rs.meta.current_page) return
  await fetchReplies(parentId, p)
}


const submitReply = async (parent) => {
  ensureReplyState(parent.id)
  const rs = repliesState.value[parent.id]
  if (!rs.replyText) return

  rs.replying = true
  try {
    const payload = {
      course_id: props.courseId,
      video_id: props.videoId,
      comment: rs.replyText,
      reply_comment_id: parent.id
    }
    const { data } = await axios.post(route('dashboard.video-comments.store'), payload)

    // Si el panel de respuestas está abierto, insertamos al final (orden ascendente)
    if (rs.open) rs.items.push(data.data)

    // Actualiza contador del padre si lo usas
    if (typeof parent.replies_count === 'number') parent.replies_count += 1

    rs.replyText = ''
  } catch (e) {
    console.error(e)
  } finally {
    rs.replying = false
  }
}




const ensureReplyState = (commentId) => {
  if (!repliesState.value[commentId]) {
    repliesState.value[commentId] = {
      loading: false,
      items: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
      open: false,
      replying: false,
      replyText: ''
    }
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

    if (window.bootstrap?.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      new window.bootstrap.Tooltip(el)
    })
  }
})
</script>

<style scoped>
.video-comments .list-group-item:hover { background-color: var(--bs-light-bg-subtle, #f8f9fa); }
.object-fit-cover { object-fit: cover; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
