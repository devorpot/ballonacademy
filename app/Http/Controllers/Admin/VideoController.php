<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Models\Activity;
use App\Enums\ActivityType; 

class VideoController extends Controller
{
public function index()
{
    $videos = \App\Models\Video::query()
        ->with([
            'course:id,title',
            'teacher:id,firstname,lastname,user_id',
            'teacher.user:id,name',
        ])
        ->latest('id')
        ->get();

    return \Inertia\Inertia::render('Admin/Videos/Index', [
        'videos' => $videos,
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


 

public function create(Request $request)
{
    $courseId = $request->integer('course_id'); // null si no viene

    $courses  = Course::select('id', 'title')->orderBy('title')->get();
    $teachers = Teacher::select('id', 'firstname', 'lastname')->orderBy('firstname')->get();

    // Si viene course_id, filtra; si no, devuelve vacío para forzar selección primero
    $lessons = $courseId
        ? Lesson::select('id', 'title')
            ->where('course_id', $courseId)
            ->orderBy('order')
            ->orderBy('title')
            ->get()
        : collect(); // []

    return Inertia::render('Admin/Videos/Create', [
        'courses'          => $courses,
        'teachers'         => $teachers,
        'lessons'          => $lessons,
        'selected_course'  => $courseId, // útil para preseleccionar en el formulario
    ]);
}



public function store(Request $request)
{
    $validated = $this->validateData($request);

    // Files
    $validated['image_cover'] = $this->handleUpload($request, 'image_cover', 'videos/image_covers');
    $validated['video_path']  = $this->handleUpload($request, 'video_path', 'videos');

    // Orden dentro del curso
    $maxOrder = Video::where('course_id', $validated['course_id'])->max('order');
    $validated['order'] = $maxOrder ? $maxOrder + 1 : 1;

    // Extras del archivo de video
    if ($file = $request->file('video_path')) {
        $validated['video_path'] = $file->store('videos', 'public');
        $validated['size']       = round($file->getSize() / 1048576, 2) . ' MB';
        $validated['duration']   = $this->getDurationFromFile($file->getRealPath()) ?? '00:00:00';
    }

    // Nuevos flags + fecha publicada (defaults)
    $validated['active']         = $request->boolean('active', true);
    $validated['private']        = $request->boolean('private', false);
    $validated['published']      = $request->boolean('published', true);
    $validated['published_date'] = $request->filled('published_date')
        ? \Illuminate\Support\Carbon::parse($request->input('published_date'))->toDateString()
        : now()->toDateString();

    DB::transaction(function () use ($request, &$validated) {
        $video = Video::create($validated);

        // Si se eligió lección, crear vínculo en lesson_videos
        if ($request->filled('lesson_id')) {
            $nextOrder = (int) LessonVideo::where('lesson_id', $request->lesson_id)->max('order');
            LessonVideo::create([
                'lesson_id' => (int) $request->lesson_id,
                'video_id'  => $video->id,
                'course_id' => (int) $validated['course_id'],
                'order'     => $nextOrder + 1,
                'active'    => true,
            ]);
        }

        $validated['__created_video_id'] = $video->id;
    });

    return redirect()
        ->route('admin.courses.videos.panel', ['course' => $validated['course_id']])
        ->with('success', 'Video creado correctamente.');
}




public function edit(Request $request, Video $video)
{
    $video->load('captions');

    $courses  = Course::select('id', 'title')->orderBy('title')->get();
    $teachers = Teacher::select('id', 'firstname', 'lastname')->orderBy('firstname')->get();

    // Si pasan ?course_id=, úsalo; si no, usa el del video
    $courseId = $request->integer('course_id', $video->course_id);
    $course   = Course::find($courseId);

    $lessons = $course
        ? Lesson::select('id', 'title')
            ->where('course_id', $course->id)
            ->orderBy('order')
            ->orderBy('title')
            ->get()
        : collect();

    return Inertia::render('Admin/Videos/Edit', [
        'video'   => [
            ...$video->toArray(),
            'stream_url' => route('admin.videos.stream', ['video' => $video->id]),
            'captions'   => $video->captions->map(fn($cap) => [
                'id'     => $cap->id,
                'lang'   => $cap->lang,
                'file'   => $cap->file,
                'label'  => $cap->lang,
                'default'=> $cap->lang === 'es',
            ]),
        ],
        'courses'         => $courses,
        'teachers'        => $teachers,
        'course'          => $course,         // curso actualmente seleccionado (puede cambiar por query)
        'lessons'         => $lessons,        // lecciones del curso seleccionado
        'selected_course' => $courseId,       // útil si quieres usarlo en el form
    ]);
}



public function update(Request $request, Video $video)
{
    $validated = $this->validateData($request, false);

    DB::transaction(function () use ($request, $video, $validated) {
        // Actualizar campos textuales/base
        $video->fill($validated);

        // Imagen
        if ($request->hasFile('image_cover')) {
            $this->deleteFile($video->image_cover);
            $video->image_cover = $request->file('image_cover')->store('videos/image_covers', 'public');
        } elseif (!$request->boolean('keep_image')) {
            $this->deleteFile($video->image_cover);
            $video->image_cover = null;
        }

        // Video
        if ($request->hasFile('video_path')) {
            $this->deleteFile($video->video_path);
            $file = $request->file('video_path');
            $video->video_path = $file->store('videos', 'public');
            $video->size       = round($file->getSize() / 1048576, 2) . ' MB';
            $video->duration   = $this->getDurationFromFile($file->getRealPath()) ?? '00:00:00';
        } elseif (!$request->boolean('keep_video')) {
            $this->deleteFile($video->video_path);
            $video->video_path = null;
            $video->size       = null;
            $video->duration   = null;
        }

        // Flags: si el checkbox no viene, queremos poder forzarlo a false.
        // Usamos has() para distinguir "no llegó" vs "llegó en false".
        if ($request->has('active'))    { $video->active    = $request->boolean('active'); }
        if ($request->has('private'))   { $video->private   = $request->boolean('private'); }
        if ($request->has('published')) { $video->published = $request->boolean('published'); }

        // Fecha publicada: si viene vacía, usamos hoy; si no viene, conservamos
        if ($request->has('published_date')) {
            $video->published_date = $request->filled('published_date')
                ? \Illuminate\Support\Carbon::parse($request->input('published_date'))->toDateString()
                : now()->toDateString();
        }

        $video->save();

        // Sincronizar vínculo lesson_videos según lesson_id (opcional: una sola lección)
        if ($request->filled('lesson_id')) {
            LessonVideo::where('video_id', $video->id)
                ->where('lesson_id', '!=', (int) $request->lesson_id)
                ->delete();

            $pivot = LessonVideo::firstOrNew([
                'lesson_id' => (int) $request->lesson_id,
                'video_id'  => $video->id,
            ]);

            $pivot->course_id = (int) ($validated['course_id'] ?? $video->course_id);

            if (!$pivot->exists || !$pivot->order) {
                $nextOrder    = (int) LessonVideo::where('lesson_id', $request->lesson_id)->max('order');
                $pivot->order = $nextOrder + 1;
            }

            $pivot->active = true;
            $pivot->save();
        } else {
            // Si no se envió lesson_id, remover cualquier vínculo existente
            LessonVideo::where('video_id', $video->id)->delete();
        }
    });

    return redirect()
        ->route('admin.courses.videos.panel', ['course' => $validated['course_id'] ?? $video->course_id])
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
    $videos = Video::query()
        ->where('course_id', $course->id)
        ->orderBy('order') // opcional si tienes columna de orden
        ->get();

    return Inertia::render('Admin/Videos/VideosPanel', [
        'course' => $course,
        'videos' => $videos,
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
        'order'   => 'required|array',
        'order.*' => 'integer',
    ]);

    $ids = array_values($validated['order']); // [videoId1, videoId2, ...]

    DB::transaction(function () use ($ids, $course) {
        foreach ($ids as $index => $videoId) {
            // Actualiza directamente la tabla videos, no la relación
            Video::query()
                ->where('id', $videoId)
                ->where('course_id', $course->id)
                ->update(['order' => $index + 1]);
        }
    });

    return response()->json(['ok' => true], 200);
}

 

 

private function validateData(Request $request, $includeFiles = true)
{
    $rules = [
        'title'              => 'required|string|max:255',
        'description'        => 'nullable|string',
        'description_short'  => 'nullable|string|max:255',
        'teacher_id'         => 'required|integer|exists:teachers,id',
        'course_id'          => 'required|exists:courses,id',
        'lesson_id'          => [
            'nullable',
            'integer',
            Rule::exists('lessons', 'id')->where(function ($q) use ($request) {
                return $q->where('course_id', $request->input('course_id'));
            }),
        ],
        'comments'           => 'nullable|string',
        'keep_image'         => 'nullable|boolean',
        'keep_video'         => 'nullable|boolean',
        'size'               => 'nullable|string|max:20',
        'duration'           => 'nullable|string|max:20',

        // Nuevos campos
        'active'             => 'nullable|boolean',
        'private'            => 'nullable|boolean',
        'published'          => 'nullable|boolean',
        'published_date'     => 'nullable|date',
    ];

    if ($includeFiles) {
        if (!$request->boolean('keep_image') || $request->hasFile('image_cover')) {
            $rules['image_cover'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:10222';
        }
        if (!$request->boolean('keep_video') || $request->hasFile('video_path')) {
            $rules['video_path'] = 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-m4v|max:1211200';
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
 