<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluation;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'description_short',
        'teacher_id',
        'comments',
        'image_cover',
 
        'video_path',
        'order',
        'duration',
        'size'
    ];

        public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

        public function evaluations()
    {
        return $this->hasMany(Evaluation::class); // FK: evaluations.video_id
    }

    public function resources()
    {
        return $this->hasMany(VideoResource::class);
    }

    public function captions()
    {
        return $this->hasMany(VideoCaption::class);
    }


    // app/Models/Video.php

public function lessonVideos()
{
    return $this->hasMany(LessonVideo::class);
}

public function lessons()
{
    return $this->belongsToMany(Lesson::class, 'lesson_videos', 'video_id', 'lesson_id')
        ->withPivot(['course_id', 'order', 'active'])
        ->withTimestamps()
        ->orderBy('lesson_videos.order');
}


}
