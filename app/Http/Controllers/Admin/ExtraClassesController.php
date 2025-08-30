<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtraClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ExtraClassesController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'title', 'category', 'active', 'order', 'created_at'];
        $sortBy  = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : 'created_at';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

        $q        = trim((string) $request->get('q', ''));
        $active   = $request->get('active');   // 0 | 1 | null
        $category = $request->get('category'); // string | null

        $query = ExtraClass::query();

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                   ->orWhere('extract', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhere('tags', 'like', "%{$q}%");
            });
        }

        if ($active !== null && $active !== '') {
            $query->where('active', (int) $active);
        }

        if ($category !== null && $category !== '') {
            $query->where('category', $category);
        }

        $extraClasses = $query->orderBy($sortBy, $sortDir)->get();

        return Inertia::render('Admin/ExtraClasses/Index', [
            'extraClasses' => $extraClasses,
            'filters' => [
                'q'        => $q,
                'active'   => $active,
                'category' => $category,
                'sortBy'   => $sortBy,
                'sortDir'  => $sortDir,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ExtraClasses/Create', [
            // Si deseas poblar selects (categorías), pásalos aquí
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request, true);

        // Subidas iniciales
        $validated['image']    = $this->handleUpload($request, 'image', 'extra_classes/images');
        $validated['cover']    = $this->handleUpload($request, 'cover', 'extra_classes/covers');
        $validated['video']    = $this->handleUpload($request, 'video', 'extra_classes/videos');
        $validated['subt_str'] = $this->handleUpload($request, 'subt_str', 'extra_classes/subtitles');

        ExtraClass::create($validated);

        return redirect()
            ->route('admin.extra-classes.index')
            ->with('success', 'Clase extra creada exitosamente');
    }

    public function edit(ExtraClass $extraClass)
    {
        return Inertia::render('Admin/ExtraClasses/Edit', [
            'extraClass' => $extraClass,
        ]);
    }

    public function update(Request $request, ExtraClass $extraClass)
    {
        $validated = $this->validateData($request, false);

        $extraClass->fill($validated);

        // Imagen
        if ($request->hasFile('image')) {
            $this->deleteFile($extraClass->image);
            $extraClass->image = $request->file('image')->store('extra_classes/images', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteFile($extraClass->image);
            $extraClass->image = null;
        }

        // Cover/portada
        if ($request->hasFile('cover')) {
            $this->deleteFile($extraClass->cover);
            $extraClass->cover = $request->file('cover')->store('extra_classes/covers', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $this->deleteFile($extraClass->cover);
            $extraClass->cover = null;
        }

        // Video
        if ($request->hasFile('video')) {
            $this->deleteFile($extraClass->video);
            $extraClass->video = $request->file('video')->store('extra_classes/videos', 'public');
        } elseif ($request->boolean('remove_video')) {
            $this->deleteFile($extraClass->video);
            $extraClass->video = null;
        }

        // Subtítulos
        if ($request->hasFile('subt_str')) {
            $this->deleteFile($extraClass->subt_str);
            $extraClass->subt_str = $request->file('subt_str')->store('extra_classes/subtitles', 'public');
        } elseif ($request->boolean('remove_subt')) {
            $this->deleteFile($extraClass->subt_str);
            $extraClass->subt_str = null;
        }

        // Si es YouTube, puedes limpiar la ruta de video local si corresponde
        if ($extraClass->is_youtube && $request->boolean('clear_local_video')) {
            $this->deleteFile($extraClass->video);
            $extraClass->video = null;
        }

        $extraClass->save();

        return redirect()
            ->route('admin.extra-classes.index')
            ->with('success', 'Clase extra actualizada correctamente');
    }

    public function destroy(ExtraClass $extraClass)
    {
        $this->deleteFile($extraClass->image);
        $this->deleteFile($extraClass->cover);
        $this->deleteFile($extraClass->video);
        $this->deleteFile($extraClass->subt_str);

        $extraClass->delete();

        return redirect()
            ->route('admin.extra-classes.index')
            ->with('success', 'Clase extra eliminada exitosamente');
    }

    public function show(ExtraClass $extraClass)
    {
        return Inertia::render('Admin/ExtraClasses/Show', [
            'extraClass' => $extraClass,
        ]);
    }

    /**
     * $includeFiles=true: valida archivos en creación.
     * $includeFiles=false: en edición no exige archivos (permite flags remove_*).
     */
    private function validateData(Request $request, bool $includeFiles = true): array
    {
        $rules = [
            'title'       => ['required', 'string', 'max:255'],
            'extract'     => ['nullable', 'string'],
            'description' => ['nullable', 'string'],

            'is_youtube'  => ['sometimes', 'boolean'],
            'youtube_url' => ['nullable', 'url', 'max:500', Rule::requiredIf(fn () => (bool) $request->boolean('is_youtube'))],

            'category'    => ['nullable', 'string', 'max:100'],
            'tags'        => ['nullable', 'string', 'max:500'],

            'active'      => ['sometimes', 'in:0,1'],
            'order'       => ['sometimes', 'integer', 'min:0'],

            // Flags de borrado
            'remove_image' => ['sometimes', 'boolean'],
            'remove_cover' => ['sometimes', 'boolean'],
            'remove_video' => ['sometimes', 'boolean'],
            'remove_subt'  => ['sometimes', 'boolean'],

            // Flag opcional para limpiar video local si se usa YouTube
            'clear_local_video' => ['sometimes', 'boolean'],
        ];

        if ($includeFiles) {
            $rules['image']    = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'];
            $rules['cover']    = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'];
            $rules['video']    = ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:102400']; // ~100MB
            $rules['subt_str'] = ['nullable', 'file', 'mimes:vtt,srt', 'max:2048'];
        } else {
            // En edición, si vienen, se validan; si no, no son requeridos
            $rules['image']    = ['sometimes', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'];
            $rules['cover']    = ['sometimes', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'];
            $rules['video']    = ['sometimes', 'file', 'mimes:mp4,webm,ogg', 'max:102400'];
            $rules['subt_str'] = ['sometimes', 'file', 'mimes:vtt,srt', 'max:2048'];
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
}
