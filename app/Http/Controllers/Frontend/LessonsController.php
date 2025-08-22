<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Video;

 
use App\Models\Teacher;


use App\Models\Evaluation;
use App\Models\EvaluationUser;

use App\Enums\EvaluationsTypes; // COURSE | VIDEO

class LessonsController extends Controller
{
    /**
     * GET /frontend/courses/{course}/lessons
     * Lista las lecciones del curso con:
     * - Bloqueo progresivo entre lecciones (is_unlocked)
     * - Bloqueo secuencial de videos dentro de cada lección (is_accessible)
     */
    public function index($courseId)
    {
        $user = Auth::user();

        // Verificar suscripción
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

        // Lecciones + videos (ordenados por pivot)
        $lessons = Lesson::query()
            ->with([
                'assignedVideos' => function ($q) {
                    $q->select(
                        'videos.id',
                        'videos.title',
                        'videos.description_short',
                        'videos.duration',
                        'videos.size',
                        'videos.image_cover',
                        'videos.video_path'
                    )->orderBy('lesson_videos.order');
                },
                'assignedEvaluations:id,title,status',
            ])
            ->withCount([
                'assignedVideos as videos_count',
                'assignedEvaluations as evaluations_count',
            ])
            ->where('course_id', $courseId)
            ->active()
            ->published()
            ->orderBy('order')
            ->get();

        // Determinar desbloqueo por lección: la primera desbloqueada, las siguientes solo si la anterior está completa
        $previousLessonCompleted = true;

        $lessonsPayload = $lessons->map(function (Lesson $lesson) use (&$previousLessonCompleted, $completedVideoIds) {
            // Progreso de la lección (basado en videos completados)
            $videoIds       = $lesson->assignedVideos->pluck('id');
            $completedCount = $videoIds->filter(fn ($id) => in_array($id, $completedVideoIds, true))->count();
            $total          = $videoIds->count();
            $isCompleted    = $total > 0 ? ($completedCount === $total) : false;

            // La lección está desbloqueada solo si la anterior estuvo completa
            $isUnlocked = $previousLessonCompleted;

            // Para la siguiente iteración, esta lección debe estar completa
            $previousLessonCompleted = $isCompleted;

            // Desbloqueo secuencial de videos dentro de la lección (solo si la lección está desbloqueada)
            $innerUnlocked = $isUnlocked;
            $videos = $lesson->assignedVideos->map(function (Video $v) use (&$innerUnlocked, $completedVideoIds, $lesson, $isUnlocked) {
                $isEnded      = in_array($v->id, $completedVideoIds, true);
                $isAccessible = $isUnlocked && $innerUnlocked;

                if (! $isEnded) {
                    $innerUnlocked = false; // el siguiente queda bloqueado
                }

                return [
                    'id'          => $v->id,
                    'title'       => $v->title,
                    'description' => $v->description_short,
                    'duration'    => $v->duration,
                    'size'        => $v->size,
                    'thumbnail'   => $v->image_cover,
                    'preview_url' => $v->video_path ? Storage::url($v->video_path) : null,
                    'lesson_id'   => $lesson->id,
                    'is_ended'      => $isEnded,
                    'is_accessible' => $isAccessible,
                ];
            })->values();

            return [
                'id'                 => $lesson->id,
                'title'              => $lesson->title,
                'description_short'  => $lesson->description_short,
                'order'              => $lesson->order,
                'publish_on'         => optional($lesson->publish_on)->toDateString(),
                'active'             => (bool) $lesson->active,

                'videos_count'       => $lesson->videos_count ?? $lesson->assignedVideos->count(),
                'evaluations_count'  => $lesson->evaluations_count ?? $lesson->assignedEvaluations->count(),
                'thumbnail'          => $lesson->image_cover ?: null,

                'progress' => [
                    'completed_videos' => $completedCount,
                    'total_videos'     => $total,
                    'is_completed'     => $isCompleted,
                ],

                'add_video'      => (bool) $lesson->add_video,
                'add_evaluation' => (bool) $lesson->add_evaluation,
                'add_materials'  => (bool) $lesson->add_materials,

                'is_unlocked'    => $isUnlocked,   // NUEVO: para bloquear la lección en la UI
                'videos'         => $videos,       // con is_ended e is_accessible
            ];
        })->values();

        return Inertia::render('Frontend/Lessons/Index', [
            'course'            => $course->only(['id', 'title', 'image_cover']),
            'lessons'           => $lessonsPayload,
            'completedVideoIds' => $completedVideoIds,
        ]);
    }

