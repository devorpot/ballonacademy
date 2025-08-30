<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Course;
use App\Models\Video;
use App\Models\Lesson; 
use App\Models\LessonEvaluation; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Enums\EvaluationStatus;
use Illuminate\Support\Facades\DB;
use App\Enums\EvaluationsTypes; // nuevo: enum para 'type' (1=Course, 2=Video)

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'eva_send_date', 'created_at'];
        $sortBy = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : 'created_at';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

        $evaluations = Evaluation::with(['course', 'video', 'lesson'])
            ->orderBy($sortBy, $sortDir)
            ->get();

        return Inertia::render('Admin/Evaluations/Index', [
            'evaluations' => $evaluations,
            'filters' => [
                'sortBy' => $sortBy,
                'sortDir' => $sortDir
            ],
            'courses' => Course::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Evaluations/Create', [
            'courses' => Course::all(),
            'statusOptions' => collect(EvaluationStatus::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label()
            ])->toArray(),
            'typeOptions' => collect(EvaluationsTypes::cases())->map(fn($t) => [
                'value' => $t->value,
                'label' => $t->label()
            ])->toArray(), // opciones para el nuevo campo 'type'
        ]);
    }

public function store(Request $request)
{
    // Validación base existente
    $validated = $this->validateData($request, true);
    // Validación específica del PDF (20 MB)
    $request->validate([
        'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
    ]);

    return DB::transaction(function () use ($request, $validated) {
        // Subidas
        $validated['eva_video_file'] = $this->handleUpload($request, 'eva_video_file', 'evaluations/videos');
        $validated['pdf_file']       = $this->handleUpload($request, 'pdf_file',       'evaluations/pdfs');

        $evaluation = Evaluation::create($validated);

        // Vinculación a lección si aplica
        $incomingType = $this->typeValue($validated['type'] ?? null);
        if ($incomingType === EvaluationsTypes::LESSON->value && !empty($validated['lesson_id'])) {
            $this->attachLessonEvaluation(
                $evaluation,
                (int) $validated['lesson_id'],
                (int) $validated['course_id']
            );
        }

        return redirect()
            ->route('admin.evaluations.index')
            ->with('success', 'Evaluación creada exitosamente');
    });
}




 public function edit(Evaluation $evaluation)
    {
        $statusLabels = [
            'SEND'        => 'Enviado',
            'REVISION'    => 'En revisión',
            'APROVEED'    => 'Aprobado',
            'NO APROVEED' => 'No aprobado',
        ];


        /*Agregamos la evaluacion si es del tipo  3 =  Evaluacion*/





        return Inertia::render('Admin/Evaluations/Edit', [
            'evaluation' => $evaluation->load(['course', 'video', 'lesson']),
            'courses' => Course::all(),
            'statusOptions' => collect(EvaluationStatus::cases())->map(function ($case) use ($statusLabels) {
                return [
                    'value' => $case->value,
                    'label' => $statusLabels[$case->value] ?? $case->value,
                ];
            }),
            'typeOptions' => collect(EvaluationsTypes::cases())->map(fn($t) => [
                'value' => $t->value,
                'label' => $t->label()
            ])->toArray(),
        ]);
    }

