<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
  public function index(Course $course)
{
    return response()->json(
        $course->videos()->orderBy('order')->get()
    );
}
    public function store(Request $request, Course $course)
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('image_cover')) {
            $validated['image_cover'] = $request->file('image_cover')->store('videos/image_covers', 'public');
        }

        $validated['course_id'] = $course->id;
        $video = Video::create($validated);

        return response()->json(['message' => 'Video creado exitosamente', 'video' => $video]);
    }

    public function update(Request $request, Course $course, Video $video)
    {
        $this->checkOwnership($course, $video);

        $validated = $this->validateData($request);

        if ($request->hasFile('image_cover')) {
            if ($video->image_cover) {
                Storage::disk('public')->delete($video->image_cover);
            }
            $validated['image_cover'] = $request->file('image_cover')->store('videos/image_covers', 'public');
        }

        $video->update($validated);

        return response()->json(['message' => 'Video actualizado correctamente', 'video' => $video]);
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

    private function validateData(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_short' => 'nullable|string|max:255',
            'teacher_id' => 'required|integer|max:255',
            'comments' => 'nullable|string',
            'image_cover' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'video_url' => 'required|string|max:255',
        ]);
    }

    private function checkOwnership(Course $course, Video $video)
    {
        if ($video->course_id !== $course->id) {
            abort(403, 'El video no pertenece a este curso');
        }
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
}
