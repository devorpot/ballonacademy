<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Video;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoController extends Controller
{ 
    /**
     * Muestra el dashboard del estudiante con sus cursos asignados
     */
   

    /**
     * Lista de cursos asignados al estudiante
     */
public function listCourseVideos($courseId)
{
    $user = auth()->user();

    // Verificar que el usuario esté suscrito al curso
    $subscription = $user->subscriptions()
        ->where('course_id', $courseId)
        ->first();

    if (! $subscription) {
        abort(403, 'No estás suscrito a este curso.');
    }

    $course = Course::findOrFail($courseId);

    // Obtener todos los videos ordenados por el campo 'order'
    $videos = Video::where('course_id', $courseId)
        ->orderBy('order')
        ->get();

    // Obtener IDs de videos que el usuario ha completado
    $completedVideoIds = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('event', 'ended')
        ->pluck('video_id')
        ->toArray();


 



    $unlocked = true;

    // Convertir videos a array y agregar is_accessible e is_ended
    $videos = $videos->map(function ($video) use (&$unlocked, $completedVideoIds) {
        $videoArray = $video->toArray();

        $videoArray['is_ended'] = in_array($video->id, $completedVideoIds);
        $videoArray['is_accessible'] = $unlocked;

        if (!in_array($video->id, $completedVideoIds)) {
            $unlocked = false;
        }

        return $videoArray;
    });

    return Inertia::render('Frontend/Videos/List', [
        'course' => $course,
        'videos' => $videos,
        'completedVideoIds' => $completedVideoIds
    ]);
}



    /**
     * Muestra los videos de un curso específico asignado al estudiante
     */
public function show($courseId, $videoId)
{
    $user = auth()->user();

    // Verificar suscripción
    $subscription = $user->subscriptions()
        ->where('course_id', $courseId)
        ->first();

    if (! $subscription) {
        abort(403, 'No estás suscrito a este curso.');
    }

    $course = Course::findOrFail($courseId);

    // Obtener el video actual
    $video = Video::where('id', $videoId)
        ->where('course_id', $courseId)
        ->firstOrFail();

    // Obtener videos anterior y siguiente
    $previousVideo = Video::where('course_id', $courseId)
        ->where('order', '<', $video->order)
        ->orderBy('order', 'desc')
        ->first();

    $nextVideo = Video::where('course_id', $courseId)
        ->where('order', '>', $video->order)
        ->orderBy('order', 'asc')
        ->first();

    // Bloquear acceso si no ha finalizado el anterior
    if ($video->order > 1 && $previousVideo) {
        $hasEndedPrevious = DB::table('video_activities')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('video_id', $previousVideo->id)
            ->where('event', 'ended')
            ->exists();

        if (! $hasEndedPrevious) {
            abort(403, 'Debes completar el video anterior antes de continuar.');
        }
    }

    // Verificar si el video actual fue completado
    $nextVideoAccessible = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('video_id', $video->id)
        ->where('event', 'ended')
        ->exists();

    // Obtener la lista de videos completa
    $completedVideoIds = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('event', 'ended')
        ->pluck('video_id')
        ->toArray();

    $videos = Video::where('course_id', $courseId)
        ->orderBy('order')
        ->get();

    $unlocked = true;

    $videos = $videos->map(function ($v) use (&$unlocked, $completedVideoIds) {
        $v->is_accessible = $unlocked;

        if (!in_array($v->id, $completedVideoIds)) {
            $unlocked = false;
        }

        return $v;
    });

    return Inertia::render('Frontend/Videos/Show', [
        'course' => $course,
        'video' => $video,
        'previousVideo' => $previousVideo,
        'nextVideo' => $nextVideo,
        'nextVideoAccessible' => $nextVideoAccessible,
        'videos' => $videos,  
    ]);
}


     


 public function streamProtectedVideo($filename)
    {
        $user = auth()->user();

        $video = Video::where('video_url', $filename)->firstOrFail();

        // Verifica si el estudiante está inscrito en el curso
        if (!$user->courses->contains($video->course_id)) {
            abort(403);
        }

        $path = storage_path("app/videos/{$filename}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }


}
