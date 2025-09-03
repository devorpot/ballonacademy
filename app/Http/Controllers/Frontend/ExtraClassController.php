<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ExtraClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExtraClassController extends Controller
{
    /**
     * Listado de clases extra visibles para usuarios.
     * - Solo activas (active = 1)
     * - Búsqueda por título, extracto, descripción y tags
     * - Filtro por categoría
     * - Orden seguro
     * - Paginación con appends
     */
    public function index(Request $request)
    {
        // Orden seguro
        $allowedSorts = ['id', 'title', 'category', 'order', 'created_at'];
        $sortBy  = in_array($request->get('sortBy'), $allowedSorts, true) ? $request->get('sortBy') : 'order';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'asc'; // por defecto ascendente para order

        // Filtros
        $q        = trim((string) $request->get('q', ''));
        $category = $request->get('category');
        $perPage  = (int) $request->get('per_page', 12);

        $query = ExtraClass::query()
            ->where('active', 1);

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                   ->orWhere('extract', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhere('tags', 'like', "%{$q}%");
            });
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }

        // Orden primario por 'order' y secundario por 'created_at' para consistencia visual
        if ($sortBy === 'order') {
            $query->orderBy('order', $sortDir)->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $extras = $query->paginate($perPage)->appends($request->all());

        // Adjuntar URLs calculadas sin depender de $appends del modelo
        $extras->getCollection()->each->append(['image_url', 'cover_url', 'video_url', 'subt_url']);

        return Inertia::render('Frontend/ExtraClasses/Index', [
            'extras'  => $extras,
            'filters' => [
                'q'        => $q,
                'category' => $category,
                'per_page' => $perPage,
                'sortBy'   => $sortBy,
                'sortDir'  => $sortDir,
            ],
            'categories' => $this->categories(),
        ]);
    }

    /**
     * Detalle de una clase extra.
     * - Solo accesible si la clase está activa
     * - Adjunta URLs calculadas para imagen, portada, video y subtítulos
     */
    public function show(ExtraClass $extra)
    {
        // Ocultar clases inactivas
        if ((int) $extra->active !== 1) {
            abort(404);
        }

        $extra->append(['image_url', 'cover_url', 'video_url', 'subt_url']);

        // Información útil para el reproductor en frontend
        $payload = [
            'id'          => $extra->id,
            'title'       => $extra->title,
            'extract'     => $extra->extract,
            'description' => $extra->description,
            'category'    => $extra->category,
            'tags'        => $extra->tags,
            'is_youtube'  => (int) $extra->is_youtube,
            'youtube_url' => $extra->youtube_url,
            'image_url'   => $extra->image_url,
            'cover_url'   => $extra->cover_url,
            'video_url'   => $extra->video_url,
            'subt_url'    => $extra->subt_url,
            'created_at'  => $extra->created_at,
        ];

        return Inertia::render('Frontend/ExtraClasses/Show', [
            'extra' => $payload,
        ]);
    }

    /**
     * Lista opcional de categorías para poblar selects en frontend.
     * Mantener en sincronía con el admin.
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
}
