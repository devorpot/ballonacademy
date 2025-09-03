<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Models\Course;
use App\Models\Video;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;

class ActivityController extends Controller
{
    /**
     * Vista inicial (Inertia). No carga listados pesados;
     * el grid se alimenta con axios contra list().
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/Activities/Index', [
            'filters' => [
                'user_id' => $request->input('user_id'),
                'perPage' => (int) $request->input('perPage', 30),
                'sortBy'  => $request->input('sortBy', 'activities.created_at'),
                'sortDir' => $request->input('sortDir', 'desc'),
            ],
        ]);
    }

    /**
     * Endpoint JSON para data-table (axios).
     * Soporta filtros, orden y paginación.
     */
  public function list(Request $request)
{
    $q = $this->baseQuery(); // ahora ya incluye JOINs

    // ====== Búsqueda (agrupada) ======
    if ($search = trim((string) $request->input('q', ''))) {
        $q->where(function ($w) use ($search) {
            $w->where('activities.description', 'like', "%{$search}%")
              ->orWhere('activities.type', 'like', "%{$search}%")
              ->orWhere('u.name', 'like', "%{$search}%")
              ->orWhere('u.email', 'like', "%{$search}%")
              ->orWhere('c.title', 'like', "%{$search}%")
              ->orWhere('v.title', 'like', "%{$search}%")
              ->orWhere('e.title', 'like', "%{$search}%");
        });
    }

    // ====== Filtros ======
    if ($request->filled('user_id'))       $q->where('activities.user_id', (int)$request->input('user_id'));
    if ($request->filled('course_id'))     $q->where('activities.course_id', (int)$request->input('course_id'));
    if ($request->filled('video_id'))      $q->where('activities.video_id', (int)$request->input('video_id'));
    if ($request->filled('evaluation_id')) $q->where('activities.evaluation_id', (int)$request->input('evaluation_id'));
    if ($request->filled('type'))          $q->where('activities.type', $request->input('type'));

    // ====== Orden (whitelist) ======
    $sortBy  = $request->input('sortBy', 'activities.created_at');
    $sortDir = strtolower($request->input('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';

    $allowed = [
        'activities.id'         => 'activities.id',
        'activities.created_at' => 'activities.created_at',
        'users.name'            => 'u.name',     // mapeo a alias
        'courses.title'         => 'c.title',
        'videos.title'          => 'v.title',
        'evaluations.title'     => 'e.title',
    ];
    $orderColumn = $allowed[$sortBy] ?? 'activities.created_at';
    $q->orderBy($orderColumn, $sortDir);

    // ====== Paginación ======
    $perPage = (int) $request->input('perPage', 30);
    $activities = $q->paginate($perPage)->appends($request->query());

    return response()->json($activities);
}

/**
 * Query base con SELECT + JOINs + with() para relaciones.
 */


    /**
     * Ver detalle de una actividad (Inertia).
     * Si quieres, puede responder JSON si se pide con Accept: application/json.
     */
    public function show(Activity $activity, Request $request)
    {
        $activity->load([
            'user:id,name,email',
            'course:id,title',
            'video:id,title',
            'evaluation:id,title',
        ]);

        if ($request->wantsJson()) {
            return response()->json($activity);
        }

        return Inertia::render('Admin/Activities/Show', [
            'activity' => [
                'id'          => $activity->id,
                'user'        => $activity->user,
                'course'      => $activity->course,
                'video'       => $activity->video,
                'evaluation'  => $activity->evaluation,
                'type'        => $activity->type,
                'description' => $activity->description,
                'created_at'  => $activity->created_at,
            ],
        ]);
    }

    /**
     * Autocomplete remoto de usuarios para el filtro (axios).
     */
    public function usersSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') return response()->json([]);

        $users = User::query()
            ->select('id','name','email')
            ->where(function($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('email','like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(12)
            ->get();

        return response()->json($users);
    }

    /**
     * Query base reusable.
     */
  private function baseQuery(): \Illuminate\Database\Eloquent\Builder
{
    return Activity::query()
        // JOINs para ordenar/filtrar por columnas relacionadas
        ->leftJoin('users as u', 'u.id', '=', 'activities.user_id')
        ->leftJoin('courses as c', 'c.id', '=', 'activities.course_id')
        ->leftJoin('videos as v', 'v.id', '=', 'activities.video_id')
        ->leftJoin('evaluations as e', 'e.id', '=', 'activities.evaluation_id')
        // seleccionar columnas del modelo base para evitar ambigüedad
        ->select([
            'activities.*',
        ])
        // eager loading para el JSON de salida
        ->with([
            'user:id,name,email',
            'course:id,title',
            'video:id,title',
            'evaluation:id,title',
        ]);
}

}