    /**
     * GET /frontend/courses/{course}/lessons/{lesson}
     * Aplica:
     * - Bloqueo si la lección anterior no está completa
     * - Desbloqueo secuencial de videos dentro de la lección
     */
    public function show($courseId, $lessonId)
{
    $user = Auth::user();

    // Verificar suscripción del usuario al curso
    $isSubscribed = $user->subscriptions()
        ->where('course_id', $courseId)
        ->exists();

    if (! $isSubscribed) {
        abort(403, 'No estás suscrito a este curso.');
    }

    $course = Course::findOrFail($courseId);

    // Cargar lección con videos y evaluaciones con los campos solicitados
    $lesson = Lesson::query()
        ->with([
            'assignedVideos' => function ($q) {
                $q->select(
                    'videos.id',
                    'videos.title',
                    'videos.duration',
                    'videos.size',
                    'videos.image_cover',
                    'videos.video_path',
                    'videos.description_short'
                )->orderBy('lesson_videos.order');
            },
            'assignedEvaluations' => function ($q) use ($courseId, $lessonId) {
                $q->select([
                    'evaluations.id',
                    'evaluations.course_id',
                    'evaluations.teacher_id',
                    'evaluations.video_id',
                    'evaluations.lesson_id',
                    'evaluations.eva_send_date',
                    'evaluations.eva_video_file',
                    'evaluations.eva_comments',
                    'evaluations.eva_type',
                    'evaluations.status',
                    'evaluations.date_evaluation',
                    'evaluations.title',
                    'evaluations.type',
                    'evaluations.points_min',
                ])
                ->with([
                    'teacher:id,firstname,lastname',
                    'video:id,title',
                ])
                ->where('evaluations.course_id', $courseId)
                ->where('evaluations.lesson_id', $lessonId);
            },
        ])
        ->where('course_id', $courseId)
        ->active()
        ->published()
        ->whereKey($lessonId)
        ->firstOrFail();

    // Validar que la lección anterior esté completa
    $previousLesson = Lesson::where('course_id', $courseId)
        ->active()
        ->published()
        ->where('order', '<', $lesson->order)
        ->orderBy('order', 'desc')
        ->first();

    if ($previousLesson) {
        $prevLessonVideoIds = $previousLesson->assignedVideos()->pluck('videos.id')->toArray();

        $prevCompletedCount = DB::table('video_activities')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->whereIn('video_id', $prevLessonVideoIds)
            ->where('event', 'ended')
            ->distinct()
            ->count('video_id');

        $prevTotal = count($prevLessonVideoIds);

        if ($prevTotal > 0 && $prevCompletedCount < $prevTotal) {
            abort(403, 'Debes completar la lección anterior antes de continuar.');
        }
    }

    // Videos completados del usuario en el curso
    $completedVideoIds = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('event', 'ended')
        ->pluck('video_id')
        ->toArray();

    // Desbloqueo secuencial de videos dentro de la lección
    $innerUnlocked = true;
    $videos = $lesson->assignedVideos->map(function (Video $v) use (&$innerUnlocked, $completedVideoIds, $lesson) {
        $isEnded      = in_array($v->id, $completedVideoIds, true);
        $isAccessible = $innerUnlocked;

        if (! $isEnded) {
            $innerUnlocked = false;
        }

        return [
            'id'            => $v->id,
            'title'         => $v->title,
            'duration'      => $v->duration,
            'size'          => $v->size,
            'thumbnail'     => $v->image_cover,
            'preview_url'   => $v->video_path ? Storage::url($v->video_path) : null,
            'description'   => $v->description_short,
            'lesson_id'     => $lesson->id,
            'is_ended'      => $isEnded,
            'is_accessible' => $isAccessible,
        ];
    })->values();

    // Progreso de la lección
    $completedCount = $videos->where('is_ended', true)->count();
    $total          = $videos->count();
    $isCompleted    = $total > 0 ? ($completedCount === $total) : false;

    // Payload de evaluaciones con los campos exactos solicitados
    $evaluationsPayload = $lesson->assignedEvaluations->map(function ($e) {
        return [
            'id'              => $e->id,
            'course_id'       => $e->course_id,
            'teacher_id'      => $e->teacher_id,
            'video_id'        => $e->video_id,
            'lesson_id'       => $e->lesson_id,
            'eva_send_date'   => optional($e->eva_send_date)->toDateTimeString(),
            'eva_video_file'  => $e->eva_video_file,
            'eva_comments'    => $e->eva_comments,
            'eva_type'        => $e->eva_type,
            'status'          => $e->status,
            'date_evaluation' => optional($e->date_evaluation)->toDateString(),
            'title'           => $e->title,
            'type'            => $e->type,
            'points_min'      => $e->points_min,
            'teacher'         => $e->teacher ? [
                'id'        => $e->teacher->id,
                'firstname' => $e->teacher->firstname,
                'lastname'  => $e->teacher->lastname,
            ] : null,
            'video'           => $e->video ? [
                'id'    => $e->video->id,
                'title' => $e->video->title,
            ] : null,
        ];
    })->values();

    return Inertia::render('Frontend/Lessons/Show', [
        'course' => $course->only(['id', 'title', 'image_cover']),
        'lesson' => [
            'id'                => $lesson->id,
            'title'             => $lesson->title,
            'instructions'      => $lesson->instructions,
            'description_short' => $lesson->description_short,
            'order'             => $lesson->order,
            'publish_on'        => optional($lesson->publish_on)->toDateString(),
            'active'            => (bool) $lesson->active,
            'add_video'         => (bool) $lesson->add_video,
            'add_evaluation'    => (bool) $lesson->add_evaluation,
            'add_materials'     => (bool) $lesson->add_materials,
            'progress'          => [
                'completed_videos' => $completedCount,
                'total_videos'     => $total,
                'is_completed'     => $isCompleted,
            ],
        ],
        'videos'            => $videos,
        'evaluations'       => $evaluationsPayload,
        'completedVideoIds' => $completedVideoIds,
    ]);
}

public function showVideo($courseId, $lessonId, $videoId)
{
    $user = auth()->user();

    // 1) Verificar suscripción al curso
    $subscription = $user->subscriptions()
        ->where('course_id', $courseId)
        ->first();

    if (! $subscription) {
        abort(403, 'No estás suscrito a este curso.');
    }

    // 2) Cargar curso y lección
    $course = Course::findOrFail($courseId);

    $lesson = Lesson::query()
        ->where('id', $lessonId)
        ->where('course_id', $courseId)
        ->firstOrFail();

    // 3) Videos asignados a la lección (ordenados por pivot lesson_videos.order)
    //    Nota: usa el nombre de relación que tengas en tu modelo (p.ej. assignedVideos o videos)
$assignedVideos = $lesson->assignedVideos()
    ->select('videos.*') // todas las columnas del video, ej: title, description, video_path, etc.
    ->with('captions')
    ->withPivot(['order','active','course_id'])
    ->orderBy('lesson_videos.order')
    ->get();

    if ($assignedVideos->isEmpty()) {
        abort(404, 'Esta lección no tiene videos asignados.');
    }

    // 4) Validar que el video actual pertenezca a la lección
    /** @var \App\Models\Video $video */
    $video = $assignedVideos->firstWhere('id', (int) $videoId);
    if (! $video) {
        abort(404, 'El video no pertenece a esta lección.');
    }

    // 5) Mapear captions con etiqueta y default
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

    // 6) Calcular anterior/siguiente dentro de la LECCIÓN
    $idx = $assignedVideos->search(fn ($v) => (int) $v->id === (int) $videoId);

    $previousVideo = $idx > 0 ? $assignedVideos[$idx - 1] : null;
    $nextVideo     = ($idx < $assignedVideos->count() - 1) ? $assignedVideos[$idx + 1] : null;

    // 7) Bloqueo: exigir haber terminado el video anterior (dentro de la lección)
    if ($previousVideo) {
        $hasEndedPrevious = DB::table('video_activities')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('video_id', $previousVideo->id)
            ->where('event', 'ended')
            ->exists();

        if (! $hasEndedPrevious) {
            abort(403, 'Debes completar el video anterior de esta lección antes de continuar.');
        }
    }

    // 8) ¿Terminó el video actual? (para habilitar el siguiente dentro de la lección)
    $nextVideoAccessible = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('video_id', $video->id)
        ->where('event', 'ended')
        ->exists();

    // 9) Lista COMPLETA de videos (pero solo los de la lección) con flags de acceso secuencial
    $completedVideoIds = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('event', 'ended')
        ->pluck('video_id')
        ->toArray();

    $unlocked = true;
    $videos = $assignedVideos->map(function ($v) use (&$unlocked, $completedVideoIds) {
        $v->is_ended = in_array($v->id, $completedVideoIds);
        $v->is_accessible = $unlocked;

        if (! $v->is_ended) {
            $unlocked = false;
        }
        return $v;
    });

    // 10) Evaluaciones (mantengo las de VIDEO y CURSO como en tu código; si tienes tipo LECCIÓN, lo puedes agregar)
    $userId = $user->id;

    $videoEvaluations = Evaluation::query()
        ->where('course_id', $courseId)
        ->where('type', EvaluationsTypes::VIDEO)
        ->where('video_id', $videoId)
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
            $eva->submitted_by_me    = $eva->submitted_by_me_count > 0;
            $eva->my_last_submission = $eva->submissions->first();
            unset($eva->submissions);
            return $eva;
        });

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
            $eva->submitted_by_me    = $eva->submitted_by_me_count > 0;
            $eva->my_last_submission = $eva->submissions->first();
            unset($eva->submissions);
            return $eva;
        });

    // 11) Recursos del video
    $videoResources = $video->resources()
        ->select('id','title','description','type','uploaded','file_src','video_src','img_src')
        ->get();

    // 12) Respuesta
    return Inertia::render('Frontend/Lessons/Videos/Show', [
        'course'              => $course->only(['id','title','image_cover']),
        'lesson'              => $lesson->only(['id','title','order']),
        'video'               => [
            ...$video->toArray(),
            'captions' => $captions,
        ],
        'previousVideo'       => $previousVideo?->only(['id','title']),
        'nextVideo'           => $nextVideo?->only(['id','title']),
        'nextVideoAccessible' => $nextVideoAccessible,
        'videos'              => $videos->map(fn ($v) => [
            'id'            => $v->id,
            'title'         => $v->title,
            'image_cover'   => $v->image_cover,
            'duration'      => $v->duration,
            'size'          => $v->size,
            'is_ended'      => (bool) $v->is_ended,
            'is_accessible' => (bool) $v->is_accessible,
        ]),
        'videoEvaluations'    => $videoEvaluations,
        'courseEvaluations'   => $courseEvaluations,
        'videoResources'      => $videoResources,
    ]);
}


}
