<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\ExtraClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use Inertia\Inertia;

class ExtraClassController extends Controller
{
    public function index(Request $request)
{
    $allowedSorts = ['id', 'title', 'category', 'active', 'order', 'created_at'];
    $sortBy  = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : null;
    $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

    // Filtros...
    $q        = trim((string) $request->get('q', ''));
    $active   = $request->get('active');
    $category = $request->get('category');
    $perPage  = (int) $request->get('per_page', 20);

    $query = ExtraClass::query();

    if ($q !== '') {
        $query->where(function ($qq) use ($q) {
            $qq->where('title', 'like', "%{$q}%")
               ->orWhere('extract', 'like', "%{$q}%")
               ->orWhere('description', 'like', "%{$q}%")
               ->orWhere('tags', 'like', "%{$q}%");
        });
    }

    if ($active !== null && in_array((int)$active, [1, 2], true)) {
        $query->where('active', (int)$active);
    }

    if (!empty($category)) {
        $query->where('category', $category);
    }

    // Si NO viene sort explícito, usa orden lógico por defecto
    if ($sortBy === null) {
        $query->orderBy('order', 'asc')
              ->orderBy('created_at', 'desc');
    } else {
        $query->orderBy($sortBy, $sortDir);
    }

    $extras = $query->paginate($perPage)->appends($request->all());

    $extras->getCollection()->each->append(['image_url', 'cover_url', 'video_url', 'subt_url']);

    return Inertia::render('Admin/ExtraClasses/Index', [
        'extras'  => $extras,
        'filters' => [
            'q'        => $q,
            'active'   => $active,
            'category' => $category,
            'per_page' => $perPage,
            'sortBy'   => $sortBy ?? 'order',
            'sortDir'  => $sortBy ? $sortDir : 'asc',
        ],
        'categories' => $this->categories(),
        'statuses'   => [1 => 'Activo', 2 => 'Inactivo'],
        'videoTypes' => [1 => 'YouTube', 2 => 'Archivo'],
    ]);
}


