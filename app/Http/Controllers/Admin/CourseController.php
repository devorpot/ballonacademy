<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Video;  // Importar el modelo de Video
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'title', 'created_at'];
        $sortBy = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : 'created_at';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

        $courses = Course::orderBy($sortBy, $sortDir)->get();


        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'filters' => [
                'sortBy' => $sortBy,
                'sortDir' => $sortDir
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Courses/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        $validated['image_cover'] = $this->handleUpload($request, 'image_cover', 'courses/covers');
        $validated['logo'] = $this->handleUpload($request, 'logo', 'courses/logos');

        $course = Course::create($validated);

        // Relacionar los videos con el curso
        if ($request->has('videos')) {
            $videos = $request->input('videos');
            foreach ($videos as $videoData) {
                // Crear el video y asociarlo al curso
                $course->videos()->create($videoData);
            }
        }

        return redirect()->route('admin.courses.index')->with('success', 'Curso creado exitosamente');
    }

    public function edit(Course $course)
        {
            $course->load('students'); // ← importante

            return Inertia::render('Admin/Courses/Edit', [
                'course' => $course,
                'videos' => $course->videos
            ]);
        }

    public function update(Request $request, Course $course)
    {
        $validated = $this->validateData($request, false);

        $course->fill($validated);

        $course->image_cover = $this->updateFile($request, $course->image_cover, 'image_cover', 'courses/covers');
        $course->logo = $this->updateFile($request, $course->logo, 'logo', 'courses/logos');

        $course->save();

        return redirect()->route('admin.courses.index')->with('success', 'Curso actualizado exitosamente');
    }

    public function destroy(Course $course)
    {
        $this->deleteFile($course->image_cover);
        $this->deleteFile($course->logo);
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Curso eliminado exitosamente');
    }

    // **Método para crear un video relacionado con un curso**
    public function addVideo(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_short' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
            'image_cover' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image_cover')) {
            $validated['image_cover'] = $request->file('image_cover')->store('videos/image_covers', 'public');
        }

        // Relacionar video al curso
        $course->videos()->create($validated);

        return redirect()->route('admin.courses.edit', $course->id)->with('success', 'Video agregado correctamente');
    }

    // **Método para actualizar un video relacionado con un curso**
    public function updateVideo(Request $request, Course $course, Video $video)
    {
        $validated = $this->validateData($request, false);

        $video->fill($validated);

        $video->image_cover = $this->updateFile($request, $video->image_cover, 'image_cover', 'videos/image_covers');
        $video->save();

        return redirect()->route('admin.courses.edit', $course->id)->with('success', 'Video actualizado correctamente');
    }

    // **Método para eliminar un video de un curso**
    public function deleteVideo(Course $course, Video $video)
    {
        $video->delete();
        return redirect()->route('admin.courses.edit', $course->id)->with('success', 'Video eliminado exitosamente');
    }

    private function validateData(Request $request, $includeFiles = true)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'description_short' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
        ];

        if ($includeFiles) {
            $rules['image_cover'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['logo'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
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

    private function updateFile(Request $request, $currentPath, $field, $path)
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

    public function show(Course $course)
{
    $course->load('students'); // Carga los estudiantes asignados

    return response()->json([ 
        'course' => $course
    ]);
}


public function assignStudents(Request $request, Course $course)
{
    logger()->info('Estudiantes recibidos:', $request->all());

    $validated = $request->validate([
        'students' => 'required|array',
        'students.*' => 'exists:users,id',
    ]);

    $course->students()->sync($validated['students']);

    return response()->json(['message' => 'Alumnos asignados']);
}






}
