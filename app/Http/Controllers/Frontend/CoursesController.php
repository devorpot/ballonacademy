<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
 
use App\Models\Course;
use App\Models\Subscription;
use App\Models\Video;
use App\Models\VideoActivity;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoursesController extends Controller
{ 
    /**
     * Muestra el dashboard del estudiante con sus cursos asignados
 

    /**
     * Lista de cursos asignados al estudiante
     */
 public function index()
{
    $user   = Auth::user();
    $userId = (int) $user->id;

    // TODOS los cursos, con counts (0 si no hay) y un flag de suscripción por curso
$courses = Course::query()
    ->withCount(['lessons as lessons_count', 'videos as videos_count'])
    ->orderBy('courses.created_at', 'asc') // más viejo → más nuevo
    ->get()
    ->map(function ($c) {
        return array_merge(
            $c->toArray(),
            [
                'lessons_count' => (int) $c->lessons_count,
                'videos_count'  => (int) $c->videos_count,
                'is_subscribed' => (bool) $c->is_subscribed,
            ]
        );
    });


    // Últimos videos vistos (con LEFT JOIN para no excluir sin actividad)
    $lastByVideo = VideoActivity::selectRaw('video_id, MAX(updated_at) as last_seen')
        ->where('user_id', $userId)
        ->where('event', 'ended')
        ->groupBy('video_id');

    $videos = Video::query()
        ->select([
            'videos.id','videos.title','videos.image_cover','videos.duration',
            'videos.lesson_id','videos.size','videos.course_id','va.last_seen',
        ])
        ->leftJoinSub($lastByVideo, 'va', fn($join) => $join->on('va.video_id','=','videos.id'))
        ->with('course:id,title')
        ->orderByRaw('va.last_seen IS NULL, va.last_seen DESC')
        ->limit(12)
        ->get()
        ->map(fn($video) => [
            'id'          => $video->id,
            'title'       => $video->title,
            'image_cover' => $video->image_cover,
            'duration'    => $video->duration,
            'size'        => $video->size,
            'lesson_id'   => $video->lesson_id,
            'course'      => [
                'id'    => $video->course_id,
                'title' => optional($video->course)->title,
            ],
            'last_seen'   => $video->last_seen,
        ]);

    $welcome = session()->pull('welcome_message');

    return Inertia::render('Frontend/Courses/Index', [
        'courses' => $courses,
        'videos'  => $videos,
        'welcome' => $welcome,
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
