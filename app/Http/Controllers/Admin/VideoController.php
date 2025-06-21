<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    // Mostrar todos los videos del curso
    public function index(Course $course)
    {
        return response()->json($course->videos);
    }

    // Crear un nuevo video
    public function store(Request $request, Course $course)
    {
        // Validar los datos del formulario
        $validated = $this->validateData($request);

        // Subir la imagen de portada si se proporciona
        if ($request->hasFile('image_cover')) {
            $validated['image_cover'] = $request->file('image_cover')->store('videos/image_covers', 'public');
        }

        // Agregar el ID del curso al video
        $validated['course_id'] = $course->id;

        // Crear el nuevo video en la base de datos
        $video = Video::create($validated);

        return redirect()->route('admin.courses.edit', $course->id)
            ->with('success', 'Video creado exitosamente');
    }

    // Actualizar un video existente
    public function update(Request $request, Course $course, Video $video)
    {
        // Validar los datos del formulario
        $validated = $this->validateData($request);

        // Si se sube una nueva imagen de portada, eliminar la antigua y guardarla
        if ($request->hasFile('image_cover')) {
            if ($video->image_cover) {
                // Eliminar la imagen antigua si existe
                Storage::disk('public')->delete($video->image_cover);
            }
            $validated['image_cover'] = $request->file('image_cover')->store('videos/image_covers', 'public');
        }

        // Actualizar el video en la base de datos
        $video->update($validated);

        return redirect()->route('admin.courses.edit', $course->id)
            ->with('success', 'Video actualizado correctamente');
    }

    // Eliminar un video
    public function destroy(Course $course, Video $video)
    {
        // Verificar la propiedad del video
        $this->checkOwnership($course, $video);

        // Eliminar la imagen de portada si existe
        if ($video->image_cover) {
            Storage::disk('public')->delete($video->image_cover);
        }

        // Eliminar el video de la base de datos
        $video->delete();

        return redirect()->route('admin.courses.edit', $course->id)
            ->with('success', 'Video eliminado exitosamente');
    }

    // Validar los datos recibidos
    private function validateData(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',  // Título requerido
            'video_url' => 'required|string|max:255', // URL del video requerida
            'description' => 'nullable|string',  // Descripción opcional
            'description_short' => 'nullable|string|max:255',  // Descripción corta opcional
            'comments' => 'nullable|string',  // Comentarios opcionales
            'image_cover' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',  // Imagen de portada opcional
        ]);
    }

    // Verificar que el video pertenezca al curso correcto
    private function checkOwnership(Course $course, Video $video)
    {
        if ($video->course_id !== $course->id) {
            abort(403, 'El video no pertenece a este curso');
        }
    }

    // Eliminar la imagen de portada de un video
    public function deleteCover(Course $course, Video $video)
    {
        // Verificar la propiedad del video
        $this->checkOwnership($course, $video);

        // Eliminar la imagen de portada
        if ($video->image_cover) {
            Storage::disk('public')->delete($video->image_cover);
            $video->image_cover = null;
            $video->save();
        }

        return response()->json(['message' => 'Cover eliminado correctamente']);
    }
}
