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
use App\Models\VideoMaterial;
use App\Models\LessonVideo;
  
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
    $userId = (int) $user->id;

    // IDs de videos completados (evento "ended") como enteros
    $completedVideoIds = DB::table('video_activities')
        ->where('user_id', $userId)
        ->where('course_id', $courseId)
        ->where('event', 'ended')
        ->pluck('video_id')
        ->map(fn ($id) => (int) $id)
        ->all();

    // Set para búsqueda O(1)
    $completedSet = array_flip($completedVideoIds);

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
    $lessonsPayload = [];

    foreach ($lessons as $lesson) {
        // Progreso de la lección
        $videoIds = $lesson->assignedVideos->pluck('id')->map(fn($id) => (int) $id);
        $total    = $videoIds->count();
        $completedCount = $videoIds->filter(fn ($id) => isset($completedSet[$id]))->count();
        $isCompleted = $total > 0 ? ($completedCount === $total) : false;

        $isUnlocked = $previousLessonCompleted;
        $previousLessonCompleted = $isCompleted;

        // Desbloqueo secuencial de videos dentro de la lección
        $innerUnlocked = $isUnlocked;
        $videos = [];

        foreach ($lesson->assignedVideos as $v) {
            $vid = (int) $v->id;
            $isEnded = isset($completedSet[$vid]);
            $isAccessible = $innerUnlocked;

            if (! $isEnded) {
                $innerUnlocked = false; // El siguiente queda bloqueado
            }

            $videos[] = [
                'id'            => $vid,
                'title'         => $v->title,
                'description'   => $v->description_short,
                'duration'      => $v->duration,
                'size'          => $v->size,
                'thumbnail'     => $v->image_cover,
                'preview_url'   => $v->video_path ? \Illuminate\Support\Facades\Storage::url($v->video_path) : null,
                'lesson_id'     => $lesson->id,
                'is_ended'      => $isEnded,
                'is_accessible' => $isAccessible,
            ];
        }

  

        $lessonsPayload[] = [
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

            'is_unlocked'    => $isUnlocked,
            'videos'         => collect($videos)->values(),
        ];
    }

    return Inertia::render('Frontend/Lessons/Index', [
        'course'            => $course->only(['id', 'title', 'image_cover']),
        'lessons'           => collect($lessonsPayload)->values(),
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

    $isSubscribed = $user->subscriptions()
        ->where('course_id', $courseId)
        ->exists();

    if (! $isSubscribed) {
        abort(403, 'No estás suscrito a este curso.');
    }

    $course = Course::findOrFail($courseId);

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
        $prevLessonVideoIds = $previousLesson->assignedVideos()->pluck('videos.id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (! empty($prevLessonVideoIds)) {
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
    }

    // Videos completados del usuario en el curso (enteros + set)
    $completedVideoIds = DB::table('video_activities')
        ->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('event', 'ended')
        ->pluck('video_id')
        ->map(fn ($id) => (int) $id)
        ->all();

    $completedSet = array_flip($completedVideoIds);

    // Desbloqueo secuencial dentro de la lección
    $innerUnlocked = true;
    $videosArr = [];

    foreach ($lesson->assignedVideos as $v) {
        $vid = (int) $v->id;
        $isEnded = isset($completedSet[$vid]);
        $isAccessible = $innerUnlocked;

        if (! $isEnded) {
            $innerUnlocked = false;
        }

        $videosArr[] = [
            'id'            => $vid,
            'title'         => $v->title,
            'duration'      => $v->duration,
            'size'          => $v->size,
            'thumbnail'     => $v->image_cover,
            'preview_url'   => $v->video_path ? \Illuminate\Support\Facades\Storage::url($v->video_path) : null,
            'description'   => $v->description_short,
            'lesson_id'     => $lesson->id,
            'is_ended'      => $isEnded,
            'is_accessible' => $isAccessible,
        ];
    }

    // Progreso de la lección
    $completedCount = collect($videosArr)->where('is_ended', true)->count();
    $total          = count($videosArr);
    $isCompleted    = $total > 0 ? ($completedCount === $total) : false;

    // Evaluaciones payload
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
        'videos'            => collect($videosArr)->values(),
        'evaluations'       => $evaluationsPayload,
        'isCompleted' =>$isCompleted,
        'completedVideoIds' => $completedVideoIds,
    ]);
}


