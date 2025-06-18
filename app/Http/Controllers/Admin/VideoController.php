<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function index(Course $course)
    {
        return Inertia::render('Admin/Videos/Index', [
            'course' => $course,
            'videos' => $course->videos
        ]);
    }

    public function create(Course $course)
    {
        return Inertia::render('Admin/Videos/Create', [
            'course' => $course
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_short' => 'nullable|string',
            'comments' => 'nullable|string',
            'image_cover' => 'nullable|string|max:255',
            'video_url' => 'required|string|max:255',
        ]);

        $course->videos()->create($validated);

        return redirect()
            ->route('admin.courses.videos.index', $course->id)
            ->with('success', 'Video creado correctamente');
    }

    public function edit(Course $course, Video $video)
    {
        return Inertia::render('Admin/Videos/Edit', [
            'course' => $course,
            'video' => $video
        ]);
    }

    public function update(Request $request, Course $course, Video $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_short' => 'nullable|string',
            'comments' => 'nullable|string',
            'image_cover' => 'nullable|string|max:255',
            'video_url' => 'required|string|max:255',
        ]);

        $video->update($validated);

        return redirect()
            ->route('admin.courses.videos.index', $course->id)
            ->with('success', 'Video actualizado correctamente');
    }

    public function destroy(Course $course, Video $video)
    {
        $video->delete();

        return redirect()
            ->route('admin.courses.videos.index', $course->id)
            ->with('success', 'Video eliminado correctamente');
    }

    public function show(Course $course, Video $video)
    {
        return Inertia::render('Admin/Videos/Show', [
            'course' => $course,
            'video' => $video
        ]);
    }
}
