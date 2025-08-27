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

class CoursesController extends Controller
{ 
    /**
     * Muestra el dashboard del estudiante con sus cursos asignados
     */
    public function dashboard()
    {
        $user = Auth::user();
        $courses = $user->courses()->withCount('videos')->get();
        return Inertia::render('Frontend/Dashboard/Index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Lista de cursos asignados al estudiante
     */
    public function index()
{
    $user = Auth::user();

    $courses = $user->courses()
        ->select('courses.*')            // asegura selección de la tabla base
        ->distinct('courses.id')       
        ->withCount('videos')
        ->get();

    return Inertia::render('Frontend/Courses/Index', [
        'courses' => $courses,
    ]);
}


    /**
     * Muestra los videos de un curso específico asignado al estudiante
     */
public function show($courseId)
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

    // Añadir flag 'is_accessible' a cada video
    $videos = $videos->map(function ($video) use (&$unlocked, $completedVideoIds) {
        $videoArray = $video->toArray();

        $videoArray['is_ended'] = in_array($video->id, $completedVideoIds);
        $videoArray['is_accessible'] = $unlocked;

        if (!in_array($video->id, $completedVideoIds)) {
            $unlocked = false;
        }

        return $videoArray;
    });

    // Solo queremos un booleano si existe registro de ended
    $hasCourseEnded = DB::table('course_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('ended', 1)
        ->exists();

    return Inertia::render('Frontend/Courses/CourseVideosList', [
        'course'             => $course,
        'videos'             => $videos,
        'completedVideoIds'  => $completedVideoIds,
        'hasCourseEnded'     => $hasCourseEnded,
    ]);
}

  
public function videoDetail($courseId, $videoId)
{
    $user = auth()->user();

    // Verificar que el usuario esté suscrito al curso
    $subscription = $user->subscriptions()
        ->where('course_id', $courseId)
        ->first();

    if (! $subscription) {
        abort(403, 'No estás suscrito a este curso.');
    }

    // Verificar que el video pertenezca al curso
    $video = Video::where('id', $videoId)
                  ->where('course_id', $courseId)
                  ->firstOrFail();

    $course = Course::findOrFail($courseId);

    return Inertia::render('Frontend/Courses/CourseVideoDetail', [
        'course' => $course,
        'video' => $video,
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