public function showVideo($courseId, $lessonId, $videoId)
{
    $user = auth()->user();

    // 1) Verificar suscripción al curso
    $isSubscribed = $user->subscriptions()
        ->where('course_id', (int) $courseId)
        ->exists();

    if (! $isSubscribed) {
        abort(403, 'No estás suscrito a este curso.');
    }

    // 2) Cargar curso y lección
    $course = Course::findOrFail((int) $courseId);

    $lesson = Lesson::query()
        ->where('id', (int) $lessonId)
        ->where('course_id', (int) $courseId)
        ->firstOrFail();

    // 3) Videos asignados a la lección (ordenados por pivot)
    $assignedVideos = $lesson->assignedVideos()
        ->select('videos.*')
        ->with('captions')
        ->withPivot(['order','active','course_id'])
        ->orderBy('lesson_videos.order')
        ->get();

    if ($assignedVideos->isEmpty()) {
        abort(404, 'Esta lección no tiene videos asignados.');
    }

    // 4) Validar que el video actual pertenezca a la lección
    $videoId = (int) $videoId;
    /** @var \App\Models\Video|null $video */
    $video = $assignedVideos->firstWhere('id', $videoId);
    if (! $video) {
        abort(404, 'El video no pertenece a esta lección.');
    }

    // 5) Mapear captions con etiqueta y default (según locale del usuario; fallback 'es')
    $preferredLang = $user->locale ?? 'es';
    $captions = $video->captions->map(function ($cap) use ($preferredLang) {
        $label = match ($cap->lang) {
            'es' => 'Español',
            'en' => 'English',
            'fr' => 'Français',
            'pt' => 'Português',
            default => strtoupper($cap->lang),
        };
        return [
            'id'      => $cap->id,
            'lang'    => $cap->lang,
            'file'    => $cap->file,
            'label'   => $label,
            'default' => $cap->lang === $preferredLang || ($preferredLang !== 'es' && $cap->lang === 'es'),
        ];
    });

    // 6) Calcular anterior/siguiente dentro de la LECCIÓN
    $idx = $assignedVideos->search(fn ($v) => (int) $v->id === $videoId);
    $previousVideo = $idx > 0 ? $assignedVideos[$idx - 1] : null;
    $nextVideo     = ($idx < $assignedVideos->count() - 1) ? $assignedVideos[$idx + 1] : null;

    // 7) Bloqueo: exigir haber terminado el video anterior (dentro de la lección)
    if ($previousVideo) {
        $hasEndedPrevious = DB::table('video_activities')
            ->where('user_id', (int) $user->id)
            ->where('course_id', (int) $courseId)
            ->where('video_id', (int) $previousVideo->id)
            ->where('event', 'ended')
            ->exists();

        if (! $hasEndedPrevious) {
            abort(403, 'Debes completar el video anterior de esta lección antes de continuar.');
        }
    }

    // 8) ¿Terminó el video actual? (para habilitar el siguiente)
    $nextVideoAccessible = DB::table('video_activities')
        ->where('user_id', (int) $user->id)
        ->where('course_id', (int) $courseId)
        ->where('video_id', $videoId)
        ->where('event', 'ended')
        ->exists();

    // 9) Set de videos completados para flags de acceso secuencial
    $completedVideoIds = DB::table('video_activities')
        ->where('user_id', (int) $user->id)
        ->where('course_id', (int) $courseId)
        ->where('event', 'ended')
        ->pluck('video_id')
        ->map(fn ($id) => (int) $id)
        ->all();
    $completedSet = array_flip($completedVideoIds);

    $unlocked = true;
    $videos = [];
    foreach ($assignedVideos as $v) {
        $vid = (int) $v->id;
        $isEnded = isset($completedSet[$vid]);
        $isAccessible = $unlocked;

        if (! $isEnded) {
            $unlocked = false;
        }

        $videos[] = [
            'id'            => $vid,
            'title'         => $v->title,
            'image_cover'   => $v->image_cover,
            'duration'      => $v->duration,
            'size'          => $v->size,
            'is_ended'      => $isEnded,
            'is_accessible' => $isAccessible,
        ];
    }

    // 10) Evaluaciones (VIDEO y CURSO)
    $userId = (int) $user->id;

    $videoEvaluations = Evaluation::query()
        ->where('course_id', (int) $courseId)
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
        ->where('course_id', (int) $courseId)
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

    // 11-BIS) MATERIALES del video
    $videoMaterials = VideoMaterial::query()
        ->where('video_id', $videoId)
        ->orderBy('name')
        ->get(['id','name','quantity','unit','notes','image','price','buy_link'])
        ->map(function ($m) {
            $qty   = is_numeric($m->quantity) ? (float) $m->quantity : 0.0;
            $price = is_numeric($m->price)    ? (float) $m->price    : 0.0;
            $total = round($qty * $price, 2);

            return [
                'id'         => $m->id,
                'name'       => $m->name,
                'quantity'   => $qty,
                'unit'       => $m->unit,
                'notes'      => $m->notes,
                'image'      => $m->image,
                'image_url'  => $m->image ? Storage::url($m->image) : null,
                'price'      => $price,
                'buy_link'   => $m->buy_link,
                'total_cost' => $total,
            ];
        });

    $materialsSummary = [
        'count'          => $videoMaterials->count(),
        'quantity_sum'   => (float) $videoMaterials->sum('quantity'),
        'total_cost_sum' => (float) $videoMaterials->sum('total_cost'),
    ];

    // 12) Navegación global
    $allVideos          = $this->videosByCourseOrderedByLesson((int) $courseId);
    $lastVideoIdLesson  = $this->lastVideoIdByCourseOrderedByLesson((int) $courseId);
    $firstVideoIdLesson = $this->firstVideoIdByCourseOrderedByLesson((int) $courseId);

    // === NUEVO: bandera para alert al entrar ===

    // ¿Terminó el video actual?
    $hasEndedCurrent = (bool) $nextVideoAccessible;

    // ¿Envió TODAS las evaluaciones del video actual?
    $hasSubmittedAllVideoEvals = $videoEvaluations->isEmpty()
        ? true
        : $videoEvaluations->every(function ($eva) {
            return ($eva->submitted_by_me ?? false)
                || optional($eva->my_last_submission)->id;
        });

    // ¿Hay siguiente video?
    $hasNextVideo = (bool) $nextVideo;

    // Mostrar alert en la vista apenas carga (si aplica)
    $shouldShowContinueAlert = $hasEndedCurrent && $hasSubmittedAllVideoEvals && $hasNextVideo;



    // === NUEVO: ¿Curso completado? (todos los videos vistos + todas las evals de video enviadas) ===
    $allVideoIds = collect($allVideos)->pluck('id')->map(fn($v) => (int)$v)->values();
    $isCourseCompleted = $this->isCourseCompletedForUser(
        (int) $courseId,
        (int) $user->id,
        $allVideoIds
    );

    // 13) Respuesta
    return Inertia::render('Frontend/Lessons/Videos/ShowVideo', [
        'course'              => $course->only(['id','title','image_cover']),
        'lesson'              => $lesson->only(['id','title','order']),
        'video'               => [
            ...$video->toArray(),
            'captions' => $captions,
        ],
        'previousVideo'       => $previousVideo?->only(['id','title','image_cover']),
        'nextVideo'           => $nextVideo?->only(['id','title','image_cover']),
        'nextVideoAccessible' => $nextVideoAccessible,
        'videos'              => collect($videos)->values(),
        'videoEvaluations'    => $videoEvaluations,
        'courseEvaluations'   => $courseEvaluations,
        'videoResources'      => $videoResources,
        'allVideos'           => $allVideos,
        'firstVideoIdLesson'  => $firstVideoIdLesson,
        'lastVideoIdLesson'   => $lastVideoIdLesson,
        'videoMaterials'      => $videoMaterials,
        'materialsSummary'    => $materialsSummary,

        // NUEVO
        'shouldShowContinueAlert' => $shouldShowContinueAlert,
        'isCourseCompleted'       => $isCourseCompleted,
    ]);
}


  protected function videosByCourseOrderedByLesson(int $courseId)
    {
        return LessonVideo::query() // si tu modelo es singular, cámbialo por LessonVideo::query()
            ->where('lesson_videos.course_id', $courseId)
            ->join('lessons', function ($q) use ($courseId) {
                $q->on('lessons.id', '=', 'lesson_videos.lesson_id')
                  ->where('lessons.course_id', '=', $courseId);
            })
            ->join('videos', 'videos.id', '=', 'lesson_videos.video_id')
            ->orderBy('lessons.order')   // número/orden de la lección
            ->orderBy('videos.id')       // desempate dentro de la misma lección (opcional)
            ->get([
                'videos.id',
                'videos.title',
                'videos.image_cover',
                'videos.duration',
                'videos.size',
                'lesson_videos.lesson_id',
                'lessons.order as lesson_number',
                'lessons.title as lesson_title',
            ]);
    }

    protected function lastVideoIdByCourseOrderedByLesson(int $courseId): ?int
    {
        return \App\Models\LessonVideo::query() // usa LessonVideo si tu modelo es singular
            ->where('lesson_videos.course_id', $courseId)
            ->join('lessons', function ($q) use ($courseId) {
                $q->on('lessons.id', '=', 'lesson_videos.lesson_id')
                  ->where('lessons.course_id', '=', $courseId);
            })
            ->join('videos', 'videos.id', '=', 'lesson_videos.video_id')
            ->orderByDesc('lessons.order')   // última lección
            ->orderByDesc('videos.id')       // último dentro de esa lección (desempate)
            ->value('videos.id');            // devuelve solo el id o null
    }

    protected function firstVideoIdByCourseOrderedByLesson(int $courseId): ?int
{
    return LessonVideo::query()
        ->where('lesson_videos.course_id', $courseId)
        ->join('lessons', function ($q) use ($courseId) {
            $q->on('lessons.id', '=', 'lesson_videos.lesson_id')
              ->where('lessons.course_id', '=', $courseId);
        })
        ->join('videos', 'videos.id', '=', 'lesson_videos.video_id')
        ->orderBy('lessons.order')   // primera lección del curso
        ->orderBy('videos.id')       // primer video dentro de esa lección
        ->value('videos.id');        // retorna solo el ID o null si no hay
}

