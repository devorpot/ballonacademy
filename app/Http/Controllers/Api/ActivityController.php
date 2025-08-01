<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Activity;
use App\Models\Profile;
use App\Enums\ActivityType;
use App\Models\CourseActivity;
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
            'evaluation_id' => null,
            'type' => ActivityType::VIDEO_END->value,
            'description' => 'El usuario '.$profile->nickname.' ha finalizado el Video '.$video->title,
        ]);

         return response()->json(['message' => 'Actividad registrada correctamente']);

    }
}
