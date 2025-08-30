<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VideoComment;
use App\Models\Video;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class VideoCommentsController extends Controller
{
    /**
     * Listar comentarios de un video (paginados).
     * GET /frontend/video-comments/video/{videoId}?per_page=10&page=1
     */
    public function index(Request $request, int $videoId)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        $comments = VideoComment::with([
                'user:id,name,avatar', // ajusta 'avatar' si tu columna/relación es distinta
            ])
            ->where('video_id', $videoId)
            ->orderBy('created_at', 'desc')
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
     * Publicar un nuevo comentario.
     * POST /frontend/video-comments
     * Campos: course_id, video_id, comment
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'video_id'  => ['required', 'integer', 'exists:videos,id'],
            'comment'   => ['required', 'string'],
        ]);

        // (Opcional) Validar coherencia: que el video pertenezca al curso
        // Si manejas esa relación en tu esquema, descomenta esto:
        // $videoCourseId = Video::where('id', $data['video_id'])->value('course_id');
        // abort_if($videoCourseId !== (int) $data['course_id'], 422, 'El video no pertenece al curso indicado.');

        $comment = VideoComment::create([
            'user_id'   => $user->id,
            'course_id' => $data['course_id'],
            'video_id'  => $data['video_id'],
            'comment'   => $data['comment'],
            'likes'     => 0,
            'dislikes'  => 0,
        ]);

        // Devolver el comentario con relación user cargada para pintarlo inmediatamente
        $comment->load('user:id,name,avatar');

        return response()->json([
            'message' => 'Comentario publicado',
            'data'    => $comment,
        ], 201);
    }

    /**
     * Like con toggle.
     * POST /frontend/video-comments/{id}/like
     */
    public function like(Request $request, int $id)
    {
        $userId = Auth::id();
        $comment = VideoComment::findOrFail($id);

        $likeKey = $this->likeCacheKey($id, $userId);
        $dislikeKey = $this->dislikeCacheKey($id, $userId);

        if (Cache::get($likeKey)) {
            // Quitar like (toggle off)
            if (($comment->likes ?? 0) > 0) {
                $comment->decrement('likes');
            }
            Cache::forget($likeKey);

            return response()->json([
                'status'   => 'unliked',
                'likes'    => $comment->likes,
                'dislikes' => $comment->dislikes,
            ]);
        }

        // Poner like
        $comment->increment('likes');
        Cache::put($likeKey, true, now()->addYear());

        // Si tenía dislike del mismo usuario, retirarlo
        if (Cache::pull($dislikeKey)) {
            if (($comment->dislikes ?? 0) > 0) {
                $comment->decrement('dislikes');
            }
        }

        return response()->json([
            'status'   => 'liked',
            'likes'    => $comment->likes,
            'dislikes' => $comment->dislikes,
        ]);
    }

    /**
     * Dislike con toggle.
     * POST /frontend/video-comments/{id}/dislike
     */
    public function dislike(Request $request, int $id)
    {
        $userId = Auth::id();
        $comment = VideoComment::findOrFail($id);

        $likeKey = $this->likeCacheKey($id, $userId);
        $dislikeKey = $this->dislikeCacheKey($id, $userId);

        if (Cache::get($dislikeKey)) {
            // Quitar dislike (toggle off)
            if (($comment->dislikes ?? 0) > 0) {
                $comment->decrement('dislikes');
            }
            Cache::forget($dislikeKey);

            return response()->json([
                'status'   => 'undisliked',
                'likes'    => $comment->likes,
                'dislikes' => $comment->dislikes,
            ]);
        }

        // Poner dislike
        $comment->increment('dislikes');
        Cache::put($dislikeKey, true, now()->addYear());

        // Si tenía like del mismo usuario, retirarlo
        if (Cache::pull($likeKey)) {
            if (($comment->likes ?? 0) > 0) {
                $comment->decrement('likes');
            }
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
