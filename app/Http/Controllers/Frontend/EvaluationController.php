<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\EvaluationUser;
use Inertia\Inertia;
use App\Models\CourseActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

class EvaluationController extends Controller
{
    public function index(Course $course)
    {
        $userId = auth()->id();

        // ¿El usuario terminó el curso? (mantengo tu lógica original)
        $hasFinishedCourse = CourseActivity::where('user_id', $userId)
            ->where('course_id', $course->id)
            ->exists();

        // Todas las evaluaciones del curso con relaciones mínimas para la vista
        $evaluations = Evaluation::query()
            ->where('course_id', $course->id)
            ->with([
                'course:id,title',
                'video:id,title',
                'lesson:id,title',
            ])
            ->orderBy('order')    // si no tienes "order", puedes dejar ->latest()
            ->get();

        // Intentos del usuario por evaluación (último intento)
        $userAttempts = EvaluationUser::query()
            ->where('user_id', $userId)
            ->whereIn('evaluation_id', $evaluations->pluck('id'))
            ->get()
            ->groupBy('evaluation_id');

        // Adjuntar flags en cada evaluación
        $evaluations = $evaluations->map(function ($eva) use ($userAttempts) {
            $attempts = $userAttempts->get($eva->id, collect());
            $last = $attempts->sortByDesc('created_at')->first();

            $eva->setAttribute('last_evaluation_user', $last);
            $eva->setAttribute('user_has_evaluated', $attempts->isNotEmpty());

            return $eva;
        });

        // Grupos por type
        $courseGroup = $evaluations->where('type', 1)->values();
        $videoGroup  = $evaluations->where('type', 2)->values();

        // Lección: agrupado por título de lección
        $lessonGroup = $evaluations->where('type', 3)
            ->groupBy(function ($eva) {
                return optional($eva->lesson)->title ?: 'Sin lección';
            })
            ->map(function ($items, $lessonTitle) {
                return [
                    'lesson_title' => $lessonTitle,
                    'evaluations'  => $items->values(),
                ];
            })
            ->values();

        // Cargar videos del curso si los usas en la vista
        $course->load('videos');

        return Inertia::render('Frontend/Evaluations/Index', [
            'course' => $course->only(['id', 'title']),
            'groups' => [
                'course' => $courseGroup, // type=1
                'video'  => $videoGroup,  // type=2
                'lesson' => $lessonGroup, // type=3 agrupadas por lección
            ],
            'canSubmitEvaluation' => $hasFinishedCourse,
            'videos' => $course->videos,
        ]);
    }

    public function download(Course $course, Evaluation $evaluation)
    {
        $this->authorizeEvaluation($evaluation, $course);

        if (!Storage::disk('public')->exists($evaluation->eva_video_file)) {
            abort(404);
        }

        return Storage::disk('public')->download($evaluation->eva_video_file);
    }

      public function retry(Course $course, Evaluation $evaluation): RedirectResponse
    {
        $userId = auth()->id();

        // Validar que la evaluación pertenece al curso
        if ((int) $evaluation->course_id !== (int) $course->id) {
            abort(403, 'Evaluación no válida para este curso.');
        }

        // (Opcional) Aquí podrías validar suscripción al curso si aplica.

        DB::transaction(function () use ($userId, $evaluation) {
            EvaluationUser::where('user_id', $userId)
                ->where('evaluation_id', $evaluation->id)
                ->delete();
        });

        // Redirección normal: Inertia seguirá el redirect y hará GET al preview
        return redirect()->route(
            'dashboard.courses.evaluations.evaluation.preview',
            ['course' => $course->id, 'evaluation' => $evaluation->id]
        )->with('success', 'Puedes volver a realizar la evaluación.');
    }
}
