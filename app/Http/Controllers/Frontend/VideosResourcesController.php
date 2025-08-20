<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoResource;
use Inertia\Inertia;
use Illuminate\Http\Request;

class VideosResourcesController extends Controller
{
    /**
     * Lista de recursos de un video.
     */
    public function index(Video $video)
    {
        $resources = $video->resources()
            ->latest()
            ->get()
            ->map(function (VideoResource $res) {
                return [
                    'id'          => $res->id,
                    'title'       => $res->title,
                    'description' => $res->description,
                    'type'        => $res->type,
                    'type_label'  => match ($res->type) {
                        1 => 'Archivo',
                        2 => 'Video',
                        3 => 'Imagen',
                        default => 'Recurso'
                    },
                    'file_src'    => $res->file_src ? asset("storage/{$res->file_src}") : null,
                    'video_src'   => $res->video_src ? asset("storage/{$res->video_src}") : null,
                    'img_src'     => $res->img_src ? asset("storage/{$res->img_src}") : null,
                    'uploaded'    => optional($res->uploaded)->format('Y-m-d'),
                ];
            });

        return Inertia::render('Frontend/Videos/Resources/Index', [
            'video'     => $video->only(['id', 'title']),
            'resources' => $resources,
        ]);
    }

    /**
     * Mostrar detalle de un recurso.
     */
    public function show(Video $video, VideoResource $resource)
    {
        // Validar pertenencia
        abort_unless($resource->video_id === $video->id, 404);

        $data = [
            'id'          => $resource->id,
            'title'       => $resource->title,
            'description' => $resource->description,
            'type'        => $resource->type,
            'type_label'  => match ($resource->type) {
                1 => 'Archivo',
                2 => 'Video',
                3 => 'Imagen',
                default => 'Recurso'
            },
            'file_src'    => $resource->file_src ? asset("storage/{$resource->file_src}") : null,
            'video_src'   => $resource->video_src ? asset("storage/{$resource->video_src}") : null,
            'img_src'     => $resource->img_src ? asset("storage/{$resource->img_src}") : null,
            'uploaded'    => optional($resource->uploaded)->format('Y-m-d'),
        ];

        return Inertia::render('Frontend/Videos/Resources/Show', [
            'video'   => $video->only(['id', 'title']),
            'resource'=> $data,
        ]);
    }
}
