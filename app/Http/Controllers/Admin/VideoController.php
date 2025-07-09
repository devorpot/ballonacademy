<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;


class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::with('course')->latest()->get();

        return Inertia::render('Admin/Videos/Index', [
            'videos' => $videos
        ]);
    }


    public function show(Video $video)
        {
            $courses = Course::select('id', 'title')->get();
            $teachers = Teacher::select('id', 'firstname', 'lastname')->get();

            return Inertia::render('Admin/Videos/Show', [
                'video' => $video,
                'courses' => $courses,
                'teachers' => $teachers,
            ]);
        }


    public function create()
    {
        $courses = Course::select('id', 'title')->get();
        $teachers = Teacher::select('id', 'firstname', 'lastname')->get();

        return Inertia::render('Admin/Videos/Create', [
            'courses' => $courses,
            'teachers' => $teachers
        ]);
    }


    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('image_cover')) {
            $validated['image_cover'] = $request->file('image_cover')->store('videos/image_covers', 'public');
        }

        $video = Video::create($validated);

        return redirect()->route('admin.videos.index')->with('success', 'Video creado correctamente.');
    }

    public function edit(Video $video)
        {
            $courses = Course::select('id', 'title')->get();
            $teachers = Teacher::select('id', 'firstname', 'lastname')->get();

            return Inertia::render('Admin/Videos/Edit', [
                'video' => $video,
                'courses' => $courses,
                'teachers' => $teachers,
            ]);
        }

public function update(Request $request, Course $course, Video $video)
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('image_cover')) {
            if ($video->image_cover) {
                Storage::disk('public')->delete($video->image_cover);
            }

            $validated['image_cover'] = $request->file('image_cover')->store('videos/image_covers', 'public');
        }

        $video->update($validated);

        return redirect()->route('admin.videos.index')->with('success', 'Video actualizado correctamente.');
    }

   public function destroy(Course $course, Video $video)
    {
        $this->checkOwnership($course, $video);

        if ($video->image_cover) {
            Storage::disk('public')->delete($video->image_cover);
        }
 
        $video->delete();

        return response()->json(['message' => 'Video eliminado exitosamente']);
    }

      public function panel(Course $course)
{
    $course->load('videos');

    return Inertia::render('Admin/Videos/VideosPanel', [
        'course' => $course,
        'videos' => $course->videos,
    ]);
}

public function list(Course $course)
{
    $videos = $course->videos()
        ->with(['teacher:id,firstname,lastname', 'course:id,title'])
        ->orderBy('order')
        ->get();

    return response()->json($videos);
}

        public function reorderVideos(Request $request, Course $course)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer'
        ]);

        foreach ($validated['order'] as $index => $videoId) {
            $course->videos()->where('id', $videoId)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }



    private function validateData(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_short' => 'nullable|string|max:255',
            'teacher_id' => 'required|integer',
            'course_id' => 'required|exists:courses,id',
            'comments' => 'nullable|string',
            'image_cover' => 'nullable|image|max:2048',
            'video_url' => 'required|string|max:255',
        ]);
    }


    private function checkOwnership(Course $course, Video $video)
    {
        if ($video->course_id !== $course->id) {
            abort(403, 'El video no pertenece a este curso');
        }
    }

}
