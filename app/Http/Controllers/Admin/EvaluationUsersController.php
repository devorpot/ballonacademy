<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EvaluationUser;
use App\Models\User;
use App\Models\Lesson; 
use App\Models\LessonEvaluation; 
use App\Models\Course;
use App\Enums\EvaluationUserStatus;  
use Inertia\Inertia;

class EvaluationUsersController extends Controller
{
    // Listar todas las evaluaciones de usuarios
  public function index(Request $request)
{
    // Requiere SIEMPRE el id del curso en la URL: /admin/evaluation-users/course/{course}/index
    $courseId = $request->route('course');
    abort_if(!$courseId, 404, 'Se requiere el ID del curso en la ruta.');

    // Trae datos mínimos del curso para encabezados/breadcrumbs
    $course = Course::select('id', 'title', 'image_cover')->findOrFail($courseId);

    // Base: solo envíos del curso indicado
    $query = EvaluationUser::with([
        'user:id,name,email',
        'course:id,title,image_cover',
        'evaluation:id,title,points_min,status,eva_type,type'
    ])->where('course_id', $course->id);

    // Filtros opcionales
    if ($request->filled('user')) {
        $query->where('user_id', (int) $request->input('user'));
    }

    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    if ($request->filled('q')) {
        $q = $request->input('q');
        $query->where(function ($sub) use ($q) {
            $sub->where('comments', 'like', "%{$q}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                ->orWhereHas('course', fn($c) => $c->where('title', 'like', "%{$q}%"))
                ->orWhereHas('evaluation', fn($e) => $e->where('title', 'like', "%{$q}%"));
        });
    }

    $perPage = max(1, (int) $request->input('per_page', 20));

    $evaluations = $query
        ->latest()
        ->paginate($perPage)
        ->withQueryString();

    return Inertia::render('Admin/Evaluations/Users/Index', [
        'evaluations' => $evaluations,
        'course'      => $course, // se usa en breadcrumbs y encabezado
        'filters'     => [
            'course'   => $course->id,
            'user'     => $request->input('user'),
            'status'   => $request->input('status'),
            'q'        => $request->input('q'),
            'per_page' => $perPage,
        ],
    ]);
}

    // Listar evaluaciones filtradas por curso
    public function byCourse($courseId)
    {
        $course = Course::findOrFail($courseId);

        $evaluations = EvaluationUser::with(['user', 'course', 'evaluation'])
            ->where('course_id', $courseId)
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Evaluations/Users/Index', [
            'evaluations' => $evaluations,
            'course' => $course
        ]);
    }

    // Mostrar detalles de una evaluación enviada por usuario
    public function show($id)
    {
        $evaluationUser = EvaluationUser::with([
            'user', 'course', 'evaluation.questions', 'approvedUser'
        ])->findOrFail($id);

        $questions = $evaluationUser->evaluation->questions->sortBy('order')->values();

        return Inertia::render('Admin/Evaluations/Users/Show', [
            'evaluationUser' => $evaluationUser,
            'questions' => $questions,
            'statusOptions' => collect(EvaluationUserStatus::cases())->map(fn($case) => [
                'label' => $case->label(),
                'value' => $case->value
            ]),
            'status_label' => $evaluationUser->status_label
        ]);
    }

    // Actualizar estatus de la evaluación
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:111,222,333,999',
        ]);

        $evaluationUser = EvaluationUser::findOrFail($id);
        $evaluationUser->status = EvaluationUserStatus::from($request->status);
        $evaluationUser->approved_user_id = auth()->id();
        $evaluationUser->save();

        return redirect()->back()->with('success', 'Estatus actualizado correctamente');
    }

    // Eliminar evaluación enviada
    public function destroy($id)
    {
        $evaluationUser = EvaluationUser::findOrFail($id);
        $evaluationUser->delete();

        return redirect()->back()->with('success', 'Evaluación eliminada correctamente');
    }
}
 