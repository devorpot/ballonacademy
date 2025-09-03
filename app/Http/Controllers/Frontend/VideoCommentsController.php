<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VideoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class VideoCommentsController extends Controller
{
    /**
     * Listar comentarios raíz (top-level) de un video (paginados)
     * GET /frontend/video-comments/video/{videoId}?per_page=10&page=1
     */
    public function index(Request $request, int $videoId)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        $comments = VideoComment::with(['user:id,name']) // agrega avatar si aplica
            ->where('video_id', $videoId)
            ->whereNull('reply_comment_id')              // solo top-level
            ->orderBy('created_at', 'desc')
            ->withCount('replies')
            ->paginate($perPage);

        return response()->json([
            'data' => $comments->items(),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page'     => $comments->perPage(),
                'total'        => $comments->total(),
                'last_page'    => $comments->lastPage(),
            ],
        ]);
    }

    /**
     * Listar respuestas de un comentario (paginadas)
     * GET /frontend/video-comments/{parentId}/replies?per_page=10&page=1
     */
    public function replies(Request $request, int $parentId)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        $parent = VideoComment::select('id','video_id')->findOrFail($parentId);

        $replies = VideoComment::with(['user:id,name'])
            ->where('video_id', $parent->video_id)
            ->where('reply_comment_id', $parentId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return response()->json([
            'data' => $replies->items(),
            'meta' => [
                'current_page' => $replies->currentPage(),
                'per_page'     => $replies->perPage(),
                'total'        => $replies->total(),
                'last_page'    => $replies->lastPage(),
            ],
        ]);
    }

    /**
     * Publicar un nuevo comentario o respuesta.
     * POST /frontend/video-comments
     * Campos: course_id, video_id, comment, reply_comment_id? (nullable)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'course_id'        => ['required', 'integer', 'exists:courses,id'],
            'video_id'         => ['required', 'integer', 'exists:videos,id'],
            'comment'          => ['required', 'string'],
            'reply_comment_id' => ['nullable', 'integer', 'exists:video_comments,id'],
        ]);

        // Si es respuesta, validamos coherencia de pertenencia al mismo video
        if (!empty($data['reply_comment_id'])) {
            $parent = VideoComment::select('id','video_id','course_id')->findOrFail($data['reply_comment_id']);
            abort_if((int)$parent->video_id !== (int)$data['video_id'], 422, 'La respuesta debe pertenecer al mismo video.');
            abort_if((int)$parent->course_id !== (int)$data['course_id'], 422, 'La respuesta debe pertenecer al mismo curso.');
        }

        $comment = VideoComment::create([
            'user_id'          => $user->id,
            'course_id'        => $data['course_id'],
            'video_id'         => $data['video_id'],
            'comment'          => $data['comment'],
            'likes'            => 0,
            'dislikes'         => 0,
            'reply_comment_id' => $data['reply_comment_id'] ?? null,
        ]);

        $comment->load('user:id,name');

        return response()->json([
            'message' => 'Comentario publicado',
            'data'    => $comment,
        ], 201);
    }

    // Like / Dislike (sin cambios de fondo; aplican para comentario o respuesta)
    public function like(Request $request, int $id)
    {
        $userId = Auth::id();
        $comment = VideoComment::findOrFail($id);

        $likeKey = $this->likeCacheKey($id, $userId);
        $dislikeKey = $this->dislikeCacheKey($id, $userId);

        if (Cache::get($likeKey)) {
            if (($comment->likes ?? 0) > 0) $comment->decrement('likes');
            Cache::forget($likeKey);
            return response()->json([
                'status'   => 'unliked',
                'likes'    => $comment->likes,
                'dislikes' => $comment->dislikes,
            ]);
        }

        $comment->increment('likes');
        Cache::put($likeKey, true, now()->addYear());

        if (Cache::pull($dislikeKey)) {
            if (($comment->dislikes ?? 0) > 0) $comment->decrement('dislikes');
        }

        return response()->json([
            'status'   => 'liked',
            'likes'    => $comment->likes,
            'dislikes' => $comment->dislikes,
        ]);
    }

    public function dislike(Request $request, int $id)
    {
        $userId = Auth::id();
        $comment = VideoComment::findOrFail($id);

        $likeKey = $this->likeCacheKey($id, $userId);
        $dislikeKey = $this->dislikeCacheKey($id, $userId);

        if (Cache::get($dislikeKey)) {
            if (($comment->dislikes ?? 0) > 0) $comment->decrement('dislikes');
            Cache::forget($dislikeKey);
            return response()->json([
                'status'   => 'undisliked',
                'likes'    => $comment->likes,
                'dislikes' => $comment->dislikes,
            ]);
        }

        $comment->increment('dislikes');
        Cache::put($dislikeKey, true, now()->addYear());

        if (Cache::pull($likeKey)) {
            if (($comment->likes ?? 0) > 0) $comment->decrement('likes');
        }

        return response()->json([
            'status'   => 'disliked',
            'likes'    => $comment->likes,
            'dislikes' => $comment->dislikes,
        ]);
    }

    private function likeCacheKey(int $commentId, int $userId): string
    {
        return "vc_like_{$commentId}_u{$userId}";
    }

    private function dislikeCacheKey(int $commentId, int $userId): string
    {
        return "vc_dislike_{$commentId}_u{$userId}";
    }
}