    public function create()
    {
        return Inertia::render('Admin/ExtraClasses/Create', [
            'categories' => $this->categories(),
            'statuses'   => [1 => 'Activo', 2 => 'Inactivo'],
            'videoTypes' => [1 => 'YouTube', 2 => 'Archivo'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request, includeFiles: true);

        // Subidas iniciales
        $validated['image']    = $this->handleUpload($request, 'image', 'extra_classes/images');
        $validated['cover']    = $this->handleUpload($request, 'cover', 'extra_classes/covers');
        $validated['video']    = $this->handleUpload($request, 'video', 'extra_classes/videos');
        $validated['subt_str'] = $this->handleUpload($request, 'subt_str', 'extra_classes/subtitles');

        $extra = ExtraClass::create($validated);

        return redirect()
            ->route('admin.extras.index')
            ->with('success', 'Clase extra creada exitosamente');
    }

    public function edit(ExtraClass $extra)
    {
        $extra->append(['image_url', 'cover_url', 'video_url', 'subt_url']);

        return Inertia::render('Admin/ExtraClasses/Edit', [
            'extra'      => $extra,
            'categories' => $this->categories(),
            'statuses'   => [1 => 'Activo', 2 => 'Inactivo'],
            'videoTypes' => [1 => 'YouTube', 2 => 'Archivo'],
        ]);
    }

    public function update(Request $request, ExtraClass $extra)
    {
        // Validación en edición (archivos opcionales)
        $validated = $this->validateData($request, includeFiles: false);

        $extra->fill($validated);

        // Imagen
        if ($request->hasFile('image')) {
            $this->deleteFile($extra->image);
            $extra->image = $request->file('image')->store('extra_classes/images', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteFile($extra->image);
            $extra->image = null;
        }

        // Cover
        if ($request->hasFile('cover')) {
            $this->deleteFile($extra->cover);
            $extra->cover = $request->file('cover')->store('extra_classes/covers', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $this->deleteFile($extra->cover);
            $extra->cover = null;
        }

        // Video (solo si is_youtube = 2)
        if ((int)$extra->is_youtube === 2) {
            if ($request->hasFile('video')) {
                $this->deleteFile($extra->video);
                $extra->video = $request->file('video')->store('extra_classes/videos', 'public');
            } elseif ($request->boolean('remove_video')) {
                $this->deleteFile($extra->video);
                $extra->video = null;
            }
        } else {
            // Si ahora es YouTube, limpiar video local si se pide
            if ($request->boolean('remove_video')) {
                $this->deleteFile($extra->video);
                $extra->video = null;
            }
        }

        // Subtítulos
        if ($request->hasFile('subt_str')) {
            $this->deleteFile($extra->subt_str);
            $extra->subt_str = $request->file('subt_str')->store('extra_classes/subtitles', 'public');
        } elseif ($request->boolean('remove_subt')) {
            $this->deleteFile($extra->subt_str);
            $extra->subt_str = null;
        }

        $extra->save();

        return redirect()
            ->route('admin.extras.index')
            ->with('success', 'Clase extra actualizada correctamente');
    }

    public function destroy(ExtraClass $extra)
    {
        $this->deleteFile($extra->image);
        $this->deleteFile($extra->cover);
        $this->deleteFile($extra->video);
        $this->deleteFile($extra->subt_str);

        $extra->delete();

        return redirect()
            ->route('admin.extras.index')
            ->with('success', 'Clase extra eliminada exitosamente');
    }

    public function show(ExtraClass $extra)
    {
        $extra->append(['image_url', 'cover_url', 'video_url', 'subt_url']);

        return Inertia::render('Admin/ExtraClasses/Show', [
            'extra' => $extra,
        ]);
    }

    /**
     * Validación. 
     * - includeFiles=true: en creación puede exigir archivos según is_youtube.
     * - includeFiles=false: en edición no exige archivos (se usan flags de borrado).
     */
    private function validateData(Request $request, bool $includeFiles = true): array
    {
        $isYoutube = (int) $request->input('is_youtube', 1); // 1=YouTube, 2=Archivo

        $rules = [
            'title'       => ['required', 'string', 'max:255'],
            'extract'     => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'tags'        => ['nullable', 'string', 'max:255'],
            'is_youtube'  => ['required', Rule::in([1, 2])],
            'youtube_url' => [$isYoutube === 1 ? 'required' : 'nullable', 'url', 'max:255'],
            'active'      => ['required', Rule::in([1, 2])],
            'order'       => ['nullable', 'integer', 'min:0'],

            // Flags de borrado
            'remove_image' => ['sometimes', 'boolean'],
            'remove_cover' => ['sometimes', 'boolean'],
            'remove_video' => ['sometimes', 'boolean'],
            'remove_subt'  => ['sometimes', 'boolean'],
        ];

        if ($includeFiles) {
            // En creación: si es archivo, video requerido; si es YouTube, video no requerido.
            $rules['image']    = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'];
            $rules['cover']    = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:6144'];
            $rules['video']    = [$isYoutube === 2 ? 'required' : 'nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska', 'max:512000']; // ~500MB
            $rules['subt_str'] = ['nullable', 'file', 'mimes:vtt,srt', 'max:2048'];
        } else {
            // En edición: archivos opcionales
            $rules['image']    = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'];
            $rules['cover']    = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:6144'];
            $rules['video']    = ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska', 'max:512000'];
            $rules['subt_str'] = ['nullable', 'file', 'mimes:vtt,srt', 'max:2048'];
        }

        return $request->validate($rules);
    }

    private function handleUpload(Request $request, string $field, string $path): ?string
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path, 'public');
        }
        return null;
    }

    private function deleteFile(?string $filePath): void
    {
        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }
    }

    /**
     * Lista opcional de categorías para poblar selects.
     * Ajusta a tus necesidades.
     */
    private function categories(): array
    {
        return [
            'Decoración',
            'Globoflexia',
            'Negocio',
            'Marketing',
            'Evento',
            'Otros',
        ];
    }

      public function reorder(Request $request)
    {
        // Opción A: array simple de IDs en el orden deseado
        if ($request->has('ids')) {
            $data = $request->validate([
                'ids'   => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct'],
            ]);

            $ids = $data['ids'];

            DB::transaction(function () use ($ids) {
                // setea order = 1..n según la posición
                foreach ($ids as $idx => $id) {
                    ExtraClass::where('id', $id)->update(['order' => $idx + 1]);
                }
            });

            return back()->with('success', 'Orden actualizado correctamente.');
        }

        // Opción B: array de objetos con id + order explícito
        if ($request->has('items')) {
            $data = $request->validate([
                'items'        => ['required', 'array', 'min:1'],
                'items.*.id'   => ['required', 'integer', 'distinct'],
                'items.*.order'=> ['required', 'integer', 'min:1'],
            ]);

            DB::transaction(function () use ($data) {
                $rows = collect($data['items'])
                    ->map(fn ($i) => ['id' => (int)$i['id'], 'order' => (int)$i['order']])
                    ->all();

                // upsert por eficiencia
                ExtraClass::upsert($rows, ['id'], ['order']);
            });

            return back()->with('success', 'Orden actualizado correctamente.');
        }

        return back()->with('error', 'Payload inválido: envía "ids" o "items".');
    }

}
