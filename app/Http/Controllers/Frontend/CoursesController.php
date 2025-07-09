<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Video;

class CoursesController extends Controller
{
    /**
     * Muestra el dashboard del estudiante con sus cursos asignados
     */
    public function dashboard()
    {
        $user = Auth::user();

        $courses = $user->courses()->withCount('videos')->get();

        return Inertia::render('Frontend/Dashboard/Index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Lista de cursos asignados al estudiante
     */
    public function index()
    {
        $user = Auth::user();

        $courses = $user->courses()->withCount('videos')->get();

        return Inertia::render('Frontend/Courses/Index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Muestra los videos de un curso específico asignado al estudiante
     */
    public function show($id)
    {
        $user = Auth::user();

        $course = $user->courses()
            ->with(['videos' => function ($query) {
                $query->orderBy('order');
            }])
            ->findOrFail($id);

        return Inertia::render('Frontend/Courses/CourseVideos', [
            'course' => $course,
            'videos' => $course->videos,
        ]);
    }

  

    public function videoDetail($courseId, $videoId)
    {
        $user = Auth::user();

        // Asegurarse que el curso pertenece al estudiante
        $course = $user->courses()->findOrFail($courseId);

        // Obtener el video dentro del curso
        $video = Video::where('course_id', $course->id)
                      ->where('id', $videoId)
                      ->firstOrFail();

        return Inertia::render('Frontend/Courses/CourseVideoDetail', [
            'course' => $course,
            'video' => $video,
        ]);
    }

}
