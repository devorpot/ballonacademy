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

use App\Models\Activity;
use App\Enums\ActivityType; 

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

    // Obtener el siguiente valor de orden en el curso
    $maxOrder = Video::where('course_id', $validated['course_id'])->max('order');
    $validated['order'] = $maxOrder ? $maxOrder + 1 : 1;


    $videoFile = $request->file('video_path');

    if ($videoFile) {
        $validated['video_path'] = $videoFile->store('videos', 'public');

        // Calcular el tamaño en MB
        $validated['size'] = round($videoFile->getSize() / 1048576, 2) . ' MB';

        // Intentar extraer duración con FFmpeg si tienes instalado, o usa un valor por defecto
        $validated['duration'] = $this->getDurationFromFile($videoFile->getRealPath()) ?? '00:00:00';
    }


$video = Video::create($validated);

   // return redirect()->route('admin.videos.index')->with('success', 'Video creado correctamente.');
      return redirect()->route('admin.courses.videos.panel', ['course' => $video->course_id])
        ->with('success', 'Video creado correctamente.');
}

public function edit(Video $video)
{
    $video->load('captions'); // Cargar subtítulos

    $courses = Course::select('id', 'title')->get();
    $teachers = Teacher::select('id', 'firstname', 'lastname')->get();
    $course = $video->course; // Relación en el modelo Video

    return Inertia::render('Admin/Videos/Edit', [
        'video' => [
            ...$video->toArray(),
            'stream_url' => route('admin.videos.stream', ['video' => $video->id]),
            'captions' => $video->captions->map(fn($cap) => [
                'id'     => $cap->id,
                'lang'   => $cap->lang,
                'file'   => $cap->file,
                'label'  => $cap->lang, // opcional
                'default' => $cap->lang === 'es',
            ])
        ],
        'courses' => $courses,
        'teachers' => $teachers,
        'course' => $course
    ]);
}


public function update(Request $request, Video $video)
{
    $validated = $this->validateData($request, false);
    $video->fill($validated);

    // Imagen
    if ($request->hasFile('image_cover')) {
        $this->deleteFile($video->image_cover);
        $video->image_cover = $request->file('image_cover')->store('videos/image_covers', 'public');
    } elseif (!$request->boolean('keep_image')) {
        $this->deleteFile($video->image_cover);
        $video->image_cover = null;
    }

    // Video (ya lo tienes bien)
    if ($request->hasFile('video_path')) {
        $this->deleteFile($video->video_path);
        $file = $request->file('video_path');
        $video->video_path = $file->store('videos', 'public');
        $video->size = round($file->getSize() / 1048576, 2) . ' MB';
        $video->duration = $this->getDurationFromFile($file->getRealPath()) ?? '00:00:00';
    } elseif (!$request->boolean('keep_video')) {
        $this->deleteFile($video->video_path);
        $video->video_path = null;
        $video->size = null;
        $video->duration = null;
    }

    $video->save();

    return redirect()->route('admin.courses.videos.panel', ['course' => $video->course_id])
        ->with('success', 'Video actualizado correctamente.');
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
        if (!$video->video_path || !Storage::disk('public')->exists($video->video_path)) {
            abort(404, 'Video no encontrado');
        }

        return response()->stream(function () use ($video) {
            $stream = Storage::disk('public')->readStream($video->video_path);
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


          \Log::info('Reordenando videos', $request->all());
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer'
        ]);

        foreach ($validated['order'] as $index => $videoId) {
            $course->videos()->where('id', $videoId)->update(['order' => $index + 1]);
        }

           return response()->json($request->all(), 200);
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
         
        
        'keep_image' => 'nullable|boolean',
        'keep_video' => 'nullable|boolean',
        'size' => 'nullable|string|max:20',
        'duration' => 'nullable|string|max:20',
    ];

    if ($includeFiles) {
        if (!$request->boolean('keep_image') || $request->hasFile('image_cover')) {
            $rules['image_cover'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
        }

        if (!$request->boolean('keep_video') || $request->hasFile('video_path')) {
            $rules['video_path'] = 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v|max:511200';
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

    private function getDurationFromFile($filePath)
{
    try {
        $output = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 \"$filePath\"");
        $seconds = floatval($output);

        if ($seconds > 0) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = floor($seconds % 60);

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
    } catch (\Exception $e) {
        // Log error si lo deseas
    }

    return null;
}


  public function getByCourse(Request $request, Course $course)
    {
        $q = trim((string) $request->query('q', ''));
        $lessonId = $request->query('lesson_id');

        $query = Video::query()
            ->where('course_id', $course->id);

        if ($q !== '') {
            $query->where('title', 'LIKE', "%{$q}%");
        }

        // excluir ya vinculados a la lección, si se provee
        if ($lessonId) {
            $query->whereDoesntHave('lessons', function ($sub) use ($lessonId) {
                $sub->where('lessons.id', $lessonId);
            });
        }

        // limita resultados para autocomplete
        $videos = $query
            ->select('id', 'title', 'image_cover', 'duration', 'size')
            ->orderBy('title')
            ->limit(15)
            ->get()
            ->map(function ($v) {
                return [
                    'id'       => $v->id,
                    'title'    => $v->title,
                    'image'    => $v->image_cover ? asset('storage/' . $v->image_cover) : null,
                    'duration' => $v->duration, // en segundos o formato que manejes
                    'size'     => $v->size,     // bytes/MB, según tu columna
                ];
            });

        return response()->json([
            'data' => $videos,
        ]);
    }


}
