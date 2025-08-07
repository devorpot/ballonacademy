<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Enums\EvaluationStatus;

class EvaluationController extends Controller
{
    public function index(Request $request)
    { 
        $allowedSorts = ['id', 'eva_send_date', 'created_at'];
        $sortBy = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : 'created_at';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

        $evaluations = Evaluation::with(['user', 'course'])
            ->orderBy($sortBy, $sortDir)
            ->get();

        return Inertia::render('Admin/Evaluations/Index', [
            'evaluations' => $evaluations,
            'filters' => [
                'sortBy' => $sortBy,
                'sortDir' => $sortDir
            ],
            'users' => User::role('student')->get(),
            'courses' => Course::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Evaluations/Create', [
            'users' => User::all(),
            'courses' => Course::all(),
            'statusOptions' => collect(EvaluationStatus::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label()
            ])->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        $validated['eva_video_file'] = $this->handleUpload($request, 'eva_video_file', 'evaluations/videos');

        Evaluation::create($validated);

        return redirect()
            ->route('admin.evaluations.index')
            ->with('success', 'Evaluación creada exitosamente');
    }

        public function edit(Evaluation $evaluation)
        {
            $statusLabels = [
                'SEND' => 'Enviado',
                'REVISION' => 'En revisión',
                'APROVEED' => 'Aprobado',
                'NO APROVEED' => 'No aprobado',
            ];

            return Inertia::render('Admin/Evaluations/Edit', [
                'evaluation' => $evaluation->load(['user', 'course']),
                'users' => User::role('student')->get(),
                'courses' => Course::all(),
                'statusOptions' => collect(\App\Enums\EvaluationStatus::cases())->map(function($case) use ($statusLabels) {
                    return [
                        'value' => $case->value,
                        'label' => $statusLabels[$case->value] ?? $case->value,
                    ];
                }),
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
        return Inertia::render('Admin/Evaluations/Show', [
            'evaluation' => $evaluation->load(['user', 'course'])
        ]);
    }

private function validateData(Request $request, $includeFiles = true)
{
    $rules = [
        'user_id'        => 'required|exists:users,id',
        'course_id'      => 'required|exists:courses,id',
        'eva_send_date'  => 'required|date',
        'eva_comments'   => 'nullable|string|max:5000',
        'status'         => 'required|in:SEND,REVISION,APROVEED,NO APROVEED', // <-- Agregado aquí
    ];

    if ($includeFiles) {
        $rules['eva_video_file'] = 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v|max:51200';
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
        // Cargar preguntas activas asociadas a la evaluación y el curso
        $questions = $evaluation->questions()
            ->where('status', true)
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Evaluations/Preview', [
            'evaluation' => $evaluation->load(['user', 'course']),
            'questions' => $questions,
        ]);
    }

}
