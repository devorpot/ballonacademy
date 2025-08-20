<?php 

// app/Http/Controllers/Admin/VideoResourcesController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VideoResourcesController extends Controller
{
    // Lista por video (devuelve JSON o Inertia según tu flujo)
    public function index(Video $video)
    {
        $resources = $video->resources()->latest()->get();
        return response()->json($resources);
        // return Inertia::render('Admin/Videos/Resources/Index', compact('video', 'resources'));
    }

    public function store(Request $request, Video $video)
    {
        $data = $this->validateData($request);

        // Subida según tipo
        $this->handleUploads($request, $data);

        $data['video_id'] = $video->id;

        $resource = VideoResource::create($data);

        return response()->json($resource, 201);
        // return redirect()->back()->with('success', 'Recurso creado');
    }

    public function show(VideoResource $resource)
    {
        return response()->json($resource);
    }

    public function update(Request $request, VideoResource $resource)
    {
        $data = $this->validateData($request, updating: true);

        // Si cambia el tipo, limpia campos previos
        if (isset($data['type']) && (int)$data['type'] !== (int)$resource->type) {
            $this->deleteStoredFiles($resource);
            $resource->file_src = $resource->video_src = $resource->img_src = null;
        }

        $this->handleUploads($request, $data, $resource);

        $resource->fill($data)->save();

        return response()->json($resource);
        // return redirect()->back()->with('success', 'Recurso actualizado');
    }

   public function destroy(Video $video, VideoResource $resource)
{
    // Validar que el recurso pertenece al video
    if ($resource->video_id !== $video->id) {
        return response()->json(['message' => 'Recurso no encontrado'], 404);
    }

    try {
        $this->deleteStoredFiles($resource);
        $resource->delete();

        return response()->json(['deleted' => true]);
    } catch (\Throwable $e) {
        \Log::error('Error al eliminar recurso', [
            'video_id'    => $video->id,
            'resource_id' => $resource->id,
            'error'       => $e->getMessage(),
        ]);
        return response()->json(['message' => 'Error al eliminar'], 500);
    }
}


    /* -------- Helpers -------- */
 

protected function validateData(Request $request, bool $updating = false): array
{
    // En store: archivos opcionales salvo el que corresponda por type.
    // En update: siempre opcionales.
    $basePresence = $updating ? 'sometimes' : 'nullable';

    return $request->validate([
        'title'       => ['required','string','max:150'],
        'description' => ['nullable','string'],
        'type'        => ['required', Rule::in([1,2,3])],
        'uploaded'    => ['nullable','date'],

        // Archivos (solo obligatorio el que corresponda)
        'file'  => [$basePresence, 'required_if:type,1', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        'video' => [$basePresence, 'required_if:type,2', 'file', 'mimetypes:video/mp4', 'max:204800'],
        'image' => [$basePresence, 'required_if:type,3', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
    ], [
        'type.in' => 'El tipo debe ser 1=file, 2=video o 3=image.',
    ]);
}


    protected function handleUploads(Request $request, array &$data, ?VideoResource $existing = null): void
    {
        // Asegura carpetas en storage/app/public
        $base = 'videos/resources';

        if ((int)$data['type'] === 1 && $request->hasFile('file')) {
            if ($existing?->file_src) Storage::disk('public')->delete($existing->file_src);
            $data['file_src'] = $request->file('file')->store("$base/files", 'public');
            $data['video_src'] = $data['img_src'] = null;
        }

        if ((int)$data['type'] === 2 && $request->hasFile('video')) {
            if ($existing?->video_src) Storage::disk('public')->delete($existing->video_src);
            $data['video_src'] = $request->file('video')->store("$base/videos", 'public');
            $data['file_src'] = $data['img_src'] = null;
        }

        if ((int)$data['type'] === 3 && $request->hasFile('image')) {
            if ($existing?->img_src) Storage::disk('public')->delete($existing->img_src);
            $data['img_src'] = $request->file('image')->store("$base/images", 'public');
            $data['file_src'] = $data['video_src'] = null;
        }
    }

    protected function deleteStoredFiles(VideoResource $resource): void
{
    foreach (['file_src', 'video_src', 'img_src'] as $col) {
        $path = $resource->$col;
        if ($path) {
            \Storage::disk('public')->delete($path);
        }
    }
}


}