public function update(Request $request, Evaluation $evaluation)
{
    // Validación base existente (sin obligar archivos)
    $validated = $this->validateData($request, false);
    // Validación específica del PDF y flag de borrado
    $request->validate([
        'pdf_file'        => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        'remove_pdf_file' => ['sometimes', 'boolean'],
    ]);

    return DB::transaction(function () use ($request, $evaluation, $validated) {
        // ¿Era de lección antes?
        $wasLesson = ($this->typeValue($evaluation->type) === EvaluationsTypes::LESSON->value);

        // Actualizar campos
        $evaluation->fill($validated);

        // Video: reemplazo si viene archivo
        $evaluation->eva_video_file = $this->updateFile(
            $request,
            $evaluation->eva_video_file,
            'eva_video_file',
            'evaluations/videos'
        );

        // PDF: reemplazo si viene archivo
        $evaluation->pdf_file = $this->updateFile(
            $request,
            $evaluation->pdf_file,
            'pdf_file',
            'evaluations/pdfs'
        );

        // PDF: borrado explícito si se marcó el flag y no se subió uno nuevo
        if ($request->boolean('remove_pdf_file') && !$request->hasFile('pdf_file')) {
            $this->deleteFile($evaluation->pdf_file);
            $evaluation->pdf_file = null;
        }

        $evaluation->save();

        // ¿Ahora es de lección?
        $incomingType = $this->typeValue($validated['type'] ?? null);
        $isLesson     = ($incomingType === EvaluationsTypes::LESSON->value);

        if ($isLesson && !empty($validated['lesson_id'])) {
            $this->attachLessonEvaluation(
                $evaluation,
                (int) $validated['lesson_id'],
                (int) $validated['course_id']
            );
        }

        if ($wasLesson && !$isLesson) {
            $this->detachLessonEvaluation($evaluation);
        }

        return redirect()
            ->route('admin.evaluations.index')
            ->with('success', 'Evaluación actualizada correctamente');
    });
}


public function destroy(Evaluation $evaluation)
{
    // Elimina archivos asociados
    $this->deleteFile($evaluation->eva_video_file);
    $this->deleteFile($evaluation->pdf_file);

    $evaluation->delete();

    return redirect()
        ->route('admin.evaluations.index')
        ->with('success', 'Evaluación eliminada exitosamente');
}

        public function show(Evaluation $evaluation)
    {
        $evaluation->load(['course', 'video','lesson', 'teacher', 'user']);
        return Inertia::render('Admin/Evaluations/Show', compact('evaluation'));
    }


    /**
     * Devuelve los videos de un curso para el combo dependiente.
     */
    public function videosByCourse($courseId)
    {
        $videos = Video::where('course_id', $courseId)
            ->orderBy('order') // ajusta si no tienes 'order'
            ->get(['id', 'title']);

        return response()->json($videos);
    }

      public function lessonsByCourse($courseId)
    {
        $videos = Lesson::where('course_id', $courseId)
            ->orderBy('order') // ajusta si no tienes 'order'
            ->get(['id', 'title']);

        return response()->json($videos);
    }

    /**
     * Validación unificada para crear/actualizar.
     * - eva_type: 1=Cuestionario, 2=Video (independiente)
     * - type: 1=Course, 2=Video (independiente). Si type=2, video_id es requerido y debe pertenecer al course_id.
     */
    private function validateData(Request $request, $includeFiles = true)
{
    $rules = [
        'course_id'      => ['required', 'exists:courses,id'],
        'eva_send_date'  => ['required', 'date'],
        'eva_type'       => ['required', 'in:1,2'],
        'type'           => ['required', Rule::in([
            EvaluationsTypes::COURSE->value,
            EvaluationsTypes::VIDEO->value,
            EvaluationsTypes::LESSON->value
        ])],
        'title'          => ['required', 'string', 'max:5000'],
        'eva_comments'   => ['nullable', 'string', 'max:5000'],
        'status'         => ['nullable', Rule::in(['SEND', 'REVISION', 'APROVEED', 'NO APROVEED'])],
        'points_min'     => ['required', 'integer', 'min:1', 'max:100'],

        // VIDEO: requerido si type=VIDEO y debe pertenecer al mismo course_id
        'video_id'       => [
            Rule::requiredIf(fn () => (int)$request->input('type') === EvaluationsTypes::VIDEO->value),
            'nullable',
            Rule::exists('videos', 'id')->where(fn ($q) =>
                $q->where('course_id', $request->input('course_id'))
            ),
        ],

        // LESSON: requerido si type=LESSON y debe pertenecer al mismo course_id
        'lesson_id'      => [
            Rule::requiredIf(fn () => (int)$request->input('type') === EvaluationsTypes::LESSON->value),
            'nullable',
            Rule::exists('lessons', 'id')->where(fn ($q) =>
                $q->where('course_id', $request->input('course_id'))
            ),
        ],
    ];

    if ($includeFiles) {
        $rules['eva_video_file'] = ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-m4v', 'max:51200'];
    }

    $validated = $request->validate($rules);

    // Limpieza de FKs según el type seleccionado para evitar colisiones
    $type = (int) ($validated['type'] ?? 0);
    if ($type === EvaluationsTypes::COURSE->value) {
        $validated['video_id']  = null;
        $validated['lesson_id'] = null;
    } elseif ($type === EvaluationsTypes::VIDEO->value) {
        $validated['lesson_id'] = null; // no es necesaria si evalúa al video
    } elseif ($type === EvaluationsTypes::LESSON->value) {
        $validated['video_id']  = null; // no es necesaria si evalúa a la lección
    }

    return $validated;
}


    private function handleUpload(Request $request, $field, $path)
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path, 'public');
        }
        return null;
    }

    private function deleteFile($filePath)
    {
        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }
    }

    private function updateFile(Request $request, $currentPath, $field, $path)
    {
        if ($request->hasFile($field)) {
            $this->deleteFile($currentPath);
            return $this->handleUpload($request, $field, $path);
        }
        return $currentPath;
    }

    public function preview(Evaluation $evaluation)
    {
        $questions = $evaluation->questions()
            ->where('status', true)
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Evaluations/Preview', [
            'evaluation' => $evaluation->load(['course', 'video', 'lesson']),
            'questions' => $questions,
        ]);
    }

     public function getByCourse(Request $request, Course $course)
    {
        $q        = trim((string) $request->query('q', ''));
        $lessonId = $request->query('lesson_id');

        $query = Evaluation::query()
            ->where('lesson_id', $lessonId)
            ->where('course_id', $course->id);

        if ($q !== '') {
            $query->where('title', 'LIKE', "%{$q}%");
        }

        // Excluir evaluaciones ya vinculadas a la lección (si se provee)
        if ($lessonId) {
            $query->whereDoesntHave('lessons', function ($sub) use ($lessonId) {
                $sub->where('lessons.id', $lessonId);
            });
        }

        // Limitar resultados para autocomplete
        $evaluations = $query
            ->select('id', 'title', 'status') // agrega más columnas si las necesitas
            ->orderBy('title')
            ->limit(15)
            ->get()
            ->map(function ($e) {
                return [
                    'id'     => $e->id,
                    'title'  => $e->title,
                    'status' => $e->status, // opcional
                ];
            });

        return response()->json([
            'data' => $evaluations,
        ]);
    }

    /**
 * Crea (o actualiza) la relación en lesson_evaluations dejando el order al final.
 */
