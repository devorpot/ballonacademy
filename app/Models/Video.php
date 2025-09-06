<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluation;
use App\Models\Teacher;

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
        'lesson_id',
        'video_path',
        'order',
        'duration',
        'size',

        'private',
        'published',
        'published_date'
    ];

    protected $casts = [
        'active'         => 'boolean',
        'private'        => 'boolean',
        'published'      => 'boolean',
        'published_date' => 'date',
        'order'          => 'integer',
        'course_id'      => 'integer',
        'lesson_id'      => 'integer',
        'teacher_id'     => 'integer',
    ];

     protected static function booted(): void
    {
        static::creating(function (Video $video) {
            if ($video->published_date === null) {
                $video->published_date = today();
            }
            // Si no te llega el flag desde el form, aplica defaults lógicos
            $video->active    = $video->active    ?? true;
            $video->private   = $video->private   ?? false;
            $video->published = $video->published ?? true;

            // Autonumerar order dentro del curso si viene vacío
            if ($video->order === null && $video->course_id) {
                $max = static::where('course_id', $video->course_id)->max('order');
                $video->order = is_null($max) ? 1 : ($max + 1);
            }
        });
    }


        public function course()
    {
        return $this->belongsTo(Course::class);
    }

  public function teacher()
{
    return $this->belongsTo(Teacher::class, 'teacher_id')
        ->withDefault(['id' => null, 'name' => '']);
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

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
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

public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('published', true);
    }

    public function scopePublic(Builder $q): Builder
    {
        return $q->where('private', false);
    }

    public function scopeForCourse(Builder $q, int $courseId): Builder
    {
        return $q->where('course_id', $courseId);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('order')->orderBy('id');
    }



}
