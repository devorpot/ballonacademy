<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LessonController extends Controller
{
    // ===== CRUD base de lecciones =====
 
    public function index()
{
    $lessons = Lesson::with([
            'course:id,title',
            'teacher:id,firstname,lastname',
            // pivote + hijo
            'lessonVideos' => fn ($q) => $q->select('id','lesson_id','video_id','course_id','order','active')->orderBy('order'),
            'lessonVideos.video:id,title,image_cover,duration,size',
            'lessonEvaluations' => fn ($q) => $q->select('id','lesson_id','evaluation_id','course_id','order','active')->orderBy('order'),
            'lessonEvaluations.evaluation:id,title,status',
        ])
        ->latest()
        ->get();

    return Inertia::render('Admin/Lessons/Index', [
        'lessons' => $lessons
    ]);
}

 
    public function create()
    {
        $courses  = Course::select('id', 'title')->get();
        $teachers = Teacher::select('id', 'firstname', 'lastname')->get();

        return Inertia::render('Admin/Lessons/Create', [
            'courses'  => $courses,
            'teachers' => $teachers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request, true);

        // Subida de archivos (mismo patrón que CourseController)
        $validated['image']       = $this->handleUpload($request, 'image', 'lessons/images');
        $validated['image_cover'] = $this->handleUpload($request, 'image_cover', 'lessons/covers');

        // Siguiente orden dentro del curso
        $maxOrder = Lesson::where('course_id', $validated['course_id'])->max('order');
        $validated['order'] = $maxOrder ? $maxOrder + 1 : 1;

        $validated = $this->normalizePayload($validated);

        $lesson = Lesson::create($validated);

        return redirect()
            ->route('admin.courses.lessons.panel', ['course' => $lesson->course_id])
            ->with('success', 'Lesson created successfully.');
    }

    public function show(Lesson $lesson)
{
    $lesson->load([
        'course:id,title',
        'teacher:id,firstname,lastname',
        'lessonVideos' => fn ($q) => $q->select('id','lesson_id','video_id','course_id','order','active')->orderBy('order'),
        'lessonVideos.video:id,title,image_cover,duration,size',
        'lessonEvaluations' => fn ($q) => $q->select('id','lesson_id','evaluation_id','course_id','order','active')->orderBy('order'),
        'lessonEvaluations.evaluation:id,title,status',
        // si sigues usando también estas:
        'assignedVideos' => fn ($q) => $q->select('videos.id','videos.title','videos.image_cover','videos.duration','videos.size')->orderBy('lesson_videos.order'),
        'assignedEvaluations' => fn ($q) => $q->select('evaluations.id','evaluations.title','evaluations.status')->orderBy('lesson_evaluations.order'),
    ]);

    $courses  = Course::select('id', 'title')->get();
    $teachers = Teacher::select('id', 'firstname', 'lastname')->get();

    return Inertia::render('Admin/Lessons/Show', [
        'lesson'   => $lesson,
        'courses'  => $courses,
        'teachers' => $teachers,
    ]);
}


public function edit(Lesson $lesson)
{
    $courses  = Course::select('id', 'title')->get();
    $teachers = Teacher::select('id', 'firstname', 'lastname')->get();

    $lesson->load([
        // belongsToMany existentes
        'assignedVideos' => fn ($q) => $q
            ->select('videos.id','videos.title','videos.image_cover','videos.duration','videos.size')
            ->orderBy('lesson_videos.order'),
        'assignedEvaluations' => fn ($q) => $q
            ->select('evaluations.id','evaluations.title','evaluations.status')
            ->orderBy('lesson_evaluations.order'),

        // pivotes + hijos
        'lessonVideos' => fn ($q) => $q
            ->select('id','lesson_id','video_id','course_id','order','active')
            ->orderBy('order'),
        'lessonVideos.video:id,title,image_cover,duration,size',

        'lessonEvaluations' => fn ($q) => $q
            ->select('id','lesson_id','evaluation_id','course_id','order','active')
            ->orderBy('order'),
        'lessonEvaluations.evaluation:id,title,status',
    ]);

    $lessonPayload = [
        'id'                => $lesson->id,
        'title'             => $lesson->title,
        'instructions'      => $lesson->instructions,
        'description_short' => $lesson->description_short,
        'publish_on'        => $lesson->publish_on,
        'course_id'         => $lesson->course_id,
        'teacher_id'        => $lesson->teacher_id,
        'order'             => $lesson->order,
        'active'            => (bool) $lesson->active,
        'add_video'         => (bool) $lesson->add_video,
        'add_evaluation'    => (bool) $lesson->add_evaluation,
        'add_materials'     => (bool) $lesson->add_materials,
        'image'             => $lesson->image,
        'image_cover'       => $lesson->image_cover,
        'course'            => optional($lesson->course)->only(['id','title']),
        // arrays existentes
        'videos' => $lesson->assignedVideos->map(fn ($v) => [
            'id'          => $v->id,
            'title'       => $v->title,
            'image_cover' => $v->image_cover,
            'duration'    => $v->duration,
            'size'        => $v->size,
            'pivot'       => [
                'order'  => $v->pivot->order,
                'active' => (bool) $v->pivot->active,
            ],
        ])->values(),

        'evaluations' => $lesson->assignedEvaluations->map(fn ($e) => [
            'id'    => $e->id,
            'title' => $e->title,
            'status'=> $e->status,
            'pivot' => [
                'order'  => $e->pivot->order,
                'active' => (bool) $e->pivot->active,
            ],
        ])->values(),

        // NUEVO: acceso crudo a los pivotes por si lo quieres directamente en la UI
        'lesson_videos' => $lesson->lessonVideos->map(fn ($lv) => [
            'id'        => $lv->id,
            'video_id'  => $lv->video_id,
            'order'     => $lv->order,
            'active'    => (bool) $lv->active,
            'video'     => optional($lv->video)->only(['id','title','image_cover','duration','size']),
        ])->values(),
        'lesson_evaluations' => $lesson->lessonEvaluations->map(fn ($le) => [
            'id'            => $le->id,
            'evaluation_id' => $le->evaluation_id,
            'order'         => $le->order,
            'active'        => (bool) $le->active,
            'evaluation'    => optional($le->evaluation)->only(['id','title','status']),
        ])->values(),
    ];

    return Inertia::render('Admin/Lessons/Edit', [
        'lesson'   => $lessonPayload,
        'courses'  => $courses,
        'teachers' => $teachers,
        'course'   => $lesson->course()->select('id','title')->first(),
    ]);
}



    public function update(Request $request, Lesson $lesson)
    {
        // En update NO obligamos reglas de file (igual que CourseController)
        $validated = $this->validateData($request, false);

        // Llenamos primero campos no file
        $lesson->fill($this->normalizePayload($validated));

        // Imagen principal
        if ($request->hasFile('image')) {
            $this->deleteFile($lesson->image);
            $lesson->image = $request->file('image')->store('lessons/images', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteFile($lesson->image);
            $lesson->image = null;
        }

        // Imagen de portada
        if ($request->hasFile('image_cover')) {
            $this->deleteFile($lesson->image_cover);
            $lesson->image_cover = $request->file('image_cover')->store('lessons/covers', 'public');
        } elseif ($request->boolean('remove_image_cover')) {
            $this->deleteFile($lesson->image_cover);
            $lesson->image_cover = null;
        }

        $lesson->save();

        return redirect()
            ->route('admin.courses.lessons.panel', ['course' => $lesson->course_id])
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Lesson $lesson)
    {
        // Borrar archivos físicos asociados (igual que CourseController)
        $this->deleteFile($lesson->image);
        $this->deleteFile($lesson->image_cover);

        $lesson->delete();

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', 'Lesson deleted successfully');
    }

    // ===== Panel y utilidades =====

    public function lessonsPanel(Course $course)
{
    $course->load([
        'lessons' => fn ($q) => $q->orderBy('order')
            ->with([
                'lessonVideos' => fn ($q2) => $q2->select('id','lesson_id','video_id','course_id','order','active')->orderBy('order'),
                'lessonVideos.video:id,title,image_cover,duration,size',
                'lessonEvaluations' => fn ($q3) => $q3->select('id','lesson_id','evaluation_id','course_id','order','active')->orderBy('order'),
                'lessonEvaluations.evaluation:id,title,status',
            ]),
    ]);

    return Inertia::render('Admin/Lessons/LessonsPanel', [
        'course'  => $course,
        'lessons' => $course->lessons,
    ]);
}


    public function list(Course $course)
{
    $lessons = $course->lessons()
        ->with([
            'teacher:id,firstname,lastname',
            'course:id,title',
            'lessonVideos' => fn ($q) => $q->select('id','lesson_id','video_id','course_id','order','active')->orderBy('order'),
            'lessonVideos.video:id,title,image_cover,duration,size',
            'lessonEvaluations' => fn ($q) => $q->select('id','lesson_id','evaluation_id','course_id','order','active')->orderBy('order'),
            'lessonEvaluations.evaluation:id,title,status',
        ])
        ->orderBy('order')
        ->get();

    return response()->json($lessons);
}


    public function reorderLessons(Request $request, Course $course)
    {
        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer'
        ]);

        foreach ($validated['order'] as $index => $lessonId) {
            $course->lessons()->where('id', $lessonId)->update(['order' => $index + 1]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    // ===== Helpers =====

    private function validateData(Request $request, bool $includeFiles = true): array
    {
        $rules = [
            'title'             => 'required|string|max:255',
            'instructions'      => 'nullable|string',
            'description_short' => 'nullable|string|max:255',
            'publish_on'        => 'required|date',
            'course_id'         => 'required|exists:courses,id',
            'teacher_id'        => 'required|exists:teachers,id',

            'active'            => 'sometimes|boolean',
            'add_video'         => 'sometimes|boolean',
            'add_evaluation'    => 'sometimes|boolean',
            'add_materials'     => 'sometimes|boolean',

            // Relacionales
            'videos'            => 'nullable|array',
            'videos.*'          => 'integer|exists:videos,id',

            'evaluations'       => 'nullable|array',
            

            'materials'         => 'nullable|array',
            'materials.*'       => 'integer|exists:video_materials,id',
        ];

        if ($includeFiles) {
            // Igual que en CourseController: reglas de archivos solo en create
            $rules['image']       = 'nullable|file|mimes:jpeg,png,jpg,gif|max:10240';
            $rules['image_cover'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:10240';
        }

        $rules['order'] = 'sometimes|integer|min:0';

        return $request->validate($rules);
    }

    private function normalizePayload(array $data): array
    {
        $data['active']         = isset($data['active']) ? (bool)$data['active'] : false;
        $data['add_video']      = isset($data['add_video']) ? (bool)$data['add_video'] : true;
        $data['add_evaluation'] = isset($data['add_evaluation']) ? (bool)$data['add_evaluation'] : false;
        $data['add_materials']  = isset($data['add_materials']) ? (bool)$data['add_materials'] : false;

        if (empty($data['add_video'])) {
            $data['videos'] = null;
        }
        if (empty($data['add_evaluation'])) {
            $data['evaluations'] = null;
        }
        if (empty($data['add_materials'])) {
            $data['materials'] = null;
        }

        if (!empty($data['add_video']) && empty($data['videos'])) {
            $data['videos'] = [];
        }
        if (!empty($data['add_evaluation']) && empty($data['evaluations'])) {
            $data['evaluations'] = [];
        }
        if (!empty($data['add_materials']) && empty($data['materials'])) {
            $data['materials'] = [];
        }

        if (!array_key_exists('order', $data)) {
            $data['order'] = 0;
        }

        return $data;
    }

    // === Helpers de archivos (idéntico a CourseController) ===

    private function handleUpload(Request $request, string $field, string $path)
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path, 'public');
        }
        return null;
    }

    private function updateFile(Request $request, ?string $currentPath, string $field, string $path)
    {
        if ($request->hasFile($field)) {
            $this->deleteFile($currentPath);
            return $this->handleUpload($request, $field, $path);
        }
        return $currentPath;
    }

    private function deleteFile($filePath)
    {
        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }
    }
}