private function attachLessonEvaluation(Evaluation $evaluation, int $lessonId, int $courseId): void
{
    // Si ya existe una fila para esta evaluación, la actualizamos; si no, la creamos al final.
    $existing = LessonEvaluation::where('evaluation_id', $evaluation->id)->first();

    if ($existing) {
        // Si cambia de lección, asignar nuevo order al final de la nueva lección
        if ((int)$existing->lesson_id !== $lessonId) {
            $nextOrder = (int) LessonEvaluation::where('lesson_id', $lessonId)->max('order');
            $existing->order = $nextOrder + 1;
        }
        $existing->lesson_id  = $lessonId;
        $existing->course_id  = $courseId;
        $existing->active     = true;
        $existing->save();
        return;
    }

    // No existía: crear al final de la lección
    $nextOrder = (int) LessonEvaluation::where('lesson_id', $lessonId)->max('order');

    LessonEvaluation::create([
        'lesson_id'     => $lessonId,
        'evaluation_id' => $evaluation->id,
        'course_id'     => $courseId,
        'order'         => $nextOrder + 1,
        'active'        => true,
    ]);
}


/**
 * Devuelve el valor (int) del tipo, acepte enum/int/string/null.
 */
private function typeValue($type): ?int
{
    if ($type instanceof \App\Enums\EvaluationsTypes) {
        return $type->value;
    }
    if ($type === null || $type === '') {
        return null;
    }
    return (int) $type;
}


/**
 * Elimina cualquier relación en lesson_evaluations para esta evaluación.
 */
private function detachLessonEvaluation(Evaluation $evaluation): void
{
    LessonEvaluation::where('evaluation_id', $evaluation->id)->delete();
}



}
