<?php

// app/Http/Controllers/Frontend/DashboardController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VideoActivity;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $userId = $user->id;

        $courses = $user->courses()
            ->withCount('videos')
            ->get(['courses.id','courses.title','courses.image_cover']);

        $lastByVideo = VideoActivity::selectRaw('video_id, MAX(updated_at) as last_seen')
            ->where('user_id', $userId)
            ->where('event', 'ended')
            ->groupBy('video_id');

        $videos = Video::query()
            ->select([
                'videos.id','videos.title','videos.image_cover','videos.duration',
                'videos.lesson_id','videos.size','videos.course_id','va.last_seen',
            ])
            ->joinSub($lastByVideo, 'va', fn($join) => $join->on('va.video_id','=','videos.id'))
            ->with('course:id,title')
            ->orderByDesc('va.last_seen')
            ->limit(12)
            ->get()
            ->map(fn($video) => [
                'id'          => $video->id,
                'title'       => $video->title,
                'image_cover' => $video->image_cover,
                'duration'    => $video->duration,
                'size'        => $video->size,
                'lesson_id'   => $video->lesson_id,
                'course'      => [
                    'id'    => $video->course_id,
                    'title' => optional($video->course)->title,
                ],
                'last_seen'   => $video->last_seen,
            ]);

        // Consumimos el flash (solo existe en la primera carga después de login)
        $welcome = session()->pull('welcome_message');

        return Inertia::render('Frontend/Dashboard/Index', [
            'courses' => $courses,
            'videos'  => $videos,
            'welcome' => $welcome, // <-- disponible en la vista
        ]);
    }
}
