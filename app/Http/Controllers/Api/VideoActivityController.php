<?php

// app/Http/Controllers/Api/VideoActivityController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VideoActivity; 
use App\Models\LessonVideo;

class VideoActivityController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'required|exists:lessons,id',
            'event' => 'required|in:play,pause,ended',
            'second' => 'nullable|numeric',
        ]);

        $user = $request->user();

        VideoActivity::create([
            'user_id' => $user->id,
            'video_id' => $request->video_id,
            'course_id' => $request->course_id,
            'lesson_id' => $request->lesson_id,
            'event' => $request->event,
            'second' => $request->second,
        ]);

        return response()->json(['status' => 'ok']);
    }
}
