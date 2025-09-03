<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoComment;
use App\Models\Course;
use App\Models\Video;
use App\Models\User;
use Illuminate\Http\Request;

class VideoCommentsController extends Controller
{
    // app/Http/Controllers/Admin/VideoCommentsController.php
// app/Http/Controllers/Admin/VideoCommentsController.php
public function index(Request $request)
{
    $q        = trim((string) $request->input('q', ''));
    $courseId = $request->input('course_id');
    $videoId  = $request->input('video_id');
    $userId   = $request->input('user_id');
    $active   = $request->input('active');

    $sortBy   = $request->input('sortBy', 'video_comments.created_at');
    $sortDir  = $request->input('sortDir', 'desc');

    $perPage  = (int) $request->input('perPage', 30);
    if ($perPage < 1 || $perPage > 100) $perPage = 30;

    $query = \App\Models\VideoComment::query()
        ->with(['user:id,name', 'course:id,title', 'video:id,title'])
        ->select('id','user_id','course_id','video_id','comment','likes','dislikes','active','created_at')
        ->when($q !== '', fn($qq) => $qq->where('comment', 'like', "%{$q}%"))
        ->when($courseId, fn($qq) => $qq->where('course_id', $courseId))
        ->when($videoId,  fn($qq) => $qq->where('video_id',  $videoId))
        ->when($userId,   fn($qq) => $qq->where('user_id',   $userId))
        ->when(!is_null($active), fn($qq) => $qq->where('active', (bool) $active))
        ->orderBy($sortBy, $sortDir);

    // IMPORTANTÍSIMO: usar paginate(), no get()
    $comments = $query->paginate($perPage)->appends($request->query());

    // catálogos para filtros
    $courses = \App\Models\Course::select('id','title')->orderBy('title')->get();
    $videos  = \App\Models\Video::select('id','title','course_id')->orderBy('title')->get();
    $users   = \App\Models\User::select('id','name')->orderBy('name')->get();

    return inertia('Admin/VideoComments/Index', [
        'comments' => $comments,  // <- así llega data/meta/links
        'courses'  => $courses,
        'videos'   => $videos,
        'users'    => $users,
        'filters'  => [
            'q'         => $q,
            'course_id' => $courseId,
            'video_id'  => $videoId,
            'user_id'   => $userId,
            'active'    => $active,
            'sortBy'    => $sortBy,
            'sortDir'   => $sortDir,
            'perPage'   => $perPage,
        ],
    ]);
}



    public function toggle(VideoComment $comment, Request $request)
    {
        try {
            $comment->active = ! $comment->active;
            $comment->save();

            if ($request->wantsJson()) {
                return response()->json(['ok' => true, 'active' => $comment->active], 200);
            }

            return back()->with('success', 'Estado del comentario actualizado.');
        } catch (\Throwable $e) {
            \Log::error('Error al actualizar estado de comentario: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'No se pudo actualizar el estado'], 500);
            }
            return back()->with('error', 'No se pudo actualizar el estado.');
        }
    }

    public function destroy(VideoComment $comment, Request $request)
    {
        try {
            $comment->delete();

            if ($request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => 'Comentario eliminado'], 200);
            }

            return back()->with('success', 'El comentario se eliminó correctamente.');
        } catch (\Throwable $e) {
            \Log::error('Error al eliminar comentario: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Ocurrió un error al eliminar'], 500);
            }
            return back()->with('error', 'Ocurrió un error al eliminar el comentario.');
        }
    }
}
