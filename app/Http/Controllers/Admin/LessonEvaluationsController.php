<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LessonEvaluationsController extends Controller
{
    /**
     * Lista JSON de evaluaciones asignadas a la lección (opcional para UI)
     */
    public function index(Lesson $lesson)
    {
        $lesson->load([
            'assignedEvaluations' => fn ($q) => $q
                ->select('evaluations.id', 'evaluations.title', 'evaluations.status')
                ->orderBy('lesson_evaluations.order'),
        ]);

        return response()->json(
            $lesson->assignedEvaluations->map(fn ($e) => [
                'id'    => $e->id,
                'title' => $e->title,
                'status'=> $e->status ?? null,
                'pivot' => [
                    'order'  => $e->pivot->order,
                    'active' => (bool) $e->pivot->active,
                ],
            ])->values()
        );
    }

    /**
     * Adjuntar una o varias evaluaciones a la lección
     */
    public function attach(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'course_id'    => ['required', 'integer', Rule::in([$lesson->course_id])],
            'evaluations'  => ['required', 'array', 'min:1'],
            'evaluations.*'=> ['integer', 'exists:evaluations,id'],
        ], [
            'course_id.in' => 'El curso no coincide con el de la lección.',
        ]);

        $courseId   = (int) $validated['course_id'];
        $evalIds    = array_values(array_unique($validated['evaluations']));

        DB::transaction(function () use ($lesson, $courseId, $evalIds) {
            $currentMax = LessonEvaluation::where('lesson_id', $lesson->id)->max('order') ?? 0;
            $order = (int) $currentMax;

            foreach ($evalIds as $eid) {
                $order++;
                LessonEvaluation::updateOrCreate(
                    [
                        'lesson_id'     => $lesson->id,
                        'evaluation_id' => $eid,
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

    /**
     * Eliminar una evaluación asignada de la lección
     */
    public function detach(Request $request, Lesson $lesson, int $evaluationId)
    {
        LessonEvaluation::where('lesson_id', $lesson->id)
            ->where('evaluation_id', $evaluationId)
            ->delete();

        return $request->wantsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('success', 'Evaluación eliminada de la lección.');
    }

    /**
     * Reordenar evaluaciones asignadas en la lección
     */
    public function reorder(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'evaluations'   => ['required', 'array', 'min:1'],
            'evaluations.*' => ['integer', 'distinct', 'exists:evaluations,id'],
        ]);

        $orderedIds = $data['evaluations'];

        // Validar pertenencia: todas deben pertenecer a esta lección
        $currentIds = $lesson->evaluations()->pluck('evaluations.id')->all();
        $diff = array_diff($orderedIds, $currentIds);

        if (!empty($diff)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Una o más evaluaciones no pertenecen a esta lección.',
                'invalid' => array_values($diff),
            ], 422);
        }

        DB::transaction(function () use ($lesson, $orderedIds) {
            foreach ($orderedIds as $index => $evaluationId) {
                $lesson->evaluations()->updateExistingPivot($evaluationId, [
                    'order' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'status'  => 'ok',
            'message' => 'Orden actualizado.',
        ], 200);
    }
}
