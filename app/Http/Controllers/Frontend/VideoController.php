<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use App\Models\Video;
use App\Models\Course;
use App\Models\Teacher;


use App\Models\Evaluation;
use App\Models\EvaluationUser;

use App\Enums\EvaluationsTypes; // COURSE | VIDEO

class VideoController extends Controller
{
    /**
     * Lista de videos del curso asignados al estudiante,
     * con flags de acceso/completado y si ya envió evaluación del video.
     */
    public function listCourseVideos($courseId)
    {
        $user = auth()->user();

        // Verificar suscripción del usuario al curso
        $subscription = $user->subscriptions()
            ->where('course_id', $courseId)
            ->first();

        if (! $subscription) {
            abort(403, 'No estás suscrito a este curso.');
        }

        $course = Course::findOrFail($courseId);
        $userId = $user->id;

        // IDs de videos completados (evento "ended")
        $completedVideoIds = DB::table('video_activities')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('event', 'ended')
            ->pluck('video_id')
            ->toArray();

        // Obtener todos los videos del curso
        $videos = Video::where('course_id', $courseId)
            ->orderBy('order')
            ->get();

        // Map de video_id => true si el usuario ya envió alguna evaluación asignada a ese video
        // (Un solo query que cruza evaluations_users con evaluations)
        $submittedByVideoIds = EvaluationUser::query()
            ->select('evaluations.video_id')
            ->join('evaluations', 'evaluations.id', '=', 'evaluations_users.evaluation_id')
            ->where('evaluations.course_id', $courseId)
            ->where('evaluations.type', EvaluationsTypes::VIDEO)
            ->where('evaluations_users.user_id', $userId)
            ->pluck('evaluations.video_id')
            ->unique()
            ->values()
            ->toArray();

        $submittedByVideoSet = array_flip($submittedByVideoIds);

        // Desbloqueo progresivo: el primer no-completado bloquea los siguientes
        $unlocked = true;

        $videos = $videos->map(function ($video) use (&$unlocked, $completedVideoIds, $submittedByVideoSet) {
            $videoArray = $video->toArray();

            $videoArray['is_ended']      = in_array($video->id, $completedVideoIds);
            $videoArray['is_accessible'] = $unlocked;

            if (! in_array($video->id, $completedVideoIds)) {
                $unlocked = false;
            }

            // ¿El usuario ya envió al menos una evaluación asignada a este video?
            $videoArray['evaluation_submitted'] = isset($submittedByVideoSet[$video->id]);

            return $videoArray;
        });

        return Inertia::render('Frontend/Videos/List', [
            'course'            => $course,
            'videos'            => $videos,
            'completedVideoIds' => $completedVideoIds,
        ]);
    }

    /**
     * Muestra un video específico del curso.
     * Aplica bloqueo si no terminó el anterior.
     * Carga evaluaciones del video (y opcional del curso) con información de si ya las envió y su última entrega.
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

    // Video actual
    $video = Video::where('id', $videoId)
        ->where('course_id', $courseId)
        ->firstOrFail();

    $video->load('captions');

    $captions = $video->captions->map(function ($cap) {
        return [
            'id'      => $cap->id,
            'lang'    => $cap->lang,
            'file'    => $cap->file,
            'label'   => match ($cap->lang) {
                'es' => 'Español',
                'en' => 'English',
                'fr' => 'Français',
                'pt' => 'Português',
                default => strtoupper($cap->lang),
            },
            'default' => $cap->lang === 'es',
        ];
    });

    // Video anterior y siguiente (por "order")
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

    // ¿Terminó el video actual? (para habilitar el siguiente)
    $nextVideoAccessible = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('video_id', $video->id)
        ->where('event', 'ended')
        ->exists();

    // Lista completa de videos con flags de acceso
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
        $v->is_ended = in_array($v->id, $completedVideoIds);
        $v->is_accessible = $unlocked;

        if (! $v->is_ended) {
            $unlocked = false;
        }
        return $v;
    });

    $userId = $user->id;

    // Evaluaciones asignadas al VIDEO actual
    $videoEvaluations = Evaluation::query()
        ->where('course_id', $courseId)
        ->where('type', EvaluationsTypes::VIDEO)
        ->where('video_id', $videoId)
        ->withCount([
            'submissions as submitted_by_me_count' => fn ($q) => $q->where('user_id', $userId),
        ])
        ->with([
            'submissions' => fn ($q) => $q->where('user_id', $userId)->latest()->limit(1),
            'questions' => fn ($q) => $q->orderBy('order'),
            'teacher:id,name',
        ])
        ->orderBy('id')
        ->get()
        ->map(function ($eva) {
            $eva->submitted_by_me   = $eva->submitted_by_me_count > 0;
            $eva->my_last_submission = $eva->submissions->first();
            unset($eva->submissions);
            return $eva;
        });

    // Evaluaciones a nivel CURSO
    $courseEvaluations = Evaluation::query()
        ->where('course_id', $courseId)
        ->where('type', EvaluationsTypes::COURSE)
        ->withCount([
            'submissions as submitted_by_me_count' => fn ($q) => $q->where('user_id', $userId),
        ])
        ->with([
            'submissions' => fn ($q) => $q->where('user_id', $userId)->latest()->limit(1),
            'questions'   => fn ($q) => $q->orderBy('order'),
            'teacher:id,name',
        ])
        ->orderBy('id')
        ->get()
        ->map(function ($eva) {
            $eva->submitted_by_me   = $eva->submitted_by_me_count > 0;
            $eva->my_last_submission = $eva->submissions->first();
            unset($eva->submissions);
            return $eva;
        });

    $videoResources = $video->resources()
        ->select('id','title','description','type','uploaded','file_src','video_src','img_src')
        ->get();

    return Inertia::render('Frontend/Videos/Show', [
        'course'               => $course,
        'video'                => [
            ...$video->toArray(),
            'captions' => $captions,
        ],
        'previousVideo'        => $previousVideo,
        'nextVideo'            => $nextVideo,
        'nextVideoAccessible'  => $nextVideoAccessible,
        'videos'               => $videos,
        'videoEvaluations'     => $videoEvaluations,
        'courseEvaluations'    => $courseEvaluations,
        'videoResources'       => $videoResources,
    ]);
}


    /**
     * Sirve un archivo de video protegido si el usuario está inscrito en el curso.
     * Ajusta la ruta de almacenamiento según tu implementación.
     */
    public function streamProtectedVideo($filename)
    {
        $user = auth()->user();

        $video = Video::where('video_url', $filename)->firstOrFail();

        // Verificar que el usuario esté inscrito en el curso del video
        if (! $user->courses->contains($video->course_id)) {
            abort(403);
        }

        $path = storage_path("app/videos/{$filename}");

        if (! file_exists($path)) {
            abort(404);
        }

        // Entrega el archivo (puedes adaptar a streaming chunked si lo necesitas)
        return response()->file($path);
    }
}
