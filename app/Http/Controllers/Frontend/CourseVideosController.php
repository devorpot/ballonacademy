<?php 
// app/Http/Controllers/Frontend/CourseVideosController.php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CourseVideosController extends Controller
{
    public function flatIndex($courseId)
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

        // IDs de videos completados (evento "ended")
        $completedVideoIds = DB::table('video_activities')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('event', 'ended')
            ->pluck('video_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $completedSet = array_flip($completedVideoIds);

        // Traer TODAS las lecciones con sus videos y órdenes
        $lessons = Lesson::query()
            ->select('id', 'title', 'order', 'course_id')
            ->where('course_id', $courseId)
            ->active()
            ->published()
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
                    )->withPivot('order')
                     ->orderBy('lesson_videos.order');
                },
            ])
            ->orderBy('order')
            ->get();

        // Aplanar: (lesson.order, lesson_videos.order)
        $flat = [];
        foreach ($lessons as $lesson) {
            foreach ($lesson->assignedVideos as $v) {
                $flat[] = [
                    'id'           => (int) $v->id,
                    'title'        => $v->title,
                    'description'  => $v->description_short,
                    'duration'     => $v->duration,
                    'size'         => $v->size,
                    'thumbnail'    => $v->image_cover,
                    'preview_url'  => $v->video_path ? \Illuminate\Support\Facades\Storage::url($v->video_path) : null,
                    'lesson_id'    => (int) $lesson->id,
                    'lesson_title' => $lesson->title,
                    'lesson_order' => (int) ($lesson->order ?? 0),
                    'video_order'  => (int) ($v->pivot->order ?? 0),
                    'is_ended'     => isset($completedSet[(int) $v->id]),
                ];
            }
        }

        // Orden explícito de seguridad (por si acaso)
        usort($flat, function ($a, $b) {
            return [$a['lesson_order'], $a['video_order'], $a['id']]
                 <=> [$b['lesson_order'], $b['video_order'], $b['id']];
        });

        // Desbloqueo secuencial GLOBAL
        $globalUnlocked = true;
        $completedCount = 0;
        $totalCount     = count($flat);

        foreach ($flat as &$row) {
            $row['is_accessible'] = $globalUnlocked;
            if ($row['is_ended']) {
                $completedCount++;
            } else {
                // el primer no completado bloquea los siguientes
                $globalUnlocked = false;
            }
        }
        unset($row);

        return Inertia::render('Frontend/Courses/CourseVideos', [
            'course' => $course->only(['id', 'title', 'image_cover']),
            'videos' => $flat,
            'summary' => [
                'completed' => $completedCount,
                'total'     => $totalCount,
            ],
            'completedVideoIds' => $completedVideoIds,
        ]);
    }
}
