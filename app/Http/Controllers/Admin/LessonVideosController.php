<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LessonVideosController extends Controller
{
    // Lista JSON de videos asignados a la lección (opcional para UI)
    public function index(Lesson $lesson)
    {
        $lesson->load([
            'assignedVideos' => fn ($q) => $q
                ->select('videos.id','videos.title','videos.image_cover','videos.duration','videos.size')
                ->orderBy('lesson_videos.order'),
        ]);

        return response()->json($lesson->assignedVideos->map(fn ($v) => [
            'id'          => $v->id,
            'title'       => $v->title,
            'image_cover' => $v->image_cover,
            'duration'    => $v->duration,
            'size'        => $v->size,
            'pivot'       => [
                'order'  => $v->pivot->order,
                'active' => (bool) $v->pivot->active,
            ],
        ])->values());
    }

    // Adjuntar uno o varios videos a la lección
    public function attach(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', Rule::in([$lesson->course_id])],
            'videos'    => ['required', 'array', 'min:1'],
            'videos.*'  => ['integer', 'exists:videos,id'],
        ], [
            'course_id.in' => 'El curso no coincide con el de la lección.',
        ]);

        $courseId = (int) $validated['course_id'];
        $videoIds = array_values(array_unique($validated['videos']));

        DB::transaction(function () use ($lesson, $courseId, $videoIds) {
            $currentMax = LessonVideo::where('lesson_id', $lesson->id)->max('order') ?? 0;
            $order = (int) $currentMax;

            foreach ($videoIds as $vid) {
                $order++;
                LessonVideo::updateOrCreate(
                    [
                        'lesson_id' => $lesson->id,
                        'video_id'  => $vid,
                    ],
                    [
                        'course_id' => $courseId,
                        'order'     => $order,
                        'active'    => true,
                    ]
                );
            }
        });

        return response()->json(['status' => 'ok'], 201);
    }

    // Eliminar un video asignado de la lección
    public function detach(Request $request, Lesson $lesson, int $videoId)
    {
        LessonVideo::where('lesson_id', $lesson->id)
            ->where('video_id', $videoId)
            ->delete();

        return $request->wantsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('success', 'Video eliminado de la lección.');
    }

    // Reordenar videos asignados en la lección
    public function reorder(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'videos'   => ['required', 'array', 'min:1'],
            'videos.*' => ['integer', 'distinct', 'exists:videos,id'],
        ]);

        $orderedIds = $data['videos'];

        // Validar pertenencia
        $currentIds = $lesson->videos()->pluck('videos.id')->all();
        $diff       = array_diff($orderedIds, $currentIds);

        if (!empty($diff)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'One or more videos do not belong to this lesson.',
                'invalid' => array_values($diff),
            ], 422);
        }

        DB::transaction(function () use ($lesson, $orderedIds) {
            foreach ($orderedIds as $index => $videoId) {
                $lesson->videos()->updateExistingPivot($videoId, [
                    'order' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'status'  => 'ok',
            'message' => 'Order updated.',
        ], 200);
    }
}