/**
 * Determina si el usuario ya vio TODOS los videos del curso y envió
 * TODAS las evaluaciones de tipo VIDEO del curso.
 *
 * @param int $courseId
 * @param int $userId
 * @param \Illuminate\Support\Collection|\Illuminate\Support\Enumerable|array $allVideoIds  // IDs de todos los videos del curso
 * @return bool
 */
private function isCourseCompletedForUser(int $courseId, int $userId, $allVideoIds): bool
{
    $allVideoIds = collect($allVideoIds)->map(fn($v) => (int)$v)->unique()->values();

    // 1) ¿Vio TODOS los videos del curso (evento 'ended' por video)?
    if ($allVideoIds->isEmpty()) {
        // Si el curso no tiene videos, lo consideramos completado por definición
        $hasEndedAllVideos = true;
    } else {
        $endedCount = DB::table('video_activities')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('event', 'ended')
            ->whereIn('video_id', $allVideoIds)
            ->distinct()
            ->count('video_id');

        $hasEndedAllVideos = ($endedCount === $allVideoIds->count());
    }

    // 2) ¿Envió TODAS las evaluaciones de tipo VIDEO del curso?
    //    Obtenemos IDs de evaluaciones de video del curso (ligadas a cualquier video del curso)
    $videoEvaIds = Evaluation::query()
        ->where('course_id', $courseId)
        ->where('type', EvaluationsTypes::VIDEO)
        ->when($allVideoIds->isNotEmpty(), fn ($q) => $q->whereIn('video_id', $allVideoIds))
        ->pluck('id');

    if ($videoEvaIds->isEmpty()) {
        // Si no hay evaluaciones de video, lo consideramos cumplido
        $hasSubmittedAllCourseVideoEvals = true;
    } else {
        // Cuenta cuántas evaluaciones de ese set tienen al menos un envío del usuario
        // Ajusta el nombre de la tabla si tu pivot/tabla es distinta
        $submittedDistinct = DB::table('evaluations_users')
            ->whereIn('evaluation_id', $videoEvaIds)
            ->where('user_id', $userId)
            ->distinct()
            ->count('evaluation_id');

        $hasSubmittedAllCourseVideoEvals = ($submittedDistinct === $videoEvaIds->count());
    }

    return $hasEndedAllVideos && $hasSubmittedAllCourseVideoEvals;
}




}
