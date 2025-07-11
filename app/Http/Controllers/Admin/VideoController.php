<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $validated['image_cover'] = $this->handleUpload($request, 'image_cover', 'videos/image_covers');
        $validated['video_path'] = $this->handleUpload($request, 'video_path', 'videos');

        Video::create($validated);

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

 
    public function update(Request $request, Video $video)
    {
        $validated = $this->validateData($request, false);

        $video->fill($validated);
        $video->image_cover = $this->updateFile($request, $video->image_cover, 'image_cover', 'videos/image_covers');
        $video->video_path = $this->updateFile($request, $video->video_path, 'video_path', 'videos');
        $video->save();

        return redirect()->route('admin.videos.index')->with('success', 'Video actualizado correctamente.');
    }




   public function deleteVideo(Course $course, Video $video)
    {
        
        $this->checkOwnership($course, $video);

        if ($video->video_path) {
            Storage::disk('public')->delete($video->video_path);
        }

        if ($video->image_cover) {
            Storage::disk('public')->delete($video->image_cover);
        }
 
        $video->delete();

        return response()->json(['message' => 'Video eliminado exitosamente']);
    }


   public function destroy(Course $course, Video $video)
    {
        
        

        $this->deleteFile($video->image_cover);
        $this->deleteFile($video->video_path);
 
        $video->delete();

      return redirect()->route('admin.videos.index')->with('success', 'Video eliminado exitosamente');
    }

      public function videosPanel(Course $course)
            {
                $course->load('videos');

                return Inertia::render('Admin/Videos/VideosPanel', [
                    'course' => $course,
                    'videos' => $course->videos,
                ]);
            }

    public function stream(Video $video): StreamedResponse
{
    if (!$video->video_path || !Storage::disk('private')->exists($video->video_path)) {
        abort(404, 'Video no encontrado');
    }

    return response()->stream(function () use ($video) {
        $stream = Storage::disk('private')->readStream($video->video_path);
        fpassthru($stream);
    }, 200, [
        'Content-Type' => 'video/mp4',
        'Content-Disposition' => 'inline; filename="' . basename($video->video_path) . '"',
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'no-cache',
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



 

private function validateData(Request $request, $includeFiles = true)
{
    $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'description_short' => 'nullable|string|max:255',
        'teacher_id' => 'required|integer',
        'course_id' => 'required|exists:courses,id',
        'comments' => 'nullable|string',
        'video_url' => 'nullable|string|max:255',
        'keep_image' => 'nullable|boolean',
        'keep_video' => 'nullable|boolean',
    ];

    if ($includeFiles) {
        if (!$request->boolean('keep_image') || $request->hasFile('image_cover')) {
            $rules['image_cover'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
        }

        if (!$request->boolean('keep_video') || $request->hasFile('video_path')) {
            $rules['video_path'] = 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v|max:51200';
        }
    }

    return $request->validate($rules);
}


    private function checkOwnership(Course $course, Video $video)
    {
        if ($video->course_id !== $course->id) {
            abort(403, 'El video no pertenece a este curso');
        }
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


}
