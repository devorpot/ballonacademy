<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Courses/Create');
    }

    public function store(Request $request)
    {
        // Limpia los campos de fecha: convierte '' en null
        $request->merge([
            'date_start' => $request->date_start ?: null,
            'date_end' => $request->date_end ?: null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'description_short' => 'nullable|string',
            'level' => 'nullable|string|max:255',
            'image_cover' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
        ]);

        Course::create($validated);

        return redirect()->route('admin.courses.index')->with('success', 'Curso creado exitosamente');
    }

    public function edit(Course $course)
    {
        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course
        ]);
    }

    public function update(Request $request, Course $course)
    {
        // Limpia los campos de fecha: convierte '' en null
        $request->merge([
            'date_start' => $request->date_start ?: null,
            'date_end' => $request->date_end ?: null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'description_short' => 'nullable|string',
            'level' => 'nullable|string|max:255',
            'image_cover' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')->with('success', 'Curso actualizado exitosamente');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Curso eliminado exitosamente');
    }
}
