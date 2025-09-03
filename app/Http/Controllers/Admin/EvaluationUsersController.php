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

// app/Http/Controllers/Admin/EvaluationUsersController.php

public function all(Request $request)
{
    // Fuente: evaluations_users (sin lesson_id porque no existe en tu tabla)
    $query = EvaluationUser::query()
        ->select([
            'id',
            'user_id',
            'course_id',
            'evaluation_id',
            'video_id',
            'status',
            'comments',
            'created_at',
        ])
        ->with([
            'user:id,name,email',
            'course:id,title',
            // Complemento por evaluation (incluye lesson/video a través de evaluation)
            'evaluation:id,title,course_id,lesson_id,video_id',
            'evaluation.lesson:id,title',
            'evaluation.video:id,title',
            // Relación directa existente en tu modelo
            'video:id,title',
        ]);

    // Filtros
    if ($request->filled('user')) {
        $query->where('user_id', (int) $request->input('user'));
    }
    if ($request->filled('course')) {
        $query->where('course_id', (int) $request->input('course'));
    }
    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }
    if ($request->filled('q')) {
        $q = trim((string) $request->input('q'));
        $query->where(function ($sub) use ($q) {
            $sub->where('comments', 'like', "%{$q}%")
                // evaluation y sus anidados
                ->orWhereHas('evaluation', fn($e) => $e->where('title', 'like', "%{$q}%"))
                ->orWhereHas('evaluation.lesson', fn($l) => $l->where('title', 'like', "%{$q}%"))
                ->orWhereHas('evaluation.video', fn($v) => $v->where('title', 'like', "%{$q}%"))
                // curso y usuario
                ->orWhereHas('course', fn($c) => $c->where('title', 'like', "%{$q}%"))
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%")
                                                 ->orWhere('email', 'like', "%{$q}%"))
                // relación directa existente
                ->orWhereHas('video', fn($v) => $v->where('title', 'like', "%{$q}%"));
        });
    }

    // Ordenamiento
    $sort = $request->input('sort', 'created_at');
    $dir  = $request->input('dir', 'desc');
    $allowedSorts = ['created_at', 'status', 'id'];
    if (!in_array($sort, $allowedSorts, true)) {
        $sort = 'created_at';
    }
    $dir = $dir === 'asc' ? 'asc' : 'desc';

    $perPage = max(1, (int) $request->input('per_page', 20));

    $submissions = $query
        ->orderBy($sort, $dir)
        ->paginate($perPage)
        ->withQueryString();



    // Opciones de filtros derivadas de evaluations_users
    $courseOptions = Course::query()
        ->whereIn('id', EvaluationUser::query()->select('course_id')->distinct())
        ->orderBy('title')
        ->get(['id', 'title'])
        ->map(fn($c) => ['value' => $c->id, 'label' => $c->title])
        ->values();

    $userOptions = User::query()
        ->whereIn('id', EvaluationUser::query()->select('user_id')->distinct())
        ->orderBy('name')
        ->get(['id', 'name', 'email'])
        ->map(fn($u) => ['value' => $u->id, 'label' => "{$u->name} — {$u->email}"])
        ->values();

 

    return Inertia::render('Admin/Evaluations/Users/All', [
        'submissions' => $submissions,
        'filters'     => [
            'user'     => $request->input('user'),
            'course'   => $request->input('course'),
            'status'   => $request->input('status'),
            'q'        => $request->input('q'),
            'per_page' => $perPage,
            'sort'     => $sort,
            'dir'      => $dir,
        ],
        'courseOptions' => $courseOptions,
        'userOptions'   => $userOptions,
        'statusOptions' => collect(\App\Enums\EvaluationUserStatus::cases())->map(fn($case) => [
            'label' => $case->label(),
            'value' => $case->value,
        ]),
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

    // app/Http/Controllers/Admin/EvaluationUsersController.php

public function byUserAndCourse(Request $request, int $courseId, int $userId)
{
    // Datos mínimos para encabezados/breadcrumbs
    $course = Course::select('id', 'title', 'image_cover')->findOrFail($courseId);
    $student = User::select('id', 'name', 'email')->findOrFail($userId);

    // Base: envíos del usuario en ese curso
    $query = EvaluationUser::query()
        ->with([
            'user:id,name,email',
            'course:id,title',
            'evaluation:id,title,points_min,status,eva_type,type',
            'lesson:id,title',
            'video:id,title',
            'approvedUser:id,name',
        ])
        ->where('course_id', $courseId)
        ->where('user_id', $userId);

    // Filtros opcionales
    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    if ($request->filled('q')) {
        $q = trim((string) $request->input('q'));
        $query->where(function ($sub) use ($q) {
            $sub->where('comments', 'like', "%{$q}%")
                ->orWhereHas('evaluation', fn($e) => $e->where('title', 'like', "%{$q}%"))
                ->orWhereHas('lesson', fn($l) => $l->where('title', 'like', "%{$q}%"))
                ->orWhereHas('video', fn($v) => $v->where('title', 'like', "%{$q}%"));
        });
    }

    // Ordenamiento opcional
    $sort = $request->input('sort', 'created_at');
    $dir  = $request->input('dir', 'desc');
    $allowedSorts = ['created_at', 'status', 'id'];
    if (!in_array($sort, $allowedSorts, true)) {
        $sort = 'created_at';
    }
    $dir = $dir === 'asc' ? 'asc' : 'desc';

    $perPage = max(1, (int) $request->input('per_page', 20));

    $submissions = $query
        ->orderBy($sort, $dir)
        ->paginate($perPage)
        ->withQueryString();

    return Inertia::render('Admin/Evaluations/Users/ByStudent', [
        'submissions' => $submissions,
        'course'      => $course,
        'student'     => $student,
        'filters'     => [
            'q'        => $request->input('q'),
            'status'   => $request->input('status'),
            'per_page' => $perPage,
            'sort'     => $sort,
            'dir'      => $dir,
        ],
        'statusOptions' => collect(\App\Enums\EvaluationUserStatus::cases())->map(fn($case) => [
            'label' => $case->label(),
            'value' => $case->value,
        ]),
    ]);
}

// app/Http/Controllers/Admin/EvaluationUsersController.php

public function byUser(Request $request, int $userId)
{
    // Alumno para encabezado/breadcrumbs
    $student = User::select('id','name','email')->findOrFail($userId);

    // Base: todos los envíos de ese usuario (en cualquier curso)
    $query = EvaluationUser::query()
        ->with([
            'evaluation:id,title,course_id,lesson_id,video_id',
            'evaluation.lesson:id,title',   // ← lesson a través de evaluation
            'evaluation.video:id,title',    // ← video a través de evaluation
            'course:id,title',
            'user:id,name,email',
        ])
        ->where('user_id', $userId);

    // Filtros opcionales
    if ($request->filled('course')) {
        $query->where('course_id', (int) $request->input('course'));
    }
    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }
    if ($request->filled('q')) {
        $q = trim((string) $request->input('q'));
        $query->where(function ($sub) use ($q) {
            $sub->where('comments', 'like', "%{$q}%")
                ->orWhereHas('evaluation', fn($e) => $e->where('title', 'like', "%{$q}%"))
                ->orWhereHas('evaluation.lesson', fn($l) => $l->where('title', 'like', "%{$q}%")) // ← corregido
                ->orWhereHas('evaluation.video', fn($v) => $v->where('title', 'like', "%{$q}%"))   // ← corregido
                ->orWhereHas('course', fn($c) => $c->where('title', 'like', "%{$q}%"));
        });
    }

    // Ordenamiento
    $sort = $request->input('sort', 'created_at');
    $dir  = $request->input('dir', 'desc');
    $allowedSorts = ['created_at', 'status', 'id'];
    if (!in_array($sort, $allowedSorts, true)) {
        $sort = 'created_at';
    }
    $dir = $dir === 'asc' ? 'asc' : 'desc';

    $perPage = max(1, (int) $request->input('per_page', 20));

    $submissions = $query
        ->orderBy($sort, $dir)
        ->paginate($perPage)
        ->withQueryString();

    // Opciones de cursos para el filtro (solo los que tengan envíos)
    $courseOptions = Course::query()
        ->whereIn('id', EvaluationUser::where('user_id', $userId)->select('course_id'))
        ->orderBy('title')
        ->get(['id', 'title'])
        ->map(fn($c) => ['value' => $c->id, 'label' => $c->title])
        ->values();

    return Inertia::render('Admin/Evaluations/Users/ByStudentAll', [
        'submissions' => $submissions,
        'student'     => $student,
        'filters'     => [
            'course'   => $request->input('course'),
            'status'   => $request->input('status'),
            'q'        => $request->input('q'),
            'per_page' => $perPage,
            'sort'     => $sort,
            'dir'      => $dir,
        ],
        'courseOptions' => $courseOptions,
        'statusOptions' => collect(\App\Enums\EvaluationUserStatus::cases())->map(fn($case) => [
            'label' => $case->label(),
            'value' => $case->value,
        ]),
    ]);
}






}
 