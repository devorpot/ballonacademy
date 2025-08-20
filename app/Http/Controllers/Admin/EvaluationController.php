<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Course;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Enums\EvaluationStatus;
use App\Enums\EvaluationsTypes; // nuevo: enum para 'type' (1=Course, 2=Video)

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'eva_send_date', 'created_at'];
        $sortBy = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : 'created_at';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

        $evaluations = Evaluation::with(['course', 'video'])
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
        $validated = $this->validateData($request, true);

        $validated['eva_video_file'] = $this->handleUpload($request, 'eva_video_file', 'evaluations/videos');

        Evaluation::create($validated);

        return redirect()
            ->route('admin.evaluations.index')
            ->with('success', 'Evaluación creada exitosamente');
    }

    public function edit(Evaluation $evaluation)
    {
        $statusLabels = [
            'SEND'        => 'Enviado',
            'REVISION'    => 'En revisión',
            'APROVEED'    => 'Aprobado',
            'NO APROVEED' => 'No aprobado',
        ];

        return Inertia::render('Admin/Evaluations/Edit', [
            'evaluation' => $evaluation->load(['course', 'video']),
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
        $validated = $this->validateData($request, false);

        $evaluation->fill($validated);

        $evaluation->eva_video_file = $this->updateFile($request, $evaluation->eva_video_file, 'eva_video_file', 'evaluations/videos');

        $evaluation->save();

        return redirect()
            ->route('admin.evaluations.index')
            ->with('success', 'Evaluación actualizada correctamente');
    }

    public function destroy(Evaluation $evaluation)
    {
        $this->deleteFile($evaluation->eva_video_file);

        $evaluation->delete();

        return redirect()
            ->route('admin.evaluations.index')
            ->with('success', 'Evaluación eliminada exitosamente');
    }

        public function show(Evaluation $evaluation)
    {
        $evaluation->load(['course', 'video', 'teacher', 'user']);
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
            'type'           => ['required', Rule::in([EvaluationsTypes::COURSE->value, EvaluationsTypes::VIDEO->value])],
            'title'          => ['required', 'string', 'max:5000'],
            'eva_comments'   => ['nullable', 'string', 'max:5000'],
            'status'         => ['nullable', Rule::in(['SEND', 'REVISION', 'APROVEED', 'NO APROVEED'])],
             'points_min'     => ['required', 'integer', 'min:1', 'max:100'],

            // video_id requerido si type=2 (Evaluación para el video) y debe pertenecer al course_id
            'video_id'       => [
                Rule::requiredIf(fn () => (int)$request->input('type') === EvaluationsTypes::VIDEO->value),
                'nullable',
                Rule::exists('videos', 'id')->where(fn ($q) =>
                    $q->where('course_id', $request->input('course_id'))
                ),
            ],
        ];

        if ($includeFiles) {
            $rules['eva_video_file'] = ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-m4v', 'max:51200'];
        }

        return $request->validate($rules);
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
            'evaluation' => $evaluation->load(['course', 'video']),
            'questions' => $questions,
        ]);
    }
}
