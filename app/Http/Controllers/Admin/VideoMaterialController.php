<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VideoMaterial; 
use Illuminate\Support\Facades\Storage;

class VideoMaterialController extends Controller
{
    // Listar materiales de un video
    public function index($video_id)
    {
        $materials = VideoMaterial::where('video_id', $video_id)->get();

        // Opcional: agrega la URL absoluta de la imagen para mostrar en frontend
        foreach ($materials as $mat) {
           // $mat->image = $mat->image ? Storage::disk('public')->url($mat->image) : null;
        }

        return response()->json($materials);
    }

    // Guardar nuevo material (uno por petición)
    public function store(Request $request, $videoId)
{
    $validated = $request->validate([
        'name'      => 'required|string|max:255',
        'quantity'  => 'nullable|string|max:255',
        'unit'      => 'nullable|string|max:255',
        'notes'     => 'nullable|string',
        'image'     => 'nullable|image|max:2048',
        'price'     => 'nullable|numeric|min:0',
       
    ]);

    $data = [
        'video_id' => $videoId,
        'name'     => $validated['name'],
        'quantity' => $validated['quantity'] ?? null,
        'unit'     => $validated['unit'] ?? null,
        'notes'    => $validated['notes'] ?? null,
        'price'    => $validated['price'] ?? null,
        'buy_link' => $validated['buy_link'] ?? null,
    ];

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('materials', 'public');
    }

    $created = \App\Models\VideoMaterial::create($data);

    return response()->json(['success' => true, 'material' => $created]);
}


    // Mostrar detalle de un material (opcional, si lo usas en el frontend)
    public function show($video_id, $id)
    {
        $material = VideoMaterial::where('video_id', $video_id)->findOrFail($id);

        // Si quieres mostrar la URL absoluta de la imagen
        $material->image = $material->image ? Storage::disk('public')->url($material->image) : null;

        return response()->json($material);
    }

    // Actualizar material (si implementas edición)
    public function update(Request $request, $video_id, $id)
{
    $material = VideoMaterial::where('video_id', $video_id)->findOrFail($id);

    $data = $request->validate([
        'name'      => 'required|string|max:255',
        'quantity'  => 'nullable|string|max:255',
        'unit'      => 'nullable|string|max:255',
        'notes'     => 'nullable|string',
        'image'     => 'nullable|image|max:2048',
        'price'     => 'nullable|numeric|min:0',
        'buy_link'  => 'nullable|url|max:255',
    ]);

    // Si mandas imagen, actualiza
    if ($request->hasFile('image')) {
        // Borra la anterior si existe
        if ($material->image) {
            Storage::disk('public')->delete($material->image);
        }
        $data['image'] = $request->file('image')->store('materials', 'public');
    }

    // Si pide quitar la imagen (y no se subió una nueva)
    if ($request->input('remove_image') && !$request->hasFile('image')) {
        if ($material->image) {
            Storage::disk('public')->delete($material->image);
        }
        $data['image'] = null;
    }

    $material->update($data);

    // Actualiza URL de imagen para el front
    $material->image = $material->image ? Storage::disk('public')->url($material->image) : null;

    return response()->json($material);
}


    // Eliminar material
    public function destroy($video_id, $id)
    {
        $material = VideoMaterial::where('video_id', $video_id)->findOrFail($id);

        // Elimina el archivo de la imagen del storage si existe
        if ($material->image) {
            Storage::disk('public')->delete($material->image);
        }

        $material->delete();

        return response()->json(['message' => 'Material eliminado']);
    }
}
