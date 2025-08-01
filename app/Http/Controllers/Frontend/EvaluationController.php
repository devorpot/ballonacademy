<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Activity;
use App\Enums\ActivityType;
use App\Models\CourseActivity;
  
use Illuminate\Support\Facades\Storage;


class EvaluationController extends Controller
{
    public function index(Course $course)
    {
        $userId = auth()->id();

        $hasFinishedCourse = CourseActivity::where('user_id', $userId)
            ->where('course_id', $course->id)
            ->exists();

       return Inertia::render('Frontend/Evaluations/Index', [
            'course' => $course,
            'evaluations' => Evaluation::with('course')
                ->where('user_id', $userId)
                ->where('course_id', $course->id)
                ->latest()
                ->get(),
            'canSubmitEvaluation' => $hasFinishedCourse,
        ]);

    }

    public function create(Course $course)
    {
        return Inertia::render('Frontend/Evaluations/Create', [
            'course' => $course,
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'eva_video_file' => 'required|file|mimetypes:video/mp4|max:51200',
            'eva_comments' => 'nullable|string',
        ]);

        $path = $request->file('eva_video_file')->store('evaluations', 'public');

       $evaluation = Evaluation::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'eva_send_date' => now()->toDateString(),
            'eva_video_file' => $path,
            'eva_comments' => $request->eva_comments,
        ]);


      Activity::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'evaluation_id' => $evaluation->id,
            'type' => ActivityType::EVALUATION_SEND->value,
            'description' => 'El usuario envió una evaluación.',
        ]);
        return redirect()
            ->route('dashboard.courses.evaluations.index', $course)
            ->with('success', 'Evaluación enviada.');
    }

    
public function edit(Course $course, Evaluation $evaluation)
{
    $this->authorizeEvaluation($evaluation, $course);

    return Inertia::render('Frontend/Evaluations/Edit', [
        'course' => $course,
        'evaluation' => $evaluation,
        'video_url' => $evaluation->eva_video_file 
            ? route('dashboard.courses.evaluations.download', [$course, $evaluation]) 
            : null
    ]);
}

    public function update(Request $request, Course $course, Evaluation $evaluation)
    {
        $this->authorizeEvaluation($evaluation, $course);

       $request->validate([
            'eva_video_file' => 'nullable|file|mimetypes:video/mp4|max:551200',
    'eva_comments' => 'nullable|string',
    'keep_video' => 'nullable|boolean',
        ]);

      if ($request->hasFile('eva_video_file')) {
        if ($evaluation->eva_video_file && Storage::disk('public')->exists($evaluation->eva_video_file)) {
            Storage::disk('public')->delete($evaluation->eva_video_file);
             }
            $path = $request->file('eva_video_file')->store('evaluations', 'public');
            $evaluation->eva_video_file = $path;
        } elseif (!$request->boolean('keep_video')) {
            if ($evaluation->eva_video_file && Storage::disk('public')->exists($evaluation->eva_video_file)) {
                Storage::disk('public')->delete($evaluation->eva_video_file);
            }
            $evaluation->eva_video_file = null;
        }


        $evaluation->eva_comments = $request->eva_comments;
        $evaluation->save();

        return redirect()
            ->route('dashboard.courses.evaluations.index', $course)
            ->with('success', 'Evaluación actualizada.');
    }

  public function destroy(Course $course, Evaluation $evaluation)
{
    $this->authorizeEvaluation($evaluation, $course);

    if ($evaluation->eva_video_file && Storage::disk('public')->exists($evaluation->eva_video_file)) {
        Storage::disk('public')->delete($evaluation->eva_video_file);
    }



    Activity::create([
        'user_id' => auth()->id(),
        'course_id' => $course->id,
        'evaluation_id' => $evaluation->id,
        'type' => ActivityType::EVALUATION_DELETE->value,
        'description' => 'El usuario eliminó su evaluación.',
    ]);


    $evaluation->delete();

    return redirect()
        ->route('dashboard.courses.evaluations.index', $course)
        ->with('success', 'Evaluación eliminada.');
}

    public function show(Course $course, Evaluation $evaluation)
    {
        $this->authorizeEvaluation($evaluation, $course);

        return Inertia::render('Frontend/Evaluations/Show', [
            'course' => $course,
            'evaluation' => $evaluation,
            'video_url' => route('dashboard.courses.evaluations.download', [$course, $evaluation])
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

    protected function authorizeEvaluation(Evaluation $evaluation, Course $course)
    {
        if (
            $evaluation->user_id !== auth()->id() ||
            $evaluation->course_id !== $course->id
        ) {
            abort(403);
        }
    }
}
