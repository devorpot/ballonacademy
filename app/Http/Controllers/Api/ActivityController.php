<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Evaluation;
use App\Models\Activity;
use App\Models\Profile;
use App\Enums\ActivityType;
use App\Models\CourseActivity;
use App\Models\LessonActivity;
use Illuminate\Support\Facades\Auth;


class ActivityController extends Controller
{
    public function courseEnded(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'activity' => 'required|string',
        ]);

         $profile = Profile::where('user_id', Auth::id())->first();

        $course = Course::find($request->course_id);

        CourseActivity::create([
            'course_id' => $request->course_id,
            'user_id' => Auth::id(),
            'activity' => $request->activity,
            'ended' => 1,
            'activity_date' => now()->toDateString(),
        ]);

         Activity::create([
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'evaluation_id' => null,
            'type' => ActivityType::COURSE_END->value,
            'description' => 'El usuario '.$profile->nickname.' ha finalizado el curso '.$course->title,
        ]);

        return response()->json(['message' => 'Actividad registrada correctamente']);
    }

public function lessonEnded(Request $request)
{
    $data = $request->validate([
        'course_id' => ['required', 'integer', 'exists:courses,id'],
        'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
    ]);

    // Validar que la lección pertenece al curso (Eloquent, sin DB::)
    $lesson = Lesson::where('id', $data['lesson_id'])
        ->where('course_id', $data['course_id'])
        ->first();

    if (! $lesson) {
        return response()->json([
            'message' => 'La lección no pertenece al curso indicado.'
        ], 422);
    }

    LessonActivity::create([
        'user_id'       => Auth::id(),
        'course_id'     => $data['course_id'],
        'lesson_id'     => $data['lesson_id'],
        'activity'      => 'ended',
        'ended'         => true,
        'activity_date' => now(),
    ]);

    return response()->json(['message' => 'Actividad de lección registrada correctamente']);
}

    public function videoEnded(Request $request){
             $request->validate([
                'course_id' => 'required|exists:courses,id',
                'video_id' => 'required|exists:videos,id'
            ]);



            $video = Video::find($request->video_id);
            $profile = Profile::where('user_id', Auth::id())->first();


            Activity::create([
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'video_id' => $request->video_id,
            'lesson_id' => $request->lesson_id,
            'evaluation_id' => null,
            'type' => ActivityType::VIDEO_END->value,
            'description' => 'El usuario '.$profile->nickname.' ha finalizado el Video '.$video->title,
        ]);

         return response()->json(['message' => 'Actividad registrada correctamente']);

    }
}
